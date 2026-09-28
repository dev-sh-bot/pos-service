import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";
import $ from "jquery";
import select2 from "select2";
import "select2/dist/css/select2.min.css";
import { CommonHelper } from "../helpers/commonHelper";
const imageUrl = `${window.location.origin}/images/srb.jfif`;


if (typeof window !== "undefined") {
    window.$ = window.jQuery = $;
    try {
        if (typeof select2 === "function") {
            select2(window, $);
        }
    } catch (e) { }
}

// ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
//  Inline style objects
// ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
const S = {
    posWrapper: {
        display: "flex", height: "calc(100vh - 106px)", gap: "0",
        background: "#f8fafc", overflow: "hidden",
        borderRadius: "16px", boxShadow: "0 4px 24px rgba(15,23,42,0.06)",
    },
    // ''‚''‚¬ Gate screen (branch/counter not yet verified) ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
    gateScreen: {
        flex: 1, display: "flex", alignItems: "center",
        justifyContent: "center", background: "#f8fafc",
    },
    gateCard: {
        background: "#fff", borderRadius: "24px",
        boxShadow: "0 16px 45px rgba(42, 105, 176, 0.12)",
        padding: "42px 46px", textAlign: "center", maxWidth: "440px", width: "100%",
        border: "1.5px solid #d0e4f5",
    },
    gateIcon: {
        width: "72px", height: "72px", borderRadius: "20px",
        background: "#2a69b0",
        display: "flex", alignItems: "center", justifyContent: "center",
        margin: "0 auto 20px", fontSize: "1.9rem", color: "#fff",
        boxShadow: "0 8px 24px rgba(42, 105, 176, 0.38)",
    },
    gateTitle: { fontSize: "1.35rem", fontWeight: 800, color: "#1a1a1a", marginBottom: "6px", letterSpacing: "-0.02em" },
    gateSubtitle: { fontSize: "0.85rem", color: "#1a3a5c", marginBottom: "26px", lineHeight: 1.5 },
    gateBranchBadge: {
        display: "inline-flex", alignItems: "center", gap: "6px",
        background: "#f0f6ff", border: "1px solid #d0e4f5", borderRadius: "22px",
        padding: "6px 16px", fontSize: "0.82rem", fontWeight: 700, color: "#2a69b0",
        marginBottom: "22px",
    },
    gateBtn: {
        width: "100%", padding: "14px 0", borderRadius: "14px",
        border: "none", fontSize: "0.95rem", fontWeight: 800, cursor: "pointer",
        background: "#2a69b0", color: "#fff",
        boxShadow: "0 6px 20px rgba(42, 105, 176, 0.4)", transition: "all 0.15s",
    },
    gateBtnSecondary: {
        width: "100%", padding: "10px 0", borderRadius: "12px",
        border: "1.5px solid #d0e4f5", fontSize: "0.85rem", fontWeight: 700,
        cursor: "pointer", background: "#f0f6ff", color: "#2a69b0",
        marginTop: "10px", transition: "all 0.15s",
    },
    stepIndicator: {
        display: "flex", alignItems: "center", justifyContent: "center",
        gap: "8px", marginBottom: "24px",
    },
    stepDot: (active, done) => ({
        width: "32px", height: "32px", borderRadius: "50%",
        display: "flex", alignItems: "center", justifyContent: "center",
        fontSize: "0.78rem", fontWeight: 800,
        background: done ? "#2a69b0" : active ? "#2a69b0" : "#f0f6ff",
        color: done || active ? "#fff" : "#2a69b0",
        border: done || active ? "none" : "1.5px solid #d0e4f5",
        boxShadow: active ? "0 0 0 4px rgba(42, 105, 176, 0.25)" : "none",
    }),
    stepLine: {
        flex: 1, height: "2px", background: "linear-gradient(90deg, #2a69b0, #d0e4f5)", maxWidth: "40px",
    },
    // ''‚''‚¬ Cart panel ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
    cartPanel: {
        width: "320px", minWidth: "300px", background: "#fff",
        display: "flex", flexDirection: "column",
        borderRadius: "16px 0 0 16px", overflow: "hidden",
        borderRight: "1px solid #e2e8f0",
    },
    cartHeader: {
        padding: "16px 18px", background: "#0f172a", color: "#fff",
        fontWeight: 700, fontSize: "0.92rem",
        display: "flex", alignItems: "center", justifyContent: "space-between",
    },
    cartHeaderBadge: {
        background: "#2563eb", color: "#fff", borderRadius: "12px",
        fontSize: "0.72rem", padding: "3px 10px", fontWeight: 600,
    },
    cartHeaderMeta: {
        fontSize: "0.72rem", color: "rgba(255,255,255,0.65)",
        display: "flex", alignItems: "center", gap: "6px", marginTop: "4px",
    },
    customerBar: {
        padding: "10px 14px", background: "#f8fafc",
        borderBottom: "1px solid #e2e8f0", display: "flex", gap: "6px",
    },
    customerSelect: {
        flex: 1, fontSize: "0.82rem", border: "1px solid #e2e8f0",
        borderRadius: "10px", padding: "6px 12px",
        color: "#0f172a", background: "#fff", outline: "none",
    },
    cartItemsArea: { flex: 1, overflowY: "auto", padding: "4px 0" },
    cartEmpty: {
        display: "flex", flexDirection: "column",
        alignItems: "center", justifyContent: "center",
        height: "100%", padding: "30px 20px", color: "#94a3b8", textAlign: "center",
    },
    cartEmptyIcon: { fontSize: "2.8rem", marginBottom: "12px", color: "#cbd5e1" },
    cartEmptyText: { fontSize: "0.84rem", fontWeight: 500, color: "#94a3b8" },
    cartRow: {
        display: "flex", alignItems: "center",
        padding: "10px 14px", gap: "8px",
        borderBottom: "1px solid #f1f5f9", transition: "background 0.12s",
    },
    cartItemName: {
        flex: 1, fontSize: "0.84rem", fontWeight: 600, color: "#0f172a",
        lineHeight: 1.3, minWidth: 0, overflow: "hidden",
        textOverflow: "ellipsis", whiteSpace: "nowrap",
    },
    cartItemPrice: { fontSize: "0.76rem", color: "#2563eb", fontWeight: 600, marginTop: "1px" },
    qtyControl: { display: "flex", alignItems: "center", gap: "3px", flexShrink: 0 },
    qtyBtn: {
        width: "24px", height: "24px", borderRadius: "6px",
        border: "1px solid #e2e8f0", background: "#f8fafc",
        color: "#475569", fontSize: "0.85rem", fontWeight: "700",
        display: "flex", alignItems: "center", justifyContent: "center",
        cursor: "pointer", padding: 0, lineHeight: 1,
    },
    qtyInput: {
        width: "36px", height: "24px", textAlign: "center",
        fontSize: "0.8rem", borderRadius: "6px", padding: "1px 3px",
        border: "1px solid #e2e8f0", outline: "none", fontWeight: "600",
    },
    cartItemTotal: {
        fontSize: "0.84rem", fontWeight: 700, color: "#0f172a",
        minWidth: "55px", textAlign: "right", flexShrink: 0,
    },
    removeBtn: {
        background: "#fef2f2", border: "none", color: "#ef4444",
        cursor: "pointer", padding: "4px 6px", borderRadius: "6px",
        fontSize: "0.75rem", flexShrink: 0, transition: "all 0.12s",
    },
    cartFooter: { padding: "14px 16px", background: "#fff", borderTop: "2px solid #f1f5f9" },
    totalsRow: {
        display: "flex", justifyContent: "space-between",
        fontSize: "0.82rem", color: "#64748b", marginBottom: "4px",
    },
    totalsGrand: {
        display: "flex", justifyContent: "space-between",
        fontSize: "1.05rem", fontWeight: 800, color: "#0f172a",
        paddingTop: "10px", borderTop: "1.5px dashed #e2e8f0",
        marginTop: "6px", marginBottom: "14px",
    },
    actionBtns: { display: "flex", gap: "8px" },
    btnCancel: {
        flex: 1, padding: "10px 0",
        background: "#fff", border: "1.5px solid #fee2e2", color: "#ef4444",
        borderRadius: "10px", fontSize: "0.83rem", fontWeight: 600, cursor: "pointer",
    },
    btnCheckout: {
        flex: 2, padding: "10px 0", border: "none", color: "#fff",
        background: "linear-gradient(135deg,#2563eb,#1d4ed8)",
        borderRadius: "10px", fontSize: "0.85rem", fontWeight: 700,
        cursor: "pointer", boxShadow: "0 4px 14px rgba(37,99,235,0.35)",
        letterSpacing: "0.02em",
    },
    // ''‚''‚¬ Products panel ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
    productsPanel: {
        flex: 1, display: "flex", flexDirection: "column",
        overflow: "hidden", background: "#f8fafc",
    },
    productsTopbar: {
        padding: "12px 16px", background: "#fff",
        borderBottom: "1px solid #e2e8f0",
        display: "flex", gap: "8px", alignItems: "center", flexShrink: 0,
    },
    inputField: {
        flex: 1, fontSize: "0.83rem", border: "1px solid #e2e8f0",
        borderRadius: "10px", padding: "8px 14px",
        color: "#0f172a", background: "#fff", outline: "none",
        boxSizing: "border-box",
        minWidth: 0,
        width: "100%",
    },
    inputFieldBarcode: {
        width: "190px", flexShrink: 0, fontSize: "0.82rem",
        border: "1px solid #e2e8f0", borderRadius: "10px",
        padding: "8px 12px", color: "#0f172a", background: "#fff", outline: "none",
    },
    productsGrid: {
        flex: 1, overflowY: "auto", padding: "16px",
        display: "grid",
        gridTemplateColumns: "repeat(auto-fill, minmax(140px, 1fr))",
        gap: "12px", alignContent: "start",
    },
    productTile: {
        background: "#fff", border: "1px solid #e2e8f0",
        borderRadius: "14px", padding: "12px 10px 10px",
        cursor: "pointer", textAlign: "center",
        display: "flex", flexDirection: "column",
        alignItems: "center", gap: "6px",
        position: "relative", overflow: "hidden", userSelect: "none",
        boxShadow: "0 4px 14px rgba(15,23,42,0.03)",
        transition: "all 0.18s ease",
    },
    productTileImgWrap: {
        width: "68px", height: "68px", borderRadius: "14px",
        overflow: "hidden", background: "#eff6ff", border: "1px solid #dbeafe",
        display: "flex", alignItems: "center", justifyContent: "center",
    },
    productTileImg: { width: "100%", height: "100%", objectFit: "cover" },
    productTileName: {
        fontSize: "0.78rem", fontWeight: 700, color: "#0f172a",
        lineHeight: 1.3, maxHeight: "2.6em", overflow: "hidden",
        textOverflow: "ellipsis", display: "-webkit-box",
        WebkitLineClamp: 2, WebkitBoxOrient: "vertical", width: "100%",
    },
    productTileStock: (low) => ({
        fontSize: "0.68rem", fontWeight: 600, padding: "2px 8px",
        borderRadius: "12px",
        background: low ? "#fef2f2" : "#ecfdf5",
        color: low ? "#dc2626" : "#059669",
    }),
    productTilePrice: { fontSize: "0.85rem", fontWeight: 800, color: "#2563eb" },
};

// ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
//  Cart Component
// ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            // ''‚''‚¬ POS gate state ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
            gateChecked: false,   // have we fetched /pos-access/status?
            branchVerified: false,
            counterVerified: false,
            activeBranch: null,    // { id, name, code }
            activeCounter: null,    // { id, name, code }
            gateLoading: false,

            // ''‚''‚¬ Cart / product state ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬
            cart: [],
            products: [],
            services: [],
            categories: [],
            deals: [],
            catalogMode: "services", // "services" or "deals"
            catalog: [],
            customers: [],
            barcode: "",
            search: "",
            isSearching: false,
            searchFocused: false,
            activeFilter: "all",
            activeCategory: "all",
            taxPercent: window.APP?.tax_enabled ? "8" : "",
            taxAmount: "",
            taxMode: "percent",
            discountPercent: "",
            discountAmount: "",
            discountMode: "percent",
            customer_id: "",
            translations: {},
            hoveredTile: null,
            hoveredRemove: null,
            lastPlacedOrder: null,
        };

        // gate
        this.checkGateStatus = this.checkGateStatus.bind(this);
        this.showBranchModal = this.showBranchModal.bind(this);
        this.showCounterModal = this.showCounterModal.bind(this);
        this.handleClearSession = this.handleClearSession.bind(this);
        // cart
        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);
        this.loadCustomers = this.loadCustomers.bind(this);
        this.initCustomerSelect2 = this.initCustomerSelect2.bind(this);
        this.showAddCustomerModal = this.showAddCustomerModal.bind(this);
        this.loadProducts = this.loadProducts.bind(this);
        this.loadCatalog = this.loadCatalog.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.handleOpenLastInvoice = this.handleOpenLastInvoice.bind(this);
        this.setTaxPercent = this.setTaxPercent.bind(this);
        this.setTaxAmount = this.setTaxAmount.bind(this);
        this.setDiscountPercent = this.setDiscountPercent.bind(this);
        this.setDiscountAmount = this.setDiscountAmount.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
        this.showReceipt = this.showReceipt.bind(this);
        this.printReceipt = this.printReceipt.bind(this);
        this.addProductToCart = this.addProductToCart.bind(this);
        this.formatAmount = this.formatAmount.bind(this);
    }

    formatAmount(val) {
        const num = Number(val) || 0;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    componentDidMount() {
        this.checkGateStatus().then(() => {
            this.loadTranslations();
            this.loadCustomers();
            this.loadProducts();
            this.restoreCartOnLoad();
        });
    }

    componentDidUpdate(previousProps, previousState) {
        if (previousState.cart !== this.state.cart) {
            try {
                if (this.state.cart.length) {
                    window.localStorage.setItem("pos_cart", JSON.stringify(this.state.cart));
                } else {
                    window.localStorage.removeItem("pos_cart");
                }
            } catch (error) {
                // Ignore storage restrictions; the server cart remains available.
            }
        }
        if (previousState.customers !== this.state.customers || previousState.customer_id !== this.state.customer_id) {
            this.initCustomerSelect2();
        }
    }

    // ''‚''‚¬ Gate helpers ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬

    /** Check if branch+counter already verified in session. */
    async checkGateStatus() {
        const urls = window.APP?.pos_access;
        if (!urls) {
            // No pos_access config ''‚'' allow through (dev / no branches set up)
            this.setState({ gateChecked: true, branchVerified: true, counterVerified: true });
            return;
        }

        try {
            const res = await axios.get(urls.status);
            this.setState({
                gateChecked: true,
                branchVerified: res.data.branch_verified,
                counterVerified: res.data.counter_verified,
                activeBranch: res.data.branch,
                activeCounter: res.data.counter,
            });
            // Load branch choices before the rest of the POS requests.
            if (!res.data.branch_verified) {
                await this.showBranchModal();
            } else if (!res.data.counter_verified) {
                await this.showCounterModal();
            }
        } catch {
            this.setState({ gateChecked: true, branchVerified: true, counterVerified: true });
        }
    }

    closeActiveSwal() {
        if (typeof Swal !== "undefined") {
            Swal.close();
        }

        document.querySelectorAll('.swal2-container').forEach(el => {
            el.remove();
        });
    }

    injectPosGateStyles() {
        if (document.getElementById("pos-gate-swal-style")) return;

        const style = document.createElement("style");
        style.id = "pos-gate-swal-style";
        style.textContent = `
            .swal2-container.swal2-backdrop-show,
            .swal2-container.swal2-container--has-backdrop,
            div.swal2-container {
                background: #ffffff !important;
                backdrop-filter: none !important;
            }
            .swal2-popup.swal2-pos-gate {
                width: min(450px, 92vw) !important;
                max-width: 450px !important;
                border-radius: 28px !important;
                background: #ffffff !important;
                position: relative !important;
                border: 1.5px solid #d0e4f5 !important;
                box-shadow: 0 12px 35px -5px rgba(0, 0, 0, 0.07), 0 4px 15px rgba(42, 105, 176, 0.12) !important;
                padding: 2.4rem 2.2rem 2rem !important;
                overflow: visible !important;
            }
            .swal2-popup.swal2-pos-gate .swal2-html-container {
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            .swal2-popup.swal2-pos-gate .swal2-actions {
                margin-top: 1.8rem !important;
                gap: 0.8rem !important;
                width: 100% !important;
            }
            .swal2-popup.swal2-pos-gate .swal2-confirm,
            .swal2-popup.swal2-pos-gate .swal2-cancel {
                border-radius: 16px !important;
                font-weight: 800 !important;
                font-size: 0.98rem !important;
                padding: 0.95rem 1.6rem !important;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            .swal2-popup.swal2-pos-gate .swal2-confirm {
                background: #2a69b0 !important;
                border: none !important;
                color: #ffffff !important;
                box-shadow: 0 6px 18px rgba(42, 105, 176, 0.28) !important;
                letter-spacing: 0.02em !important;
                width: 100% !important;
            }
            .swal2-popup.swal2-pos-gate .swal2-confirm:hover {
                box-shadow: 0 10px 24px rgba(42, 105, 176, 0.4) !important;
                transform: translateY(-2px) !important;
            }
            .swal2-popup.swal2-pos-gate .swal2-cancel {
                background: #f0f6ff !important;
                border: 1.5px solid #d0e4f5 !important;
                color: #8c733e !important;
            }
            .swal2-popup.swal2-pos-gate .swal2-cancel:hover {
                background: #f5eedf !important;
                color: #1a3a5c !important;
                transform: translateY(-1px) !important;
            }
            .swal2-popup.swal2-pos-gate select,
            .swal2-popup.swal2-pos-gate input {
                transition: all 0.2s ease !important;
            }
            .swal2-popup.swal2-pos-gate select:focus,
            .swal2-popup.swal2-pos-gate input:focus {
                border-color: #2a69b0 !important;
                background: #ffffff !important;
                box-shadow: 0 0 0 4px rgba(42, 105, 176, 0.14) !important;
            }

            /* Select2 Custom POS Gateway Styling */
            .swal2-popup.swal2-pos-gate .select2-container {
                width: 100% !important;
                text-align: left !important;
            }
            .swal2-popup.swal2-pos-gate .select2-container--default .select2-selection--single {
                height: 48px !important;
                border-radius: 16px !important;
                border: 1.5px solid #d0e4f5 !important;
                background: #f0f6ff !important;
                display: flex !important;
                align-items: center !important;
                padding-left: 2.6rem !important;
                padding-right: 1rem !important;
                box-shadow: none !important;
                transition: all 0.2s ease !important;
            }
            .swal2-popup.swal2-pos-gate .select2-container--default.select2-container--open .select2-selection--single,
            .swal2-popup.swal2-pos-gate .select2-container--default.select2-container--focus .select2-selection--single {
                border-color: #2a69b0 !important;
                background: #ffffff !important;
                box-shadow: 0 0 0 4px rgba(42, 105, 176, 0.14) !important;
                outline: none !important;
            }
            .swal2-popup.swal2-pos-gate .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #1a1a1a !important;
                font-size: 0.95rem !important;
                font-weight: 600 !important;
                line-height: 46px !important;
                padding-left: 0 !important;
            }
            .swal2-popup.swal2-pos-gate .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 46px !important;
                right: 0.9rem !important;
            }
            .swal2-popup.swal2-pos-gate .select2-container--default .select2-selection--single .select2-selection__arrow b {
                border-color: #2a69b0 transparent transparent transparent !important;
                border-width: 6px 5px 0 5px !important;
            }
            .swal2-popup.swal2-pos-gate .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
                border-color: transparent transparent #2a69b0 transparent !important;
                border-width: 0 5px 6px 5px !important;
            }
            .select2-dropdown {
                border-radius: 16px !important;
                border: 1.5px solid #d0e4f5 !important;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
                background: #ffffff !important;
                overflow: hidden !important;
                z-index: 99999999 !important;
                padding: 6px !important;
            }
            .select2-results__option {
                border-radius: 10px !important;
                padding: 10px 14px !important;
                font-size: 0.92rem !important;
                font-weight: 600 !important;
                color: #262626 !important;
                margin-bottom: 2px !important;
                transition: all 0.15s ease !important;
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected],
            .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
                background: #2a69b0 !important;
                color: #ffffff !important;
            }
            .select2-container--default .select2-results__option[aria-selected="true"] {
                background: #f0f6ff !important;
                color: #2a69b0 !important;
                font-weight: 700 !important;
            }
        `;
        document.head.appendChild(style);
    }

    /** Step 1 ''‚'' Branch selection + password. */
    async showBranchModal() {
        this.closeActiveSwal();
        this.injectPosGateStyles();
        const urls = window.APP?.pos_access;
        if (!urls) return;

        // Fetch branches assigned to user
        let branches = [];
        try {
            const res = await axios.get(urls.branches);
            branches = res.data;
        } catch {
            Swal.fire("Error", "Could not load branches.", "error");
            return;
        }

        if (branches.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Branches Assigned",
                text: "You have no branches assigned. Contact your administrator.",
                confirmButtonColor: "#2a69b0",
            });
            return;
        }

        const branchOptions = branches
            .map(b => `<option value="${b.id}">${b.name} (${b.code})</option>`)
            .join("");

        const { value: formValues, isConfirmed } = await Swal.fire({
            title: "",
            html: `
                <div style="text-align:center;padding:0.2rem 0;">
                    <div style="width:68px;height:68px;border-radius:22px;background:#2a69b0;display:inline-flex;align-items:center;justify-content:center;color:#ffffff;font-size:1.65rem;box-shadow:0 8px 24px rgba(42, 105, 176, 0.25);margin-bottom:1.2rem;">
                        <i class="fas fa-lock"></i>
                    </div>

                    <div style="font-size:1.95rem;font-weight:800;color:#1a1a1a;margin:0 0 0.2rem;letter-spacing:-0.03em;">${CommonHelper.getBrandName()}</div>
                    <div style="font-size:0.75rem;letter-spacing:0.18em;color:#2a69b0;text-transform:uppercase;font-weight:700;margin-bottom:1rem;">${CommonHelper.getBrandSubtitle()}</div>

                    <div style="display:inline-flex;align-items:center;gap:0.55rem;background:rgba(42, 105, 176, 0.1);border:1px solid rgba(42, 105, 176, 0.3);border-radius:20px;padding:0.4rem 1.1rem;margin:0 auto 1.6rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#2a69b0;box-shadow:0 0 8px #2a69b0;"></span>
                        <span style="font-size:0.76rem;letter-spacing:0.12em;color:#2a69b0;text-transform:uppercase;font-weight:800;">Branch Access</span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:1.2rem;text-align:left;max-width:370px;margin:0 auto;">
                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:#262626;margin-bottom:0.5rem;">
                                Select Branch
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-building" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.95rem;pointer-events:none;z-index:10;"></i>
                                <select id="swal-branch" style="width:100%;padding:0.85rem 2.5rem 0.85rem 2.8rem;border:1.5px solid #d0e4f5;border-radius:16px;font-size:0.95rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;appearance:none;-webkit-appearance:none;cursor:pointer;box-sizing:border-box;transition:all 0.2s ease;">
                                    ${branchOptions}
                                </select>
                                <i id="swal-branch-arrow" class="fas fa-chevron-down" style="position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;pointer-events:none;font-size:0.85rem;z-index:3;"></i>
                            </div>
                        </div>

                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:#262626;margin-bottom:0.5rem;">
                                Branch Password
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-key" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.95rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-branch-pass" type="password"
                                       placeholder="Enter branch password"
                                       style="width:100%;padding:0.85rem 2.8rem 0.85rem 2.8rem;border:1.5px solid #d0e4f5;border-radius:16px;font-size:0.95rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;box-sizing:border-box;"
                                       autocomplete="off">
                                <button type="button" id="swal-pass-toggle" style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#2a69b0;cursor:pointer;font-size:0.95rem;padding:4px;display:flex;align-items:center;justify-content:center;z-index:2;">
                                    <i class="fas fa-eye" id="swal-pass-toggle-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`,
            showCancelButton: false,
            confirmButtonText: "Verify Branch",
            confirmButtonColor: "#2a69b0",
            allowOutsideClick: false,
            allowEscapeKey: false,
            focusConfirm: false,
            showLoaderOnConfirm: true,
            customClass: { popup: "swal2-pos-gate" },
            didOpen: () => {
                const toggleBtn = document.getElementById("swal-pass-toggle");
                const passInput = document.getElementById("swal-branch-pass");
                const icon = document.getElementById("swal-pass-toggle-icon");
                if (toggleBtn && passInput && icon) {
                    toggleBtn.addEventListener("click", () => {
                        if (passInput.type === "password") {
                            passInput.type = "text";
                            icon.className = "fas fa-eye-slash";
                            toggleBtn.style.color = "#2a69b0";
                        } else {
                            passInput.type = "password";
                            icon.className = "fas fa-eye";
                            toggleBtn.style.color = "#2a69b0";
                        }
                    });
                }

                const runInit = () => {
                    try {
                        if (typeof $.fn?.select2 !== 'function' && typeof select2 === 'function') {
                            select2(window, $);
                        }
                        const $select = $('#swal-branch');
                        if ($select.length && typeof $.fn?.select2 === 'function') {
                            if (!$select.hasClass('select2-hidden-accessible')) {
                                $select.select2({
                                    dropdownParent: $('.swal2-popup.swal2-pos-gate'),
                                    minimumResultsForSearch: Infinity,
                                    width: '100%'
                                });
                                const arrow = document.getElementById("swal-branch-arrow");
                                if (arrow) arrow.style.display = "none";
                            }
                        }
                    } catch (e) {
                        console.error("Select2 init error:", e);
                    }
                };

                setTimeout(runInit, 20);
                setTimeout(runInit, 120);
            },
            preConfirm: async () => {
                const branch_id = document.getElementById("swal-branch").value;
                const password = document.getElementById("swal-branch-pass").value;

                if (!password) {
                    Swal.showValidationMessage("Please enter the branch password.");
                    return false;
                }

                try {
                    const res = await axios.post(urls.verify_branch, { branch_id, password });
                    return res.data;
                } catch (err) {
                    Swal.showValidationMessage(
                        err.response?.data?.message || "Incorrect password."
                    );
                    return false;
                }
            },
        });

        if (isConfirmed && formValues) {
            this.setState({ branchVerified: true, activeBranch: formValues.branch });
            await this.showCounterModal();
        }
    }

    /** Step 2 ''‚'' Counter selection + password. */
    async showCounterModal() {
        this.closeActiveSwal();
        this.injectPosGateStyles();
        const urls = window.APP?.pos_access;
        if (!urls) return;

        let counters = [];
        try {
            const res = await axios.get(urls.counters);
            counters = res.data;
        } catch {
            Swal.fire("Error", "Could not load counters.", "error");
            return;
        }

        if (counters.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Counters Assigned",
                text: "You have no counters assigned to this branch. Contact your administrator.",
                confirmButtonColor: "#2a69b0",
            });
            return;
        }

        const counterOptions = counters
            .map(c => `<option value="${c.id}">${c.name} (${c.code})</option>`)
            .join("");

        const { value: formValues, isConfirmed } = await Swal.fire({
            title: "",
            html: `
                <div style="text-align:center;padding:0.2rem 0;">
                    <div style="width:68px;height:68px;border-radius:22px;background:#2a69b0;display:inline-flex;align-items:center;justify-content:center;color:#ffffff;font-size:1.65rem;box-shadow:0 8px 24px rgba(42, 105, 176, 0.25);margin-bottom:1.2rem;">
                        <i class="fas fa-lock"></i>
                    </div>

                    <div style="font-size:1.95rem;font-weight:800;color:#1a1a1a;margin:0 0 0.2rem;letter-spacing:-0.03em;">${CommonHelper.getBrandName()}</div>
                    <div style="font-size:0.75rem;letter-spacing:0.18em;color:#2a69b0;text-transform:uppercase;font-weight:700;margin-bottom:1rem;">${CommonHelper.getBrandSubtitle()}</div>

                    <div style="display:inline-flex;align-items:center;gap:0.55rem;background:rgba(42, 105, 176, 0.1);border:1px solid rgba(42, 105, 176, 0.3);border-radius:20px;padding:0.4rem 1.1rem;margin:0 auto 1.6rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#2a69b0;box-shadow:0 0 8px #2a69b0;"></span>
                        <span style="font-size:0.76rem;letter-spacing:0.12em;color:#2a69b0;text-transform:uppercase;font-weight:800;">Counter Access</span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:1.2rem;text-align:left;max-width:370px;margin:0 auto;">
                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:#262626;margin-bottom:0.5rem;">
                                Select Counter
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-cash-register" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.95rem;pointer-events:none;z-index:10;"></i>
                                <select id="swal-counter" style="width:100%;padding:0.85rem 2.5rem 0.85rem 2.8rem;border:1.5px solid #d0e4f5;border-radius:16px;font-size:0.95rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;appearance:none;-webkit-appearance:none;cursor:pointer;box-sizing:border-box;transition:all 0.2s ease;">
                                    ${counterOptions}
                                </select>
                                <i id="swal-counter-arrow" class="fas fa-chevron-down" style="position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;pointer-events:none;font-size:0.85rem;z-index:3;"></i>
                            </div>
                        </div>

                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:#262626;margin-bottom:0.5rem;">
                                Counter Password
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-key" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.95rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-counter-pass" type="password"
                                       placeholder="Enter counter password"
                                       style="width:100%;padding:0.85rem 2.8rem 0.85rem 2.8rem;border:1.5px solid #d0e4f5;border-radius:16px;font-size:0.95rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;box-sizing:border-box;"
                                       autocomplete="off">
                                <button type="button" id="swal-pass-toggle" style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#2a69b0;cursor:pointer;font-size:0.95rem;padding:4px;display:flex;align-items:center;justify-content:center;z-index:2;">
                                    <i class="fas fa-eye" id="swal-pass-toggle-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`,
            showCancelButton: true,
            cancelButtonText: "Back to Branch",
            confirmButtonText: "Open POS",
            confirmButtonColor: "#2a69b0",
            allowOutsideClick: false,
            allowEscapeKey: false,
            focusConfirm: false,
            showLoaderOnConfirm: true,
            customClass: { popup: "swal2-pos-gate" },
            didOpen: () => {
                const toggleBtn = document.getElementById("swal-pass-toggle");
                const passInput = document.getElementById("swal-counter-pass");
                const icon = document.getElementById("swal-pass-toggle-icon");
                if (toggleBtn && passInput && icon) {
                    toggleBtn.addEventListener("click", () => {
                        if (passInput.type === "password") {
                            passInput.type = "text";
                            icon.className = "fas fa-eye-slash";
                            toggleBtn.style.color = "#2a69b0";
                        } else {
                            passInput.type = "password";
                            icon.className = "fas fa-eye";
                            toggleBtn.style.color = "#2a69b0";
                        }
                    });
                }

                const runInit = () => {
                    try {
                        if (typeof $.fn?.select2 !== 'function' && typeof select2 === 'function') {
                            select2(window, $);
                        }
                        const $select = $('#swal-counter');
                        if ($select.length && typeof $.fn?.select2 === 'function') {
                            if (!$select.hasClass('select2-hidden-accessible')) {
                                $select.select2({
                                    dropdownParent: $('.swal2-popup.swal2-pos-gate'),
                                    minimumResultsForSearch: Infinity,
                                    width: '100%'
                                });
                                const arrow = document.getElementById("swal-counter-arrow");
                                if (arrow) arrow.style.display = "none";
                            }
                        }
                    } catch (e) {
                        console.error("Select2 init error:", e);
                    }
                };

                setTimeout(runInit, 20);
                setTimeout(runInit, 120);
            },
            preConfirm: async () => {
                const counter_id = document.getElementById("swal-counter").value;
                const password = document.getElementById("swal-counter-pass").value;

                if (!password) {
                    Swal.showValidationMessage("Please enter the counter password.");
                    return false;
                }

                try {
                    const res = await axios.post(urls.verify_counter, { counter_id, password });
                    return res.data;
                } catch (err) {
                    Swal.showValidationMessage(
                        err.response?.data?.message || "Incorrect password."
                    );
                    return false;
                }
            },
        });

        if (isConfirmed && formValues) {
            this.setState({ counterVerified: true, activeCounter: formValues.counter });
        } else if (!isConfirmed) {
            // Clicked "back to branch" ''‚'' restart branch selection
            await axios.post(window.APP.pos_access.clear).catch(() => { });
            this.setState({ branchVerified: false, activeBranch: null });
            this.showBranchModal();
        }
    }

    /** Clear session and force re-verification. */
    handleClearSession() {
        Swal.fire({
            title: "Switch Branch/Counter?",
            text: "This will clear your current POS session and ask you to re-enter passwords.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, switch",
            confirmButtonColor: "#ef4444",
            cancelButtonText: "Cancel",
        }).then(async res => {
            if (res.isConfirmed) {
                await axios.post(window.APP.pos_access.clear).catch(() => { });
                this.setState({
                    branchVerified: false, counterVerified: false,
                    activeBranch: null, activeCounter: null,
                });
                this.showBranchModal();
            }
        });
    }

    // ''‚''‚¬ Cart / product methods (unchanged) ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬

    loadTranslations() {
        axios.get(window.APP.locale_cart_url)
            .then(res => this.setState({ translations: res.data }))
            .catch(() => this.setState({ translations: {} }));
    }

    loadCustomers() {
        axios.get("/admin/customers", { headers: { Accept: "application/json" } })
            .then(res => {
                const customers = Array.isArray(res.data) ? res.data : [];
                this.setState({ customers }, () => this.initCustomerSelect2());
            })
            .catch(() => this.setState({ customers: [] }));
    }

    initCustomerSelect2() {
        setTimeout(() => {
            if (typeof $ !== "undefined" && typeof $.fn?.select2 === "function") {
                const $el = $("#pos-customer-select");
                if ($el.length) {
                    if ($el.hasClass("select2-hidden-accessible")) {
                        $el.select2("destroy");
                    }
                    $el.select2({
                        placeholder: "Search / Select Customer...",
                        allowClear: true,
                        width: "100%"
                    }).off("change.posCust").on("change.posCust", (e) => {
                        this.setState({ customer_id: e.target.value });
                    });
                }
            }
        }, 50);
    }

    async showAddCustomerModal() {
        const { value: formValues } = await Swal.fire({
            title: "",
            html: `
                <div style="text-align:center;padding:0.2rem 0 0.5rem;">
                    <div style="width:60px;height:60px;border-radius:20px;background:#2a69b0;display:inline-flex;align-items:center;justify-content:center;color:#ffffff;font-size:1.5rem;box-shadow:0 8px 22px rgba(42, 105, 176, 0.3);margin-bottom:0.8rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div style="font-size:1.45rem;font-weight:800;color:#1a1a1a;margin-bottom:0.2rem;letter-spacing:-0.02em;">Create New Customer</div>
                    <div style="font-size:0.8rem;color:#64748b;margin-bottom:1.2rem;font-weight:500;">Add customer details for quick POS checkout</div>

                    <div style="text-align:left;display:flex;flex-direction:column;gap:0.9rem;max-width:380px;margin:0 auto;">
                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:#262626;margin-bottom:0.35rem;display:flex;align-items:center;justify-content:space-between;">
                                <span>First Name <span style="color:#2a69b0;">*</span></span>
                                <span style="font-size:0.7rem;color:#2a69b0;font-weight:600;">Required</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-user" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-first" type="text" placeholder="e.g. Ayesha"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid #d0e4f5;border-radius:14px;font-size:0.9rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:#262626;margin-bottom:0.35rem;display:block;">
                                Last Name <span style="font-size:0.72rem;color:#94a3b8;font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="far fa-user" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-last" type="text" placeholder="e.g. Khan"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid #d0e4f5;border-radius:14px;font-size:0.9rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:#262626;margin-bottom:0.35rem;display:block;">
                                Phone Number <span style="font-size:0.72rem;color:#94a3b8;font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-phone-alt" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-phone" type="text" placeholder="e.g. +92 300 1234567"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid #d0e4f5;border-radius:14px;font-size:0.9rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:#262626;margin-bottom:0.35rem;display:block;">
                                Email Address <span style="font-size:0.72rem;color:#94a3b8;font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-envelope" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-email" type="email" placeholder="e.g. ayesha@example.com"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid #d0e4f5;border-radius:14px;font-size:0.9rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:#262626;margin-bottom:0.35rem;display:block;">
                                Address <span style="font-size:0.72rem;color:#94a3b8;font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-map-marker-alt" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#2a69b0;font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-address" type="text" placeholder="e.g. Clifton Block 5, Karachi"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid #d0e4f5;border-radius:14px;font-size:0.9rem;font-weight:600;outline:none;background:#f0f6ff;color:#1a1a1a;box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
            `,

            showCancelButton: true,
            confirmButtonText: "Save & Select Customer",
            confirmButtonColor: "#2a69b0",
            cancelButtonText: "Cancel",
            focusConfirm: false,
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                const first_name = document.getElementById("swal-cust-first").value.trim();
                const last_name = document.getElementById("swal-cust-last").value.trim();
                const phone = document.getElementById("swal-cust-phone").value.trim();
                const email = document.getElementById("swal-cust-email").value.trim();
                const address = document.getElementById("swal-cust-address").value.trim();

                if (!first_name) {
                    Swal.showValidationMessage("First Name is required!");
                    return false;
                }

                try {
                    const res = await axios.post("/admin/customers", {
                        first_name, last_name, phone, email, address
                    }, {
                        headers: { Accept: "application/json" }
                    });
                    return res.data;
                } catch (err) {
                    const msg = err.response?.data?.message || err.response?.data?.errors?.first_name?.[0] || "Could not create customer.";
                    Swal.showValidationMessage(msg);
                    return false;
                }
            }
        });

        if (formValues && formValues.customer) {
            const newCust = formValues.customer;
            this.setState(prevState => ({
                customers: [newCust, ...prevState.customers],
                customer_id: newCust.id
            }), () => this.initCustomerSelect2());

            const fullName = [newCust.first_name, newCust.last_name].filter(Boolean).join(" ");
            Swal.fire({
                icon: "success",
                title: "Customer Selected!",
                text: `${fullName} has been created & selected.`,
                confirmButtonColor: "#2a69b0",
                timer: 2000
            });
        }
    }

    loadProducts(search = "") {
        this.loadCatalog(search);
    }

    loadCatalog(search = "") {
        this.setState({ isSearching: true });
        const query = search ? `search=${encodeURIComponent(search)}` : "";
        const showProducts = !!(window.APP?.show_products !== false);
        const showServices = !!window.APP?.show_services;

        const productRequest = showProducts
            ? axios.get(`/admin/products${query ? `?${query}` : ""}`, { headers: { Accept: "application/json" } })
            : Promise.resolve({ data: { data: [] } });

        const serviceQuery = [query, "all=1"].filter(Boolean).join("&");
        const serviceRequest = showServices
            ? axios.get(`/admin/services?${serviceQuery}`, { headers: { Accept: "application/json" } })
            : Promise.resolve({ data: { data: [] } });

        const categoryRequest = showServices
            ? axios.get("/admin/cart/categories", { headers: { Accept: "application/json" } })
            : Promise.resolve({ data: { data: [] } });

        const dealQuery = [query, "all=1", "status=1"].filter(Boolean).join("&");
        const dealRequest = axios.get(`/admin/deals?${dealQuery}`, { headers: { Accept: "application/json" } })
            .catch(() => ({ data: { data: [] } }));

        Promise.all([productRequest, serviceRequest, categoryRequest, dealRequest])
            .then(([productRes, serviceRes, categoryRes, dealRes]) => {
                const products = Array.isArray(productRes.data.data) ? productRes.data.data.map(item => ({ ...item, item_type: 'product' })) : [];
                const services = Array.isArray(serviceRes.data.data) ? serviceRes.data.data.map(item => ({
                    ...item,
                    item_type: 'service',
                    category_id: item.category_id ?? item.category?.id ?? null,
                    category_name: item.category?.name || item.category_name || "Uncategorized",
                    price: Number(item.rate ?? item.price ?? 0),
                    quantity: Number.POSITIVE_INFINITY
                })) : [];
                const categories = Array.isArray(categoryRes.data.data) ? categoryRes.data.data : [];
                const deals = Array.isArray(dealRes?.data?.data) ? dealRes.data.data.map(item => ({
                    ...item,
                    item_type: 'deal',
                    price: Number(item.discounted_amount ?? 0),
                    original_price: Number(item.original_amount ?? 0),
                    discount_percentage: Number(item.discount_percentage ?? 0),
                    quantity: Number.POSITIVE_INFINITY,
                    services: Array.isArray(item.services) ? item.services : [],
                })) : [];
                this.setState({ products, services, categories, deals, catalog: [...products, ...services, ...deals], isSearching: false });
            })
            .catch(() => this.setState({ products: [], services: [], categories: [], deals: [], catalog: [], isSearching: false }));
    }

    loadCart() {
        axios.get("/admin/cart", { headers: { Accept: "application/json" } })
            .then(res => this.setState({ cart: Array.isArray(res.data) ? res.data : [] }))
            .catch(() => this.setState({ cart: [] }));
    }

    restoreCartOnLoad() {
        let savedCart = [];
        try {
            savedCart = JSON.parse(window.localStorage.getItem("pos_cart") || "[]");
            if (!Array.isArray(savedCart)) savedCart = [];
        } catch (error) {
            savedCart = [];
        }

        axios.post("/admin/cart/empty", { _method: "DELETE" })
            .then(() => {
                const restoreRequests = savedCart.flatMap(item => {
                    const quantity = Math.max(1, Number(item.pivot?.quantity || 1));
                    return Array.from({ length: quantity }, () => axios.post("/admin/cart", { barcode: item.barcode }));
                });

                return Promise.all(restoreRequests).then(() => {
                    if (savedCart.length) {
                        this.setState({ cart: savedCart });
                    } else {
                        this.resetSaleAdjustments();
                    }
                });
            })
            .catch(() => this.resetSaleAdjustments());
    }

    resetSaleAdjustments() {
        this.setState({
            cart: [],
            taxPercent: window.APP?.tax_enabled ? "8" : "",
            taxAmount: "",
            taxMode: "percent",
            discountPercent: "",
            discountAmount: "",
            discountMode: "percent",
        });
        try {
            window.localStorage.removeItem("pos_cart");
        } catch (error) {
            // Ignore storage restrictions.
        }
    }

    handleOnChangeBarcode(e) { this.setState({ barcode: e.target.value }); }

    handleScanBarcode(e) {
        e.preventDefault();
        const { barcode } = this.state;
        if (!barcode) return;
        axios.post("/admin/cart", { barcode })
            .then(() => { this.loadCart(); this.setState({ barcode: "" }); })
            .catch(err => Swal.fire("Error!", err.response?.data?.message || "Failed", "error"));
    }

    getCartItemKey(item) {
        return `${item.item_type || "product"}:${item.id}`;
    }

    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map(c => {
            if (this.getCartItemKey(c) === this.getCartItemKey({ item_type: product_id.includes(':') ? product_id.split(':')[0] : 'product', id: Number(product_id.split(':')[1] ?? product_id) })) c.pivot.quantity = qty;
            return c;
        });
        this.setState({ cart });
        if (!qty) return;
        axios.post("/admin/cart/change-qty", { product_id, quantity: qty })
            .catch(err => Swal.fire("Error!", err.response?.data?.message || "Failed", "error"));
    }

    getTotal(cart) {
        return sum(cart.map(c => c.pivot.quantity * Number(c.price ?? c.discounted_amount ?? c.rate ?? 0))).toFixed(2);
    }

    getSaleTotals(cart = this.state.cart) {
        const subtotal = Number(this.getTotal(cart));
        const taxPercent = Number(this.state.taxPercent) || 0;
        const discountPercent = Number(this.state.discountPercent) || 0;
        const taxAmount = this.state.taxMode === "amount"
            ? Number(this.state.taxAmount) || 0
            : subtotal * taxPercent / 100;
        const discountAmount = this.state.discountMode === "amount"
            ? Number(this.state.discountAmount) || 0
            : subtotal * discountPercent / 100;

        return {
            subtotal,
            taxPercent,
            taxAmount,
            discountPercent,
            discountAmount,
            total: Math.max(0, subtotal + taxAmount - discountAmount),
        };
    }

    setTaxPercent(event) {
        const taxPercent = Number(event.target.value) || 0;
        const subtotal = Number(this.getTotal(this.state.cart));
        this.setState({ taxPercent: event.target.value, taxAmount: (subtotal * taxPercent / 100).toFixed(2), taxMode: "percent" });
    }

    setTaxAmount(event) {
        const taxAmount = Number(event.target.value) || 0;
        const subtotal = Number(this.getTotal(this.state.cart));
        this.setState({ taxAmount: event.target.value, taxPercent: subtotal ? (taxAmount / subtotal * 100).toFixed(2) : "0.00", taxMode: "amount" });
    }

    setDiscountPercent(event) {
        const discountPercent = Number(event.target.value) || 0;
        const subtotal = Number(this.getTotal(this.state.cart));
        this.setState({ discountPercent: event.target.value, discountAmount: (subtotal * discountPercent / 100).toFixed(2), discountMode: "percent" });
    }

    setDiscountAmount(event) {
        const discountAmount = Number(event.target.value) || 0;
        const subtotal = Number(this.getTotal(this.state.cart));
        this.setState({ discountAmount: event.target.value, discountPercent: subtotal ? (discountAmount / subtotal * 100).toFixed(2) : "0.00", discountMode: "amount" });
    }

    handleClickDelete(product_id) {
        const itemKey = String(product_id);
        axios.post("/admin/cart/delete", { product_id: itemKey, _method: "DELETE" })
            .then(() => this.setState({ cart: this.state.cart.filter(c => this.getCartItemKey(c) !== itemKey) }));
    }

    handleEmptyCart() {
        Swal.fire({
            title: "Clear cart?", text: "All items will be removed.",
            icon: "warning", showCancelButton: true,
            confirmButtonText: "Yes, clear", confirmButtonColor: "#ef4444",
        }).then(result => {
            if (result.isConfirmed) {
                axios.post("/admin/cart/empty", { _method: "DELETE" })
                    .then(() => this.resetSaleAdjustments());
            }
        });
    }

    handleChangeSearch(e) {
        const val = e.target.value;
        this.setState({ search: val, isSearching: true });
        clearTimeout(this._searchTimer);
        this._searchTimer = setTimeout(() => this.loadProducts(val), 280);
    }

    handleSeach(e) {
        if (e.keyCode === 13) {
            clearTimeout(this._searchTimer);
            this.loadProducts(this.state.search);
        } else if (e.keyCode === 27) {
            clearTimeout(this._searchTimer);
            this.setState({ search: "" }, () => this.loadProducts(""));
        }
    }

    addProductToCart(barcode) {
        const catalogItem = [...this.state.products, ...this.state.services, ...this.state.deals].find(item => item.barcode === barcode);
        if (!catalogItem) return;

        const itemType = catalogItem.item_type || "product";
        const itemKey = `${itemType}:${catalogItem.id}`;
        const inCart = this.state.cart.find(c => this.getCartItemKey(c) === itemKey);

        if (inCart) {
            this.setState({
                cart: this.state.cart.map(c => {
                    if (this.getCartItemKey(c) === itemKey && (c.quantity === Number.POSITIVE_INFINITY || c.quantity > c.pivot.quantity)) c.pivot.quantity += 1;
                    return c;
                }),
            });
        } else {
            if (itemType === "product" && Number(catalogItem.quantity) <= 0) {
                Swal.fire({ icon: "warning", title: "Out of stock", text: catalogItem.name, timer: 1800, showConfirmButton: false });
                return;
            }
            const newItem = {
                ...catalogItem,
                price: Number(catalogItem.price ?? catalogItem.discounted_amount ?? catalogItem.rate ?? 0),
                quantity: itemType === "product" ? Number(catalogItem.quantity ?? 0) : Number.POSITIVE_INFINITY,
                pivot: { quantity: 1, product_id: catalogItem.id, user_id: 1 }
            };
            this.setState({ cart: [...this.state.cart, newItem] });
        }

        axios.post("/admin/cart", { barcode })
            .catch(err => Swal.fire("Error!", err.response?.data?.message || "Failed", "error"));
    }

    setCustomerId(e) { this.setState({ customer_id: e.target.value }); }

    handleClickSubmit() {
        const { translations, cart, customer_id, customers, activeBranch } = this.state;
        if (!cart || cart.length === 0) {
            Swal.fire({
                icon: "warning",
                title: translations["cart_empty"] || "Cart is Empty",
                text: "Please add products, services, or deals to the cart before placing an order.",
                confirmButtonColor: "#2a69b0"
            });
            return;
        }

        const saleTotals = this.getSaleTotals(cart);
        const total = saleTotals.total.toFixed(2);
        const currency = window.APP?.currency_symbol || "PKR";
        const itemRates = cart.reduce((rates, item) => {
            rates[this.getCartItemKey(item)] = Number(item.price ?? item.discounted_amount ?? item.rate ?? 0);
            return rates;
        }, {});
        const orderPayload = {
            customer_id,
            item_rates: itemRates,
            subtotal: saleTotals.subtotal,
            tax_percent: saleTotals.taxPercent,
            tax_amount: saleTotals.taxAmount,
            discount_percent: saleTotals.discountPercent,
            discount_amount: saleTotals.discountAmount,
        };

        const selectedCust = (customers || []).find(c => String(c.id) === String(customer_id));
        const customerName = selectedCust
            ? [selectedCust.first_name, selectedCust.last_name].filter(Boolean).join(" ")
            : "Walk-in Customer";
        const customerPhone = selectedCust?.phone ? selectedCust.phone : "";
        const totalQty = cart.reduce((sum, item) => sum + Number(item.pivot?.quantity || 0), 0);

        const itemsRowsHtml = cart.map(item => {
            const unitPrice = Number(item.price ?? item.discounted_amount ?? item.rate ?? 0);
            const qty = Number(item.pivot?.quantity || 0);
            const lineTotal = unitPrice * qty;
            const isDeal = item.item_type === 'deal' || item.item_type === 2;
            const isService = item.item_type === 'service' || item.item_type === 1;
            const typeBadge = isDeal
                ? `<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px;border-radius:5px;font-size:0.67rem;font-weight:700;background:#eff6ff;color:#2a69b0;border:1px solid #bfdbfe;"><i class="fas fa-tags" style="font-size:0.62rem;"></i> Deal</span>`
                : isService
                ? `<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px;border-radius:5px;font-size:0.67rem;font-weight:700;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;"><i class="fas fa-spa" style="font-size:0.62rem;"></i> Service</span>`
                : `<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px;border-radius:5px;font-size:0.67rem;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;"><i class="fas fa-box-open" style="font-size:0.62rem;"></i> Product</span>`;

            return `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 6px; text-align: left; vertical-align: middle;">
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.84rem; line-height: 1.3;">${item.name}</div>
                        <div style="margin-top: 3px;">${typeBadge}</div>
                    </td>
                    <td style="padding: 8px 6px; text-align: center; vertical-align: middle; font-weight: 700; color: #334155; font-size: 0.84rem;">
                        ${qty}
                    </td>
                    <td style="padding: 8px 6px; text-align: right; vertical-align: middle; color: #64748b; font-size: 0.82rem; font-family: monospace;">
                        ${this.formatAmount(unitPrice)}
                    </td>
                    <td style="padding: 8px 6px; text-align: right; vertical-align: middle; font-weight: 800; color: #0f172a; font-size: 0.85rem; font-family: monospace;">
                        ${this.formatAmount(lineTotal)}
                    </td>
                </tr>
            `;
        }).join("");

        const modalHtml = `
            <div style="text-align: left; font-family: inherit; color: #1e293b;">
                <!-- Header Card -->
                <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 1.5px solid #e2e8f0; margin-bottom: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #2a69b0 0%, #1a4d8c 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.25rem; box-shadow: 0 4px 12px rgba(42, 105, 176, 0.25); flex-shrink: 0;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; line-height: 1.2;">
                            Order Confirmation & Payment
                        </div>
                        <div style="font-size: 0.76rem; color: #64748b; margin-top: 2px;">
                            Review purchase details below. Only Received Amount is editable.
                        </div>
                    </div>
                </div>

                <!-- Customer & Summary Details Pill -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 12px; margin-bottom: 12px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; font-size: 0.78rem;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="color: #64748b; font-weight: 600;"><i class="fas fa-user-circle" style="color: #2a69b0;"></i> Customer:</span>
                        <strong style="color: #0f172a;">${customerName}</strong>
                        ${customerPhone ? `<span style="color: #64748b; font-size: 0.72rem;">(${customerPhone})</span>` : ""}
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="background: #eff6ff; color: #2a69b0; border: 1px solid #bfdbfe; padding: 2px 7px; border-radius: 6px; font-weight: 700; font-size: 0.72rem;">
                            ${totalQty} Total Items
                        </span>
                        ${activeBranch ? `<span style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 2px 7px; border-radius: 6px; font-weight: 600; font-size: 0.72rem;">${activeBranch.name}</span>` : ""}
                    </div>
                </div>

                <!-- Selected Items List (Scrollable Table) -->
                <div style="border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 12px; background: #fff;">
                    <div style="background: #f1f5f9; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 0.72rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                        Selected Services & Deals (View-Only)
                    </div>
                    <div style="max-height: 170px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 0.72rem; color: #64748b; text-transform: uppercase;">
                                    <th style="padding: 6px 8px; text-align: left; font-weight: 700;">Item</th>
                                    <th style="padding: 6px 8px; text-align: center; font-weight: 700; width: 45px;">Qty</th>
                                    <th style="padding: 6px 8px; text-align: right; font-weight: 700; width: 75px;">Rate</th>
                                    <th style="padding: 6px 8px; text-align: right; font-weight: 700; width: 85px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsRowsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Order Totals Breakdown -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #64748b; margin-bottom: 5px;">
                        <span>Subtotal</span>
                        <strong style="color: #1e293b; font-family: monospace;">${currency} ${this.formatAmount(saleTotals.subtotal)}</strong>
                    </div>
                    ${saleTotals.discountAmount > 0 ? `
                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #059669; margin-bottom: 5px;">
                            <span>Discount ${saleTotals.discountPercent ? `(${saleTotals.discountPercent}%)` : ''}</span>
                            <strong style="font-family: monospace;">-${currency} ${this.formatAmount(saleTotals.discountAmount)}</strong>
                        </div>
                    ` : ""}
                    ${saleTotals.taxAmount > 0 ? `
                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #2a69b0; margin-bottom: 5px;">
                            <span>Sales Tax ${saleTotals.taxPercent ? `(${saleTotals.taxPercent}%)` : ''}</span>
                            <strong style="font-family: monospace;">+${currency} ${this.formatAmount(saleTotals.taxAmount)}</strong>
                        </div>
                    ` : ""}
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 6px; border-top: 1.5px dashed #cbd5e1; margin-top: 6px;">
                        <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">Payable Total</span>
                        <span style="font-size: 1.15rem; font-weight: 900; color: #2a69b0; font-family: monospace;">${currency} ${this.formatAmount(saleTotals.total)}</span>
                    </div>
                </div>

                <!-- Editable Cash Received Input Box -->
                <div style="background: #ffffff; border: 2px solid #2a69b0; border-radius: 12px; padding: 12px 14px; box-shadow: 0 4px 14px rgba(42, 105, 176, 0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="swal-received-amount" style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-hand-holding-usd" style="color: #2a69b0; font-size: 0.95rem;"></i>
                            Received Amount <span style="font-size: 0.72rem; color: #2a69b0; font-weight: 700; background: #eff6ff; padding: 1px 6px; border-radius: 4px; border: 1px solid #bfdbfe;">EDITABLE</span>
                        </label>
                        <span style="font-size: 0.74rem; font-weight: 700; color: #64748b;">
                            Due: <b style="color: #0f172a;">${currency} ${total}</b>
                        </span>
                    </div>

                    <div style="position: relative; display: flex; align-items: center;">
                        <span style="position: absolute; left: 12px; font-weight: 800; color: #64748b; font-size: 1rem; pointer-events: none;">${currency}</span>
                        <input
                            id="swal-received-amount"
                            type="number"
                            step="0.01"
                            min="${total}"
                            value="${total}"
                            style="width: 100%; box-sizing: border-box; padding: 9px 12px 9px 48px; font-size: 1.2rem; font-weight: 800; color: #0f172a; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;"
                            autocomplete="off"
                        />
                    </div>

                    <!-- Quick Preset Buttons -->
                    <div style="display: flex; gap: 5px; margin-top: 8px; flex-wrap: wrap;">
                        <button type="button" id="swal-btn-exact" style="flex: 1; min-width: 80px; padding: 5px 8px; border: 1px solid #93c5fd; background: #eff6ff; color: #1e40af; border-radius: 6px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            Exact (${total})
                        </button>
                        <button type="button" class="swal-preset-btn" data-add="500" style="padding: 5px 9px; border: 1px solid #e2e8f0; background: #ffffff; color: #334155; border-radius: 6px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            +500
                        </button>
                        <button type="button" class="swal-preset-btn" data-add="1000" style="padding: 5px 9px; border: 1px solid #e2e8f0; background: #ffffff; color: #334155; border-radius: 6px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            +1000
                        </button>
                        <button type="button" class="swal-preset-btn" data-add="5000" style="padding: 5px 9px; border: 1px solid #e2e8f0; background: #ffffff; color: #334155; border-radius: 6px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            +5000
                        </button>
                    </div>

                    <!-- Change / Short Amount Live Calculation -->
                    <div id="swal-change-box" style="margin-top: 9px; padding: 7px 10px; border-radius: 7px; background: #f0fdf4; border: 1px solid #bbf7d0; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.78rem; font-weight: 700; color: #166534;" id="swal-change-label">Change Due:</span>
                        <span style="font-size: 0.95rem; font-weight: 800; color: #15803d; font-family: monospace;" id="swal-change-val">${currency} 0.00</span>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            html: modalHtml,
            width: 550,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle" style="margin-right: 6px;"></i> Confirm & Place Order',
            confirmButtonColor: "#2a69b0",
            cancelButtonText: "Cancel",
            cancelButtonColor: "#64748b",
            focusConfirm: false,
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            didOpen: () => {
                const inputEl = document.getElementById("swal-received-amount");
                const changeBox = document.getElementById("swal-change-box");
                const changeLabel = document.getElementById("swal-change-label");
                const changeVal = document.getElementById("swal-change-val");
                const targetTotal = Number(total);

                const updateChange = () => {
                    if (!inputEl || !changeBox || !changeLabel || !changeVal) return;
                    const val = Number(inputEl.value) || 0;
                    const diff = val - targetTotal;
                    if (diff >= -0.001) {
                        changeBox.style.background = "#f0fdf4";
                        changeBox.style.borderColor = "#bbf7d0";
                        changeLabel.style.color = "#166534";
                        changeLabel.textContent = "Change Due:";
                        changeVal.style.color = "#15803d";
                        changeVal.textContent = `${currency} ${diff > 0 ? diff.toFixed(2) : "0.00"}`;
                    } else {
                        changeBox.style.background = "#fef2f2";
                        changeBox.style.borderColor = "#fecaca";
                        changeLabel.style.color = "#991b1b";
                        changeLabel.textContent = "Remaining Due:";
                        changeVal.style.color = "#dc2626";
                        changeVal.textContent = `${currency} ${Math.abs(diff).toFixed(2)}`;
                    }
                };

                if (inputEl) {
                    inputEl.addEventListener("input", updateChange);
                    inputEl.addEventListener("focus", () => {
                        inputEl.style.background = "#ffffff";
                        inputEl.style.borderColor = "#2a69b0";
                    });
                    inputEl.addEventListener("blur", () => {
                        inputEl.style.background = "#f8fafc";
                    });
                    setTimeout(() => {
                        inputEl.focus();
                        inputEl.select();
                    }, 50);
                }

                const exactBtn = document.getElementById("swal-btn-exact");
                if (exactBtn && inputEl) {
                    exactBtn.addEventListener("click", () => {
                        inputEl.value = targetTotal.toFixed(2);
                        updateChange();
                        inputEl.focus();
                    });
                }

                document.querySelectorAll(".swal-preset-btn").forEach(btn => {
                    btn.addEventListener("click", () => {
                        if (!inputEl) return;
                        const add = Number(btn.getAttribute("data-add")) || 0;
                        const current = Number(inputEl.value) || targetTotal;
                        inputEl.value = (current + add).toFixed(2);
                        updateChange();
                        inputEl.focus();
                    });
                });
            },
            preConfirm: () => {
                const inputEl = document.getElementById("swal-received-amount");
                const amount = inputEl ? inputEl.value : total;
                const numAmount = Number(amount);
                if (isNaN(numAmount) || numAmount < Number(total)) {
                    Swal.showValidationMessage(`Received amount must be at least ${currency} ${total}`);
                    return false;
                }
                return axios.post("/admin/orders", { ...orderPayload, amount: numAmount.toFixed(2) })
                    .then(res => {
                        this.resetSaleAdjustments();
                        this.loadCart();
                        if (res.data?.order) {
                            this.setState({ lastPlacedOrder: res.data.order });
                        }
                        return res.data;
                    })
                    .catch(err => {
                        Swal.showValidationMessage(err.response?.data?.message || "Error creating order");
                        return false;
                    });
            },
            didClose: () => {
                setTimeout(() => {
                    document.querySelectorAll(".swal2-container").forEach(el => el.remove());
                    document.body.classList.remove("swal2-shown", "swal2-height-auto");
                    document.body.style.overflow = "";
                    document.querySelectorAll("[aria-hidden='true']").forEach(el => el.removeAttribute("aria-hidden"));
                    window.focus();
                }, 100);
            }
        }).then(result => {
            if (result.isConfirmed && result.value?.order) {
                this.showReceipt(result.value.order);
            }
        });
    }

    handleOpenLastInvoice() {
        if (this.state.lastPlacedOrder) {
            this.showReceipt(this.state.lastPlacedOrder);
        } else {
            Swal.fire({
                icon: "info",
                title: "No Recent Invoice",
                text: "Please place an order first to view its invoice.",
                confirmButtonColor: "#2a69b0",
                timer: 2500,
            });
        }
    }

