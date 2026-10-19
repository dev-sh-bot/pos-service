<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // RefreshDatabase may recreate a database. Never allow it on business data.
        if ($app['config']->get('database.default') === 'mysql') {
            $connection = $app['config']->get('database.connections.mysql');
            if (($connection['database'] ?? null) !== 'pos_service_redesign_feature_test'
                || !in_array($connection['host'] ?? null, ['127.0.0.1', 'localhost'], true)
                || !empty($connection['url'])) {
                throw new \LogicException('MySQL tests require the local pos_service_redesign_feature_test database.');
            }
        }

        return $app;
    }
}
