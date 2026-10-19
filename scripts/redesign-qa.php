<?php

declare(strict_types=1);

// This utility is deliberately confined to one disposable local database.
require __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

const QA_DATABASE = 'pos_service_redesign_test';
const FEATURE_DATABASE = 'pos_service_redesign_feature_test';
$action = $argv[1] ?? 'status';
if (!in_array($action, ['status', 'prepare', 'prepare-features', 'seed', 'serve'], true)) {
    fwrite(STDERR, "Usage: php scripts/redesign-qa.php [status|prepare|prepare-features|seed|serve]\n");
    exit(1);
}
$targetDatabase = $action === 'prepare-features' ? FEATURE_DATABASE : QA_DATABASE;

$environment = [
    'APP_ENV' => $action === 'serve' ? 'local' : 'testing',
    'APP_URL' => 'http://127.0.0.1:8011',
    'APP_DEBUG' => 'false',
    'DEBUGBAR_ENABLED' => 'false',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => $targetDatabase,
    'DATABASE_URL' => '',
    'DB_URL' => '',
    'CACHE_STORE' => 'array',
    'CACHE_DRIVER' => 'array',
    'SESSION_DRIVER' => $action === 'serve' ? 'file' : 'array',
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
];
foreach ($environment as $name => $value) {
    putenv($name . '=' . $value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}

// Export credentials privately for the child development server, never to output.
foreach (['DB_USERNAME', 'DB_PASSWORD', 'APP_KEY'] as $name) {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
    if ($value !== false && $value !== null) {
        putenv($name . '=' . $value);
    }
}

if (getenv('DB_DATABASE') !== $targetDatabase || getenv('DB_HOST') !== '127.0.0.1') {
    throw new RuntimeException('Disposable QA database guard failed.');
}

if (in_array($action, ['prepare', 'prepare-features'], true)) {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;charset=utf8mb4',
        $_ENV['DB_USERNAME'] ?? 'root',
        $_ENV['DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $targetDatabase . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection = $app['config']->get('database.connections.mysql');
if ($app['config']->get('database.default') !== 'mysql'
    || ($connection['database'] ?? null) !== $targetDatabase
    || ($connection['host'] ?? null) !== '127.0.0.1'
    || !empty($connection['url'])) {
    throw new RuntimeException('Resolved QA connection guard failed; refusing database work.');
}

if ($action === 'serve') {
    $command = escapeshellarg(PHP_BINARY) . ' -S 127.0.0.1:8011 -t '
        . escapeshellarg(dirname(__DIR__) . '/public') . ' '
        . escapeshellarg(dirname(__DIR__) . '/server.php');
    passthru($command, $exitCode);
    exit($exitCode);
}

if (in_array($action, ['prepare', 'prepare-features'], true)) {
    Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
    echo Illuminate\Support\Facades\Artisan::output();
}
if (in_array($action, ['prepare', 'seed'], true)) {
    Tests\Support\RedesignQaFixtures::create();
    echo 'QA fixtures ready in ' . QA_DATABASE . ".\n";
}

echo 'Database: ' . Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "\n";
echo 'Driver: ' . Illuminate\Support\Facades\DB::connection()->getDriverName() . "\n";
