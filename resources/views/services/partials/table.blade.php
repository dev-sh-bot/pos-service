<div class="table-responsive snd-table-scroll">
    <table class="table table-modern mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Image</th>
                <th>Barcode</th>
                <th>Rate</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($services as $service)
                <tr>
                    <td>{{ $service->id }}</td>
                    <td>{{ $service->name }}</td>
                    <td>{{ $service->category?->name ?? '—' }}</td>
                    <td><img src="{{ $service->image_url }}" alt="{{ $service->name }}" style="width:42px;height:42px;object-fit:cover;border-radius:8px;"></td>
                    <td>{{ $service->barcode ?? '—' }}</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($service->rate, 2) }}</td>
                    <td><span class="badge badge-{{ $service->status ? 'success' : 'secondary' }}">{{ $service->status ? 'Active' : 'Inactive' }}</span></td>
                    <td>{{ $service->created_at }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-primary" aria-label="Edit service"><x-snd-icon name="square-pen" /></a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-url="{{ route('services.destroy', $service) }}" aria-label="Delete service"><x-snd-icon name="trash" /></button>
                    </td>
                </tr>
            @empty
                <tr class="snd-empty-row">
                    <td colspan="9" class="snd-table-empty-cell">
                        <x-snd-empty-state icon="briefcase" message="No services found." :url="route('services.create')" action-label="Create First Service" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="services-pagination px-3 py-2">
    <x-snd-pagination :paginator="$services" />
</div>
