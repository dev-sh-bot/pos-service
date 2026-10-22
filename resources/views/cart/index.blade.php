@extends('layouts.admin')

@section('title', __('order.title'))
@section('body-class', 'sidebar-collapse')

@section('content')
    <div id="cart"></div>
@endsection

@section('css')
<style>
.content-wrapper {
    padding-bottom: 0 !important;
    background: var(--snd-surface-soft) !important;
    min-height: calc(100vh - 57px) !important;
    height: calc(100vh - 57px) !important;
    overflow: hidden !important;
}
.content-header {
    display: none !important;
}
.main-footer {
    display: none !important;
}
.content {
    padding: 0 !important;
    margin: 0 !important;
    height: calc(100vh - 57px) !important;
    max-height: calc(100vh - 57px) !important;
    overflow: hidden !important;
}
#cart {
    width: 100%;
    height: 100%;
}
/* Ensure background never turns white when print/receipt modal is open */
body:not(.printing-receipt) {
    background-color: var(--snd-surface-soft) !important;
}
body.swal2-shown {
    background-color: var(--snd-surface-soft) !important;
}
.swal2-container.swal2-backdrop-show {
    background-color: rgba(15, 23, 42, 0.5) !important;
    backdrop-filter: blur(2px);
}
.swal2-container.swal2-backdrop-hide,
.swal2-container:not(.swal2-backdrop-show) {
    pointer-events: none !important;
}
#receipt-print-frame {
    pointer-events: none !important;
}

/* ═════════════════════════════════════════════════════════════════════
   Product & Deal Grid Professional Row Separation & Shelf Line Dividers
   ═════════════════════════════════════════════════════════════════════ */

/* 1. Grid Container: Spacious 5-6 cards per row instead of 12 cramped items */
div:has(> .pos-product-card) {
    grid-template-columns: repeat(auto-fill, minmax(235px, 1fr)) !important;
    row-gap: 36px !important;
    column-gap: 18px !important;
    padding: 10px 6px 40px !important;
}

div:has(> .pos-deal-card) {
    grid-template-columns: repeat(auto-fill, minmax(315px, 1fr)) !important;
    row-gap: 36px !important;
    column-gap: 18px !important;
    padding: 10px 6px 40px !important;
}

/* 2. Product Card Sizing, Polish & Aesthetics */
.pos-product-card {
    position: relative !important;
    overflow: visible !important;
    border-radius: 18px !important;
    min-height: 255px !important;
    background: #ffffff !important;
    border: 1.5px solid var(--snd-border) !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
    display: flex !important;
    flex-direction: column !important;
}

.pos-product-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 28px rgba(103, 88, 189, 0.18) !important;
    border-color: #b9aefe !important;
}

/* Card Header (Icon/Image Box) - Increased Height */
.pos-product-card > div:first-child {
    height: 110px !important;
    border-top-left-radius: 16px !important;
    border-top-right-radius: 16px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, rgba(103, 88, 189, 0.08) 0%, rgba(103, 88, 189, 0.15) 100%) !important;
}

.pos-product-card > div:first-child > div {
    width: 58px !important;
    height: 58px !important;
    border-radius: 16px !important;
    font-size: 1.75rem !important;
    box-shadow: 0 4px 12px rgba(103, 88, 189, 0.12) !important;
}

/* Card Body - Spacious padding */
.pos-product-card > div:nth-child(2) {
    padding: 14px 16px 16px !important;
    display: flex !important;
    flex-direction: column !important;
    flex: 1 !important;
    border-bottom-left-radius: 16px !important;
    border-bottom-right-radius: 16px !important;
}

/* Category Label & Item # */
.pos-product-card > div:nth-child(2) > div:first-child {
    margin-bottom: 8px !important;
}
.pos-product-card > div:nth-child(2) > div:first-child span:first-child {
    font-size: 0.74rem !important;
    font-weight: 800 !important;
    color: #505469 !important;
    background: var(--snd-surface-soft) !important;
    padding: 2px 8px !important;
    border-radius: 6px !important;
    letter-spacing: 0.03em !important;
}
.pos-product-card > div:nth-child(2) > div:first-child span:last-child {
    font-size: 0.74rem !important;
    font-weight: 800 !important;
    color: #6758bd !important;
    background: #f0edff !important;
    padding: 2px 7px !important;
    border-radius: 6px !important;
    border: 1px solid #d9d4f5 !important;
}

/* Service / Product Name Text (Prominently Increased) */
.pos-product-card > div:nth-child(2) > div:nth-child(2) {
    font-size: 1.05rem !important;
    font-weight: 800 !important;
    line-height: 1.35 !important;
    color: #1a1a2e !important;
    margin-bottom: 12px !important;
    min-height: 48px !important;
    letter-spacing: -0.01em !important;
}

