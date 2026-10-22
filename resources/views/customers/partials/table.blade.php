<div class="table-responsive snd-table-scroll">
    <table class="table table-modern mb-0">
        <thead>
            <tr>
                <th>{{ __('customer.ID') }}</th>
                <th>{{ __('customer.Avatar') }}</th>
                <th>{{ __('customer.First_Name') }}</th>
                <th>{{ __('customer.Last_Name') }}</th>
                <th>{{ __('customer.Email') }}</th>
                <th>{{ __('customer.Phone') }}</th>
                <th>{{ __('customer.Address') }}</th>
                <th>{{ __('common.Created_At') }}</th>
                <th>{{ __('customer.Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>
                        @if($customer->avatar)
                            <img width="38" height="38" src="{{ $customer->avatar_url }}" alt="" class="rounded-circle" style="object-fit:cover;" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                        @endif
                        <span class="customer-avatar-initials" aria-label="{{ $customer->getFullNameAttribute() }}" @if($customer->avatar) style="display:none;" @endif>{{ mb_strtoupper(mb_substr($customer->first_name ?? '', 0, 1) . mb_substr($customer->last_name ?? '', 0, 1)) }}</span>
                    </td>
                    <td>{{ $customer->first_name }}</td>
                    <td>{{ $customer->last_name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->address }}</td>
                    <td>{{ $customer->created_at }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-primary" aria-label="Edit customer"><x-snd-icon name="square-pen" /></a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-url="{{ route('customers.destroy', $customer) }}" aria-label="Delete customer"><x-snd-icon name="trash" /></button>
                    </td>
                </tr>
            @empty
                <tr class="snd-empty-row">
                    <td colspan="9" class="snd-table-empty-cell">
                        <x-snd-empty-state icon="users" message="No customers found." :url="route('customers.create')" action-label="Add First Customer" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="customers-pagination px-3 py-2">
    <x-snd-pagination :paginator="$customers" />
</div>
