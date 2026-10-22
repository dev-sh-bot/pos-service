<div class="table-responsive snd-table-scroll">
    <table class="table table-modern mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Services</th>
                <th>Sort</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ $category->services_count }}</td>
                    <td>{{ $category->sort_order }}</td>
                    <td><span class="badge badge-{{ $category->status ? 'success' : 'secondary' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span></td>
                    <td>{{ $category->created_at }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-primary" aria-label="Edit category"><x-snd-icon name="square-pen" /></a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-url="{{ route('categories.destroy', $category) }}" aria-label="Delete category"><x-snd-icon name="trash" /></button>
                    </td>
                </tr>
            @empty
                <tr class="snd-empty-row">
                    <td colspan="8" class="snd-table-empty-cell">
                        <x-snd-empty-state icon="list" message="No categories found." :url="route('categories.create')" action-label="Create First Category" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="categories-pagination px-3 py-2">
    <x-snd-pagination :paginator="$categories" />
</div>