/* Bottom Bar: Price & Buttons */
.pos-product-card > div:nth-child(2) > div:last-child {
    padding-top: 12px !important;
    border-top: 1.5px dashed var(--snd-border) !important;
    margin-top: auto !important;
}

/* Price */
.pos-product-card > div:nth-child(2) > div:last-child > span:first-child {
    font-size: 1.18rem !important;
    font-weight: 850 !important;
    color: #6758bd !important;
    letter-spacing: -0.02em !important;
}

/* +/- Quantity Controls */
.pos-product-card > div:nth-child(2) > div:last-child button {
    width: 32px !important;
    height: 32px !important;
    border-radius: 9px !important;
    font-size: 1rem !important;
    font-weight: 800 !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
}

.pos-product-card > div:nth-child(2) > div:last-child span {
    font-size: 0.95rem !important;
    font-weight: 800 !important;
    min-width: 24px !important;
    text-align: center !important;
    color: #6758bd !important;
}

/* 3. The Continuous Horizontal Line Divider Under Each Row */
.pos-product-card::after {
    content: "";
    position: absolute;
    bottom: -18px;
    left: -11px;
    right: -11px;
    height: 1.5px;
    background: var(--snd-border-strong);
    pointer-events: none;
    z-index: 1;
}

/* 4. Deal Card Row Divider */
.pos-deal-card {
    position: relative !important;
    overflow: visible !important;
    border-radius: 18px !important;
}

.pos-deal-card::after {
    content: "";
    position: absolute;
    bottom: -18px;
    left: -11px;
    right: -11px;
    height: 1.5px;
    background: var(--snd-border-strong);
    pointer-events: none;
    z-index: 1;
}

/* 5. Ordered Items Box Custom Theme-Blue Scrollbar */
.pos-order-items {
    scrollbar-width: thin !important;
    scrollbar-color: #6758bd #eef4fb !important;
}

.pos-order-items::-webkit-scrollbar {
    width: 6px !important;
    height: 6px !important;
}

.pos-order-items::-webkit-scrollbar-track {
    background: #eef4fb !important;
    border-radius: 10px !important;
}

.pos-order-items::-webkit-scrollbar-thumb {
    background: #6758bd !important;
    border-radius: 10px !important;
}

.pos-order-items::-webkit-scrollbar-thumb:hover {
    background: #806fda !important;
}
</style>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.body.classList.contains('sidebar-collapse')) {
            document.body.classList.add('sidebar-collapse');
        }

        // Dynamically ensure row-gap and column sizing are enforced on any dynamically mounted grid
        const applyGridSeparation = function () {
            document.querySelectorAll('.pos-product-card, .pos-deal-card').forEach(function (card) {
                var grid = card.parentElement;
                if (grid && grid.style && grid.style.display === 'grid') {
                    if (card.classList.contains('pos-product-card')) {
                        grid.style.setProperty('grid-template-columns', 'repeat(auto-fill, minmax(235px, 1fr))', 'important');
                    } else if (card.classList.contains('pos-deal-card')) {
                        grid.style.setProperty('grid-template-columns', 'repeat(auto-fill, minmax(315px, 1fr))', 'important');
                    }
                    grid.style.setProperty('row-gap', '36px', 'important');
                    grid.style.setProperty('column-gap', '18px', 'important');
                    grid.style.setProperty('padding-bottom', '40px', 'important');
                }
            });
        };

        applyGridSeparation();

        // Observe DOM for React catalog updates (category switches, search, etc.)
        var cartContainer = document.getElementById('cart');
        if (cartContainer && window.MutationObserver) {
            var observer = new MutationObserver(applyGridSeparation);
            observer.observe(cartContainer, { childList: true, subtree: true });
        }

        // Global Enter key handler for Branch Access & Counter Access modals
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const swalPopup = document.querySelector('.swal2-popup.swal2-pos-gate, .swal2-popup');
                if (swalPopup && !swalPopup.classList.contains('swal2-hide') && swalPopup.offsetParent !== null) {
                    // If select2 dropdown is open, let user select with enter
                    if (document.querySelector('.select2-container--open')) {
                        return;
                    }

                    const confirmBtn = swalPopup.querySelector('.swal2-confirm');
                    if (confirmBtn && !confirmBtn.disabled) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Visual button press animation effect
                        confirmBtn.style.transform = 'scale(0.96)';
                        confirmBtn.style.opacity = '0.85';

                        setTimeout(function () {
                            confirmBtn.style.transform = '';
                            confirmBtn.style.opacity = '';
                            if (window.Swal && typeof window.Swal.clickConfirm === 'function') {
                                window.Swal.clickConfirm();
                            } else {
                                confirmBtn.click();
                            }
                        }, 80);
                    }
                }
            }
        }, true);
    });
</script>
@endsection
