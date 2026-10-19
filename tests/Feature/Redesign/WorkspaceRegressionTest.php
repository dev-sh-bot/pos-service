<?php

declare(strict_types=1);

use App\Models\{Branch, Counter, Order, Permission, Product, Purchase, Role, Setting, User};
use Illuminate\Support\Facades\{DB, Http};
use Tests\Support\RedesignQaFixtures;

beforeEach(function () {
    $this->qa = RedesignQaFixtures::create();
    $this->actingAs($this->qa['user']);
    Http::preventStrayRequests();
});

dataset('workspace screens', [
    'dashboard' => ['home', null, 'home'],
    'settings' => ['settings.index', null, 'settings.edit'],
    'permissions' => ['permissions.index', null, 'permissions.index'],
    'users list' => ['users.index', null, 'users.index'],
    'users create' => ['users.create', null, 'users.create'],
    'users edit' => ['users.edit', 'user', 'users.edit'],
    'roles list' => ['roles.index', null, 'roles.index'],
    'roles create' => ['roles.create', null, 'roles.create'],
    'roles edit' => ['roles.edit', 'role', 'roles.edit'],
    'branches list' => ['branches.index', null, 'branches.index'],
    'branches create' => ['branches.create', null, 'branches.create'],
    'branches edit' => ['branches.edit', 'branch', 'branches.edit'],
    'counters list' => ['counters.index', null, 'counters.index'],
    'counters create' => ['counters.create', null, 'counters.create'],
    'counters edit' => ['counters.edit', 'counter', 'counters.edit'],
    'products list' => ['products.index', null, 'products.index'],
    'products create' => ['products.create', null, 'products.create'],
    'products edit' => ['products.edit', 'product', 'products.edit'],
    'categories list' => ['categories.index', null, 'categories.index'],
    'categories create' => ['categories.create', null, 'categories.create'],
    'categories edit' => ['categories.edit', 'category', 'categories.edit'],
    'services list' => ['services.index', null, 'services.index'],
    'services create' => ['services.create', null, 'services.create'],
    'services edit' => ['services.edit', 'service', 'services.edit'],
    'deals list' => ['deals.index', null, 'deals.index'],
    'deals create' => ['deals.create', null, 'deals.create'],
    'deals edit' => ['deals.edit', 'deal', 'deals.edit'],
    'customers list' => ['customers.index', null, 'customers.index'],
    'customers create' => ['customers.create', null, 'customers.create'],
    'customers edit' => ['customers.edit', 'customer', 'customers.edit'],
    'suppliers list' => ['suppliers.index', null, 'suppliers.index'],
    'suppliers create' => ['suppliers.create', null, 'suppliers.create'],
    'orders list' => ['orders.index', null, 'orders.index'],
    'sale terminal' => ['cart.index', null, 'cart.index'],
    'purchases list' => ['purchases.index', null, 'purchases.index'],
    'purchases create' => ['purchases.create', null, 'purchases.create'],
    'purchase detail' => ['purchases.show', 'purchase', 'purchases.show'],
    'purchase receipt' => ['purchases.receipt', 'purchase', 'purchases.receipt'],
]);

test('every authenticated screen renders its expected view', function (string $name, ?string $fixture, string $view) {
    $response = $this->get(route($name, $fixture ? $this->qa[$fixture] : []));
    $response->assertOk();
    if ($name === 'purchases.receipt') {
        $response->assertHeader('content-type', 'application/pdf');
        expect(substr($response->getContent(), 0, 5))->toBe('%PDF-');
        return;
    }
    $response->assertViewIs($view);
    expect($response->getContent())->not->toContain('Whoops, looks like something went wrong.');
    [$module, $screen] = array_pad(explode('.', $name, 2), 2, null);
    if (in_array($screen, ['create', 'edit'], true) && $module !== 'purchases') {
        $action = $screen === 'create'
            ? route($module . '.store')
            : route($module . '.update', $this->qa[$fixture]);
        $response->assertSee('action="' . $action . '"', false)->assertSee('name="_token"', false);
        $fields = [
            'users' => ['first_name', 'last_name', 'email'],
            'roles' => ['name', 'description'],
            'branches' => ['name', 'code', 'password'],
            'counters' => ['branch_id', 'name', 'code', 'password'],
            'products' => ['name', 'barcode', 'price', 'quantity', 'image'],
            'categories' => ['name', 'sort_order'],
            'services' => ['name', 'category_id', 'rate', 'barcode'],
            'deals' => ['name', 'services[]', 'original_amount', 'discounted_amount'],
            'customers' => ['first_name', 'last_name', 'email', 'phone'],
            'suppliers' => ['first_name', 'last_name', 'email', 'phone'],
        ];
        foreach ($fields[$module] ?? [] as $field) {
            $response->assertSee('name="' . $field . '"', false);
        }
    }
})->with('workspace screens');

