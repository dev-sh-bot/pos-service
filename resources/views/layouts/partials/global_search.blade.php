{{-- ============================================================
     INPL-POS ''‚'' Luxury Pearl Global Search Component
     ============================================================ --}}

<div class="global-search-container" id="global-search-wrapper">
    {{-- Search Input Form / Trigger --}}
    <div class="global-search-input-box" id="global-search-box">
        <i class="fas fa-search global-search-icon" id="global-search-icon"></i>

        <input type="text"
               id="global-search-input"
               class="global-search-input"
               placeholder="{{ __('Search sales, services, customers, products...') }}"
               autocomplete="off"
               spellcheck="false"
               aria-label="Global Search"
               aria-expanded="false"
               aria-haspopup="listbox">

        {{-- Loading Spinner --}}
        <span class="global-search-spinner d-none" id="global-search-spinner">
            <i class="fas fa-circle-notch fa-spin"></i>
        </span>

        {{-- Clear Button --}}
        <button type="button" class="global-search-clear d-none" id="global-search-clear" title="Clear search">
            <i class="fas fa-times-circle"></i>
        </button>

        {{-- Keyboard Shortcut Badge --}}
        <div class="global-search-shortcut d-none d-xl-flex" id="global-search-kbd">
            <kbd>Ctrl</kbd><kbd>K</kbd>
        </div>
    </div>

    {{-- Dropdown Results Card --}}
    <div class="global-search-dropdown d-none" id="global-search-dropdown" role="listbox">
        {{-- Category Filter Pills --}}
        <div class="global-search-filters d-none" id="global-search-filters">
            {{-- Injected dynamically --}}
        </div>

        {{-- Results Scrollable Container --}}
        <div class="global-search-body" id="global-search-results">
            {{-- Default quick suggestions or live results inserted here --}}
        </div>

        {{-- Footer Controls / Tips --}}
        <div class="global-search-footer">
            <div class="global-search-hints">
                <span class="hint-item"><kbd>↑</kbd><kbd>↓</kbd> {{ __('Navigate') }}</span>
                <span class="hint-item"><kbd>↵</kbd> {{ __('Select') }}</span>
                <span class="hint-item"><kbd>ESC</kbd> {{ __('Close') }}</span>
            </div>
            <div class="global-search-summary text-muted" id="global-search-summary">
                <span class="inpl-brand-badge"><i class="fas fa-bolt text-primary mr-1"></i>POS Search</span>
            </div>
        </div>
    </div>
</div>

{{-- Mobile Search Overlay (for small screens) --}}
<div class="mobile-search-backdrop d-none" id="mobile-search-backdrop"></div>

<style>
/* ──────────────────────────────────────────────────────────
   Luxury Pearl Global Search Styling
   ────────────────────────────────────────────────────────── */
.global-search-container {
    position: relative;
    z-index: 1040;
}

.snd-search-host {
    position: relative;
    width: min(100%, 620px);
    margin-left: 5px;
}

.global-search-input-box {
    position: relative;
    display: flex;
    align-items: center;
    width: 280px;
    height: 38px;
    background: var(--snd-surface-soft);
    border: 1.5px solid var(--snd-border-strong);
    border-radius: 9999px;
    padding: 0 12px 0 36px;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}

.global-search-input-box:hover {
    background: #ffffff;
    border-color: #b9aefe;
    box-shadow: 0 2px 6px var(--snd-primary-wash, rgba(151, 134, 238, 0.1));
}

.global-search-input-box.is-focused {
    width: 400px;
    background: #ffffff;
    border-color: var(--snd-primary-deep, #6758bd);
    box-shadow: 0 0 0 3.5px var(--snd-primary-ring, rgba(151, 134, 238, 0.26)), 0 4px 12px rgba(15, 23, 42, 0.08);
}

.global-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.82rem;
    color: #626b7b;
    pointer-events: none;
    transition: color 0.2s;
}

