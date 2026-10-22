<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Counter;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\SaleReturn;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    /**
     * Handle global live search across the entire application with strict permission checks.
     */
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['total' => 0, 'groups' => []], 401);
        }

        $query = trim((string) $request->input('q', ''));
        $isAdmin = (bool) $user->isSuperAdmin();

        $currency = config('settings.currency_symbol', '$');

        // If query is empty, return default quick navigation shortcuts
        if ($query === '') {
            $quickNav = $this->getQuickNavigation($user);
            return response()->json([
                'query' => '',
                'total' => count($quickNav),
                'groups' => [
                    [
                        'key' => 'navigation',
                        'label' => 'Quick Navigation',
                        'icon' => 'fas fa-compass',
                        'color' => '#2a69b0',
                        'bg' => '#eff6ff',
                        'items' => $quickNav,
                    ],
                ],
            ]);
        }

        $groups = [];
        $totalCount = 0;

        // 1. Navigation / Menu Items Search
        $navMatches = $this->searchNavigation($user, $query);
        if (!empty($navMatches)) {
            $groups[] = [
                'key' => 'navigation',
                'label' => 'Pages & Menus',
                'icon' => 'fas fa-compass',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
                'items' => $navMatches,
            ];
            $totalCount += count($navMatches);
        }

        // 2. Orders & Invoices (Permissions: orders.view)
        if ($user->can('orders.view') && Schema::hasTable('orders')) {
            $orders = $this->searchOrders($user, $isAdmin, $query, $currency);
            if ($orders->isNotEmpty()) {
                $groups[] = [
                    'key' => 'orders',
                    'label' => 'Sales & Invoices',
                    'icon' => 'fas fa-receipt',
                    'color' => '#059669',
                    'bg' => '#ecfdf5',
                    'items' => $orders->toArray(),
                ];
                $totalCount += $orders->count();
            }
        }

        // 3. Services (Permissions: services.view)
        if ($user->can('services.view') && Schema::hasTable('services')) {
            $services = $this->searchServices($user, $query, $currency);
            if ($services->isNotEmpty()) {
                $groups[] = [
                    'key' => 'services',
                    'label' => 'Services',
                    'icon' => 'fas fa-spa',
                    'color' => '#7c3aed',
                    'bg' => '#f5f3ff',
                    'items' => $services->toArray(),
                ];
                $totalCount += $services->count();
            }
        }

        // 4. Products (Permissions: products.view)
        if ($user->can('products.view') && Schema::hasTable('products')) {
            $products = $this->searchProducts($user, $query, $currency);
            if ($products->isNotEmpty()) {
                $groups[] = [
                    'key' => 'products',
                    'label' => 'Products',
                    'icon' => 'fas fa-box-open',
                    'color' => '#2563eb',
                    'bg' => '#eff6ff',
                    'items' => $products->toArray(),
                ];
                $totalCount += $products->count();
            }
        }

        // 5. Deals & Packages (Permissions: deals.view)
        if ($user->can('deals.view') && Schema::hasTable('deals')) {
            $deals = $this->searchDeals($user, $query, $currency);
            if ($deals->isNotEmpty()) {
                $groups[] = [
                    'key' => 'deals',
                    'label' => 'Deals & Packages',
                    'icon' => 'fas fa-tags',
                    'color' => '#d97706',
                    'bg' => '#fffbeb',
                    'items' => $deals->toArray(),
                ];
                $totalCount += $deals->count();
            }
        }

        // 6. Customers (Permissions: customers.view)
        if ($user->can('customers.view') && Schema::hasTable('customers')) {
            $customers = $this->searchCustomers($user, $query);
            if ($customers->isNotEmpty()) {
                $groups[] = [
                    'key' => 'customers',
                    'label' => 'Customers',
                    'icon' => 'fas fa-users',
                    'color' => '#0d9488',
                    'bg' => '#f0fdfa',
                    'items' => $customers->toArray(),
                ];
                $totalCount += $customers->count();
            }
        }

        // 7. Categories (Permissions: categories.view)
        if ($user->can('categories.view') && Schema::hasTable('categories')) {
            $categories = $this->searchCategories($user, $query);
            if ($categories->isNotEmpty()) {
                $groups[] = [
                    'key' => 'categories',
                    'label' => 'Categories',
                    'icon' => 'fas fa-layer-group',
                    'color' => '#475569',
                    'bg' => '#f1f5f9',
                    'items' => $categories->toArray(),
                ];
                $totalCount += $categories->count();
            }
        }

        // 8. Sale Returns (Permissions: sale_returns.view or sale_returns.list)
        if (($user->can('sale_returns.view') || $user->can('sale_returns.list')) && Schema::hasTable('sale_returns')) {
            $returns = $this->searchSaleReturns($user, $isAdmin, $query, $currency);
            if ($returns->isNotEmpty()) {
                $groups[] = [
                    'key' => 'sale_returns',
                    'label' => 'Sale Returns',
                    'icon' => 'fas fa-undo-alt',
                    'color' => '#e11d48',
                    'bg' => '#fff1f2',
                    'items' => $returns->toArray(),
                ];
                $totalCount += $returns->count();
            }
        }

        // 9. Branches & Counters (Permissions: branches.view / counters.view)
        if (($user->can('branches.view') || $user->can('counters.view')) && Schema::hasTable('branches')) {
            $locations = $this->searchLocations($user, $query);
            if (!empty($locations)) {
                $groups[] = [
                    'key' => 'locations',
                    'label' => 'Branches & Counters',
                    'icon' => 'fas fa-store-alt',
                    'color' => '#0891b2',
                    'bg' => '#ecfeff',
                    'items' => $locations,
                ];
                $totalCount += count($locations);
            }
        }

        // 10. Staff / Users (Permissions: users.view)
        if ($user->can('users.view') && Schema::hasTable('users')) {
            $users = $this->searchUsers($user, $query);
            if ($users->isNotEmpty()) {
                $groups[] = [
                    'key' => 'users',
                    'label' => 'Users & Staff',
                    'icon' => 'fas fa-user-cog',
                    'color' => '#6366f1',
                    'bg' => '#eef2ff',
                    'items' => $users->toArray(),
                ];
                $totalCount += $users->count();
            }
        }

        // 11. Roles (Permissions: roles.view)
        if ($user->can('roles.view') && Schema::hasTable('roles')) {
            $roles = $this->searchRoles($user, $query);
            if ($roles->isNotEmpty()) {
                $groups[] = [
                    'key' => 'roles',
                    'label' => 'Roles & Access',
                    'icon' => 'fas fa-user-shield',
                    'color' => '#4338ca',
                    'bg' => '#e0e7ff',
                    'items' => $roles->toArray(),
                ];
                $totalCount += $roles->count();
            }
        }

        // 12. Suppliers (Permissions: suppliers.view)
        if ($user->can('suppliers.view') && Schema::hasTable('suppliers')) {
            $suppliers = $this->searchSuppliers($user, $query);
            if ($suppliers->isNotEmpty()) {
                $groups[] = [
                    'key' => 'suppliers',
                    'label' => 'Suppliers',
                    'icon' => 'fas fa-truck',
                    'color' => '#4b5563',
                    'bg' => '#f3f4f6',
                    'items' => $suppliers->toArray(),
                ];
                $totalCount += $suppliers->count();
            }
        }

        // 13. Purchases (Permissions: purchases.view)
        if ($user->can('purchases.view') && Schema::hasTable('purchases')) {
            $purchases = $this->searchPurchases($user, $query, $currency);
            if ($purchases->isNotEmpty()) {
                $groups[] = [
                    'key' => 'purchases',
                    'label' => 'Purchases',
                    'icon' => 'fas fa-truck-loading',
                    'color' => '#15803d',
                    'bg' => '#f0fdf4',
                    'items' => $purchases->toArray(),
                ];
                $totalCount += $purchases->count();
            }
        }

        return response()->json([
            'query' => $query,
            'total' => $totalCount,
            'groups' => $groups,
        ]);
    }

    /**
     * Quick navigation shortcuts when search input is empty.
     */
    protected function getQuickNavigation($user): array
    {
        $items = [];

        if ($user->can('dashboard.view')) {
            $items[] = [
                'title' => 'Dashboard',
                'subtitle' => 'Main performance metrics & overview',
                'url' => route('home'),
                'icon' => 'fas fa-tachometer-alt',
                'badge' => 'Dashboard',
                'type' => 'navigation',
            ];
        }

        if ($user->can('orders.create') && Route::has('cart.index')) {
            $items[] = [
                'title' => 'POS Terminal',
                'subtitle' => 'Open Point of Sale register & billing',
                'url' => route('cart.index'),
                'icon' => 'fas fa-cash-register',
                'badge' => 'POS Terminal',
                'type' => 'navigation',
            ];
        }

        if ($user->can('orders.view') && Route::has('orders.index')) {
            $items[] = [
                'title' => 'Sales & Invoices',
                'subtitle' => 'Browse and filter completed sales orders',
                'url' => route('orders.index'),
                'icon' => 'fas fa-receipt',
                'badge' => 'Sales',
                'type' => 'navigation',
            ];
        }

        if ($user->can('services.view') && Route::has('services.index')) {
            $items[] = [
                'title' => 'Services Management',
                'subtitle' => 'Manage salon services, pricing & categories',
                'url' => route('services.index'),
                'icon' => 'fas fa-spa',
                'badge' => 'Services',
                'type' => 'navigation',
            ];
        }

        if ($user->can('customers.view') && Route::has('customers.index')) {
            $items[] = [
                'title' => 'Customers Directory',
                'subtitle' => 'Client records, contacts & history',
                'url' => route('customers.index'),
                'icon' => 'fas fa-users',
                'badge' => 'Clients',
                'type' => 'navigation',
            ];
        }

        if ($user->can('settings.view') && Route::has('settings.index')) {
            $items[] = [
                'title' => 'System Settings',
                'subtitle' => 'Store details, currency, SRB & tax configurations',
                'url' => route('settings.index'),
                'icon' => 'fas fa-cog',
                'badge' => 'Settings',
                'type' => 'navigation',
            ];
        }

        return $items;
    }

    /**
     * Search available navigation pages based on permissions.
     */
    protected function searchNavigation($user, string $query): array
    {
        $allPages = [
            [
                'title' => 'Dashboard',
                'subtitle' => 'Dashboard overview & revenue analytics',
                'keywords' => 'dashboard home analytics sales stats metrics',
                'url' => route('home'),
                'icon' => 'fas fa-tachometer-alt',
                'badge' => 'Page',
                'permission' => 'dashboard.view',
            ],
            [
                'title' => 'POS Terminal (Cash Register)',
                'subtitle' => 'New sale billing & checkout counter',
                'keywords' => 'pos point of sale cart checkout bill register counter cash sell',
                'url' => route('cart.index'),
                'icon' => 'fas fa-cash-register',
                'badge' => 'POS',
                'permission' => 'orders.create',
            ],
            [
                'title' => 'Sales & Invoices History',
                'subtitle' => 'List of all customer invoices and payments',
                'keywords' => 'sales orders invoices payments billing transactions receipt',
                'url' => route('orders.index'),
                'icon' => 'fas fa-receipt',
                'badge' => 'Sales',
                'permission' => 'orders.view',
            ],
            [
                'title' => 'Sale Return Register',
                'subtitle' => 'Process customer return & refund vouchers',
                'keywords' => 'sale return refunds reverse invoice replace item voucher',
                'url' => route('sale-returns.index'),
                'icon' => 'fas fa-undo-alt',
                'badge' => 'Returns',
                'permission' => 'sale_returns.view',
            ],
            [
                'title' => 'Return List & Records',
                'subtitle' => 'View all processed sale return histories',
                'keywords' => 'return list refund logs vouchers records history',
                'url' => route('sale-returns.list'),
                'icon' => 'fas fa-clipboard-list',
                'badge' => 'Returns',
                'permission' => 'sale_returns.list',
            ],
            [
                'title' => 'Customers Directory',
                'subtitle' => 'Customer profiles, contacts and statements',
                'keywords' => 'customers clients directory address phone contact people',
                'url' => route('customers.index'),
                'icon' => 'fas fa-users',
                'badge' => 'Customers',
                'permission' => 'customers.view',
            ],
            [
                'title' => 'Add New Customer',
                'subtitle' => 'Register a new customer profile',
                'keywords' => 'create add new customer client register',
                'url' => route('customers.create'),
                'icon' => 'fas fa-user-plus',
                'badge' => 'New Customer',
                'permission' => 'customers.create',
            ],
            [
                'title' => 'Services Catalog',
                'subtitle' => 'Browse and configure all salon services',
                'keywords' => 'services catalog treatments makeup hair styling spa rates',
                'url' => route('services.index'),
                'icon' => 'fas fa-spa',
                'badge' => 'Services',
                'permission' => 'services.view',
            ],
            [
                'title' => 'Add New Service',
                'subtitle' => 'Create a new salon service with custom rate & barcode',
                'keywords' => 'add create service new treatment spa bridal',
                'url' => route('services.create'),
                'icon' => 'fas fa-plus-circle',
                'badge' => 'New Service',
                'permission' => 'services.create',
            ],
            [
                'title' => 'Deals & Packages',
                'subtitle' => 'Package deals, bundles and promotional discounts',
                'keywords' => 'deals packages bundles promotions discounts offers combo',
                'url' => route('deals.index'),
                'icon' => 'fas fa-tags',
                'badge' => 'Deals',
                'permission' => 'deals.view',
            ],
            [
                'title' => 'Add New Deal',
                'subtitle' => 'Create promotional deal package with discount',
                'keywords' => 'create deal add package discount combo bundle',
                'url' => route('deals.create'),
                'icon' => 'fas fa-tag',
                'badge' => 'New Deal',
                'permission' => 'deals.create',
            ],
            [
                'title' => 'Categories',
                'subtitle' => 'Service and product group categories',
                'keywords' => 'categories departments groups sorting sections',
                'url' => route('categories.index'),
                'icon' => 'fas fa-layer-group',
                'badge' => 'Categories',
                'permission' => 'categories.view',
            ],
            [
                'title' => 'Products Inventory',
                'subtitle' => 'Retail products inventory & stock levels',
                'keywords' => 'products inventory stock items retail goods barcode price',
                'url' => route('products.index'),
                'icon' => 'fas fa-box-open',
                'badge' => 'Products',
                'permission' => 'products.view',
            ],
            [
                'title' => 'Branches Management',
                'subtitle' => 'Manage salon branches, codes and SRB credentials',
                'keywords' => 'branches locations stores outlets shops',
                'url' => route('branches.index'),
                'icon' => 'fas fa-code-branch',
                'badge' => 'Branches',
                'permission' => 'branches.view',
            ],
            [
                'title' => 'Billing Counters',
                'subtitle' => 'Configure checkout counters and terminals',
                'keywords' => 'counters terminals tills cash desks desks',
                'url' => route('counters.index'),
                'icon' => 'fas fa-desktop',
                'badge' => 'Counters',
                'permission' => 'counters.view',
            ],
            [
                'title' => 'User & Staff Management',
                'subtitle' => 'User accounts, role assignments and branch links',
                'keywords' => 'users staff employees accounts cashiers logins security',
                'url' => route('users.index'),
                'icon' => 'fas fa-user-cog',
                'badge' => 'Users',
                'permission' => 'users.view',
            ],
            [
                'title' => 'Add New User',
                'subtitle' => 'Create a new staff or cashier user account',
                'keywords' => 'create user new staff register employee',
                'url' => route('users.create'),
                'icon' => 'fas fa-user-plus',
                'badge' => 'New User',
                'permission' => 'users.create',
            ],
            [
                'title' => 'Roles & Permissions',
                'subtitle' => 'Access control roles and system permissions',
                'keywords' => 'roles permissions access authorization security capabilities',
                'url' => route('roles.index'),
                'icon' => 'fas fa-user-shield',
                'badge' => 'Security',
                'permission' => 'roles.view',
            ],
            [
                'title' => 'System Settings',
                'subtitle' => 'Store logo, currency, tax, SRB POS, and general setup',
                'keywords' => 'settings configuration setup logo currency tax srb options',
                'url' => route('settings.index'),
                'icon' => 'fas fa-cog',
                'badge' => 'Settings',
                'permission' => 'settings.view',
            ],
        ];

        $lowerQuery = mb_strtolower($query);
        $matches = [];

        foreach ($allPages as $page) {
            // Check permission
            if (!$user->can($page['permission'])) {
                continue;
            }

            // Match title, subtitle, or keywords
            $searchableText = mb_strtolower($page['title'] . ' ' . $page['subtitle'] . ' ' . $page['keywords']);
            if (Str::contains($searchableText, $lowerQuery)) {
                $matches[] = [
                    'id' => 'nav-' . Str::slug($page['title']),
                    'title' => $page['title'],
                    'subtitle' => $page['subtitle'],
                    'url' => $page['url'],
                    'icon' => $page['icon'],
                    'badge' => $page['badge'],
                    'type' => 'navigation',
                ];
            }
        }

        return array_slice($matches, 0, 5);
    }

    /**
     * Search Orders & Invoices with branch permission scoping.
     */
    protected function searchOrders($user, bool $isAdmin, string $query, string $currency)
    {
        $allowedBranchIds = $isAdmin ? null : $user->branches->pluck('id')->all();

        return Order::query()
            ->with(['customer', 'branch'])
            ->when(!$isAdmin && !empty($allowedBranchIds), function ($q) use ($allowedBranchIds) {
                $q->whereIn('branch_id', $allowedBranchIds);
            })
            ->where(function ($q) use ($query) {
                $q->where('invoice_no', 'like', "%{$query}%")
                    ->orWhere('id', 'like', "%{$query}%")
                    ->orWhere('srb_invoice_id', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($cq) use ($query) {
                        $cq->where('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->latest()
            ->take(6)
            ->get()
            ->map(function (Order $order) use ($currency) {
                $customerName = $order->customer ? trim($order->customer->first_name . ' ' . $order->customer->last_name) : 'Walk-in Customer';
                $branchName = $order->branch ? $order->branch->name : null;
                $date = $order->created_at ? $order->created_at->format('M d, Y h:i A') : '';

                $subInfo = $customerName;
                if ($branchName) {
                    $subInfo .= ' • ' . $branchName;
                }
                if ($date) {
                    $subInfo .= ' • ' . $date;
                }

                return [
                    'id' => $order->id,
                    'title' => 'Invoice #' . ($order->invoice_no ?: 'INV-' . $order->id),
                    'subtitle' => $subInfo,
                    'url' => route('orders.index', ['search' => $order->invoice_no ?: $order->id]),
                    'icon' => 'fas fa-receipt',
                    'badge' => $currency . ' ' . number_format($order->total_amount, 2),
                    'meta' => $order->srb_invoice_id ? 'SRB: ' . $order->srb_invoice_id : null,
                    'type' => 'orders',
                ];
            });
    }

    /**
     * Search Services.
     */
    protected function searchServices($user, string $query, string $currency)
    {
        $canEdit = $user->can('services.edit');

        return Service::query()
            ->with('category')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->take(6)
            ->get()
            ->map(function (Service $service) use ($canEdit, $currency) {
                $categoryName = $service->category ? $service->category->name : 'Uncategorized';
                $subtitle = 'Category: ' . $categoryName;
                if ($service->barcode) {
                    $subtitle .= ' • Code: ' . $service->barcode;
                }

                return [
                    'id' => $service->id,
                    'title' => $service->name,
                    'subtitle' => $subtitle,
                    'url' => $canEdit ? route('services.edit', $service->id) : route('services.index', ['search' => $service->name]),
                    'icon' => 'fas fa-spa',
                    'badge' => $currency . ' ' . number_format($service->rate, 2),
                    'type' => 'services',
                ];
            });
    }

    /**
     * Search Retail Products.
     */
    protected function searchProducts($user, string $query, string $currency)
    {
        $canEdit = $user->can('products.edit');

        return Product::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->take(6)
            ->get()
            ->map(function (Product $product) use ($canEdit, $currency) {
                $subtitle = 'Stock: ' . $product->quantity;
                if ($product->barcode) {
                    $subtitle .= ' • Barcode: ' . $product->barcode;
                }

                return [
                    'id' => $product->id,
                    'title' => $product->name,
                    'subtitle' => $subtitle,
                    'url' => $canEdit ? route('products.edit', $product->id) : route('products.index', ['search' => $product->name]),
                    'icon' => 'fas fa-box-open',
                    'badge' => $currency . ' ' . number_format($product->price, 2),
                    'type' => 'products',
                ];
            });
    }

    /**
     * Search Deals.
     */
    protected function searchDeals($user, string $query, string $currency)
    {
        $canEdit = $user->can('deals.edit');

        return Deal::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->take(6)
            ->get()
            ->map(function (Deal $deal) use ($canEdit, $currency) {
                $subtitle = 'Discount: ' . number_format($deal->discount_percentage, 0) . '% OFF';
                if ($deal->barcode) {
                    $subtitle .= ' • Barcode: ' . $deal->barcode;
                }

                return [
                    'id' => $deal->id,
                    'title' => $deal->name,
                    'subtitle' => $subtitle,
                    'url' => $canEdit ? route('deals.edit', $deal->id) : route('deals.index', ['search' => $deal->name]),
                    'icon' => 'fas fa-tags',
                    'badge' => $currency . ' ' . number_format($deal->discounted_amount, 2),
                    'type' => 'deals',
                ];
            });
    }

    /**
     * Search Customers.
     */
    protected function searchCustomers($user, string $query)
    {
        $canEdit = $user->can('customers.edit');

        return Customer::query()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(6)
            ->get()
            ->map(function (Customer $customer) use ($canEdit) {
                $fullName = trim($customer->first_name . ' ' . $customer->last_name) ?: 'Unnamed Customer';
                $contacts = array_filter([$customer->phone, $customer->email]);
                $subtitle = !empty($contacts) ? implode(' • ', $contacts) : ($customer->address ?: 'No contact details');

                return [
                    'id' => $customer->id,
                    'title' => $fullName,
                    'subtitle' => $subtitle,
                    'url' => $canEdit ? route('customers.edit', $customer->id) : route('customers.index'),
                    'icon' => 'fas fa-user',
                    'badge' => 'Customer',
                    'type' => 'customers',
                ];
            });
    }

    /**
     * Search Categories.
     */
    protected function searchCategories($user, string $query)
    {
        $canEdit = $user->can('categories.edit');

        return Category::query()
            ->where('name', 'like', "%{$query}%")
            ->take(6)
            ->get()
            ->map(function (Category $category) use ($canEdit) {
                return [
                    'id' => $category->id,
                    'title' => $category->name,
                    'subtitle' => 'Service & item category',
                    'url' => $canEdit ? route('categories.edit', $category->id) : route('categories.index'),
                    'icon' => 'fas fa-layer-group',
                    'badge' => 'Category',
                    'type' => 'categories',
                ];
            });
    }

    /**
     * Search Sale Returns.
     */
    protected function searchSaleReturns($user, bool $isAdmin, string $query, string $currency)
    {
        $allowedBranchIds = $isAdmin ? null : $user->branches->pluck('id')->all();

        return SaleReturn::query()
            ->with(['order'])
            ->when(!$isAdmin && !empty($allowedBranchIds), function ($q) use ($allowedBranchIds) {
                $q->whereIn('branch_id', $allowedBranchIds);
            })
            ->where(function ($q) use ($query) {
                $q->where('return_no', 'like', "%{$query}%")
                    ->orWhere('srb_invoice_id', 'like', "%{$query}%")
                    ->orWhere('return_reason', 'like', "%{$query}%")
                    ->orWhereHas('order', function ($oq) use ($query) {
                        $oq->where('invoice_no', 'like', "%{$query}%");
                    });
            })
            ->latest()
            ->take(6)
            ->get()
            ->map(function (SaleReturn $ret) use ($currency) {
                $subtitle = 'Refund: ' . ($ret->refund_method ? ucfirst($ret->refund_method) : 'Cash');
                if ($ret->return_reason) {
                    $subtitle .= ' • ' . Str::limit($ret->return_reason, 25);
                }

                return [
                    'id' => $ret->id,
                    'title' => 'Return #' . $ret->return_no,
                    'subtitle' => $subtitle,
                    'url' => route('sale-returns.list'),
                    'icon' => 'fas fa-undo-alt',
                    'badge' => $currency . ' ' . number_format($ret->refund_total, 2),
                    'type' => 'sale_returns',
                ];
            });
    }

    /**
     * Search Branches and Counters.
     */
    protected function searchLocations($user, string $query): array
    {
        $results = [];

        if ($user->can('branches.view')) {
            $canEditBranch = $user->can('branches.edit');
            $branches = Branch::query()
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('address', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                })
                ->take(4)
                ->get();

            foreach ($branches as $b) {
                $results[] = [
                    'id' => 'branch-' . $b->id,
                    'title' => $b->name,
                    'subtitle' => 'Branch Code: ' . $b->code . ($b->phone ? ' • ' . $b->phone : ''),
                    'url' => $canEditBranch ? route('branches.edit', $b->id) : route('branches.index'),
                    'icon' => 'fas fa-code-branch',
                    'badge' => 'Branch',
                    'type' => 'branches',
                ];
            }
        }

        if ($user->can('counters.view') && Schema::hasTable('counters')) {
            $canEditCounter = $user->can('counters.edit');
            $counters = Counter::query()
                ->with('branch')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhereHas('branch', function ($bq) use ($query) {
                            $bq->where('name', 'like', "%{$query}%");
                        });
                })
                ->take(4)
                ->get();

            foreach ($counters as $c) {
                $results[] = [
                    'id' => 'counter-' . $c->id,
                    'title' => $c->name,
                    'subtitle' => 'Counter Code: ' . $c->code . ($c->branch ? ' • Branch: ' . $c->branch->name : ''),
                    'url' => $canEditCounter ? route('counters.edit', $c->id) : route('counters.index'),
                    'icon' => 'fas fa-desktop',
                    'badge' => 'Counter',
                    'type' => 'counters',
                ];
            }
        }

        return $results;
    }

    /**
     * Search Users / Staff.
     */
    protected function searchUsers($user, string $query)
    {
        $canEdit = $user->can('users.edit');

        return User::query()
            ->with('roles')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(6)
            ->get()
            ->map(function (User $u) use ($canEdit) {
                $roleName = $u->roles->first() ? $u->roles->first()->name : 'User';

                return [
                    'id' => $u->id,
                    'title' => $u->getFullname(),
                    'subtitle' => $u->email,
                    'url' => $canEdit ? route('users.edit', $u->id) : route('users.index'),
                    'icon' => 'fas fa-user-cog',
                    'badge' => $roleName,
                    'type' => 'users',
                ];
            });
    }

    /**
     * Search Roles.
     */
    protected function searchRoles($user, string $query)
    {
        $canEdit = $user->can('roles.edit');

        return Role::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->take(4)
            ->get()
            ->map(function (Role $role) use ($canEdit) {
                return [
                    'id' => $role->id,
                    'title' => $role->name,
                    'subtitle' => $role->description ?: 'System security role',
                    'url' => $canEdit ? route('roles.edit', $role->id) : route('roles.index'),
                    'icon' => 'fas fa-user-shield',
                    'badge' => 'Role',
                    'type' => 'roles',
                ];
            });
    }

    /**
     * Search Suppliers.
     */
    protected function searchSuppliers($user, string $query)
    {
        $canEdit = $user->can('suppliers.edit');

        return Supplier::query()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->take(5)
            ->get()
            ->map(function (Supplier $s) use ($canEdit) {
                return [
                    'id' => $s->id,
                    'title' => $s->full_name,
                    'subtitle' => $s->phone ?: $s->email,
                    'url' => $canEdit ? route('suppliers.edit', $s->id) : route('suppliers.index'),
                    'icon' => 'fas fa-truck',
                    'badge' => 'Supplier',
                    'type' => 'suppliers',
                ];
            });
    }

    /**
     * Search Purchases.
     */
    protected function searchPurchases($user, string $query, string $currency)
    {
        return Purchase::query()
            ->with('supplier')
            ->where(function ($q) use ($query) {
                $q->where('id', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%")
                    ->orWhereHas('supplier', function ($sq) use ($query) {
                        $sq->where('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%");
                    });
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Purchase $p) use ($currency) {
                $supplierName = $p->supplier ? $p->supplier->full_name : 'General Supplier';
                return [
                    'id' => $p->id,
                    'title' => 'Purchase #' . $p->id,
                    'subtitle' => $supplierName . ' • ' . ($p->purchase_date ? $p->purchase_date->format('M d, Y') : ''),
                    'url' => route('purchases.index'),
                    'icon' => 'fas fa-truck-loading',
                    'badge' => $currency . ' ' . number_format($p->total_amount, 2),
                    'type' => 'purchases',
                ];
            });
    }
}
