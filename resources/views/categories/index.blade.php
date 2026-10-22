@extends('layouts.admin')

@section('title', 'Categories')
@section('content-header', 'Categories')
@section('content-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary"><x-snd-icon name="plus" /> Create Category</a>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        .snd-live-search-card { border: 1px solid var(--snd-border); border-radius: var(--snd-radius-lg); box-shadow: var(--snd-shadow); }
        .snd-live-search-hint { color: var(--snd-muted); font-size: .78rem; margin-top: .4rem; }
        .snd-live-search-input:focus { border-color: var(--snd-primary); box-shadow: 0 0 0 3px var(--snd-primary-ring); }
    </style>
@endsection

@section('content')
    <div class="card snd-live-search-card mb-3">
        <div class="card-body">
            <label for="categorySearchInput">Search categories</label>
            <div class="snd-search-control">
                <input id="categorySearchInput" type="search" class="form-control snd-live-search-input"
                       placeholder="Search by name, slug, or ID" value="{{ request('search') }}" autocomplete="off">
                <button type="button" id="categorySearchButton" class="btn btn-primary snd-search-action" aria-label="Search categories" title="Search categories">
                    <x-snd-icon name="search" />
                </button>
            </div>
            <div class="snd-live-search-hint">Results update as you type.</div>
        </div>
    </div>

    <div class="card">
        <div id="categoriesTableContainer">
            @include('categories.partials.table', ['categories' => $categories])
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('categorySearchInput');
            const searchButton = document.getElementById('categorySearchButton');
            const container = document.getElementById('categoriesTableContainer');
            let debounceTimer = null;
            let requestController = null;

            function loadCategories(url, historyMode) {
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
                    if (!response.ok) throw new Error('Unable to load categories.');
                    return response.text();
                }).then(function (html) {
                    container.innerHTML = html;
                }).catch(function (error) {
                    if (error.name !== 'AbortError') console.error(error);
                });
            }

            function searchCategories() {
                const url = new URL("{{ route('categories.index') }}", window.location.origin);
                const query = searchInput.value.trim();
                if (query) url.searchParams.set('search', query);
                loadCategories(url.toString(), 'replace');
            }

            function refreshCategories() {
                loadCategories(window.location.href, null);
            }

            searchInput.addEventListener('input', function () {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(searchCategories, 250);
            });
            searchButton.addEventListener('click', function () {
                window.clearTimeout(debounceTimer);
                searchCategories();
            });
            searchInput.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                window.clearTimeout(debounceTimer);
                searchCategories();
            });

            container.addEventListener('click', function (event) {
                const link = event.target.closest('.categories-pagination a');
                if (!link) return;
                event.preventDefault();
                loadCategories(link.href, 'push');
            });

            window.addEventListener('popstate', function () { loadCategories(window.location.href, null); });

            document.addEventListener('click', function (event) {
                const button = event.target.closest('.btn-delete');
                if (!button) return;
                event.preventDefault();
                Swal.fire({
                    title: 'Delete category?',
                    text: 'Services in this category will become uncategorized.',
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
                            refreshCategories();
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