test('the legacy order creation URL still redirects to the terminal', function () {
    $this->get(route('orders.create'))->assertRedirect(route('cart.index'));
});

dataset('protected workspace routes', [
    'home', 'settings.index', 'permissions.index', 'users.index', 'roles.index',
    'branches.index', 'counters.index', 'products.index', 'categories.index',
    'services.index', 'customers.index', 'suppliers.index', 'orders.index', 'purchases.index', 'cart.index',
]);

test('unprivileged accounts remain forbidden from protected modules', function (string $name) {
    $this->actingAs(User::factory()->create())->get(route($name))->assertForbidden();
})->with('protected workspace routes');

test('a read-only product permission does not grant create access', function () {
    $staff = User::factory()->create();
    $staff->permissions()->attach(Permission::where('name', 'products.view')->firstOrFail());
    $this->actingAs($staff)->get(route('products.index'))->assertOk();
    $this->get(route('products.create'))->assertForbidden();
    $this->post(route('products.store'), [])->assertForbidden();
});

test('guest access redirects before rendering protected pages', function () {
    auth()->logout();
    foreach (['home', 'cart.index', 'purchases.index', 'settings.index', 'users.index'] as $name) {
        $this->get(route($name))->assertRedirect(route('login'));
    }
});

test('login registration and password recovery screens retain form contracts', function () {
    auth()->logout();
    $this->get(route('login'))->assertOk()->assertSee('name="email"', false)->assertSee('name="password"', false)->assertSee(route('login'), false);
    $this->get(route('register'))->assertOk()->assertSee('name="first_name"', false)->assertSee('name="last_name"', false)->assertSee('name="password_confirmation"', false);
    $this->get(route('password.request'))->assertOk()->assertSee(route('password.email'), false);
    $this->get(route('password.reset', ['token' => 'qa-reset-token']))->assertOk()->assertSee('name="token"', false)->assertSee(route('password.update'), false);
});

test('authentication and failed validation still use the existing routes', function () {
    auth()->logout();
    $this->post(route('login'), ['email' => 'qa-redesign@example.test', 'password' => 'wrong-password'])->assertSessionHasErrors('email');
    $this->post(route('login'), ['email' => 'qa-redesign@example.test', 'password' => 'RedesignQA!2026'])->assertRedirect('/admin');
    $this->assertAuthenticatedAs($this->qa['user']);
    $this->get(route('password.confirm'))->assertOk()->assertSee('name="password"', false);
    $this->post(route('logout'))->assertRedirect('/');
    $this->assertGuest();
});

test('settings form preserves all checkout and catalog configuration fields', function () {
    $response = $this->get(route('settings.index'))->assertOk();
    foreach (['app_name', 'app_description', 'currency_symbol', 'business_logo', 'show_products', 'show_services', 'tax_enabled', 'discount_enabled', 'editable_item_rate', 'srb_enabled'] as $field) {
        $response->assertSee('name="' . $field . '"', false);
    }
    $this->post(route('settings.store'), ['show_products' => '0', 'show_services' => '1', 'tax_enabled' => '0', 'discount_enabled' => '0', 'editable_item_rate' => '0', 'srb_enabled' => '0'])
        ->assertRedirect(route('settings.index'));
    $this->assertDatabaseHas('settings', ['key' => 'show_products', 'value' => '0']);
    $this->assertDatabaseHas('settings', ['key' => 'show_services', 'value' => '1']);
});