buildSrbBarcodeSvg(reference = "SRB-000000") {
    const displayRef = String(reference).toUpperCase(); // dash ke sath dikhane ke liye
    const cleanRef = displayRef.replace(/[^A-Z0-9]/g, "") || "SRB000000";
    const bars = [
        "101001101101", "110100101011", "110101001011", "110101011001", "101101001011",
        "110011010011", "110110100101", "110110101001", "110010101011", "110010110101"
    ];

    let pattern = "";
    for (let i = 0; i < cleanRef.length; i += 1) {
        const index = (cleanRef.charCodeAt(i) % 10 + i) % 10;
        pattern += bars[index % bars.length];
    }

    const barSegments = [];
    for (let i = 0; i < pattern.length; i += 1) {
        if (pattern[i] === "1") {
            const width = 2;
            const x = barSegments.length * 2.2;
            barSegments.push(`<rect x="${x}" y="8" width="${width}" height="44" fill="#111827" />`);
        }
    }

    return `
        <svg xmlns="http://www.w3.org/2000/svg" width="240" height="72" viewBox="0 0 240 72" role="img" aria-label="Barcode">
            <g fill="#111827">
                ${barSegments.join("")}
            </g>
            <text x="120" y="67" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" fill="#111827">${displayRef}</text>
        </svg>
    `;
}

    buildSrbLogoSvg() {
        return `
            <svg xmlns="http://www.w3.org/2000/svg" width="220" height="54" viewBox="0 0 220 54" role="img" aria-label="SRB logo">
                <defs>
                    <linearGradient id="srbGradient" x1="0%" x2="100%" y1="0%" y2="0%">
                        <stop offset="0%" stop-color="#0f172a" />
                        <stop offset="100%" stop-color="#1d4ed8" />
                    </linearGradient>
                </defs>
                <rect x="0" y="0" width="220" height="54" rx="12" fill="#f8fafc" stroke="#dbeafe" />
                <rect x="12" y="11" width="32" height="32" rx="8" fill="url(#srbGradient)" />
                <text x="28" y="33" text-anchor="middle" font-size="18" font-weight="700" fill="#ffffff" font-family="Arial, sans-serif">S</text>
                <text x="55" y="35" font-size="24" font-weight="800" fill="#0f172a" font-family="Arial, sans-serif">RB</text>
                <text x="120" y="36" font-size="11" letter-spacing="1.5" fill="#475569" font-family="Arial, sans-serif">SECURE PAYMENT</text>
            </svg>
        `;
    }

    showReceipt(order) {
        if (!order) return;
        const currency = window.APP.currency_symbol || "";
        const customerName = order.customer
            ? `${order.customer.first_name || ""} ${order.customer.last_name || ""}`.trim() || order.customer.name || "Walk-in Customer"
            : "Walk-in Customer";
        const createdAtObj = order.created_at ? new Date(order.created_at) : new Date();
        const invoiceDate = createdAtObj.toLocaleDateString('en-US');
        const invoiceTime = createdAtObj.toLocaleTimeString('en-US', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        }) + ' PST';
        const createdAt = `${invoiceDate}, ${invoiceTime}`;
        const branchName = order.branch?.name || "Main Branch";
        const counterName = order.counter?.name || "Counter 1";
        const invoiceNo = order.invoice_no || `POS-${String(order.id).padStart(3, "0")}`;
        const headerHTML = CommonHelper.getReceiptHeaderHTML({
            branchName: branchName,
            branchAddress: order.branch?.address,
            branchPhone: order.branch?.phone,
        });
        const footerHTML = CommonHelper.getReceiptFooterHTML(createdAt);

        const items = (order.items || []).map(item => {
            const quantity = Number(item.quantity || 0);
            const lineTotal = Number(item.price || 0);
            const unitPrice = quantity > 0 ? (lineTotal / quantity) : lineTotal;
            const name = item.item_name || (Number(item.item_type) === 2 ? (item.deal?.name || "Deal Package") : Number(item.item_type) === 1 ? (item.service?.name || "Service") : (item.product?.name || "Product"));

            return `<div class="receipt-item-block">
                <div class="receipt-item-name">${name}</div>
                <div class="receipt-item-row">
                    <span class="col-item"></span>
                    <span class="col-qty">${quantity}</span>
                    <span class="col-price">${unitPrice.toFixed(2)}</span>
                    <span class="col-total">${lineTotal.toFixed(2)}</span>
                </div>
            </div>`;
        }).join("");

        const subtotal = Number(order.subtotal ?? (order.items || []).reduce((s, i) => s + Number(i.price || 0), 0));
        const taxPercent = Number(order.tax_percent || 0);
        const taxAmount = Number(order.tax_amount || 0);
        const discountPercent = Number(order.discount_percent || 0);
        const discountAmount = Number(order.discount_amount || 0);
        const total = Number(order.total_amount ?? Math.max(0, subtotal + taxAmount - discountAmount));
        const received = (order.payments || []).reduce((s, p) => s + Number(p.amount || 0), 0);
        const changeDue = received > total ? received - total : 0;
        const valueForSales = Math.max(0, subtotal - discountAmount);

        const paymentStatus = received > 0 ? "PAID (CASH)" : "UNPAID";
        const salesByName = order.user?.name || (window.APP && window.APP.user_name) || null;

        const barcodeSvg = this.buildSrbBarcodeSvg(invoiceNo);

        const metaRows = [
            { label: "Receipt No.", value: invoiceNo },
            paymentStatus ? { label: "Payment Status", value: paymentStatus } : null,
            { label: "Invoice Date", value: invoiceDate },
            { label: "Time", value: invoiceTime },
            salesByName ? { label: "Sales By", value: salesByName } : null,
            { label: "Terminal", value: counterName },
            { label: "Customer", value: customerName },
        ].filter(Boolean);

        const metaHTML = metaRows.map(row =>
            `<div class="meta-row"><span class="meta-label">${row.label}</span><span class="meta-value">${row.value}</span></div>`
        ).join("");

        const srbInvoiceId = order.srb_invoice_id;
        const srbQrCodeLink = order.srb_qr_code_link;
        const srbQrImage = srbQrCodeLink
            ? `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(srbQrCodeLink)}`
            : "";
        const srbFooter = srbInvoiceId ? `
        <div class="srb-footer">
            <div class="srb-row" style="display:flex;align-items:center;justify-content:center;gap:12px;">
                <div class="srb-logo-cell logos-srb" style="display:flex;align-items:center;justify-content:center;width:95px;height:95px;margin-top:20px;">
                    <img src="${imageUrl}" alt="SRB Logo" style="width:95px !important;height:95px !important;object-fit:contain;display:block;">
                </div>
                <div class="srb-code-cell" style="display:flex;flex-direction:column;align-items:center;">
                    <div class="srb-label">SRB Invoice No.</div>
                    <div class="srb-invoice-id">${srbInvoiceId}</div>
                    ${srbQrImage ? `<div><img class="srb-qr" src="${srbQrImage}" style="width:80px !important;height:80px !important;display:block;"></div>` : ""}
                </div>
            </div>
            <div class="srb-verify">Scan to verify this invoice</div>
        </div>` : "";

        const receiptHTML = `
            <div id="thermal-receipt" class="thermal-receipt">
                ${headerHTML}
                <div class="receipt-divider"></div>

                <div class="meta-grid">${metaHTML}</div>
                <div class="receipt-divider-double"></div>
                <div class="receipt-table-head"><span class="col-item">ITEM DESCRIPTION</span><span class="col-qty">QTY</span><span class="col-price">PRICE</span><span class="col-total">AMOUNT</span></div>
                <div class="receipt-divider"></div>
                ${items}
                <div class="receipt-divider"></div>
                <div class="receipt-summary">
                    <div class="summary-row"><span>SUBTOTAL:</span><span>${currency} ${subtotal.toFixed(2)}</span></div>
                    ${discountAmount > 0 ? `<div class="summary-row"><span>Discount (${discountPercent.toFixed(2)}%)</span><span>-${currency} ${discountAmount.toFixed(2)}</span></div>` : ""}
                    ${discountAmount > 0 ? `<div class="summary-row"><span>Value for Sales</span><span>${currency} ${valueForSales.toFixed(2)}</span></div>` : ""}
                    ${taxAmount > 0 ? `<div class="summary-row"><span>Total Sales Tax (${taxPercent.toFixed(2)}%)</span><span>${currency} ${taxAmount.toFixed(2)}</span></div>` : ""}
                    <div class="receipt-divider"></div>
                    <div class="summary-row"><span>Total Value Including Sales Tax</span><span>${currency} ${subtotal.toFixed(2)}</span></div>
                    <div class="receipt-divider"></div>
                    <div class="summary-row grand-total"><span>NET TOTAL</span><span>${currency} ${total.toFixed(2)}</span></div>
                    <div class="receipt-divider"></div>
                    <div class="summary-row"><span>Cash</span><span>${currency} ${received.toFixed(2)}</span></div>
                    <div class="summary-row"><span>Change Due</span><span>${currency} ${changeDue.toFixed(2)}</span></div>
                </div>

                <div class="receipt-divider"></div>

                <div class="terms-block">
                    <div class="terms-title">Terms &amp; Conditions of Sale</div>
                    -Amount against services and products are non refundable and non exchangeable.<br>
                    -Advance payments for any service are non refundable.<br>
                    -Make up advances are non refundable.
                </div>
                ${srbFooter}
                <div class="receipt-divider"></div>
                ${footerHTML}
            </div>`;

        Swal.fire({
            html: `<style>
                .receipt-modal.swal2-popup {
                    background: #ffffff !important;
                    border-radius: 16px !important;
                    padding: 16px 12px 20px !important;
                    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35) !important;
                    max-width: 410px !important;
                }
                .receipt-modal .swal2-html-container {
                    margin: 0 !important;
                    padding: 2px 6px !important;
                    overflow-y: auto !important;
                    overflow-x: hidden !important;
                    max-height: 72vh !important;
                    text-align: left !important;
                }
                .receipt-modal .swal2-actions {
                    margin-top: 14px !important;
                    gap: 10px !important;
                }
                ${CommonHelper.getReceiptStyles()}
            </style>
            ${receiptHTML}`,
            width: 410,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-print" style="margin-right: 6px;"></i> Print Receipt',
            confirmButtonColor: "#2a69b0",
            cancelButtonText: "Close",
            customClass: { popup: "receipt-modal" },
            didClose: () => {
                setTimeout(() => {
                    document.querySelectorAll(".swal2-container").forEach(el => el.remove());
                    document.body.classList.remove("swal2-shown", "swal2-height-auto");
                    document.body.style.overflow = "";
                    document.querySelectorAll("[aria-hidden='true']").forEach(el => el.removeAttribute("aria-hidden"));
                    window.focus();
                }, 100);
            }
        }).then(result => {
            if (result.isConfirmed) {
                this.printReceipt(order, receiptHTML);
            }
        });
    }

    printReceipt(order, receiptHtmlContent = null) {
        if (!order) return;
        let content = receiptHtmlContent;
        if (!content) {
            const el = document.getElementById("thermal-receipt");
            content = el ? el.outerHTML : "";
        }
        if (!content) return;

        const printStyles = CommonHelper.getThermalPrintStyles();
        const title = `Receipt #${order.invoice_no || order.id}`;

        // Open print window synchronously while user gesture is active
        const printWin = window.open("", "_blank", "width=450,height=700");

        // Immediately close SweetAlert and purge any screen-blocking overlays
        try {
            Swal.close();
        } catch (e) {}
        document.querySelectorAll(".swal2-container").forEach(el => el.remove());
        document.body.classList.remove("swal2-shown", "swal2-height-auto");
        document.body.style.overflow = "";
        document.querySelectorAll("[aria-hidden='true']").forEach(el => el.removeAttribute("aria-hidden"));

        if (!printWin) {
            Swal.fire({
                icon: "warning",
                title: "Popup Blocked",
                text: "Please allow popups for this site in your browser to print receipts.",
                confirmButtonColor: "#2a69b0",
            });
            return;
        }

        printWin.document.open();
        printWin.document.write(`<!doctype html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>${title}</title>
            <style>${printStyles}</style>
        </head>
        <body>
            ${content}
        </body>
        </html>`);
        printWin.document.close();

        const restoreParentUI = () => {
            window.focus();
            document.querySelectorAll(".swal2-container").forEach(el => el.remove());
            document.body.classList.remove("swal2-shown", "swal2-height-auto");
            document.body.style.overflow = "";
            document.querySelectorAll("[aria-hidden='true']").forEach(el => el.removeAttribute("aria-hidden"));
        };

        const doPrint = () => {
            try {
                printWin.focus();
                printWin.print();
                printWin.close();
            } catch (e) {}
            restoreParentUI();
        };

        const images = printWin.document.images;
        if (images && images.length > 0) {
            let loaded = 0;
            const checkDone = () => {
                loaded++;
                if (loaded >= images.length) setTimeout(doPrint, 100);
            };
            for (let i = 0; i < images.length; i++) {
                if (images[i].complete) {
                    loaded++;
                } else {
                    images[i].onload = checkDone;
                    images[i].onerror = checkDone;
                }
            }
            if (loaded >= images.length) setTimeout(doPrint, 100);
        } else {
            setTimeout(doPrint, 100);
        }
    }

    // ''‚''‚¬ Render helpers ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬

    renderCartItem(c) {
        const { hoveredRemove } = this.state;
        const currency = window.APP.currency_symbol || "";
        const itemKey = this.getCartItemKey(c);
        const unitPrice = Number(c.price ?? c.rate ?? 0);
        const lineTotal = (unitPrice * c.pivot.quantity).toFixed(2);
        return (
            <div key={itemKey} style={S.cartRow}>
                <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={S.cartItemName} title={c.name}>{c.name}</div>
                    <div style={S.cartItemPrice}>{this.formatAmount(unitPrice)} each</div>
                </div>
                <div style={S.qtyControl}>
                    <button style={S.qtyBtn}
                        onClick={() => this.handleChangeQty(itemKey, Math.max(1, Number(c.pivot.quantity) - 1))}>-</button>
                    <input type="number" style={S.qtyInput} value={c.pivot.quantity} min={1}
                        onChange={e => this.handleChangeQty(itemKey, e.target.value)} />
                    <button style={S.qtyBtn}
                        onClick={() => this.handleChangeQty(itemKey, Number(c.pivot.quantity) + 1)}>+</button>
                </div>
                <div style={S.cartItemTotal}>{this.formatAmount(lineTotal)}</div>
                <button
                    style={{ ...S.removeBtn, color: hoveredRemove === itemKey ? "#ef4444" : "#d1d5db", background: hoveredRemove === itemKey ? "#fef2f2" : "none" }}
                    onMouseEnter={() => this.setState({ hoveredRemove: itemKey })}
                    onMouseLeave={() => this.setState({ hoveredRemove: null })}
                    onClick={() => this.handleClickDelete(itemKey)}>
                    <i className="fas fa-times"></i>
                </button>
            </div>
        );
    }

    renderProductTile(p) {
        const { hoveredTile } = this.state;
        const currency = window.APP.currency_symbol || "";
        const isHovered = hoveredTile === p.id;
        const isService = p.item_type === "service";
        const stockValue = Number.isFinite(Number(p.quantity)) ? Number(p.quantity) : null;
        const isLow = !isService && stockValue !== null && window.APP.warning_quantity > stockValue;
        const displayPrice = Number(p.price ?? p.rate ?? 0);

        return (
            <div key={`${p.item_type || "product"}:${p.id}`}
                style={{ ...S.productTile, borderColor: isHovered ? "#0ea5b0" : "#e8ecf2", boxShadow: isHovered ? "0 6px 18px rgba(14,165,176,0.18)" : "none", transform: isHovered ? "translateY(-2px)" : "none" }}
                onMouseEnter={() => this.setState({ hoveredTile: p.id })}
                onMouseLeave={() => this.setState({ hoveredTile: null })}
                onClick={() => this.addProductToCart(p.barcode)}
                title={p.name}>
                <div style={{ position: "absolute", top: 0, left: 0, right: 0, height: "3px", background: isHovered ? "#0ea5b0" : "transparent" }} />
                <div style={S.productTileImgWrap}>
                    {p.image_url
                        ? <img src={p.image_url} alt={p.name} style={S.productTileImg}
                            onError={e => { e.target.style.display = "none"; e.target.nextSibling.style.display = "flex"; }} />
                        : null}
                    <span style={{ display: p.image_url ? "none" : "flex", alignItems: "center", justifyContent: "center", width: "100%", height: "100%", fontSize: "1.6rem", color: "#cbd5e1" }}>
                        <i className={`fas fa-${isService ? "tags" : "box-open"}`}></i>
                    </span>
                </div>
                <div style={S.productTileName}>{p.name}</div>
                <span style={S.productTileStock(isLow || isService ? false : isLow)}>{isService ? "Service" : (isLow ? "" : "") + `${stockValue ?? 0} left`}</span>
                <div style={S.productTilePrice}>{currency}{displayPrice.toFixed(2)}</div>
            </div>
        );
    }

    // ''‚''‚¬ Gate screen ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬

    renderGateScreen() {
        const { branchVerified, activeBranch } = this.state;
        const step = branchVerified ? 2 : 1;

        return (
            <div style={S.gateScreen}>
                <div style={S.gateCard}>
                    {/* Step indicators */}
                    <div style={S.stepIndicator}>
                        <div style={S.stepDot(step === 1, step > 1)}>
                            {step > 1 ? <i className="fas fa-check" style={{ fontSize: "0.7rem" }}></i> : "1"}
                        </div>
                        <div style={S.stepLine}></div>
                        <div style={S.stepDot(step === 2, false)}>2</div>
                    </div>

                    <div style={S.gateIcon}>
                        <i className={`fas fa-${step === 1 ? "code-branch" : "desktop"}`}></i>
                    </div>

                    <div style={S.gateTitle}>
                        {step === 1 ? "Branch Access Required" : "Counter Access Required"}
                    </div>
                    <div style={S.gateSubtitle}>
                        {step === 1
                            ? "Select your branch and enter the branch password to continue."
                            : "Select your counter and enter the counter password to open the POS."}
                    </div>

                    {step === 2 && activeBranch && (
                        <div style={S.gateBranchBadge}>
                            <i className="fas fa-check-circle"></i>
                            {activeBranch.name} ({activeBranch.code})
                        </div>
                    )}

                    <button
                        style={S.gateBtn}
                        onClick={step === 1 ? this.showBranchModal : this.showCounterModal}>
                        <i className={`fas fa-${step === 1 ? "key" : "unlock"} mr-2`}></i>
                        {step === 1 ? "Enter Branch Password" : "Enter Counter Password"}
                    </button>
                </div>
            </div>
        );
    }

    // ''‚''‚¬ Main render ''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚''‚¬

    render() {
        const { cart, products, services, categories, deals = [], catalogMode = "services", customers, customer_id, barcode, translations,
            gateChecked, branchVerified, counterVerified,
            activeBranch, activeCounter, search, isSearching, searchFocused } = this.state;

        const filteredDeals = (deals || []).filter(deal => {
            if (!search) return true;
            const term = search.toLowerCase();
            const matchName = String(deal.name || "").toLowerCase().includes(term);
            const matchBarcode = String(deal.barcode || "").toLowerCase().includes(term);
            const matchService = (deal.services || []).some(s => String(s.name || "").toLowerCase().includes(term));
            return matchName || matchBarcode || matchService;
        });

        const currency = window.APP.currency_symbol || "";
        const total = this.getTotal(cart);
        const saleTotals = this.getSaleTotals(cart);
        const taxEnabled = !!window.APP?.tax_enabled;
        const discountEnabled = !!window.APP?.discount_enabled;
        const editableItemRate = !!window.APP?.editable_item_rate;
        const itemCount = cart.reduce((s, c) => s + Number(c.pivot.quantity), 0);
        const catalogItems = [...products, ...services];
        const showProducts = !!(window.APP?.show_products !== false);
        const showServices = !!window.APP?.show_services;
        const activeCategory = this.state.activeCategory || "all";
        const derivedCategories = categories.length
            ? categories
            : Array.from(new Map(services.filter(item => item.category_id).map(item => [item.category_id, { id: item.category_id, name: item.category_name }])).values());
        const filterOptions = [
            { key: "all", label: "All" },
            ...(showProducts ? [{ key: "product", label: "Products" }] : []),
            ...((showServices ? derivedCategories : []).map(category => ({ key: `category:${category.id}`, label: category.name }))),
        ];
        const filteredCatalog = catalogItems.filter(item => {
            if (activeCategory === "all") return true;
            if (activeCategory === "product") return (item.item_type || "product") === "product";
            if (String(activeCategory).startsWith("category:")) {
                const categoryId = Number(String(activeCategory).split(":")[1]);
                return item.item_type === "service" && Number(item.category_id) === categoryId;
            }
            return true;
        });
        const groupedCatalog = activeCategory === "all" && showServices
            ? [
                ...(showProducts && filteredCatalog.some(item => item.item_type === "product") ? [{ key: "products", label: "Products", items: filteredCatalog.filter(item => item.item_type === "product") }] : []),
                ...derivedCategories.map(category => ({
                    key: `category:${category.id}`,
                    label: category.name,
                    items: filteredCatalog.filter(item => item.item_type === "service" && Number(item.category_id) === Number(category.id)),
                })).filter(group => group.items.length > 0),
                ...(filteredCatalog.some(item => item.item_type === "service" && !item.category_id) ? [{ key: "uncategorized", label: "Uncategorized", items: filteredCatalog.filter(item => item.item_type === "service" && !item.category_id) }] : []),
            ]
            : [{ key: activeCategory, label: "", items: filteredCatalog }];

        // Not yet checked session status
        if (!gateChecked) {
            return (
                <div style={{ ...S.gateScreen, height: "calc(100vh - 106px)" }}>
                    <div style={{ color: "#2a69b0", fontSize: "2rem" }}>
                        <i className="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            );
        }

        // Gate not passed ''‚'' show lock screen
        if (!branchVerified || !counterVerified) {
            return <div style={S.posWrapper}>{this.renderGateScreen()}</div>;
        }

        // ════════════════════════════════════════════════════════════════════════════════
        // POS screen
        // ════════════════════════════════════════════════════════════════════════════════
        const shellStyle = {
            width: "100%",
            height: "100%",
            padding: 0,
            margin: 0,
            background: "#f1f5f9",
            boxSizing: "border-box",
            display: "flex",
            alignItems: "stretch",
            justifyContent: "stretch",
            overflow: "hidden",
        };

        const panelStyle = {
            width: "100%",
            height: "100%",
            maxWidth: "100%",
            background: "#f1f5f9",
            borderRadius: 0,
            boxShadow: "none",
            border: "none",
            overflow: "hidden",
            display: "flex",
            flexDirection: "column",
            flex: 1,
        };

        const getCategoryIcon = (key, label) => {
            const l = String(label || "").toLowerCase().trim();
            if (key === "all") return "fas fa-border-all";
            if (key === "product") return "fas fa-box-open";

            // Facial & Skin care
            if (l.includes("facial") || l.includes("skin") || l.includes("hydra") || l.includes("glow") || l.includes("acne") || l.includes("whitening")) {
                return "fas fa-smile-beam";
            }

            // Makeup & Bridal
            if (l.includes("makeup") || l.includes("make-up") || l.includes("bride") || l.includes("bridal") || l.includes("party make") || l.includes("baraat") || l.includes("valima") || l.includes("nikkah") || l.includes("mehndi")) {
                return "fas fa-gem";
            }

            // Hair & Styling & Cut
            if (l.includes("hair") || l.includes("color") || l.includes("colour") || l.includes("cut") || l.includes("scalp") || l.includes("styling") || l.includes("keratin") || l.includes("rebound") || l.includes("blowdry")) {
                return "fas fa-cut";
            }

            // Nails, Hands & Feet
            if (l.includes("nail") || l.includes("hand") || l.includes("feet") || l.includes("foot") || l.includes("manicure") || l.includes("pedicure") || l.includes("acrylic") || l.includes("gel")) {
                return "fas fa-hand-holding-heart";
            }

            // Massage & Hammam & Spa & Body
            if (l.includes("hammam") || l.includes("bath") || l.includes("tub")) {
                return "fas fa-hot-tub";
            }
            if (l.includes("massage") || l.includes("body") || l.includes("spa") || l.includes("relax") || l.includes("therapy")) {
                return "fas fa-spa";
            }

            // Wax & Threading & Laser
            if (l.includes("wax") || l.includes("thread") || l.includes("laser") || l.includes("bleach")) {
                return "fas fa-feather-alt";
            }

            // Food & Dining
            if (l.includes("food") || l.includes("burg") || l.includes("pizza") || l.includes("bbq") || l.includes("meal") || l.includes("rice") || l.includes("karahi") || l.includes("platter") || l.includes("roll")) {
                return "fas fa-utensils";
            }

            // Drinks & Beverages
            if (l.includes("drink") || l.includes("bever") || l.includes("tea") || l.includes("coffee") || l.includes("juice") || l.includes("shake") || l.includes("smoothie") || l.includes("water") || l.includes("soda")) {
                return "fas fa-mug-hot";
            }

            // Desserts & Sweets (match dessert/cake/bakery/sweet specifically without matching 'service')
            if (l.includes("dessert") || l.includes("cake") || l.includes("pastry") || l.includes("bakery") || l.includes("sweet") || l.includes("ice cream") || l.includes("ice-cream")) {
                return "fas fa-ice-cream";
            }

            // General Service / Special / Deal
            if (l.includes("service") || l.includes("package") || l.includes("deal") || l.includes("special")) {
                return "fas fa-magic";
            }

            // Default Fallback
            return "fas fa-tag";
        };

        const getCategoryItemCount = (catKey) => {
            if (catKey === "all") return catalogItems.length;
            if (catKey === "product") return catalogItems.filter(item => (item.item_type || "product") === "product").length;
            if (String(catKey).startsWith("category:")) {
                const categoryId = Number(String(catKey).split(":")[1]);
                return catalogItems.filter(item => item.item_type === "service" && Number(item.category_id) === categoryId).length;
            }
            return 0;
        };

        const activeCategoryOption = filterOptions.find(cat => cat.key === activeCategory) || filterOptions[0];
        const dashboardCards = filteredCatalog;

        return (
            <div className="pos-shell" style={shellStyle}>
                <div className="pos-panel" style={panelStyle}>
                    <div className="pos-layout" style={{ display: "grid", gridTemplateColumns: "minmax(0, 1fr) 410px", gridTemplateRows: "1fr", flex: 1, minHeight: 0, height: "100%", overflow: "hidden" }}>
                        <div className="pos-catalog" style={{ display: "flex", flexDirection: "column", gap: "10px", padding: "10px 12px", borderRight: "1px solid #e2e8f0", background: "#f1f5f9", minWidth: 0, overflow: "hidden", height: "100%", boxSizing: "border-box" }}>
                            {/* Top Header Bar: Services / Deals Mode Switcher + Category Info + Search */}
                            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: "10px", background: "#ffffff", padding: "10px 14px", borderRadius: "14px", border: "1px solid #e2e8f0", boxShadow: "0 2px 8px rgba(15, 23, 42, 0.03)", flexShrink: 0 }}>
                                <div style={{ display: "flex", alignItems: "center", gap: "12px", minWidth: 0, flexWrap: "wrap" }}>
                                    {/* Mode Switcher Pills: Services vs Deals */}
                                    <div style={{
                                        display: "inline-flex",
                                        alignItems: "center",
                                        background: "#f1f5f9",
                                        padding: "4px",
                                        borderRadius: "12px",
                                        border: "1px solid #e2e8f0",
                                        gap: "4px"
                                    }}>
                                        <button
                                            type="button"
                                            onClick={() => this.setState({ catalogMode: "services" })}
                                            style={{
                                                display: "inline-flex",
                                                alignItems: "center",
                                                gap: "7px",
                                                padding: "6px 14px",
                                                borderRadius: "9px",
                                                border: "none",
                                                cursor: "pointer",
                                                fontSize: "0.84rem",
                                                fontWeight: 800,
                                                background: catalogMode === "services" ? "linear-gradient(135deg, #2a69b0 0%, #174276 100%)" : "transparent",
                                                color: catalogMode === "services" ? "#ffffff" : "#475569",
                                                boxShadow: catalogMode === "services" ? "0 2px 8px rgba(42, 105, 176, 0.35)" : "none",
                                                transition: "all 0.18s ease"
                                            }}
                                        >
                                            <i className="fas fa-spa" style={{ fontSize: "0.85rem" }}></i>
                                            <span>Services</span>
                                            <span style={{
                                                fontSize: "0.68rem",
                                                padding: "2px 7px",
                                                borderRadius: "8px",
                                                background: catalogMode === "services" ? "rgba(255,255,255,0.24)" : "#e2e8f0",
                                                color: catalogMode === "services" ? "#ffffff" : "#334155",
                                                fontWeight: 700
                                            }}>
                                                {services.length}
                                            </span>
                                        </button>

                                        <button
                                            type="button"
                                            onClick={() => this.setState({ catalogMode: "deals" })}
                                            style={{
                                                display: "inline-flex",
                                                alignItems: "center",
                                                gap: "7px",
                                                padding: "6px 14px",
                                                borderRadius: "9px",
                                                border: "none",
                                                cursor: "pointer",
                                                fontSize: "0.84rem",
                                                fontWeight: 800,
                                                background: catalogMode === "deals" ? "linear-gradient(135deg, #2a69b0 0%, #174276 100%)" : "transparent",
                                                color: catalogMode === "deals" ? "#ffffff" : "#475569",
                                                boxShadow: catalogMode === "deals" ? "0 2px 8px rgba(42, 105, 176, 0.35)" : "none",
                                                transition: "all 0.18s ease"
                                            }}
                                        >
                                            <i className="fas fa-tags" style={{ fontSize: "0.85rem" }}></i>
                                            <span>Deals</span>
                                            <span style={{
                                                fontSize: "0.68rem",
                                                padding: "2px 7px",
                                                borderRadius: "8px",
                                                background: catalogMode === "deals" ? "rgba(255,255,255,0.24)" : "#e2e8f0",
                                                color: catalogMode === "deals" ? "#ffffff" : "#334155",
                                                fontWeight: 700
                                            }}>
                                                {deals.length}
                                            </span>
                                        </button>
                                    </div>

                                    {/* Vertical separator */}
                                    <div style={{ width: "1px", height: "26px", background: "#e2e8f0" }}></div>

                                    {/* Active Title & Count */}
                                    {catalogMode === "services" ? (
                                        <div style={{ display: "flex", alignItems: "center", gap: "8px", minWidth: 0 }}>
                                            <div style={{ width: "32px", height: "32px", borderRadius: "10px", background: "linear-gradient(135deg, #2a69b0 0%, #174276 100%)", display: "flex", alignItems: "center", justifyContent: "center", color: "#fff", fontSize: "0.9rem", boxShadow: "0 2px 6px rgba(42,105,176,0.3)", flexShrink: 0 }}>
                                                <i className={getCategoryIcon(activeCategoryOption?.key, activeCategoryOption?.label)}></i>
                                            </div>
                                            <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                                                <span style={{ fontSize: "0.98rem", fontWeight: 800, color: "#0f172a", whiteSpace: "nowrap" }}>
                                                    {activeCategoryOption?.label || "All Items"}
                                                </span>
                                                <span style={{ background: "#eff6ff", color: "#2a69b0", fontSize: "0.72rem", fontWeight: 700, padding: "2px 8px", borderRadius: "12px", border: "1px solid #bfdbfe", whiteSpace: "nowrap" }}>
                                                    {dashboardCards.length} {dashboardCards.length === 1 ? 'item' : 'items'}
                                                </span>
                                            </div>
                                        </div>
                                    ) : (
                                        <div style={{ display: "flex", alignItems: "center", gap: "8px", minWidth: 0 }}>
                                            <div style={{ width: "32px", height: "32px", borderRadius: "10px", background: "linear-gradient(135deg, #2a69b0 0%, #174276 100%)", display: "flex", alignItems: "center", justifyContent: "center", color: "#fff", fontSize: "0.9rem", boxShadow: "0 2px 6px rgba(42,105,176,0.3)", flexShrink: 0 }}>
                                                <i className="fas fa-tags"></i>
                                            </div>
                                            <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                                                <span style={{ fontSize: "0.98rem", fontWeight: 800, color: "#0f172a", whiteSpace: "nowrap" }}>
                                                    All Deal Packages
                                                </span>
                                                <span style={{ background: "#eff6ff", color: "#2a69b0", fontSize: "0.72rem", fontWeight: 700, padding: "2px 8px", borderRadius: "12px", border: "1px solid #bfdbfe", whiteSpace: "nowrap" }}>
                                                    {filteredDeals.length} {filteredDeals.length === 1 ? 'deal' : 'deals'}
                                                </span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Quick Search Box */}
                                <div style={{ display: "flex", alignItems: "center", gap: "6px", flexShrink: 0 }}>
                                    <div
                                        style={{
                                            display: "flex",
                                            gap: "8px",
                                            alignItems: "center",
                                            background: "#ffffff",
                                            border: searchFocused ? "1.5px solid #2a69b0" : "1.5px solid #cbd5e1",
                                            boxShadow: searchFocused ? "0 0 0 3px rgba(42, 105, 176, 0.15), 0 2px 8px rgba(0,0,0,0.04)" : "0 1px 3px rgba(0,0,0,0.02)",
                                            borderRadius: "12px",
                                            padding: "6px 14px",
                                            transition: "all 0.22s cubic-bezier(0.4, 0, 0.2, 1)",
                                            width: searchFocused || search ? "280px" : "240px",
                                            maxWidth: "100%",
                                            boxSizing: "border-box",
                                        }}
                                    >
                                        {isSearching ? (
                                            <i className="fas fa-circle-notch fa-spin" style={{ color: "#2a69b0", fontSize: "0.85rem", flexShrink: 0 }}></i>
                                        ) : (
                                            <i className="fas fa-search" style={{ color: searchFocused ? "#2a69b0" : "#64748b", fontSize: "0.82rem", flexShrink: 0 }}></i>
                                        )}
                                        <input
                                            type="text"
                                            value={search || ""}
                                            placeholder={catalogMode === "deals" ? "Search deals or barcode..." : "Search products or barcode..."}
                                            onFocus={() => this.setState({ searchFocused: true })}
                                            onBlur={() => this.setState({ searchFocused: false })}
                                            onChange={this.handleChangeSearch}
                                            onKeyDown={this.handleSeach}
                                            style={{ border: "none", background: "transparent", outline: "none", fontSize: "0.84rem", width: "100%", minWidth: 0, color: "#0f172a", fontWeight: 500 }}
                                        />
                                        {isSearching && (
                                            <span style={{ fontSize: "0.65rem", fontWeight: 700, color: "#2a69b0", background: "#eff6ff", padding: "1px 5px", borderRadius: "6px", flexShrink: 0 }}>
                                                Searching
                                            </span>
                                        )}
                                        {!isSearching && search ? (
                                            <i
                                                className="fas fa-times-circle"
                                                onClick={() => this.setState({ search: "" }, () => this.loadProducts(""))}
                                                style={{ cursor: "pointer", color: "#94a3b8", fontSize: "0.85rem", transition: "color 0.15s", flexShrink: 0 }}
                                                title="Clear search (ESC)"
                                            />
                                        ) : null}
                                    </div>
                                </div>
                            </div>

                            {/* Categories Wrap Section (Shown only in Services mode - No Slider, No Clipping) */}
                            {catalogMode === "services" && (
                                <div style={{
                                    background: "#ffffff",
                                    borderRadius: "14px",
                                    border: "1px solid #e2e8f0",
                                    padding: "12px 14px 14px 14px",
                                    boxShadow: "0 2px 8px rgba(15, 23, 42, 0.03)",
                                    display: "flex",
                                    flexWrap: "wrap",
                                    alignItems: "center",
                                    gap: "8px 10px",
                                    flexShrink: 0,
                                    maxHeight: "170px",
                                    overflowY: "auto",
                                    boxSizing: "border-box",
                                }}>
                                    {filterOptions.map((cat) => {
                                        const isActive = (this.state.activeCategory || "all") === cat.key;
                                        const itemCount = getCategoryItemCount(cat.key);
                                        const iconClass = getCategoryIcon(cat.key, cat.label);
                                        return (
                                            <button
                                                key={cat.key}
                                                type="button"
                                                onClick={() => this.setState({ activeCategory: cat.key })}
                                                style={{
                                                    display: "inline-flex",
                                                    alignItems: "center",
                                                    gap: "7px",
                                                    padding: "7px 14px",
                                                    borderRadius: "10px",
                                                    border: isActive ? "1.5px solid #1a4d87" : "1px solid #e2e8f0",
                                                    background: isActive ? "linear-gradient(135deg, #2a69b0 0%, #1a4d87 100%)" : "#ffffff",
                                                    color: isActive ? "#ffffff" : "#334155",
                                                    fontWeight: isActive ? 700 : 600,
                                                    fontSize: "0.81rem",
                                                    cursor: "pointer",
                                                    whiteSpace: "nowrap",
                                                    margin: "2px 0",
                                                    boxShadow: isActive ? "0 3px 10px rgba(42, 105, 176, 0.28)" : "0 1px 3px rgba(0,0,0,0.02)",
                                                    transition: "all 0.16s cubic-bezier(0.4, 0, 0.2, 1)",
                                                }}
                                                onMouseEnter={(e) => {
                                                    if (!isActive) {
                                                        e.currentTarget.style.background = "#eff6ff";
                                                        e.currentTarget.style.borderColor = "#93c5fd";
                                                        e.currentTarget.style.color = "#2a69b0";
                                                    }
                                                }}
                                                onMouseLeave={(e) => {
                                                    if (!isActive) {
                                                        e.currentTarget.style.background = "#ffffff";
                                                        e.currentTarget.style.borderColor = "#e2e8f0";
                                                        e.currentTarget.style.color = "#334155";
                                                    }
                                                }}
                                            >
                                                <i className={iconClass} style={{ fontSize: "0.85rem", color: isActive ? "#93c5fd" : "#2a69b0" }}></i>
                                                <span>{cat.label}</span>
                                                <span
                                                    style={{
                                                        fontSize: "0.68rem",
                                                        fontWeight: 700,
                                                        padding: "2px 7px",
                                                        borderRadius: "8px",
                                                        background: isActive ? "rgba(255, 255, 255, 0.24)" : "#f1f5f9",
                                                        color: isActive ? "#ffffff" : "#475569",
                                                        marginLeft: "2px",
                                                    }}
                                                >
                                                    {itemCount}
                                                </span>
                                            </button>
                                        );
                                    })}
                                </div>
                            )}

                            {/* Active Search Progress Shimmer Line */}
                            {isSearching && (
                                <div style={{ position: "relative", height: "3px", width: "100%", background: "#e0edff", borderRadius: "3px", overflow: "hidden", flexShrink: 0 }}>
                                    <div className="pos-search-progress-bar" style={{ position: "absolute", top: 0, left: 0, height: "100%", width: "40%", background: "linear-gradient(90deg, #2a69b0, #60a5fa, #2a69b0)", borderRadius: "3px" }} />
                                </div>
                            )}

                            {/* Main Catalog View: Deals or Products Grid */}
                            {catalogMode === "deals" ? (
                                <div style={{ flex: 1, minHeight: 0, overflowY: "auto", overflowX: "hidden", padding: "4px 2px", boxSizing: "border-box" }}>
                                    <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(310px, 1fr))", gap: "14px", width: "100%", boxSizing: "border-box" }}>
                                        {filteredDeals.length === 0 ? (
                                            <div style={{ gridColumn: "1 / -1", background: "#fff", borderRadius: "16px", padding: "60px 20px", textAlign: "center", border: "1px solid #e2e8f0", boxShadow: "0 2px 8px rgba(15, 23, 42, 0.03)" }}>
                                                <div style={{ width: "56px", height: "56px", borderRadius: "50%", background: "#eff6ff", border: "2px solid #bfdbfe", display: "inline-flex", alignItems: "center", justifyContent: "center", color: "#2a69b0", fontSize: "1.5rem", marginBottom: "14px" }}>
                                                    <i className="fas fa-tags"></i>
                                                </div>
                                                <div style={{ fontWeight: 800, fontSize: "1.05rem", color: "#0f172a", marginBottom: "4px" }}>
                                                    {search ? `No deals found matching "${search}"` : "No Deal Packages Available"}
                                                </div>
                                                <div style={{ fontSize: "0.8rem", color: "#64748b", marginBottom: search ? "14px" : 0 }}>
                                                    {search ? "Try searching by another keyword, barcode, or included service." : "Create active deal packages in Deal Management to display them here."}
                                                </div>
                                                {search && (
                                                    <button
                                                        type="button"
                                                        onClick={() => this.setState({ search: "" }, () => this.loadProducts(""))}
                                                        style={{ background: "#eff6ff", border: "1px solid #bfdbfe", color: "#2a69b0", padding: "7px 16px", borderRadius: "10px", fontSize: "0.82rem", fontWeight: 700, cursor: "pointer", transition: "all 0.15s ease" }}
                                                    >
                                                        <i className="fas fa-redo-alt mr-1"></i> Clear Search
                                                    </button>
                                                )}
                                            </div>
                                        ) : (
                                            filteredDeals.map((deal) => {
                                                const originalPrice = Number(deal.original_amount ?? deal.original_price ?? 0);
                                                const dealPrice = Number(deal.discounted_amount ?? deal.price ?? 0);
                                                const discountPercent = Number(deal.discount_percentage ?? 0);
                                                const savings = Math.max(0, originalPrice - dealPrice);
                                                const itemKey = `deal:${deal.id}`;
                                                const cartItem = cart.find(c => this.getCartItemKey(c) === itemKey);
                                                const inCartQty = cartItem ? Number(cartItem.pivot?.quantity || 0) : 0;
                                                const includedServices = Array.isArray(deal.services) ? deal.services : [];

                                                return (
                                                    <div
                                                        key={itemKey}
                                                        className="pos-deal-card"
                                                        onClick={() => this.addProductToCart(deal.barcode)}
                                                        style={{
                                                            background: inCartQty > 0 ? "#f8fbff" : "#ffffff",
                                                            border: inCartQty > 0 ? "2px solid #2a69b0" : "1.5px solid #e2e8f0",
                                                            borderRadius: "16px",
                                                            overflow: "hidden",
                                                            boxShadow: inCartQty > 0 ? "0 8px 24px rgba(42, 105, 176, 0.18)" : "0 2px 10px rgba(15, 23, 42, 0.04)",
                                                            cursor: "pointer",
                                                            transition: "all 0.2s cubic-bezier(0.4, 0, 0.2, 1)",
                                                            boxSizing: "border-box",
                                                            display: "flex",
                                                            flexDirection: "column",
                                                        }}
                                                        onMouseEnter={(e) => {
                                                            if (inCartQty === 0) {
                                                                e.currentTarget.style.borderColor = "#93c5fd";
                                                                e.currentTarget.style.boxShadow = "0 8px 24px rgba(42, 105, 176, 0.12)";
                                                                e.currentTarget.style.transform = "translateY(-2px)";
                                                            }
                                                        }}
                                                        onMouseLeave={(e) => {
                                                            if (inCartQty === 0) {
                                                                e.currentTarget.style.borderColor = "#e2e8f0";
                                                                e.currentTarget.style.boxShadow = "0 2px 10px rgba(15, 23, 42, 0.04)";
                                                                e.currentTarget.style.transform = "translateY(0)";
                                                            }
                                                        }}
                                                    >
                                                        {/* Top Deal Header */}
                                                        <div style={{
                                                            padding: "10px 14px",
                                                            background: inCartQty > 0 ? "linear-gradient(135deg, #e0efff 0%, #d0e4f5 100%)" : "linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)",
                                                            borderBottom: "1px solid #e2e8f0",
                                                            display: "flex",
                                                            alignItems: "center",
                                                            justifyContent: "space-between",
                                                            gap: "8px"
                                                        }}>
                                                            <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                                                                <span style={{
                                                                    background: "linear-gradient(135deg, #2a69b0 0%, #174276 100%)",
                                                                    color: "#ffffff",
                                                                    fontSize: "0.7rem",
                                                                    fontWeight: 800,
                                                                    padding: "3px 9px",
                                                                    borderRadius: "7px",
                                                                    letterSpacing: "0.04em",
                                                                    display: "inline-flex",
                                                                    alignItems: "center",
                                                                    gap: "5px",
                                                                    boxShadow: "0 2px 5px rgba(42, 105, 176, 0.28)"
                                                                }}>
                                                                    <i className="fas fa-tags" style={{ fontSize: "0.68rem" }}></i>
                                                                    DEAL PACKAGE
                                                                </span>
                                                                {discountPercent > 0 && (
                                                                    <span style={{
                                                                        background: "#eff6ff",
                                                                        color: "#1e40af",
                                                                        border: "1px solid #bfdbfe",
                                                                        fontSize: "0.7rem",
                                                                        fontWeight: 800,
                                                                        padding: "2px 8px",
                                                                        borderRadius: "6px"
                                                                    }}>
                                                                        {discountPercent.toFixed(0)}% OFF
                                                                    </span>
                                                                )}
                                                            </div>
                                                            {deal.barcode && (
                                                                <span style={{ fontSize: "0.7rem", color: "#475569", fontWeight: 700, fontFamily: "monospace", background: "#ffffff", padding: "2px 8px", borderRadius: "6px", border: "1px solid #e2e8f0" }}>
                                                                    {deal.barcode}
                                                                </span>
                                                            )}
                                                        </div>

                                                        {/* Deal Body */}
                                                        <div style={{ padding: "14px 16px 12px", flex: 1, display: "flex", flexDirection: "column", gap: "10px" }}>
                                                            <div style={{
                                                                fontWeight: 800,
                                                                fontSize: "0.98rem",
                                                                color: "#0f172a",
                                                                lineHeight: 1.35
                                                            }}>
                                                                {deal.name}
                                                            </div>

                                                            {/* Included Services Section - Shows ALL service names clearly */}
                                                            <div>
                                                                <div style={{
                                                                    display: "flex",
                                                                    alignItems: "center",
                                                                    justifyContent: "space-between",
                                                                    marginBottom: "6px"
                                                                }}>
                                                                    <span style={{
                                                                        fontSize: "0.72rem",
                                                                        fontWeight: 700,
                                                                        color: "#64748b",
                                                                        textTransform: "uppercase",
                                                                        letterSpacing: "0.04em",
                                                                        display: "flex",
                                                                        alignItems: "center",
                                                                        gap: "5px"
                                                                    }}>
                                                                        <i className="fas fa-list-check" style={{ color: "#2a69b0", fontSize: "0.72rem" }}></i>
                                                                        Included Services ({includedServices.length})
                                                                    </span>
                                                                </div>

                                                                <div style={{
                                                                    display: "flex",
                                                                    flexDirection: "column",
                                                                    gap: "5px",
                                                                    maxHeight: "150px",
                                                                    overflowY: "auto",
                                                                    paddingRight: "2px"
                                                                }}>
                                                                    {includedServices.length === 0 ? (
                                                                        <span style={{ fontSize: "0.76rem", color: "#94a3b8", fontStyle: "italic" }}>No services specified</span>
                                                                    ) : (
                                                                        includedServices.map((srv, sIdx) => (
                                                                            <div
                                                                                key={sIdx}
                                                                                style={{
                                                                                    display: "flex",
                                                                                    alignItems: "center",
                                                                                    gap: "7px",
                                                                                    padding: "5px 9px",
                                                                                    borderRadius: "8px",
                                                                                    background: inCartQty > 0 ? "#eff6ff" : "#f8fafc",
                                                                                    border: inCartQty > 0 ? "1px solid #bfdbfe" : "1px solid #e2e8f0",
                                                                                    fontSize: "0.78rem",
                                                                                    color: "#1e293b",
                                                                                    fontWeight: 600,
                                                                                    lineHeight: 1.25
                                                                                }}
                                                                            >
                                                                                <i className="fas fa-check-circle" style={{ color: "#2a69b0", fontSize: "0.74rem", flexShrink: 0 }}></i>
                                                                                <span style={{ wordBreak: "break-word" }}>{srv.name}</span>
                                                                            </div>
                                                                        ))
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {/* Deal Footer: Pricing & Actions */}
                                                        <div style={{
                                                            padding: "11px 16px",
                                                            borderTop: "1px dashed #e2e8f0",
                                                            background: inCartQty > 0 ? "rgba(42, 105, 176, 0.05)" : "#fafafa",
                                                            display: "flex",
                                                            alignItems: "center",
                                                            justifyContent: "space-between",
                                                            gap: "10px",
                                                            marginTop: "auto"
                                                        }}>
                                                            <div>
                                                                {originalPrice > dealPrice && (
                                                                    <div style={{ fontSize: "0.74rem", color: "#94a3b8", textDecoration: "line-through", fontWeight: 600, lineHeight: 1 }}>
                                                                        {this.formatAmount(originalPrice)}
                                                                    </div>
                                                                )}
                                                                <div style={{ fontSize: "1.15rem", fontWeight: 800, color: inCartQty > 0 ? "#174276" : "#0f172a", lineHeight: 1.2 }}>
                                                                    {this.formatAmount(dealPrice)}
                                                                </div>
                                                                {savings > 0 && (
                                                                    <div style={{ fontSize: "0.7rem", color: "#2a69b0", fontWeight: 700 }}>
                                                                        Save {this.formatAmount(savings)}
                                                                    </div>
                                                                )}
                                                            </div>

                                                            <div style={{ display: "flex", alignItems: "center", gap: "5px" }}>
                                                                {inCartQty > 0 ? (
                                                                    <div style={{ display: "flex", alignItems: "center", gap: "4px" }} onClick={e => e.stopPropagation()}>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => {
                                                                                if (inCartQty > 1) {
                                                                                    this.handleChangeQty(itemKey, inCartQty - 1);
                                                                                } else {
                                                                                    this.handleClickDelete(itemKey);
                                                                                }
                                                                            }}
                                                                            style={{ width: "28px", height: "28px", borderRadius: "8px", border: "1px solid #cbd5e1", background: "#ffffff", color: "#0f172a", cursor: "pointer", fontWeight: 800, fontSize: "0.9rem", display: "flex", alignItems: "center", justifyContent: "center", transition: "all 0.15s ease" }}
                                                                        >
                                                                            -
                                                                        </button>
                                                                        <span style={{ minWidth: "24px", textAlign: "center", fontWeight: 800, fontSize: "0.9rem", color: "#2a69b0" }}>
                                                                            {inCartQty}
                                                                        </span>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => this.handleChangeQty(itemKey, inCartQty + 1)}
                                                                            style={{ width: "28px", height: "28px", borderRadius: "8px", border: "none", background: "#2a69b0", color: "#ffffff", cursor: "pointer", fontWeight: 800, fontSize: "0.9rem", display: "flex", alignItems: "center", justifyContent: "center", boxShadow: "0 2px 6px rgba(42, 105, 176, 0.3)", transition: "all 0.15s ease" }}
                                                                        >
                                                                            +
                                                                        </button>
                                                                    </div>
                                                                ) : (
                                                                    <button
                                                                        type="button"
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            this.addProductToCart(deal.barcode);
                                                                        }}
                                                                        style={{
                                                                            background: "linear-gradient(135deg, #2a69b0 0%, #174276 100%)",
                                                                            color: "#ffffff",
                                                                            border: "none",
                                                                            borderRadius: "10px",
                                                                            padding: "7px 14px",
                                                                            fontSize: "0.82rem",
                                                                            fontWeight: 800,
                                                                            cursor: "pointer",
                                                                            display: "flex",
                                                                            alignItems: "center",
                                                                            gap: "6px",
                                                                            boxShadow: "0 3px 10px rgba(42, 105, 176, 0.32)",
                                                                            transition: "all 0.18s ease"
                                                                        }}
                                                                    >
                                                                        <i className="fas fa-plus"></i>
                                                                        Add Deal
                                                                    </button>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                );
                                            })
                                        )}
                                    </div>
                                </div>
                            ) : (
                                <div style={{ flex: 1, minHeight: 0, overflowY: "auto", overflowX: "hidden", padding: "4px 2px", boxSizing: "border-box" }}>
                                    <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(138px, 1fr))", gap: "10px", width: "100%", boxSizing: "border-box" }}>
                                        {isSearching && dashboardCards.length === 0 ? (
                                            <div style={{ gridColumn: "1 / -1", background: "#fff", borderRadius: "16px", padding: "60px 20px", textAlign: "center", border: "1px solid #e2e8f0", boxShadow: "0 2px 8px rgba(15, 23, 42, 0.03)" }}>
                                                <div style={{ width: "48px", height: "48px", borderRadius: "50%", background: "#eff6ff", border: "2px solid #bfdbfe", display: "inline-flex", alignItems: "center", justifyContent: "center", color: "#2a69b0", fontSize: "1.3rem", marginBottom: "12px" }}>
                                                    <i className="fas fa-circle-notch fa-spin"></i>
                                                </div>
                                                <div style={{ fontWeight: 800, fontSize: "0.95rem", color: "#0f172a", marginBottom: "4px" }}>Searching catalog...</div>
                                                <div style={{ fontSize: "0.76rem", color: "#64748b" }}>Looking for &ldquo;{search}&rdquo; across products and services</div>
                                            </div>
                                        ) : dashboardCards.length === 0 ? (
                                            <div style={{ gridColumn: "1 / -1", background: "#fff", borderRadius: "16px", padding: "50px 20px", textAlign: "center", color: "#64748b", border: "1px solid #e2e8f0" }}>
                                                <div style={{ width: "48px", height: "48px", borderRadius: "50%", background: "#f8fafc", display: "inline-flex", alignItems: "center", justifyContent: "center", color: "#94a3b8", fontSize: "1.3rem", marginBottom: "10px" }}>
                                                    <i className="fas fa-search"></i>
                                                </div>
                                                <div style={{ fontWeight: 800, fontSize: "0.95rem", color: "#1e293b", marginBottom: "4px" }}>
                                                    {search ? `No results found for "${search}"` : "No items found in this category"}
                                                </div>
                                                <div style={{ fontSize: "0.76rem", color: "#64748b", marginBottom: search ? "12px" : 0 }}>
                                                    {search ? "Check spelling or try a different keyword/barcode." : "Select another category to view products."}
                                                </div>
                                                {search && (
                                                    <button
                                                        type="button"
                                                        onClick={() => this.setState({ search: "" }, () => this.loadProducts(""))}
                                                        style={{ background: "#eff6ff", border: "1px solid #bfdbfe", color: "#2a69b0", padding: "6px 14px", borderRadius: "10px", fontSize: "0.78rem", fontWeight: 700, cursor: "pointer", transition: "all 0.15s ease" }}
                                                    >
                                                        <i className="fas fa-redo-alt" style={{ marginRight: "6px" }}></i>
                                                        Clear Search & Show All
                                                    </button>
                                                )}
                                            </div>
                                        ) : (
                                            dashboardCards.map((item, idx) => {
                                                const displayPrice = Number(item.price ?? item.rate ?? 0);
                                                const productLabel = item.item_type === 'service' ? 'Service' : 'Menu';
                                                const iconColor = idx % 2 === 0 ? '#2a69b0' : '#2a69b0';
                                                const itemKey = `${item.item_type || 'product'}:${item.id}`;
                                                const cartItem = cart.find(c => this.getCartItemKey(c) === itemKey);
                                                const inCartQty = cartItem ? Number(cartItem.pivot?.quantity || 0) : 0;
                                                return (
                                                    <div
                                                        key={itemKey}
                                                        className="pos-product-card"
                                                        onClick={() => this.addProductToCart(item.barcode)}
                                                        style={{
                                                            background: inCartQty > 0 ? "#f8fbff" : "#ffffff",
                                                            border: inCartQty > 0 ? "1.5px solid #2a69b0" : "1px solid #e2e8f0",
                                                            borderRadius: "14px",
                                                            overflow: "hidden",
                                                            boxShadow: inCartQty > 0 ? "0 6px 16px rgba(42, 105, 176, 0.14)" : "0 2px 6px rgba(15, 23, 42, 0.03)",
                                                            cursor: "pointer",
                                                            transition: "all 0.18s ease",
                                                            boxSizing: "border-box",
                                                        }}
                                                        onMouseEnter={(e) => {
                                                            if (inCartQty === 0) {
                                                                e.currentTarget.style.borderColor = "#93c5fd";
                                                                e.currentTarget.style.boxShadow = "0 4px 14px rgba(42, 105, 176, 0.10)";
                                                                e.currentTarget.style.transform = "translateY(-2px)";
                                                            }
                                                        }}
                                                        onMouseLeave={(e) => {
                                                            if (inCartQty === 0) {
                                                                e.currentTarget.style.borderColor = "#e2e8f0";
                                                                e.currentTarget.style.boxShadow = "0 2px 6px rgba(15, 23, 42, 0.03)";
                                                                e.currentTarget.style.transform = "translateY(0)";
                                                            }
                                                        }}
                                                    >
                                                        <div style={{ height: "80px", background: "rgba(42, 105, 176, 0.1)", display: "flex", alignItems: "center", justifyContent: "center" }}>
                                                            <div style={{ width: "48px", height: "48px", borderRadius: "14px", background: "rgba(42, 105, 176, 0.12)", display: "flex", alignItems: "center", justifyContent: "center", fontSize: "1.35rem", color: iconColor }}>
                                                                <i className={`fas fa-${item.item_type === 'service' ? 'tags' : 'spa'}`}></i>
                                                            </div>
                                                        </div>
                                                        <div style={{ padding: "8px 9px 10px" }}>
                                                            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "4px" }}>
                                                                <span style={{ fontSize: "0.66rem", fontWeight: 700, color: "#64748b", letterSpacing: "0.02em", textTransform: "uppercase" }}>{productLabel}</span>
                                                                <span style={{ fontSize: "0.66rem", color: "#2a69b0", fontWeight: 700 }}>#{idx + 1}</span>
                                                            </div>
                                                            <div style={{ fontWeight: 700, fontSize: "0.8rem", color: "#1f2937", marginBottom: "6px", minHeight: "34px", lineHeight: "1.2", display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden" }} title={item.name}>{item.name}</div>
                                                            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", gap: "6px", paddingTop: "6px", borderTop: "1px dashed #e2e8f0", marginTop: "auto" }}>
                                                                <span style={{ fontSize: "0.86rem", fontWeight: 800, color: inCartQty > 0 ? "#1c4e8a" : "#0f172a", minWidth: 0, whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }} title={this.formatAmount(displayPrice)}>
                                                                    {this.formatAmount(displayPrice)}
                                                                </span>
                                                                <div style={{ display: "flex", alignItems: "center", gap: "3px", flexShrink: 0 }}>
                                                                    <button
                                                                        type="button"
                                                                        disabled={inCartQty === 0}
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            if (cartItem) {
                                                                                if (inCartQty > 1) {
                                                                                    this.handleChangeQty(itemKey, inCartQty - 1);
                                                                                } else {
                                                                                    this.handleClickDelete(itemKey);
                                                                                }
                                                                            }
                                                                        }}
                                                                        style={{ width: "22px", height: "22px", borderRadius: "6px", border: inCartQty > 0 ? "1px solid #cbd5e1" : "1px solid #e2e8f0", background: inCartQty > 0 ? "#fff" : "#f8fafc", color: inCartQty > 0 ? "#334155" : "#cbd5e1", cursor: inCartQty > 0 ? "pointer" : "default", fontSize: "0.75rem", fontWeight: 700, display: "flex", alignItems: "center", justifyContent: "center", padding: 0, boxSizing: "border-box", transition: "all 0.15s ease" }}
                                                                        title="Decrease quantity"
                                                                    >-</button>
                                                                    {inCartQty > 0 && (
                                                                        <span style={{ minWidth: "15px", textAlign: "center", fontSize: "0.72rem", fontWeight: 800, color: "#2a69b0" }}>
                                                                            {inCartQty}
                                                                        </span>
                                                                    )}
                                                                    <button
                                                                        type="button"
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            this.addProductToCart(item.barcode);
                                                                        }}
                                                                        style={{ width: "22px", height: "22px", borderRadius: "6px", border: "none", background: "#2a69b0", color: "#fff", cursor: "pointer", fontSize: "0.75rem", fontWeight: 700, display: "flex", alignItems: "center", justifyContent: "center", padding: 0, boxSizing: "border-box", boxShadow: "0 2px 5px rgba(42, 105, 176, 0.28)", transition: "all 0.15s ease" }}
                                                                        title="Add to cart"
                                                                    >+</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                );
                                            })
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>

                        <aside className="pos-order-panel" style={{ background: "#f8f9fb", padding: "16px 18px 14px", minWidth: 0, minHeight: 0, overflow: "hidden", display: "flex", flexDirection: "column" }}>
                            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "12px" }}>
                                <div style={{ fontWeight: 800, color: "#1f2937", fontSize: "1.15rem" }}>Order #{cart.length > 0 ? cart[0]?.id || "-" : "-"}</div>
                                <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                                    <button onClick={() => this.handleClearSession()} style={{ border: "1px solid #e5e7eb", background: "#fff", borderRadius: "10px", padding: "7px 10px", cursor: "pointer" }}><i className="fas fa-exchange-alt"></i></button>
                                </div>
                            </div>


                            {/* Customer Select & Quick Add Bar */}
                            <div className="pos-customer-wrapper" style={{ background: "#fff", border: "1.5px solid #d0e4f5", borderRadius: "14px", padding: "10px 12px", marginBottom: "12px", boxShadow: "0 4px 12px rgba(15,23,42,0.03)" }}>
                                <div style={{ fontSize: "0.72rem", fontWeight: 700, color: "#64748b", textTransform: "uppercase", letterSpacing: "0.04em", marginBottom: "6px", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                                    <span style={{ display: "flex", alignItems: "center", gap: "5px" }}>
                                        <i className="fas fa-user-circle" style={{ color: "#2a69b0", fontSize: "0.88rem" }}></i>
                                        Customer
                                    </span>
                                    <button
                                        type="button"
                                        onClick={this.showAddCustomerModal}
                                        style={{ border: "none", background: "#2a69b0", color: "#fff", borderRadius: "8px", padding: "4px 10px", fontSize: "0.72rem", fontWeight: 700, cursor: "pointer", display: "inline-flex", alignItems: "center", gap: "4px", boxShadow: "0 2px 6px rgba(42, 105, 176, 0.3)" }}>
                                        <i className="fas fa-user-plus"></i> + Add New
                                    </button>
                                </div>

                                <select
                                    id="pos-customer-select"
                                    className="form-control"
                                    value={customer_id || ""}
                                    onChange={(e) => this.setState({ customer_id: e.target.value })}
                                    style={{ width: "100%" }}>
                                    <option value="">Walk-in Customer (Default)</option>
                                    {customers.map(cust => {
                                        const fullName = [cust.first_name, cust.last_name].filter(Boolean).join(" ");
                                        return (
                                            <option key={cust.id} value={cust.id}>
                                                {fullName} {cust.phone ? `(${cust.phone})` : ""}
                                            </option>
                                        );
                                    })}
                                </select>
                            </div>

                            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "12px" }}>
                                <div style={{ fontWeight: 800, color: "#1f2937", fontSize: "1rem" }}>Ordered Items</div>
                                <div style={{ color: "#64748b", fontSize: "0.8rem" }}>Total Items: {itemCount}</div>
                            </div>

                            <div className="pos-order-items" style={{ display: "flex", flexDirection: "column", gap: "7px", flex: 1, minHeight: 0, overflowY: "auto", paddingRight: "4px" }}>
                                {cart.length === 0 ? (
                                    <div style={{ background: "#fff", border: "1px solid #e5e7eb", borderRadius: "14px", padding: "28px 20px", textAlign: "center", color: "#64748b" }}>Cart is empty</div>
                                ) : (
                                    cart.map(item => {
                                        const unitPrice = Number(item.price ?? item.discounted_amount ?? item.rate ?? 0);
                                        const qty = Number(item.pivot?.quantity || 0);
                                        const isDeal = item.item_type === 'deal' || item.item_type === 2;
                                        const isService = item.item_type === 'service' || item.item_type === 1;
                                        return (
                                            <div className="pos-order-item" key={this.getCartItemKey(item)} style={{ background: "#fff", border: isDeal ? "1.5px solid #bfdbfe" : "1px solid #e5e7eb", borderRadius: "12px", padding: "8px 10px", display: "flex", flexDirection: "column", gap: "5px" }}>
                                                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                                                    <div style={{ minWidth: 0, flex: 1 }}>
                                                        <div className="pos-order-item-name" style={{ fontWeight: 700, color: "#1f2937" }}>{item.name}</div>
                                                        <div style={{ marginTop: "3px" }}>
                                                            <span style={{
                                                                display: "inline-flex",
                                                                alignItems: "center",
                                                                gap: "4px",
                                                                fontSize: "0.68rem",
                                                                fontWeight: 700,
                                                                padding: "1px 6px",
                                                                borderRadius: "6px",
                                                                background: "#eff6ff",
                                                                color: "#2a69b0",
                                                                border: "1px solid #bfdbfe"
                                                            }}>
                                                                <i className={`fas fa-${isDeal ? 'tags' : isService ? 'spa' : 'box-open'}`} style={{ fontSize: "0.65rem" }}></i>
                                                                {isDeal ? 'Deal Package' : isService ? 'Service' : 'Product'}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <button onClick={() => this.handleClickDelete(this.getCartItemKey(item))} style={{ width: "24px", height: "24px", borderRadius: "999px", border: "none", background: "#f87171", color: "#fff", cursor: "pointer", fontSize: "0.7rem" }}><i className="fas fa-times"></i></button>
                                                </div>

                                                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: "8px" }}>
                                                    <div style={{ display: "flex", alignItems: "center", border: "1px solid #e5e7eb", borderRadius: "10px", overflow: "hidden" }}>
                                                        <button onClick={() => this.handleChangeQty(this.getCartItemKey(item), Math.max(1, qty - 1))} style={{ width: "26px", height: "26px", border: "none", background: "#fff", cursor: "pointer" }}>-</button>
                                                        <span style={{ minWidth: "22px", textAlign: "center", fontWeight: 700 }}>{qty}</span>
                                                        <button onClick={() => this.handleChangeQty(this.getCartItemKey(item), qty + 1)} style={{ width: "26px", height: "26px", border: "none", background: "#fff", cursor: "pointer" }}>+</button>
                                                    </div>
                                                    {editableItemRate ? (
                                                        <input type="number" min="0" step="0.01" value={unitPrice} onClick={e => e.stopPropagation()} onChange={e => {
                                                            const nextPrice = e.target.value;
                                                            this.setState({ cart: cart.map(cartItem => this.getCartItemKey(cartItem) === this.getCartItemKey(item) ? { ...cartItem, price: nextPrice } : cartItem) });
                                                        }} style={{ ...S.inputField, width: "88px", padding: "5px 6px", textAlign: "right" }} />
                                                    ) : <span style={{ fontWeight: 700, color: "#1f2937" }}>{currency}{(unitPrice * qty).toFixed(2)}</span>}
                                                </div>
                                            </div>
                                        );
                                    })
                                )}
                            </div>

                            <div className="pos-order-summary" style={{ flexShrink: 0, marginTop: "12px", background: "#fff", border: "1px solid rgba(42, 105, 176, 0.25)", borderRadius: "14px", padding: "12px" }}>
                                <div style={{ display: "flex", justifyContent: "space-between", fontSize: "0.8rem", color: "#64748b", marginBottom: "8px" }}><span>Sub Total</span><span>{this.formatAmount(saleTotals.subtotal)}</span></div>
                                {taxEnabled && <div style={{ marginBottom: "10px" }}>
                                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", fontSize: "0.8rem", color: "#64748b", marginBottom: "5px" }}><span>Tax</span><span>{this.formatAmount(saleTotals.taxAmount)}</span></div>
                                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "6px" }}>
                                        <input type="number" min="0" step="0.01" placeholder="Tax %" value={saleTotals.taxPercent || ""} onChange={this.setTaxPercent} style={S.inputField} />
                                        <input type="number" min="0" step="0.01" placeholder="Tax amount" value={saleTotals.taxAmount ? saleTotals.taxAmount.toFixed(2) : ""} onChange={this.setTaxAmount} style={S.inputField} />
                                    </div>
                                </div>}
                                {discountEnabled && <div style={{ marginBottom: "10px" }}>
                                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", fontSize: "0.8rem", color: "#64748b", marginBottom: "5px" }}><span>Discount</span><span>-{this.formatAmount(saleTotals.discountAmount)}</span></div>
                                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "6px" }}>
                                        <input type="number" min="0" step="0.01" placeholder="Discount %" value={saleTotals.discountPercent || ""} onChange={this.setDiscountPercent} style={S.inputField} />
                                        <input type="number" min="0" step="0.01" placeholder="Discount amount" value={saleTotals.discountAmount ? saleTotals.discountAmount.toFixed(2) : ""} onChange={this.setDiscountAmount} style={S.inputField} />
                                    </div>
                                </div>}
                                <div style={{ display: "flex", justifyContent: "space-between", fontSize: "1rem", fontWeight: 800, color: "#1f2937", paddingTop: "8px", borderTop: "1px solid #e5e7eb", marginTop: "8px" }}><span>Amount to Pay</span><span style={{ color: "#2a69b0" }}>{this.formatAmount(saleTotals.total)}</span></div>
                            </div>

                            <div className="pos-order-actions" style={{ flexShrink: 0, display: "flex", flexDirection: "column", gap: "8px", marginTop: "12px" }}>
                                <button onClick={this.handleClickSubmit} style={{ background: "#2a69b0", border: "none", borderRadius: "12px", color: "#fff", fontWeight: 800, fontSize: "0.9rem", padding: "12px", cursor: "pointer", boxShadow: "0 6px 20px rgba(42, 105, 176, 0.38)" }}>Place an Order</button>
                                <div style={{ display: "grid", gridTemplateColumns: "repeat(3, minmax(0, 1fr))", gap: "8px" }}>
                                    <button
                                        onClick={() => (this.state.cart.length > 0 ? this.handleClickSubmit() : this.state.lastPlacedOrder ? this.printReceipt(this.state.lastPlacedOrder) : this.handleClickSubmit())}
                                        style={{ border: "1px solid #d0e4f5", background: "#fff", borderRadius: "10px", padding: "9px 6px", fontSize: "0.74rem", color: "#1a3a5c", fontWeight: 700, cursor: "pointer", transition: "all 0.15s" }}>
                                        <i className="fas fa-print" style={{ marginRight: "4px" }}></i>Print
                                    </button>
                                    <button
                                        onClick={this.handleOpenLastInvoice}
                                        style={{ border: "1px solid #d0e4f5", background: "#fff", borderRadius: "10px", padding: "9px 6px", fontSize: "0.74rem", color: "#1a3a5c", fontWeight: 700, cursor: "pointer", transition: "all 0.15s" }}>
                                        <i className="fas fa-file-invoice" style={{ marginRight: "4px" }}></i>Invoice
                                    </button>
                                    <button
                                        onClick={() => Swal.fire({ icon: "info", title: "Draft Saved", text: "Your current cart items are active and saved in your session.", timer: 2000, showConfirmButton: false })}
                                        style={{ border: "1px solid #d0e4f5", background: "#fff", borderRadius: "10px", padding: "9px 6px", fontSize: "0.74rem", color: "#1a3a5c", fontWeight: 700, cursor: "pointer", transition: "all 0.15s" }}>
                                        <i className="fas fa-save" style={{ marginRight: "4px" }}></i>Draft
                                    </button>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        );
    }
}

export default Cart;

const cartRoot = document.getElementById("cart");
if (cartRoot) createRoot(cartRoot).render(<Cart />);

