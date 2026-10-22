@extends('layouts.admin')

@section('title', __('customer.Customer_List'))
@section('content-header', __('customer.Customer_List'))
@section('content-actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><x-snd-icon name="user-plus" class="mr-1" /> {{ __('customer.Add_Customer') }}</a>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        .snd-live-search-card { width: 100%; border: 1px solid var(--snd-border); border-radius: var(--snd-radius-lg); box-shadow: var(--snd-shadow); }
        .snd-live-search-card .card-body { padding: 16px 18px; }
        .snd-live-search-card label { display: block; margin-bottom: 7px; color: var(--snd-ink); font-size: .78rem; font-weight: 600; }
        .snd-live-search-card { max-width: none; }
        .snd-live-search-input { min-width: 0; }
        .snd-live-search-hint { color: var(--snd-muted); font-size: .78rem; margin-top: .4rem; }
        .snd-live-search-input:focus { border-color: var(--snd-primary); box-shadow: 0 0 0 3px var(--snd-primary-ring); }
        .customer-avatar-initials { width: 36px; height: 36px; border-radius: 50%; background: var(--snd-primary-soft); color: var(--snd-primary-deep); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
        @media (max-width: 575.98px) {
            .snd-live-search-card .card-body { padding: 14px; }
        }
    </style>
@endsection

@section('content')
    <div class="card snd-live-search-card mb-3">
        <div class="card-body">
            <label for="customerSearchInput">Search customers</label>
            <div class="snd-search-control">
                <input id="customerSearchInput" type="search" class="form-control snd-live-search-input"
                       placeholder="Search by name, phone, email, or ID" value="{{ request('search') }}" autocomplete="off">
                <button type="button" id="customerSearchButton" class="btn btn-primary snd-search-action" aria-label="Search customers" title="Search customers">
                    <x-snd-icon name="search" />
                </button>
            </div>
            <div class="snd-live-search-hint">Results update as you type.</div>
        </div>
    </div>

    <div class="card">
        <div id="customersTableContainer">
            @include('customers.partials.table', ['customers' => $customers])
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('customerSearchInput');
            const searchButton = document.getElementById('customerSearchButton');
            const container = document.getElementById('customersTableContainer');
            let debounceTimer = null;
            let requestController = null;

            function loadCustomers(url, historyMode) {
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
                    if (!response.ok) throw new Error('Unable to load customers.');
                    return response.text();
                }).then(function (html) {
                    container.innerHTML = html;
                }).catch(function (error) {
                    if (error.name !== 'AbortError') console.error(error);
                });
            }

            function searchCustomers() {
                const url = new URL("{{ route('customers.index') }}", window.location.origin);
                const query = searchInput.value.trim();
                if (query) url.searchParams.set('search', query);
                loadCustomers(url.toString(), 'replace');
            }

            function refreshCustomers() { loadCustomers(window.location.href, null); }

            searchInput.addEventListener('input', function () {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(searchCustomers, 250);
            });
            searchButton.addEventListener('click', function () {
                window.clearTimeout(debounceTimer);
                searchCustomers();
            });
            searchInput.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                window.clearTimeout(debounceTimer);
                searchCustomers();
            });

            container.addEventListener('click', function (event) {
                const link = event.target.closest('.customers-pagination a');
                if (!link) return;
                event.preventDefault();
                loadCustomers(link.href, 'push');
            });

            window.addEventListener('popstate', function () {
                const url = new URL(window.location.href);
                searchInput.value = url.searchParams.get('search') || '';
                loadCustomers(url.toString(), null);
            });

            document.addEventListener('click', function (event) {
                const button = event.target.closest('.btn-delete');
                if (!button) return;
                event.preventDefault();
                Swal.fire({
                    title: 'Delete customer?',
                    text: 'Are you sure you want to delete this customer?',
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
                            refreshCustomers();
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