test('terminal runtime configuration reflects disabled feature settings', function () {
    config(['settings.show_products' => false, 'settings.show_services' => true, 'settings.tax_enabled' => false, 'settings.discount_enabled' => false, 'settings.editable_item_rate' => false, 'settings.srb_enabled' => false]);
    $response = $this->get(route('cart.index'))->assertOk();
    foreach (['show_products', 'tax_enabled', 'discount_enabled', 'editable_item_rate', 'srb_enabled'] as $field) {
        $response->assertSee('"' . $field . '":false', false);
    }
    $response->assertSee('"show_services":true', false);
});

test('branch and counter passwords preserve their two-stage session flow', function () {
    $this->getJson(route('pos-access.status'))->assertOk()->assertJsonPath('branch_verified', false)->assertJsonPath('counter_verified', false);
    $this->postJson(route('pos-access.verify-counter'), ['counter_id' => $this->qa['counter']->id, 'password' => 'CounterQA!2026'])->assertForbidden();
    $this->postJson(route('pos-access.verify-branch'), ['branch_id' => $this->qa['branch']->id, 'password' => 'wrong'])->assertUnprocessable();
    $this->postJson(route('pos-access.verify-branch'), ['branch_id' => $this->qa['branch']->id, 'password' => 'BranchQA!2026'])->assertOk()->assertSessionHas('pos_branch_id', $this->qa['branch']->id);
    $this->getJson(route('pos-access.counters'))->assertOk()->assertJsonFragment(['id' => $this->qa['counter']->id]);
    $this->postJson(route('pos-access.verify-counter'), ['counter_id' => $this->qa['counter']->id, 'password' => 'CounterQA!2026'])->assertOk()->assertSessionHas('pos_counter_id', $this->qa['counter']->id);
    $this->getJson(route('pos-access.status'))->assertOk()->assertJsonPath('branch_verified', true)->assertJsonPath('counter_verified', true);
    $this->postJson(route('pos-access.clear'))->assertOk()->assertSessionMissing('pos_branch_id')->assertSessionMissing('pos_counter_id');
});

test('unassigned and inactive branches and counters stay inaccessible', function () {
    $staff = User::factory()->create();
    $this->actingAs($staff)->postJson(route('pos-access.verify-branch'), ['branch_id' => $this->qa['branch']->id, 'password' => 'BranchQA!2026'])->assertForbidden();
    $this->actingAs($this->qa['user']);
    $this->qa['branch']->update(['is_active' => false]);
    $this->postJson(route('pos-access.verify-branch'), ['branch_id' => $this->qa['branch']->id, 'password' => 'BranchQA!2026'])->assertForbidden();
    $this->qa['branch']->update(['is_active' => true]);
    $this->withSession(['pos_branch_id' => $this->qa['branch']->id]);
    $this->qa['counter']->update(['is_active' => false]);
    $this->postJson(route('pos-access.verify-counter'), ['counter_id' => $this->qa['counter']->id, 'password' => 'CounterQA!2026'])->assertForbidden();
});

test('product service and deal cart identities remain distinct', function () {
    foreach (['QA-PROD-01', 'QA-SVC-01', 'QA-DEAL-01'] as $barcode) {
        $this->postJson(route('cart.store'), ['barcode' => $barcode])->assertOk()->assertJsonPath('success', true);
    }
    $cart = $this->getJson(route('cart.index'))->assertOk()->assertJsonCount(3)->json();
    expect(array_column($cart, 'item_type'))->toEqualCanonicalizing(['product', 'service', 'deal']);
    $this->postJson('/admin/cart/change-qty', ['product_id' => 'service:' . $this->qa['service']->id, 'quantity' => 3])->assertOk();
    $this->assertDatabaseHas('user_cart', ['user_id' => $this->qa['user']->id, 'item_type' => 1, 'item_id' => $this->qa['service']->id, 'quantity' => 3]);
    $this->assertDatabaseHas('user_cart', ['user_id' => $this->qa['user']->id, 'item_type' => 0, 'item_id' => $this->qa['product']->id, 'quantity' => 1]);
    $this->deleteJson('/admin/cart/delete', ['product_id' => 'deal:' . $this->qa['deal']->id])->assertOk();
    $this->getJson(route('cart.index'))->assertJsonCount(2);
    $this->postJson('/admin/cart/change-qty', ['product_id' => 'product:' . $this->qa['product']->id, 'quantity' => 999])->assertStatus(400);
    $this->deleteJson('/admin/cart/empty')->assertOk();
    $this->getJson(route('cart.index'))->assertExactJson([]);
});

