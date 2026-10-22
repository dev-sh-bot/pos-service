@extends('layouts.admin')

@section('title', 'Services')
@section('content-header', 'Services')
@section('content-actions')
    <a href="{{ route('services.create') }}" class="btn btn-primary"><x-snd-icon name="plus" /> Create Service</a>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        .snd-live-search-card { border: 1px solid var(--snd-border); border-radius: var(--snd-radius-lg); box-shadow: var(--snd-shadow); }
        .snd-live-search-hint { color: var(--snd-muted); font-size: .78rem; margin-top: .4rem; }
        .snd-live-search-input:focus, .snd-live-search-filter:focus { border-color: var(--snd-primary); box-shadow: 0 0 0 3px var(--snd-primary-ring); }
    </style>
@endsection

@section('content')
    <div class="card snd-live-search-card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-7 mb-3 mb-md-0">
                    <label for="serviceSearchInput">Search services</label>
                    <div class="snd-search-control">
                        <input id="serviceSearchInput" type="search" class="form-control snd-live-search-input"
                               placeholder="Search by name, barcode, or ID" value="{{ request('search') }}" autocomplete="off">
                        <button type="button" id="serviceSearchButton" class="btn btn-primary snd-search-action" aria-label="Search services" title="Search services">
                            <x-snd-icon name="search" />
                        </button>
                    </div>
                </div>
                <div class="col-md-5">
                    <label for="serviceCategoryFilter">Category</label>
                    <select id="serviceCategoryFilter" class="form-control snd-live-search-filter">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="snd-live-search-hint">Search and category filters update the table as you work.</div>
        </div>
    </div>

    <div class="card">
        <div id="servicesTableContainer">
            @include('services.partials.table', ['services' => $services])
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('serviceSearchInput');
            const searchButton = document.getElementById('serviceSearchButton');
            const categoryFilter = document.getElementById('serviceCategoryFilter');
            const container = document.getElementById('servicesTableContainer');
            let debounceTimer = null;
            let requestController = null;

            function loadServices(url, historyMode) {
                if (requestController) requestController.abort();
                requestController = new AbortController();
                const displayUrl = new URL(url, window.location.origin);
                displayUrl.searchParams.delete('ajax_table');
                if (historyMode === 'push') window.history.pushState({}, '', displayUrl.toString());
                if (historyMode === 'replace') window.history.replaceState({}, '', displayUrl.toString());

                const requestUrl = new URL(displayUrl.toString());
                requestUrl.searchParams.set('ajax_table', '1');
                return fetch(requestUrl.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    signal: requestController.signal
                }).then(function (response) {
                    if (!response.ok) throw new Error('Unable to load services.');
                    return response.text();
                }).then(function (html) {
                    container.innerHTML = html;
                }).catch(function (error) {
                    if (error.name !== 'AbortError') console.error(error);
                });
            }

            function applyFilters() {
                const url = new URL("{{ route('services.index') }}", window.location.origin);
                const query = searchInput.value.trim();
                const categoryId = categoryFilter.value;
                if (query) url.searchParams.set('search', query);
                if (categoryId) url.searchParams.set('category_id', categoryId);
                loadServices(url.toString(), 'replace');
            }

            function refreshServices() { loadServices(window.location.href, null); }

            searchInput.addEventListener('input', function () {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(applyFilters, 250);
            });
            searchButton.addEventListener('click', function () {
                window.clearTimeout(debounceTimer);
                applyFilters();
            });
            searchInput.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                window.clearTimeout(debounceTimer);
                applyFilters();
            });
            categoryFilter.addEventListener('change', applyFilters);

            container.addEventListener('click', function (event) {
                const link = event.target.closest('.services-pagination a');
                if (!link) return;
                event.preventDefault();
                loadServices(link.href, 'push');
            });

            window.addEventListener('popstate', function () {
                const url = new URL(window.location.href);
                searchInput.value = url.searchParams.get('search') || '';
                categoryFilter.value = url.searchParams.get('category_id') || '';
                loadServices(url.toString(), null);
            });

            document.addEventListener('click', function (event) {
                const button = event.target.closest('.btn-delete');
                if (!button) return;
                event.preventDefault();
                Swal.fire({
                    title: 'Delete service?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    fetch(button.dataset.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    }).then(function (response) { return response.json(); }).then(function (payload) {
                        if (payload.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
                            refreshServices();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Unable to delete', text: payload.message || 'Please try again.' });
                        }
                    }).catch(function () {
                        Swal.fire({ icon: 'error', title: 'Unable to delete', text: 'Please try again.' });
                    });
                });
            });
        });
    </script>
@endsection
