@php
    $managementSections = [
        'products' => ['label' => 'Products', 'group' => 'Catalog', 'icon' => 'package', 'description' => 'Build a clear, reliable product catalog for every sale.', 'notes' => ['Keep each barcode unique so items scan correctly.', 'Check price and stock before saving.', 'Use an image to make items easier to find at checkout.']],
        'categories' => ['label' => 'Categories', 'group' => 'Catalog', 'icon' => 'tags', 'description' => 'Give your service catalog a structure that is easy to browse.', 'notes' => ['Choose a name your team will recognize.', 'Sort order controls how categories appear.', 'Availability follows the selected status.']],
        'services' => ['label' => 'Services', 'group' => 'Catalog', 'icon' => 'sparkles', 'description' => 'Organize the services your team delivers, with rates ready for checkout.', 'notes' => ['Group related services into a category.', 'Confirm the rate and barcode.', 'Add a clear image and description for your team.']],
        'deals' => ['label' => 'Deals', 'group' => 'Catalog', 'icon' => 'tags', 'description' => 'Bring services together into packages with clear pricing and savings.', 'notes' => ['Select every service included in the package.', 'Review the calculated original amount.', 'Set the final package price and availability.']],
        'customers' => ['label' => 'Customers', 'group' => 'People', 'icon' => 'users', 'description' => 'Keep customer details close to the transactions they belong to.', 'notes' => ['Use the customer’s preferred contact details.', 'Check phone and email before saving.', 'The profile is available when creating a sale.']],
        'suppliers' => ['label' => 'Suppliers', 'group' => 'People', 'icon' => 'truck', 'description' => 'Keep supplier information organized for purchasing and inventory.', 'notes' => ['Enter a recognizable supplier name.', 'Keep phone, email and address current.', 'Saved suppliers are available on new purchases.']],
        'orders' => ['label' => 'Orders', 'group' => 'Sales', 'icon' => 'receipt', 'description' => 'Follow every transaction, outstanding balance and printed receipt.', 'notes' => []],
        'purchases' => ['label' => 'Purchases', 'group' => 'Inventory', 'icon' => 'shopping-bag', 'description' => 'Track incoming inventory, supplier records and purchase receipts.', 'notes' => []],
        'branches' => ['label' => 'Branches', 'group' => 'Organization', 'icon' => 'git-branch', 'description' => 'Set up the locations where your team works and sells.', 'notes' => ['Keep the branch code easy to recognize.', 'The POS password protects access to this location.', 'Review user assignments and fiscal registration details.']],
        'counters' => ['label' => 'Counters', 'group' => 'Organization', 'icon' => 'monitor', 'description' => 'Manage the checkout counters connected to each branch.', 'notes' => ['Choose the branch this counter belongs to.', 'Set a clear counter name and code.', 'Review the access password and assigned operators.']],
        'users' => ['label' => 'Users', 'group' => 'Access', 'icon' => 'user-cog', 'description' => 'Give each operator the account and workspace access they need.', 'notes' => ['Check the sign-in and profile details.', 'Review roles and direct permissions.', 'Assign the branches and counters the user can access.']],
        'roles' => ['label' => 'Roles', 'group' => 'Access', 'icon' => 'shield', 'description' => 'Keep responsibilities clear with permissions grouped into reusable roles.', 'notes' => ['Use a clear role name and unique slug.', 'Review each permission group.', 'Changes apply to users assigned to this role.']],
        'permissions' => ['label' => 'Permissions', 'group' => 'Access', 'icon' => 'shield-check', 'description' => 'Explore the actions available across your workspace, organized by module.', 'notes' => []],
        'settings' => ['label' => 'settings.title', 'group' => 'Workspace', 'icon' => 'sliders', 'description' => 'Fine-tune the identity, catalog and checkout experience of your business.', 'notes' => ['Keep business and receipt branding current.', 'Choose the controls available at checkout.', 'Review fiscal settings before saving.']],
    ];
    $managementSection = $managementSections[$module];
@endphp

@if(($variant ?? 'overview') === 'guide')
    <aside class="management-guide" aria-label="{{ __($managementSection['label']) }} {{ __('guide') }}">
        <span class="management-kicker">{{ __($managementSection['group']) }} / {{ __($managementSection['label']) }}</span>
        <span class="management-guide-icon"><x-snd-icon :name="$managementSection['icon']" /></span>
        <h2>{{ __($managementSection['label']) }} <span>{{ __('details') }}</span></h2>
        <p>{{ __($managementSection['description']) }}</p>
        <ol class="management-guide-notes">
            @foreach($managementSection['notes'] as $managementNote)
                <li><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ __($managementNote) }}</li>
            @endforeach
        </ol>
        <div class="management-guide-footer"><x-snd-icon name="circle-check" /><span>{{ __('Review your details, then save your changes.') }}</span></div>
    </aside>
@else
    <div class="management-overview">
        <div class="management-overview-copy">
            <span class="management-kicker"><x-snd-icon :name="$managementSection['icon']" />{{ __($managementSection['group']) }} / {{ __($managementSection['label']) }}</span>
            <p>{{ __($managementSection['description']) }}</p>
        </div>
        @if(isset($records))
            <div class="management-record-count"><strong>{{ method_exists($records, 'total') ? $records->total() : $records->count() }}</strong><span>{{ __('records') }}</span></div>
        @else
            <span class="management-overview-mark" aria-hidden="true"><x-snd-icon :name="$managementSection['icon']" /></span>
        @endif
    </div>
@endif