test('mixed checkout preserves custom rates totals payment stock and session attribution', function () {
    foreach (['QA-PROD-01', 'QA-SVC-01', 'QA-DEAL-01'] as $barcode) {
        $this->postJson(route('cart.store'), ['barcode' => $barcode])->assertOk();
    }
    $response = $this->withSession(['pos_branch_id' => $this->qa['branch']->id, 'pos_counter_id' => $this->qa['counter']->id])
        ->postJson(route('orders.store'), [
            'customer_id' => $this->qa['customer']->id, 'amount' => 3500, 'subtotal' => 3100,
            'tax_percent' => 8, 'tax_amount' => 248, 'discount_percent' => 5, 'discount_amount' => 155,
            'item_rates' => ['product:' . $this->qa['product']->id => 400, 'service:' . $this->qa['service']->id => 1500, 'deal:' . $this->qa['deal']->id => 1200],
        ])->assertCreated()->assertJsonPath('success', true);
    $order = Order::findOrFail($response->json('order_id'));
    expect($order->total_amount)->toBe(3193.0)
        ->and($order->items()->count())->toBe(3)
        ->and($order->payments()->sum('amount'))->toEqual(3500)
        ->and($order->branch_id)->toBe($this->qa['branch']->id)
        ->and($order->counter_id)->toBe($this->qa['counter']->id)
        ->and($this->qa['product']->fresh()->quantity)->toBe(5)
        ->and(DB::table('user_cart')->where('user_id', $this->qa['user']->id)->count())->toBe(0);
    $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'item_type' => 0, 'item_id' => $this->qa['product']->id, 'price' => 400]);
    $this->get(route('orders.index'))->assertOk()->assertSee($order->invoice_no)->assertSee('data-invoice-no=', false);
    Http::assertNothingSent();
});

test('partial payment preserves balance checks and payment records', function () {
    $this->post(route('orders.partial-payment'), ['order_id' => $this->qa['order']->id, 'amount' => 200])->assertRedirect(route('orders.index'))->assertSessionHas('success');
    expect($this->qa['order']->fresh()->receivedAmount())->toBe(1700.0);
    $this->post(route('orders.partial-payment'), ['order_id' => $this->qa['order']->id, 'amount' => 1000])->assertRedirect(route('orders.index'))->assertSessionHasErrors();
    expect($this->qa['order']->payments()->count())->toBe(2);
});

test('SRB failure rolls back checkout and preserves the operator cart', function () {
    config(['settings.srb_enabled' => true, 'settings.srb_business_name' => 'QA Studio', 'settings.srb_ntn' => '1234567']);
    $this->qa['branch']->update(['srb_pos_id' => '1234', 'srb_pos_user' => 'qa-pos', 'srb_pos_password' => 'qa-only']);
    Http::fake(['pos.srb.gos.pk/*' => Http::response(['resCode' => '99', 'message' => 'QA rejection'], 400)]);
    $this->postJson(route('cart.store'), ['barcode' => 'QA-PROD-01'])->assertOk();
    $before = Order::count();
    $this->withSession(['pos_branch_id' => $this->qa['branch']->id, 'pos_counter_id' => $this->qa['counter']->id])
        ->postJson(route('orders.store'), ['subtotal' => 500, 'tax_percent' => 0, 'amount' => 500])->assertStatus(400)->assertJsonPath('success', false);
    expect(Order::count())->toBe($before)->and($this->qa['product']->fresh()->quantity)->toBe(6);
    $this->getJson(route('cart.index'))->assertJsonCount(1);
    Http::assertSentCount(1);
});

