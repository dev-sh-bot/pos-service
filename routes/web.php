<?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\PurchaseCartController;
use App\Http\Controllers\Inventory\PurchaseController;
use App\Http\Controllers\Management\BranchController;
use App\Http\Controllers\Management\CounterController;
use App\Http\Controllers\Management\CustomerController;
use App\Http\Controllers\Management\PermissionController;
use App\Http\Controllers\Management\RoleController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Management\SupplierController;
use App\Http\Controllers\Management\CategoryController;
use App\Http\Controllers\Management\ServiceController;
use App\Http\Controllers\Management\DealController;
use App\Http\Controllers\Pos\BranchAccessController;
use App\Http\Controllers\Pos\CartController;
use App\Http\Controllers\Pos\OrderController;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\Settings\SrbPayloadController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn(): Redirector|RedirectResponse => redirect('/admin'));

Route::get('/clear-cache', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('migrate');
    Artisan::call('db:seed');


    return "Cache cleared and storage link recreated.";
});

Auth::routes();

Route::prefix('admin')->middleware(['auth', 'locale'])->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // ── Branch & Counter management ────────────────────────────
    Route::resource('branches', BranchController::class);
    Route::post('branches/{branch}/assign-users', [BranchController::class, 'assignUsers'])->name('branches.assign-users');

    Route::resource('counters', CounterController::class);
    Route::post('counters/{counter}/assign-users', [CounterController::class, 'assignUsers'])->name('counters.assign-users');

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('deals', DealController::class);
    Route::post('deals/{deal}/toggle-status', [DealController::class, 'toggleStatus'])->name('deals.toggle-status');
    Route::resource('customers', CustomerController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('suppliers', SupplierController::class);

    // POS Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/categories', [CartController::class, 'categories'])->name('cart.categories');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
    Route::delete('/cart/delete', [CartController::class, 'delete']);
    Route::delete('/cart/empty', [CartController::class, 'empty']);

    // ── Branch / Counter POS access (password verification) ────
    Route::prefix('pos-access')->name('pos-access.')->group(function (): void {
        Route::get('/status',          [BranchAccessController::class, 'status'])->name('status');
        Route::get('/branches',        [BranchAccessController::class, 'branches'])->name('branches');
        Route::post('/verify-branch',  [BranchAccessController::class, 'verifyBranch'])->name('verify-branch');
        Route::get('/counters',        [BranchAccessController::class, 'counters'])->name('counters');
        Route::post('/verify-counter', [BranchAccessController::class, 'verifyCounter'])->name('verify-counter');
        Route::post('/clear',          [BranchAccessController::class, 'clearSession'])->name('clear');
    });

    Route::get('/purchases/data', [PurchaseController::class, 'data'])->name('purchases.data');
    Route::get('/purchases/{purchase}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.receipt');
    Route::resource('purchases', PurchaseController::class);

    // Purchase Cart API
    Route::prefix('purchase-cart')->name('purchase-cart.')->group(function (): void {
        Route::get('/', [PurchaseCartController::class, 'index'])->name('index');
        Route::post('/', [PurchaseCartController::class, 'store'])->name('store');
        Route::post('/change-qty', [PurchaseCartController::class, 'changeQty'])->name('change-qty');
        Route::post('/change-price', [PurchaseCartController::class, 'changePrice'])->name('change-price');
        Route::delete('/delete', [PurchaseCartController::class, 'delete'])->name('delete');
        Route::delete('/empty', [PurchaseCartController::class, 'empty'])->name('empty');
    });

    // Orders
    Route::post('/orders/partial-payment', [OrderController::class, 'partialPayment'])->name('orders.partial-payment');

    // Translations
    Route::get('/locale/{type}', function ($type) {
        $translations = trans($type);
        return response()->json($translations);
    });

    // Language Switch
    Route::get('/lang-switch/{lang}', function ($lang) {
        $supportedLocales = ['en', 'es'];

        if (in_array($lang, $supportedLocales)) {
            session(['locale' => $lang]);
            app()->setLocale($lang);
        }

        return redirect()->back();
    })->name('lang.switch');
});