.global-search-input-box.is-focused .global-search-icon {
    color: var(--snd-primary-deep, #6758bd);
}

.global-search-input {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    outline: none;
    font-size: 0.82rem;
    font-weight: 500;
    color: #1a1a2e;
    padding-right: 48px;
}

.global-search-input::placeholder {
    color: #626b7b;
    font-weight: 400;
    font-size: 0.8rem;
}

.global-search-spinner {
    position: absolute;
    right: 32px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.8rem;
    color: var(--snd-primary-deep, #6758bd);
}

.global-search-clear {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #626b7b;
    font-size: 0.85rem;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s, transform 0.15s;
}

.global-search-clear:hover {
    color: #c9342d;
    transform: translateY(-50%) scale(1.1);
}

.global-search-shortcut {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    gap: 3px;
    pointer-events: none;
}

.global-search-shortcut kbd {
    background: var(--snd-surface-soft);
    color: #626b7b;
    border-radius: 4px;
    padding: 2px 5px;
    font-size: 0.65rem;
    font-weight: 700;
    font-family: inherit;
    line-height: 1.1;
    border: 1px solid var(--snd-border-strong);
    box-shadow: 0 1px 1px rgba(0,0,0,0.06);
}

.global-search-input-box.is-focused .global-search-shortcut {
    display: none !important;
}

/* ──────────────────────────────────────────────────────────
   Results Dropdown Card
   ────────────────────────────────────────────────────────── */
.global-search-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    width: 620px;
    max-width: calc(100vw - 32px);
    background: #ffffff;
    border: 1.5px solid var(--snd-border-strong);
    border-radius: 16px;
    box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(208, 228, 245, 0.6);
    overflow: hidden;
    animation: searchDropdownFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1050;
}

@keyframes searchDropdownFade {
    0% {
        opacity: 0;
        transform: translateY(-6px) scale(0.99);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ──────────────────────────────────────────────────────────
   Filter Tabs / Pills
   ────────────────────────────────────────────────────────── */
.global-search-filters {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: var(--snd-surface-soft);
    border-bottom: 1px solid #edf2f7;
    overflow-x: auto;
    scrollbar-width: none;
}

.global-search-filters::-webkit-scrollbar {
    display: none;
}

.search-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.73rem;
    font-weight: 600;
    border: 1px solid var(--snd-border);
    background: #ffffff;
    color: #626b7b;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}

.search-pill-btn:hover {
    background: var(--snd-primary-soft, #f0edff);
    border-color: var(--snd-border-strong, #d9d4f5);
    color: var(--snd-primary-deep, #6758bd);
}

.search-pill-btn.active {
    background: var(--snd-button-primary, var(--snd-primary, #9786ee));
    border-color: var(--snd-button-primary, var(--snd-primary, #9786ee));
    color: #ffffff !important;
    box-shadow: 0 2px 6px var(--snd-primary-ring, rgba(151,134,238,.26));
}

.search-pill-btn .pill-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 1px 6px;
    font-size: 0.65rem;
    font-weight: 700;
}

.search-pill-btn.active .pill-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* ──────────────────────────────────────────────────────────
   Body Container
   ────────────────────────────────────────────────────────── */
.global-search-body {
    max-height: 420px;
    overflow-y: auto;
    padding: 6px 0;
    scroll-behavior: smooth;
}

.global-search-body::-webkit-scrollbar {
    width: 6px;
}
.global-search-body::-webkit-scrollbar-track {
    background: transparent;
}
.global-search-body::-webkit-scrollbar-thumb {
    background: var(--snd-border-strong);
    border-radius: 10px;
}
.global-search-body::-webkit-scrollbar-thumb:hover {
    background: #626b7b;
}

/* ──────────────────────────────────────────────────────────
   Category Group Headers & Items
   ────────────────────────────────────────────────────────── */
.search-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px 4px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #626b7b;
}

.search-group-title {
    display: flex;
    align-items: center;
    gap: 7px;
}

.search-group-badge {
    background: var(--snd-surface-soft);
    color: #505469;
    border-radius: 10px;
    padding: 1px 7px;
    font-size: 0.65rem;
    font-weight: 700;
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px;
    margin: 2px 8px;
    border-radius: 10px;
    text-decoration: none !important;
    color: inherit;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
    border: 1px solid transparent;
}

.search-result-item:hover,
.search-result-item.is-selected {
    background: #f0edff;
    border-color: var(--snd-border-strong);
    outline: none;
}

.search-result-item.is-selected {
    box-shadow: inset 3px 0 0 var(--snd-primary-deep, #6758bd);
}

.item-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.95rem;
    transition: transform 0.15s;
}

.search-result-item:hover .item-icon-box,
.search-result-item.is-selected .item-icon-box {
    transform: scale(1.06);
}

.item-content {
    flex: 1;
    min-width: 0;
}

.item-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1a1a2e;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-subtitle {
    font-size: 0.74rem;
    color: #626b7b;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-meta-side {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.item-badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1.1;
    background: var(--snd-surface-soft);
    color: #505469;
    border: 1px solid var(--snd-border);
}

.item-badge.badge-price {
    background: #eaf9ee;
    color: #1f7a3d;
    border-color: #c7ebd0;
}

.item-action-icon {
    font-size: 0.75rem;
    color: var(--snd-border-strong);
    transition: transform 0.15s, color 0.15s;
}

.search-result-item:hover .item-action-icon,
.search-result-item.is-selected .item-action-icon {
    color: var(--snd-primary-deep, #6758bd);
    transform: translateX(2px);
}

/* ──────────────────────────────────────────────────────────
   Text Highlight
   ────────────────────────────────────────────────────────── */
.gs-mark {
    background: var(--snd-primary-wash, rgba(151, 134, 238, 0.1));
    color: var(--snd-primary-hover, #806fda);
    border-radius: 3px;
    padding: 0 2px;
    font-weight: 800;
}

/* ──────────────────────────────────────────────────────────
   Empty & Default State
   ────────────────────────────────────────────────────────── */
.search-empty-state {
    padding: 36px 20px;
    text-align: center;
}

.search-empty-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--snd-surface-soft);
    color: #626b7b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 12px;
}

.search-empty-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.search-empty-desc {
    font-size: 0.78rem;
    color: #626b7b;
    max-width: 320px;
    margin: 0 auto;
    line-height: 1.4;
}

.search-initial-tip {
    padding: 10px 16px;
    background: var(--snd-surface-soft);
    border-radius: 10px;
    margin: 8px 12px 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.74rem;
    color: #626b7b;
}

/* ──────────────────────────────────────────────────────────
   Footer
   ────────────────────────────────────────────────────────── */
.global-search-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    background: var(--snd-surface-soft);
    border-top: 1px solid #edf2f7;
    font-size: 0.72rem;
}

.global-search-hints {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #626b7b;
}

.global-search-hints .hint-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.global-search-hints kbd {
    background: var(--snd-surface-soft);
    color: #505469;
    border-radius: 4px;
    padding: 1px 4px;
    font-size: 0.65rem;
    font-weight: 700;
    line-height: 1;
    border: 1px solid var(--snd-border-strong);
}

.inpl-brand-badge {
    font-size: 0.7rem;
    font-weight: 700;
    color: #505469;
    letter-spacing: 0.02em;
}

/* ──────────────────────────────────────────────────────────
   Mobile & Responsive Adjustments
   ────────────────────────────────────────────────────────── */
@media (max-width: 991.98px) {
    #navbar-search-section {
        display: none;
    }

    #navbar-search-section.mobile-active {
        display: flex !important;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: #ffffff;
        z-index: 1070;
        padding: 10px 14px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.12);
        margin: 0 !important;
        align-items: center;
    }

    #navbar-search-section.mobile-active .global-search-container {
        width: 100%;
        position: static;
    }

    #navbar-search-section.mobile-active .global-search-input-box {
        width: 100% !important;
        height: 42px;
    }

    #navbar-search-section.mobile-active .global-search-dropdown {
        position: fixed;
        top: 68px;
        left: 8px;
        right: 8px;
        width: auto;
        max-width: none;
        max-height: calc(100vh - 85px);
        z-index: 1080;
    }

    .mobile-search-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
        z-index: 1065;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchUrl = '{{ route("admin.global-search") }}';
    const wrapper = document.getElementById('global-search-wrapper');
    const box = document.getElementById('global-search-box');
    const input = document.getElementById('global-search-input');
    const spinner = document.getElementById('global-search-spinner');
    const clearBtn = document.getElementById('global-search-clear');
    const kbd = document.getElementById('global-search-kbd');
    const dropdown = document.getElementById('global-search-dropdown');
    const filtersBar = document.getElementById('global-search-filters');
    const resultsContainer = document.getElementById('global-search-results');
    const summarySpan = document.getElementById('global-search-summary');
    const mobileBackdrop = document.getElementById('mobile-search-backdrop');

    let debounceTimer = null;
    let abortController = null;
    let currentData = null;
    let activeFilterKey = 'all';
    let selectedIndex = -1;

    // ── Global Keyboard Shortcut (Ctrl+K or /) ──
    document.addEventListener('keydown', function (e) {
        // Ctrl+K or Cmd+K
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            focusSearch();
        }
        // "/" hotkey if not typing in an input
        if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            focusSearch();
        }
    });

    function focusSearch() {
        if (input) {
            input.focus();
            input.select();
            openDropdown();
        }
    }

    // ── Focus & Blur Handlers ──
    input.addEventListener('focus', function () {
        box.classList.add('is-focused');
        openDropdown();
        if (!currentData || input.value.trim() === '') {
            fetchResults('');
        }
    });

    clearBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        input.value = '';
        clearBtn.classList.add('d-none');
        input.focus();
        fetchResults('');
    });

    // ── Input Changes with Debounce ──
    input.addEventListener('input', function () {
        const val = input.value.trim();
        if (val.length > 0) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchResults(val);
        }, 220);
    });

    // ── Keydown Navigation inside Search ──
    input.addEventListener('keydown', function (e) {
        const items = resultsContainer.querySelectorAll('.search-result-item');
        if (dropdown.classList.contains('d-none')) {
            if (e.key === 'ArrowDown') {
                openDropdown();
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (items.length > 0) {
                selectedIndex = (selectedIndex + 1) % items.length;
                updateSelection(items);
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (items.length > 0) {
                selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                updateSelection(items);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && selectedIndex < items.length) {
                items[selectedIndex].click();
            } else if (items.length > 0) {
                items[0].click();
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            closeDropdown();
            input.blur();
        }
    });

    function updateSelection(items) {
        items.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('is-selected');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('is-selected');
            }
        });
    }

    // ── Fetch Results via AJAX ──
    function fetchResults(query) {
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        spinner.classList.remove('d-none');

        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            signal: abortController.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Search failed');
            return response.json();
        })
        .then(data => {
            spinner.classList.add('d-none');
            currentData = data;
            activeFilterKey = 'all';
            selectedIndex = -1;
            renderFilters(data);
            renderResults(data, activeFilterKey, query);
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                spinner.classList.add('d-none');
                console.error('Search error:', err);
            }
        });
    }

    // ── Render Category Filter Tabs ──
    function renderFilters(data) {
        if (!data || !data.groups || data.groups.length <= 1) {
            filtersBar.classList.add('d-none');
            filtersBar.innerHTML = '';
            return;
        }

        filtersBar.classList.remove('d-none');
        let html = `
            <button type="button" class="search-pill-btn ${activeFilterKey === 'all' ? 'active' : ''}" data-filter="all">
                <span>All</span>
                <span class="pill-count">${data.total}</span>
            </button>
        `;

        data.groups.forEach(group => {
            const count = group.items ? group.items.length : 0;
            if (count > 0) {
                const isActive = activeFilterKey === group.key ? 'active' : '';
                html += `
                    <button type="button" class="search-pill-btn ${isActive}" data-filter="${group.key}">
                        <i class="${group.icon}" style="color:${isActive ? '#fff' : group.color};font-size:0.7rem;"></i>
                        <span>${group.label}</span>
                        <span class="pill-count">${count}</span>
                    </button>
                `;
            }
        });

        filtersBar.innerHTML = html;

        // Attach click listeners to filter pills
        filtersBar.querySelectorAll('.search-pill-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                activeFilterKey = this.getAttribute('data-filter');
                filtersBar.querySelectorAll('.search-pill-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedIndex = -1;
                renderResults(currentData, activeFilterKey, input.value.trim());
                input.focus();
            });
        });
    }

    // ── Highlight Matching Query ──
    function highlight(text, query) {
        if (!text) return '';
        if (!query || query.length < 2) return escapeHtml(text);

        const escapedText = escapeHtml(text);
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return escapedText.replace(regex, '<mark class="gs-mark">$1</mark>');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Render Results List ──
    function renderResults(data, filterKey, query) {
        if (!data || !data.groups || data.groups.length === 0) {
            renderEmptyState(query);
            return;
        }

        let filteredGroups = data.groups;
        if (filterKey !== 'all') {
            filteredGroups = data.groups.filter(g => g.key === filterKey);
        }

        let totalVisibleItems = 0;
        let html = '';

        filteredGroups.forEach(group => {
            if (!group.items || group.items.length === 0) return;
            totalVisibleItems += group.items.length;

            html += `
                <div class="search-group" data-group="${group.key}">
                    <div class="search-group-header">
                        <div class="search-group-title">
                            <i class="${group.icon}" style="color:${group.color};"></i>
                            <span>${group.label}</span>
                        </div>
                        <span class="search-group-badge">${group.items.length}</span>
                    </div>
            `;

            group.items.forEach(item => {
                const highlightedTitle = highlight(item.title, query);
                const highlightedSubtitle = highlight(item.subtitle, query);
                const isPrice = item.badge && (item.badge.includes('$') || item.badge.includes('Rs') || item.badge.includes('PKR'));

                html += `
                    <a href="${item.url}" class="search-result-item" role="option">
                        <div class="item-icon-box" style="background:${group.bg};color:${group.color};">
                            <i class="${item.icon}"></i>
                        </div>
                        <div class="item-content">
                            <div class="item-title">${highlightedTitle}</div>
                            <div class="item-subtitle">${highlightedSubtitle}</div>
                        </div>
                        <div class="item-meta-side">
                            ${item.badge ? `<span class="item-badge ${isPrice ? 'badge-price' : ''}">${escapeHtml(item.badge)}</span>` : ''}
                            <i class="fas fa-chevron-right item-action-icon"></i>
                        </div>
                    </a>
                `;
            });

            html += `</div>`;
        });

        if (totalVisibleItems === 0) {
            renderEmptyState(query);
            return;
        }

        // Tip footer banner on default empty query
        if (!query) {
            html += `
                <div class="search-initial-tip">
                    <i class="fas fa-lightbulb text-warning"></i>
                    <span>Type order #, customer phone, service name, or barcode to search instantly.</span>
                </div>
            `;
        }

        resultsContainer.innerHTML = html;

        // Update footer summary
        if (summarySpan) {
            if (query) {
                summarySpan.innerHTML = `<span class="font-weight-bold text-dark">${totalVisibleItems}</span> results found`;
            } else {
                summarySpan.innerHTML = `<span class="inpl-brand-badge"><i class="fas fa-bolt text-primary mr-1"></i>Quick Jump</span>`;
            }
        }
    }

    function renderEmptyState(query) {
        resultsContainer.innerHTML = `
            <div class="search-empty-state">
                <div class="search-empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="search-empty-title">No results found ${query ? `for "${escapeHtml(query)}"` : ''}</div>
                <div class="search-empty-desc">
                    We couldn't find any matches matching your permissions. Try searching by customer phone, invoice number, or product title.
                </div>
            </div>
        `;
        if (summarySpan) {
            summarySpan.innerHTML = `0 results`;
        }
    }

    // ── Open / Close Dropdown ──
    const navSearchSection = document.getElementById('navbar-search-section');

    function openDropdown() {
        dropdown.classList.remove('d-none');
        input.setAttribute('aria-expanded', 'true');
        if (mobileBackdrop) {
            mobileBackdrop.classList.remove('d-none');
        }
        if (navSearchSection && window.innerWidth < 992) {
            navSearchSection.classList.add('mobile-active');
        }
    }

    function closeDropdown() {
        dropdown.classList.add('d-none');
        box.classList.remove('is-focused');
        input.setAttribute('aria-expanded', 'false');
        if (mobileBackdrop) {
            mobileBackdrop.classList.add('d-none');
        }
        if (navSearchSection) {
            navSearchSection.classList.remove('mobile-active');
        }
    }

    // Click outside to close
    document.addEventListener('click', function (e) {
        if (!wrapper.contains(e.target)) {
            closeDropdown();
        }
    });

    if (mobileBackdrop) {
        mobileBackdrop.addEventListener('click', function () {
            closeDropdown();
        });
    }

    // Mobile Search Trigger Button in Navbar (if present)
    const mobileSearchBtn = document.getElementById('mobile-search-btn');
    if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', function (e) {
            e.preventDefault();
            focusSearch();
        });
    }
});
</script>