test('successful SRB checkout retains fiscal fields without a real network request', function () {
    config(['settings.srb_enabled' => true, 'settings.srb_business_name' => 'QA Studio', 'settings.srb_ntn' => '1234567']);
    $this->qa['branch']->update(['srb_pos_id' => '1234', 'srb_pos_user' => 'qa-pos', 'srb_pos_password' => 'qa-only']);
    Http::fake(['pos.srb.gos.pk/*' => Http::response(['resCode' => '00', 'srbInvoiceId' => 'QA-SRB-1001', 'QRCodeLink' => 'https://example.test/qa-invoice'], 200)]);
    $this->postJson(route('cart.store'), ['barcode' => 'QA-SVC-01'])->assertOk();
    $response = $this->withSession(['pos_branch_id' => $this->qa['branch']->id, 'pos_counter_id' => $this->qa['counter']->id])
        ->postJson(route('orders.store'), ['subtotal' => 1500, 'tax_percent' => 8, 'tax_amount' => 120, 'amount' => 1620])
        ->assertCreated()->assertJsonPath('order.srb_invoice_id', 'QA-SRB-1001')->assertJsonPath('order.srb_qr_code_link', 'https://example.test/qa-invoice');
    $this->assertDatabaseHas('orders', ['id' => $response->json('order_id'), 'srb_status' => 'submitted']);
    Http::assertSent(fn ($request) => (float) $request['saleValue'] === 1500.0 && (float) $request['taxAmount'] === 120.0 && (float) $request['netAmount'] === 1620.0);
    $this->get(route('orders.index'))->assertOk()->assertSee('QA-SRB-1001');
});

test('purchase cart and completed receipt preserve inventory behavior', function () {
    $this->postJson(route('purchase-cart.store'), ['barcode' => 'QA-PROD-01'])->assertOk();
    $this->postJson(route('purchase-cart.change-qty'), ['product_id' => $this->qa['product']->id, 'quantity' => 3])->assertOk();
    $this->postJson(route('purchase-cart.change-price'), ['product_id' => $this->qa['product']->id, 'purchase_price' => 350])->assertOk();
    $this->getJson(route('purchase-cart.index'))->assertOk()->assertJsonCount(1);
    $this->post(route('purchases.store'), [
        'supplier_id' => $this->qa['supplier']->id, 'purchase_date' => now()->toDateString(), 'status' => 'completed',
        'total_amount' => 1050, 'notes' => 'QA regression purchase', 'items' => [['product_id' => $this->qa['product']->id, 'quantity' => 3, 'purchase_price' => 350]],
    ])->assertRedirect(route('purchases.index'))->assertSessionHas('success');
    expect($this->qa['product']->fresh()->quantity)->toBe(9)->and((float) $this->qa['product']->fresh()->purchase_price)->toBe(350.0);
    $purchase = Purchase::where('notes', 'QA regression purchase')->firstOrFail();
    $receipt = $this->get(route('purchases.receipt', $purchase))->assertOk()->assertHeader('content-type', 'application/pdf');
    expect(substr($receipt->getContent(), 0, 5))->toBe('%PDF-');
    expect(view('purchases.receipt', ['purchase' => $purchase->load('items.product', 'supplier', 'user')])->render())->toContain('Studio Care Kit', 'QA-PROD-01', '1,050.00');
});

test('catalog JSON filters and locale endpoints remain available', function () {
    $this->getJson(route('products.index', ['search' => 'Studio']))->assertOk();
    $this->getJson(route('services.index', ['all' => 1, 'category_id' => $this->qa['category']->id]))->assertOk()->assertJsonCount(1, 'data');
    $this->getJson(route('deals.index', ['all' => 1, 'status' => 1]))->assertOk()->assertJsonCount(1, 'data');
    $this->getJson(route('cart.categories'))->assertOk()->assertJsonFragment(['slug' => 'qa-essentials']);
    $this->getJson(route('purchases.data', ['status' => 'completed']))->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/admin/locale/cart')->assertOk()->assertJsonStructure(['title', 'checkout', 'quantity']);
    $this->from(route('home'))->get(route('lang.switch', 'es'))->assertRedirect(route('home'))->assertSessionHas('locale', 'es');
});
