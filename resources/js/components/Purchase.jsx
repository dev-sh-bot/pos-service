import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";
import { CommonHelper } from "../helpers/commonHelper";

class Purchase extends Component {
    constructor(props) {
        super(props);

        // Preload branches from window.APP if available
        const appBranches = window.APP?.branches || [];
        const initialBranchId = window.APP?.session_branch_id || (appBranches.length > 0 ? appBranches[0].id : "");
        let initialCounterId = window.APP?.session_counter_id || "";

        if (initialBranchId && !initialCounterId) {
            const foundBranch = appBranches.find(b => String(b.id) === String(initialBranchId));
            if (foundBranch && foundBranch.counters && foundBranch.counters.length > 0) {
                initialCounterId = foundBranch.counters[0].id;
            }
        }

        this.state = {
            cart: [],
            products: [],
            categories: [],
            suppliers: [],
            branches: appBranches,
            branch_id: initialBranchId,
            counter_id: initialCounterId,
            search: "",
            barcode: "",
            activeCategory: "all",
            supplier_id: "",
            purchase_date: new Date().toISOString().split("T")[0],
            payment_method: "cash",
            subtotal: 0,
            taxPercent: "",
            taxAmount: "",
            discountPercent: "",
            discountAmount: "",
            notes: "",
            status: "completed",
            loading: false,
            lastPurchase: null,
            showReceiptModal: false,
            searchFocused: false,
        };

        this.loadCart = this.loadCart.bind(this);
        this.loadProducts = this.loadProducts.bind(this);
        this.loadCategories = this.loadCategories.bind(this);
        this.loadSuppliers = this.loadSuppliers.bind(this);
        this.loadBranches = this.loadBranches.bind(this);
        this.addProductToCart = this.addProductToCart.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleChangePrice = this.handleChangePrice.bind(this);
        this.handleClickDelete = this.handleClickDelete.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);
        this.handleTaxPercentChange = this.handleTaxPercentChange.bind(this);
        this.handleTaxAmountChange = this.handleTaxAmountChange.bind(this);
        this.handleDiscountPercentChange = this.handleDiscountPercentChange.bind(this);
        this.handleDiscountAmountChange = this.handleDiscountAmountChange.bind(this);
        this.handleSubmitPurchase = this.handleSubmitPurchase.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.showReceiptPreview = this.showReceiptPreview.bind(this);
        this.buildReceiptHtml = this.buildReceiptHtml.bind(this);
        this.printReceipt = this.printReceipt.bind(this);
        this.showAddSupplierModal = this.showAddSupplierModal.bind(this);
        this.handleBranchChange = this.handleBranchChange.bind(this);
    }

    componentDidMount() {
        this.loadCategories();
        this.loadSuppliers();
        this.loadBranches();
        this.loadProducts();
        this.loadCart();
    }

    loadCategories() {
        axios.get("/admin/cart/categories")
            .then((res) => {
                const categories = res.data.data || res.data || [];
                this.setState({ categories });
            })
            .catch(() => {
                this.setState({ categories: [] });
            });
    }

    loadSuppliers() {
        axios.get("/admin/suppliers", { headers: { Accept: "application/json" } })
            .then((res) => {
                const suppliers = Array.isArray(res.data) ? res.data : (res.data.data || []);
                this.setState({ suppliers });
                if (!this.state.supplier_id && suppliers.length > 0) {
                    this.setState({ supplier_id: suppliers[0].id });
                }
            })
            .catch(() => {
                this.setState({ suppliers: [] });
            });
    }

    loadBranches() {
        if (this.state.branches && this.state.branches.length > 0) return;

        axios.get("/admin/pos-access/branches", { headers: { Accept: "application/json" } })
            .then((res) => {
                const branches = Array.isArray(res.data) ? res.data : [];
                this.setState({ branches });
                if (!this.state.branch_id && branches.length > 0) {
                    this.handleBranchChange(branches[0].id, branches);
                }
            })
            .catch(() => {});
    }

    handleBranchChange(branchId, branchesList = null) {
        const branches = branchesList || this.state.branches;
        const selectedBranch = branches.find(b => String(b.id) === String(branchId));
        let counterId = "";

        if (selectedBranch && selectedBranch.counters && selectedBranch.counters.length > 0) {
            counterId = selectedBranch.counters[0].id;
        }

        this.setState({
            branch_id: branchId,
            counter_id: counterId
        });
    }

    loadProducts(search = "") {
        const query = search ? `?search=${encodeURIComponent(search)}&per_page=250` : "?per_page=250";
        axios.get(`/admin/products${query}`, { headers: { Accept: "application/json" } })
            .then((res) => {
                const products = res.data.data || [];
                this.setState({ products });
            })
            .catch(() => {
                this.setState({ products: [] });
            });
    }

    loadCart() {
        axios.get("/admin/purchase-cart", { headers: { Accept: "application/json" } })
            .then((res) => {
                const cart = res.data || [];
                this.setState({ cart }, this.recalculateTotals);
            })
            .catch(() => {
                this.setState({ cart: [] });
            });
    }

    addProductToCart(product) {
        axios.post("/admin/purchase-cart", { product_id: product.id, barcode: product.barcode }, { headers: { Accept: "application/json" } })
            .then(() => {
                this.loadCart();
            })
            .catch((err) => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: err.response?.data?.message || "Failed to add product",
                    timer: 1500,
                    showConfirmButton: false,
                });
            });
    }

    handleScanBarcode(e) {
        if (e && e.preventDefault) e.preventDefault();
        const barcode = this.state.barcode.trim();
        if (!barcode) return;

        axios.post("/admin/purchase-cart", { barcode }, { headers: { Accept: "application/json" } })
            .then(() => {
                this.setState({ barcode: "", search: "" });
                this.loadCart();
            })
            .catch((err) => {
                Swal.fire({
                    icon: "warning",
                    title: "Not Found",
                    text: err.response?.data?.message || "Product not found with this barcode",
                    timer: 1500,
                    showConfirmButton: false,
                });
            });
    }

    handleChangeQty(productId, qty) {
        const numericQty = parseFloat(qty);
        if (isNaN(numericQty) || numericQty < 1) return;

        axios.post("/admin/purchase-cart/change-qty", { product_id: productId, quantity: numericQty }, { headers: { Accept: "application/json" } })
            .then(() => {
                this.loadCart();
            });
    }

    handleChangePrice(productId, price) {
        const numericPrice = parseFloat(price);
        if (isNaN(numericPrice) || numericPrice < 0) return;

        axios.post("/admin/purchase-cart/change-price", { product_id: productId, purchase_price: numericPrice }, { headers: { Accept: "application/json" } })
            .then(() => {
                this.loadCart();
            });
    }

    handleClickDelete(productId) {
        axios.delete("/admin/purchase-cart/delete", { data: { product_id: productId }, headers: { Accept: "application/json" } })
            .then(() => {
                this.loadCart();
            });
    }

    handleEmptyCart() {
        Swal.fire({
            title: "Empty purchase cart?",
            text: "All items currently in the purchase cart will be cleared.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "var(--snd-button-primary)",
            cancelButtonColor: "#626b7b",
            confirmButtonText: "Yes, clear it!",
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete("/admin/purchase-cart/empty", { headers: { Accept: "application/json" } })
                    .then(() => {
                        this.setState({
                            cart: [],
                            subtotal: 0,
                            taxPercent: "",
                            taxAmount: "",
                            discountPercent: "",
                            discountAmount: "",
                        });
                    });
            }
        });
    }

    calculateSubtotal() {
        return this.state.cart.reduce((acc, item) => {
            const qty = parseFloat(item.pivot?.quantity) || 1;
            const price = parseFloat(item.pivot?.purchase_price) || 0;
            return acc + qty * price;
        }, 0);
    }

    recalculateTotals() {
        const subtotal = this.calculateSubtotal();
        let { taxPercent, taxAmount, discountPercent, discountAmount } = this.state;

        if (taxPercent !== "") {
            taxAmount = round2((subtotal * parseFloat(taxPercent)) / 100);
        }
        if (discountPercent !== "") {
            discountAmount = round2((subtotal * parseFloat(discountPercent)) / 100);
        }

        this.setState({ subtotal, taxAmount, discountAmount });
    }

    handleTaxPercentChange(e) {
        const val = e.target.value;
        const subtotal = this.calculateSubtotal();
        if (val === "") {
            this.setState({ taxPercent: "", taxAmount: "" });
            return;
        }
        const percent = Math.max(0, parseFloat(val) || 0);
        const amount = (subtotal * percent) / 100;
        this.setState({ taxPercent: val, taxAmount: round2(amount) });
    }

    handleTaxAmountChange(e) {
        const val = e.target.value;
        const subtotal = this.calculateSubtotal();
        if (val === "") {
            this.setState({ taxAmount: "", taxPercent: "" });
            return;
        }
        const amount = Math.max(0, parseFloat(val) || 0);
        const percent = subtotal > 0 ? (amount / subtotal) * 100 : 0;
        this.setState({ taxAmount: val, taxPercent: round2(percent) });
    }

    handleDiscountPercentChange(e) {
        const val = e.target.value;
        const subtotal = this.calculateSubtotal();
        if (val === "") {
            this.setState({ discountPercent: "", discountAmount: "" });
            return;
        }
        const percent = Math.min(100, Math.max(0, parseFloat(val) || 0));
        const amount = (subtotal * percent) / 100;
        this.setState({ discountPercent: val, discountAmount: round2(amount) });
    }

    handleDiscountAmountChange(e) {
        const val = e.target.value;
        const subtotal = this.calculateSubtotal();
        if (val === "") {
            this.setState({ discountAmount: "", discountPercent: "" });
            return;
        }
        const amount = Math.min(subtotal, Math.max(0, parseFloat(val) || 0));
        const percent = subtotal > 0 ? (amount / subtotal) * 100 : 0;
        this.setState({ discountAmount: val, discountPercent: round2(percent) });
    }

    showAddSupplierModal() {
        Swal.fire({
            title: "Add New Supplier",
            html: `
                <div style="text-align: left; font-size: 0.88rem;">
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 700; margin-bottom: 4px; display: block; color: #1a1a2e;">First Name *</label>
                        <input id="swal-first-name" class="swal2-input" placeholder="Supplier first name" style="margin: 0; width: 100%; border-radius: 10px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 700; margin-bottom: 4px; display: block; color: #1a1a2e;">Last Name</label>
                        <input id="swal-last-name" class="swal2-input" placeholder="Supplier last name" style="margin: 0; width: 100%; border-radius: 10px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 700; margin-bottom: 4px; display: block; color: #1a1a2e;">Phone Number</label>
                        <input id="swal-phone" class="swal2-input" placeholder="Phone number" style="margin: 0; width: 100%; border-radius: 10px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 700; margin-bottom: 4px; display: block; color: #1a1a2e;">Email</label>
                        <input id="swal-email" class="swal2-input" placeholder="Email address" style="margin: 0; width: 100%; border-radius: 10px;">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: "Save Supplier",
            confirmButtonColor: "var(--snd-button-primary)",
            focusConfirm: false,
            preConfirm: () => {
                const firstName = document.getElementById("swal-first-name").value.trim();
                if (!firstName) {
                    Swal.showValidationMessage("First name is required");
                    return false;
                }
                return {
                    first_name: firstName,
                    last_name: document.getElementById("swal-last-name").value.trim(),
                    phone: document.getElementById("swal-phone").value.trim(),
                    email: document.getElementById("swal-email").value.trim(),
                };
            },
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post("/admin/suppliers", result.value, { headers: { Accept: "application/json" } })
                    .then((res) => {
                        const newSupplier = res.data.supplier;
                        this.setState((prev) => ({
                            suppliers: [newSupplier, ...prev.suppliers],
                            supplier_id: newSupplier.id,
                        }));
                        Swal.fire({
                            icon: "success",
                            title: "Supplier Created",
                            text: `${newSupplier.first_name} has been selected.`,
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: "error",
                            title: "Failed to create supplier",
                            text: err.response?.data?.message || "Please check the entered values.",
                        });
                    });
            }
        });
    }

    handleClickSubmit() {
        const { cart, supplier_id, suppliers, branch_id, branches, counter_id, purchase_date, taxPercent, taxAmount, discountPercent, discountAmount, notes } = this.state;

        if (!cart || cart.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Cart is Empty",
                text: "Please add products to the purchase cart before placing an order.",
                confirmButtonColor: "var(--snd-button-primary)"
            });
            return;
        }

        if (!supplier_id) {
            Swal.fire({
                icon: "warning",
                title: "Select Supplier",
                text: "Please select a supplier for this purchase.",
                confirmButtonColor: "var(--snd-button-primary)"
            });
            return;
        }

        const subtotal = this.calculateSubtotal();
        const tAmount = parseFloat(taxAmount) || 0;
        const dAmount = parseFloat(discountAmount) || 0;
        const tPercent = parseFloat(taxPercent) || 0;
        const dPercent = parseFloat(discountPercent) || 0;
        const grandTotal = Math.max(0, subtotal + tAmount - dAmount);
        const total = grandTotal.toFixed(2);
        const currency = window.APP?.currency_symbol || "Rs.";

        const selectedSupp = (suppliers || []).find(s => String(s.id) === String(supplier_id));
        const supplierName = selectedSupp
            ? [selectedSupp.first_name, selectedSupp.last_name].filter(Boolean).join(" ")
            : "Selected Supplier";

        const activeBranch = (branches || []).find(b => String(b.id) === String(branch_id));
        const totalQty = cart.reduce((sum, item) => sum + Number(item.pivot?.quantity || 1), 0);

        const itemsRowsHtml = cart.map(item => {
            const unitPrice = Number(item.pivot?.purchase_price ?? item.purchase_price ?? item.price ?? 0);
            const qty = Number(item.pivot?.quantity || 1);
            const lineTotal = unitPrice * qty;

            return `
                <tr style="border-bottom: 1px solid var(--snd-surface-soft);">
                    <td style="padding: 10px 10px; text-align: left; vertical-align: middle;">
                        <div style="font-weight: 700; color: #1a1a2e; font-size: 1.02rem; line-height: 1.3;">${item.name}</div>
                        <div style="margin-top: 4px;">
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:0.82rem;font-weight:700;background:var(--snd-surface-soft);color:#505469;border:1px solid var(--snd-border);">
                                <i class="fas fa-box-open" style="font-size:0.75rem;"></i> Product
                            </span>
                        </div>
                    </td>
                    <td style="padding: 10px 10px; text-align: center; vertical-align: middle; font-weight: 700; color: #505469; font-size: 1.05rem;">
                        ${qty}
                    </td>
                    <td style="padding: 10px 10px; text-align: right; vertical-align: middle; color: #626b7b; font-size: 1.02rem; font-family: monospace;">
                        ${unitPrice.toFixed(2)}
                    </td>
                    <td style="padding: 10px 10px; text-align: right; vertical-align: middle; font-weight: 800; color: #1a1a2e; font-size: 1.06rem; font-family: monospace;">
                        ${lineTotal.toFixed(2)}
                    </td>
                </tr>
            `;
        }).join("");

        const modalHtml = `
            <div style="text-align: left; font-family: inherit; color: #1a1a2e;">
                <!-- Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 14px; border-bottom: 1.5px solid var(--snd-surface-soft); margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--snd-primary-deep) 0%, var(--snd-primary-hover) 100%); display: flex; align-items: center; justify-content: center; color: var(--snd-button-primary-text); font-size: 1.4rem; box-shadow: 0 4px 10px var(--snd-primary-ring); flex-shrink: 0;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.38rem; font-weight: 800; color: #1a1a2e; letter-spacing: -0.01em; line-height: 1.2;">
                                Purchase Order Confirmation
                            </div>
                            <div style="font-size: 0.90rem; color: #626b7b; margin-top: 2px;">
                                Review purchase details & select payment method
                            </div>
                        </div>
                    </div>
                    <!-- Badges -->
                    <div style="display: flex; align-items: center; gap: 7px; flex-wrap: wrap; justify-content: flex-end;">
                        <span style="display: inline-flex; align-items: center; gap: 6px; background: var(--snd-surface-soft); border: 1px solid var(--snd-border); border-radius: 20px; padding: 5px 13px; font-size: 0.90rem; font-weight: 700; color: #505469;">
                            <i class="fas fa-user-tie" style="color: var(--snd-primary-deep);"></i> ${supplierName}
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 5px; background: var(--snd-primary-soft); border: 1px solid var(--snd-border-strong); border-radius: 20px; padding: 5px 12px; font-size: 0.90rem; font-weight: 700; color: var(--snd-primary-deep);">
                            <i class="fas fa-boxes"></i> ${totalQty} Items
                        </span>
                        ${activeBranch ? `<span style="display: inline-flex; align-items: center; gap: 5px; background: var(--snd-surface-soft); border: 1px solid var(--snd-border); border-radius: 20px; padding: 5px 12px; font-size: 0.88rem; font-weight: 600; color: #505469;"><i class="fas fa-warehouse"></i> ${activeBranch.name}</span>` : ""}
                    </div>
                </div>

                <!-- 1. ORDER SUMMARY (Unified Card: Items List + Totals) -->
                <div style="background: var(--snd-surface-soft); border: 1.5px solid var(--snd-border); border-radius: 12px; padding: 13px 15px; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; padding-bottom: 7px; border-bottom: 1px solid var(--snd-border);">
                        <span style="font-size: 0.92rem; font-weight: 800; color: #505469; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 7px;">
                            <i class="fas fa-receipt" style="color: var(--snd-primary-deep);"></i> Purchase Items Summary
                        </span>
                        <span style="font-size: 0.82rem; color: #626b7b; font-weight: 600; background: var(--snd-surface-soft); padding: 3px 10px; border-radius: 10px;">View-Only</span>
                    </div>

                    <!-- Scrollable items table -->
                    <div style="max-height: 155px; overflow-y: auto; margin-bottom: 10px; background: #ffffff; border: 1px solid var(--snd-border); border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                            <thead>
                                <tr style="background: var(--snd-surface-soft); border-bottom: 1px solid var(--snd-border); font-size: 0.86rem; color: #626b7b; text-transform: uppercase; position: sticky; top: 0; z-index: 1;">
                                    <th style="padding: 8px 10px; text-align: left; font-weight: 700;">Item</th>
                                    <th style="padding: 8px 10px; text-align: center; font-weight: 700; width: 48px;">Qty</th>
                                    <th style="padding: 8px 10px; text-align: right; font-weight: 700; width: 95px;">Cost</th>
                                    <th style="padding: 8px 10px; text-align: right; font-weight: 700; width: 100px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsRowsHtml}
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals breakdown -->
                    <div style="display: flex; flex-direction: column; gap: 5px; padding-top: 5px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.98rem; color: #626b7b;">
                            <span>Subtotal</span>
                            <strong style="color: #1a1a2e; font-family: monospace;">${currency} ${subtotal.toFixed(2)}</strong>
                        </div>
                        ${dAmount > 0 ? `
                            <div style="display: flex; justify-content: space-between; font-size: 0.98rem; color: #1f7a3d;">
                                <span>Discount ${dPercent ? `(${dPercent}%)` : ''}</span>
                                <strong style="font-family: monospace;">-${currency} ${dAmount.toFixed(2)}</strong>
                            </div>
                        ` : ""}
                        ${tAmount > 0 ? `
                            <div style="display: flex; justify-content: space-between; font-size: 0.98rem; color: var(--snd-primary-deep);">
                                <span>Tax ${tPercent ? `(${tPercent}%)` : ''}</span>
                                <strong style="font-family: monospace;">+${currency} ${tAmount.toFixed(2)}</strong>
                            </div>
                        ` : ""}
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1.5px dashed var(--snd-border-strong); margin-top: 4px;">
                            <span style="font-size: 1.15rem; font-weight: 800; color: #1a1a2e;">Payable Total</span>
                            <span style="font-size: 1.65rem; font-weight: 900; color: var(--snd-primary-deep); font-family: monospace;">${currency} ${total}</span>
                        </div>
                    </div>
                </div>

                <!-- 2. PAYMENT METHOD SELECTION -->
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <label style="font-weight: 800; font-size: 0.94rem; color: #505469; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 7px;">
                            <i class="fas fa-wallet" style="color: var(--snd-primary-deep);"></i> Payment Method
                        </label>
                        <span style="font-size: 0.84rem; color: #626b7b; font-weight: 600;">Choose Cash or Card</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <!-- Cash Option Card -->
                        <div id="swal-pay-cash" role="button" tabindex="0" style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 2px solid var(--snd-primary-deep); border-radius: 12px; background: var(--snd-primary-soft); cursor: pointer; transition: all 0.2s ease; position: relative; user-select: none; box-shadow: 0 2px 6px var(--snd-primary-ring);">
                            <input type="radio" name="swal_payment_mode" id="swal-radio-cash" value="cash" checked style="position: absolute; opacity: 0; pointer-events: none;" />
                            <div class="swal-pay-icon" style="width: 44px; height: 44px; border-radius: 10px; background: var(--snd-button-primary); color: var(--snd-button-primary-text); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; transition: all 0.2s;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div class="swal-pay-title" style="font-size: 1.15rem; font-weight: 800; color: #1a1a2e; line-height: 1.2;">Cash</div>
                                <div class="swal-pay-sub" style="font-size: 0.86rem; color: #626b7b; margin-top: 2px;">Cash Drawer</div>
                            </div>
                            <div class="swal-pay-indicator" style="width: 24px; height: 24px; border-radius: 50%; background: var(--snd-button-primary); color: var(--snd-button-primary-text); display: flex; align-items: center; justify-content: center; font-size: 0.82rem; flex-shrink: 0; transition: all 0.2s;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>

                        <!-- Card Option Card -->
                        <div id="swal-pay-card" role="button" tabindex="0" style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 2px solid var(--snd-border-strong); border-radius: 12px; background: var(--snd-surface-soft); cursor: pointer; transition: all 0.2s ease; position: relative; user-select: none;">
                            <input type="radio" name="swal_payment_mode" id="swal-radio-card" value="card" style="position: absolute; opacity: 0; pointer-events: none;" />
                            <div class="swal-pay-icon" style="width: 44px; height: 44px; border-radius: 10px; background: var(--snd-surface-soft); color: #626b7b; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; transition: all 0.2s;">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div class="swal-pay-title" style="font-size: 1.15rem; font-weight: 800; color: #1a1a2e; line-height: 1.2;">Card / Bank</div>
                                <div class="swal-pay-sub" style="font-size: 0.86rem; color: #626b7b; margin-top: 2px;">Electronic / POS</div>
                            </div>
                            <div class="swal-pay-indicator" style="width: 24px; height: 24px; border-radius: 50%; background: transparent; border: 2px solid var(--snd-border-strong); color: transparent; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; flex-shrink: 0; transition: all 0.2s;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. CASH SETTLEMENT PANEL -->
                <div id="swal-cash-panel" style="display: block; margin-bottom: 8px;">
                    <div style="background: #ffffff; border: 1.5px solid var(--snd-primary-deep); border-radius: 12px; padding: 14px 16px; box-shadow: 0 2px 8px var(--snd-primary-ring);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label for="swal-received-amount" style="font-weight: 800; font-size: 1.00rem; color: #1a1a2e; margin: 0; display: flex; align-items: center; gap: 7px;">
                                <i class="fas fa-hand-holding-usd" style="color: var(--snd-primary-deep);"></i> Paid Cash Amount
                            </label>
                            <span style="font-size: 0.92rem; font-weight: 700; color: #626b7b;">
                                Total Due: <b style="color: #1a1a2e;">${currency} ${total}</b>
                            </span>
                        </div>

                        <div style="position: relative; display: flex; align-items: center;">
                            <span style="position: absolute; left: 16px; font-weight: 800; color: #626b7b; font-size: 1.25rem; pointer-events: none;">${currency}</span>
                            <input
                                id="swal-received-amount"
                                type="number"
                                step="0.01"
                                min="0"
                                value="${total}"
                                style="width: 100%; box-sizing: border-box; padding: 11px 16px 11px 64px; font-size: 1.60rem; font-weight: 800; color: #1a1a2e; background: #ffffff; border: 1.5px solid var(--snd-border-strong); border-radius: 8px; outline: none;"
                                autocomplete="off"
                            />
                        </div>

                        <!-- Live Change Box -->
                        <div id="swal-change-box" style="margin-top: 10px; padding: 10px 14px; border-radius: 8px; background: #eaf9ee; border: 1px solid #c7ebd0; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.96rem; font-weight: 700; color: #166534;" id="swal-change-label">Change / Balance:</span>
                            <span style="font-size: 1.32rem; font-weight: 800; color: #1f7a3d; font-family: monospace;" id="swal-change-val">${currency} 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- 4. CARD SETTLEMENT PANEL -->
                <div id="swal-card-panel" style="display: none; margin-bottom: 8px;">
                    <div style="background: #ffffff; border: 1.5px solid var(--snd-primary-deep); border-radius: 12px; padding: 14px 16px; box-shadow: 0 2px 8px var(--snd-primary-ring);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--snd-surface-soft);">
                            <span style="font-size: 0.94rem; font-weight: 800; color: #505469; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 7px;">
                                <i class="fas fa-credit-card" style="color: var(--snd-primary-deep);"></i> Card & Account Details
                            </span>
                            <span style="font-size: 0.82rem; font-weight: 700; background: var(--snd-primary-soft); color: var(--snd-primary-deep); border: 1px solid var(--snd-border-strong); border-radius: 6px; padding: 3px 10px;">
                                POS TERMINAL
                            </span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="font-size: 0.92rem; font-weight: 700; color: #505469; display: block; margin-bottom: 6px;">
                                    Account / Card Holder
                                </label>
                                <input
                                    id="swal-card-holder-name"
                                    type="text"
                                    placeholder="e.g. Supplier Name"
                                    autocomplete="off"
                                    style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 1.02rem; font-weight: 600; color: #1a1a2e; background: #ffffff; border: 1.5px solid var(--snd-border-strong); border-radius: 8px; outline: none;"
                                />
                            </div>
                            <div>
                                <label style="font-size: 0.92rem; font-weight: 700; color: #505469; display: block; margin-bottom: 6px;">
                                    Last 4 Digits / Ref
                                </label>
                                <input
                                    id="swal-card-last-four"
                                    type="text"
                                    maxlength="6"
                                    placeholder="4242"
                                    autocomplete="off"
                                    style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 1.02rem; font-weight: 700; color: #1a1a2e; background: #ffffff; border: 1.5px solid var(--snd-border-strong); border-radius: 8px; outline: none;"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            html: modalHtml,
            width: 720,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle" style="margin-right: 6px;"></i> Complete Purchase',
            confirmButtonColor: "var(--snd-button-primary)",
            cancelButtonText: "Cancel",
            cancelButtonColor: "#626b7b",
            focusConfirm: false,
            didOpen: () => {
                const cashCard = document.getElementById("swal-pay-cash");
                const cardCard = document.getElementById("swal-pay-card");
                const cashRadio = document.getElementById("swal-radio-cash");
                const cardRadio = document.getElementById("swal-radio-card");
                const cashPanel = document.getElementById("swal-cash-panel");
                const cardPanel = document.getElementById("swal-card-panel");
                const receivedInput = document.getElementById("swal-received-amount");
                const changeVal = document.getElementById("swal-change-val");

                const updateChange = () => {
                    const received = parseFloat(receivedInput.value) || 0;
                    const change = received - parseFloat(total);
                    if (change >= 0) {
                        changeVal.innerText = `${currency} ${change.toFixed(2)}`;
                        changeVal.style.color = "#1f7a3d";
                    } else {
                        changeVal.innerText = `Balance Due: ${currency} ${Math.abs(change).toFixed(2)}`;
                        changeVal.style.color = "#c9342d";
                    }
                };

                receivedInput.addEventListener("input", updateChange);

                const selectMode = (mode) => {
                    if (mode === "cash") {
                        cashRadio.checked = true;
                        cashCard.style.border = "2px solid var(--snd-primary-deep)";
                        cashCard.style.background = "var(--snd-primary-soft)";
                        cardCard.style.border = "2px solid var(--snd-border-strong)";
                        cardCard.style.background = "var(--snd-surface-soft)";
                        cashPanel.style.display = "block";
                        cardPanel.style.display = "none";
                    } else {
                        cardRadio.checked = true;
                        cardCard.style.border = "2px solid var(--snd-primary-deep)";
                        cardCard.style.background = "var(--snd-primary-soft)";
                        cashCard.style.border = "2px solid var(--snd-border-strong)";
                        cashCard.style.background = "var(--snd-surface-soft)";
                        cashPanel.style.display = "none";
                        cardPanel.style.display = "block";
                    }
                };

                cashCard.addEventListener("click", () => selectMode("cash"));
                cardCard.addEventListener("click", () => selectMode("card"));
            },
            preConfirm: () => {
                const mode = document.querySelector('input[name="swal_payment_mode"]:checked')?.value || "cash";
                const received = parseFloat(document.getElementById("swal-received-amount")?.value) || grandTotal;
                return {
                    payment_method: mode,
                    received_amount: received
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.handleSubmitPurchase(result.value);
            }
        });
    }

    handleSubmitPurchase(paymentDetails = {}) {
        const { cart, supplier_id, branch_id, counter_id, purchase_date, taxPercent, taxAmount, discountPercent, discountAmount, notes } = this.state;

        const subtotal = this.calculateSubtotal();
        const tAmount = parseFloat(taxAmount) || 0;
        const dAmount = parseFloat(discountAmount) || 0;
        const totalAmount = Math.max(0, subtotal + tAmount - dAmount);
        const method = paymentDetails.payment_method || "cash";

        const payload = {
            supplier_id,
            branch_id: branch_id || null,
            counter_id: counter_id || null,
            purchase_date,
            subtotal,
            tax_percent: parseFloat(taxPercent) || 0,
            tax_amount: tAmount,
            discount_percent: parseFloat(discountPercent) || 0,
            discount_amount: dAmount,
            total_amount: totalAmount,
            payment_method: method,
            status: "completed",
            notes,
            items: cart.map((item) => ({
                product_id: item.id,
                quantity: parseFloat(item.pivot.quantity) || 1,
                purchase_price: parseFloat(item.pivot.purchase_price) || 0,
            })),
        };

        this.setState({ loading: true });

        axios.post("/admin/purchases", payload, { headers: { Accept: "application/json" } })
            .then((res) => {
                const purchase = res.data.purchase;
                this.setState({
                    cart: [],
                    subtotal: 0,
                    taxPercent: "",
                    taxAmount: "",
                    discountPercent: "",
                    discountAmount: "",
                    notes: "",
                    loading: false,
                    lastPurchase: purchase,
                    showReceiptModal: false,
                });
                this.loadProducts();

                // Open print thermal receipt popup matching sale receipt
                this.showReceiptPreview(purchase);
            })
            .catch((err) => {
                this.setState({ loading: false });
                Swal.fire({
                    icon: "error",
                    title: "Purchase Failed",
                    text: err.response?.data?.message || "Failed to create purchase. Please try again.",
                });
            });
    }

    showReceiptPreview(purchase) {
        if (!purchase) return;
        const receiptHTML = this.buildReceiptHtml(purchase);

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
            confirmButtonColor: "var(--snd-button-primary)",
            cancelButtonText: "Close",
            cancelButtonColor: "#626b7b",
            customClass: { popup: "receipt-modal" },
        }).then(result => {
            if (result.isConfirmed) {
                this.printReceipt(purchase, receiptHTML);
            }
        });
    }

    buildReceiptHtml(purchase) {
        const currency = window.APP?.currency_symbol || "Rs.";
        const supplierName = purchase.supplier
            ? `${purchase.supplier.first_name || ""} ${purchase.supplier.last_name || ""}`.trim() || purchase.supplier.name || "General Supplier"
            : "General Supplier";
        const createdAtObj = purchase.created_at ? new Date(purchase.created_at) : new Date();
        const invoiceDate = createdAtObj.toLocaleDateString('en-US');
        const invoiceTime = createdAtObj.toLocaleTimeString('en-US', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        }) + ' PST';
        const createdAt = `${invoiceDate}, ${invoiceTime}`;
        const branchName = purchase.branch?.name || "Main Branch";
        const counterName = purchase.counter?.name || "Counter 1";
        const purchaseNo = purchase.purchase_no || `PUR-${String(purchase.id).padStart(4, "0")}`;

        const headerHTML = CommonHelper.getReceiptHeaderHTML({
            branchName: branchName,
            branchAddress: purchase.branch?.address,
            branchPhone: purchase.branch?.phone,
        });
        const footerHTML = CommonHelper.getReceiptFooterHTML(createdAt);

        const items = (purchase.items || []).map(item => {
            const quantity = Number(item.quantity || 1);
            const unitPrice = Number(item.purchase_price ?? item.price ?? 0);
            const lineTotal = quantity * unitPrice;
            const name = item.product?.name || item.item_name || "Product";

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

        const subtotal = Number(purchase.subtotal || 0);
        const taxPercent = Number(purchase.tax_percent || 0);
        const taxAmount = Number(purchase.tax_amount || 0);
        const discountPercent = Number(purchase.discount_percent || 0);
        const discountAmount = Number(purchase.discount_amount || 0);
        const total = Number(purchase.total_amount || Math.max(0, subtotal + taxAmount - discountAmount));
        const paymentMode = String(purchase.payment_method || "cash").toLowerCase();
        const isCard = paymentMode === "card";

        const metaRows = [
            { label: "Purchase No.", value: purchaseNo },
            { label: "Voucher Type", value: "GOODS RECEIPT NOTE" },
            { label: "Payment Mode", value: isCard ? "CARD (ELECTRONIC)" : "CASH" },
            { label: "Purchase Date", value: purchase.purchase_date || invoiceDate },
            { label: "Time", value: invoiceTime },
            { label: "Terminal", value: counterName },
            { label: "Branch", value: branchName },
            { label: "Supplier", value: supplierName },
        ].filter(Boolean);

        const metaHTML = metaRows.map(row =>
            `<div class="meta-row"><span class="meta-label">${row.label}</span><span class="meta-value">${row.value}</span></div>`
        ).join("");

        return `
            <div id="thermal-receipt" class="thermal-receipt">
                ${headerHTML}

                <div class="receipt-divider-double"></div>
                <div class="meta-grid">${metaHTML}</div>
                <div class="receipt-divider-double"></div>
                <div class="receipt-table-head"><span class="col-item">ITEM</span><span class="col-qty">QTY</span><span class="col-price">PRICE</span><span class="col-total">AMOUNT</span></div>
                <div class="receipt-divider"></div>
                ${items}
                <div class="receipt-divider"></div>
                <div class="receipt-summary">
                    <div class="summary-row"><span>SUBTOTAL:</span><span>${currency} ${subtotal.toFixed(2)}</span></div>
                    ${discountAmount > 0 ? `<div class="summary-row"><span>Discount (${discountPercent.toFixed(2)}%)</span><span>-${currency} ${discountAmount.toFixed(2)}</span></div>` : ""}
                    ${taxAmount > 0 ? `<div class="summary-row"><span>Tax (${taxPercent.toFixed(2)}%)</span><span>${currency} ${taxAmount.toFixed(2)}</span></div>` : ""}
                    <div class="receipt-divider"></div>
                    <div class="summary-row grand-total"><span>NET TOTAL</span><span>${currency} ${total.toFixed(2)}</span></div>
                    <div class="receipt-divider"></div>
                    ${isCard
                        ? `<div class="summary-row"><span>Payment:</span><span>Card / Bank</span></div>
                           <div class="summary-row"><span>Amount Paid:</span><span>${currency} ${total.toFixed(2)}</span></div>`
                        : `<div class="summary-row"><span>Payment:</span><span>Cash</span></div>
                           <div class="summary-row"><span>Amount Paid:</span><span>${currency} ${total.toFixed(2)}</span></div>`
                    }
                </div>

                <div class="receipt-divider"></div>

                <div class="terms-block">
                    <div class="terms-title">Goods Receipt &amp; Inventory Note</div>
                    -All items received and inspected in good condition.<br>
                    -Quantities updated in inventory ledger.<br>
                    -Authorized Purchase Voucher.
                </div>
                <div class="receipt-divider"></div>
                ${footerHTML}
            </div>
        `;
    }

    printReceipt(purchase, receiptHtmlContent = null) {
        if (!purchase) return;
        let content = receiptHtmlContent;
        if (!content) {
            content = this.buildReceiptHtml(purchase);
        }
        if (!content) return;

        const printStyles = CommonHelper.getThermalPrintStyles();
        const title = `Purchase Receipt #${purchase.purchase_no || purchase.id}`;

        const printWin = window.open("", "_blank", "width=450,height=700");
        try { Swal.close(); } catch (e) {}

        if (!printWin) {
            alert("Popup blocker blocked the receipt print window. Please allow popups for this site.");
            return;
        }

        printWin.document.open();
        printWin.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>${title}</title>
                ${printStyles}
            </head>
            <body>
                ${content}
                <script>
                    window.addEventListener('load', function() {
                        setTimeout(function() {
                            window.focus();
                            window.print();
                            window.close();
                        }, 250);
                    });
                <\/script>
            </body>
            </html>
        `);
        printWin.document.close();
    }

    render() {
        const {
            cart,
            products,
            categories,
            suppliers,
            branches,
            branch_id,
            counter_id,
            search,
            barcode,
            activeCategory,
            supplier_id,
            purchase_date,
            payment_method,
            subtotal,
            taxPercent,
            taxAmount,
            discountPercent,
            discountAmount,
            notes,
            loading,
            showReceiptModal,
            lastPurchase,
            searchFocused,
        } = this.state;

        const currency = window.APP?.currency_symbol || "Rs.";

        // Selected branch & its counters
        const selectedBranch = branches.find(b => String(b.id) === String(branch_id));
        const activeCounters = selectedBranch?.counters || [];

        // Filter products by category and search
        const filteredProducts = products.filter((prod) => {
            const matchesCat = activeCategory === "all" || String(prod.category_id) === String(activeCategory);
            const matchesSearch = !search ||
                prod.name.toLowerCase().includes(search.toLowerCase()) ||
                (prod.barcode && prod.barcode.toLowerCase().includes(search.toLowerCase()));
            return matchesCat && matchesSearch;
        });

        const totalTax = parseFloat(taxAmount) || 0;
        const totalDiscount = parseFloat(discountAmount) || 0;
        const grandTotal = Math.max(0, subtotal + totalTax - totalDiscount);
        const totalItemsCount = sum(cart.map((item) => parseFloat(item.pivot?.quantity) || 1));

        const getCategoryIcon = (label) => {
            const l = String(label || "").toLowerCase();
            if (l.includes("food") || l.includes("burg") || l.includes("pizza") || l.includes("bbq")) return "fas fa-utensils";
            if (l.includes("drink") || l.includes("tea") || l.includes("coffee") || l.includes("bever")) return "fas fa-mug-hot";
            if (l.includes("dessert") || l.includes("cake") || l.includes("sweet")) return "fas fa-ice-cream";
            return "fas fa-box-open";
        };

        const renderProductCard = (prod, idx) => {
            const stock = prod.quantity !== undefined ? prod.quantity : 0;
            const costRate = prod.purchase_price ? parseFloat(prod.purchase_price) : (prod.price ? parseFloat(prod.price) : 0);
            const cartItem = cart.find(c => c.id === prod.id);
            const inCartQty = cartItem ? parseFloat(cartItem.pivot?.quantity || 0) : 0;

            return (
                <div
                    key={prod.id}
                    className="pos-product-card"
                    onClick={() => this.addProductToCart(prod)}
                    style={{
                        background: inCartQty > 0 ? "var(--snd-primary-soft)" : "#ffffff",
                        border: inCartQty > 0 ? "1.5px solid var(--snd-primary)" : "1px solid var(--snd-border)",
                        borderRadius: "14px",
                        overflow: "hidden",
                        boxShadow: inCartQty > 0 ? "0 6px 16px var(--snd-primary-ring)" : "0 2px 6px rgba(15, 23, 42, 0.03)",
                        cursor: "pointer",
                        transition: "all 0.18s ease",
                        boxSizing: "border-box",
                    }}
                >
                    <div style={{ height: "92px", background: "radial-gradient(circle at 82% 24%, rgba(151,134,238,.17) 0 30px, transparent 31px), linear-gradient(135deg, #f7f5ff 0%, var(--snd-primary-soft) 56%, #e9e4ff 100%)", display: "flex", alignItems: "center", justifyContent: "center" }}>
                        <div style={{ width: "50px", height: "50px", borderRadius: "15px", background: "var(--snd-primary)", display: "flex", alignItems: "center", justifyContent: "center", fontSize: "1.45rem", color: "#ffffff", boxShadow: "0 6px 14px var(--snd-primary-ring)" }}>
                            <i className="fas fa-box"></i>
                        </div>
                    </div>
                    <div style={{ padding: "14px 16px 16px", display: "flex", flexDirection: "column", flex: 1 }}>
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "8px" }}>
                            <span style={{ fontSize: "0.74rem", fontWeight: 800, color: "#505469", background: "var(--snd-surface-soft)", padding: "2px 8px", borderRadius: "6px", letterSpacing: "0.03em", textTransform: "uppercase" }}>
                                {prod.category?.name || "General"}
                            </span>
                            <span style={{ fontSize: "0.74rem", color: "var(--snd-primary)", fontWeight: 800, background: "var(--snd-primary-soft)", padding: "2px 7px", borderRadius: "6px", border: "1px solid var(--snd-border-strong)" }}>
                                #{idx + 1}
                            </span>
                        </div>
                        <div style={{ fontWeight: 800, fontSize: "1.05rem", color: "#1a1a2e", marginBottom: "6px", minHeight: "44px", lineHeight: "1.35", display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden", letterSpacing: "-0.01em" }} title={prod.name}>
                            {prod.name}
                        </div>
                        <div style={{ fontSize: "0.74rem", color: "#626b7b", fontWeight: 600, marginBottom: "8px" }}>
                            Current Stock: <span style={{ color: stock > 0 ? "#1f7a3d" : "#c9342d", fontWeight: 800 }}>{stock}</span>
                        </div>
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", gap: "8px", paddingTop: "12px", borderTop: "1.5px dashed var(--snd-border)", marginTop: "auto" }}>
                            <div>
                                <div style={{ fontSize: "0.68rem", color: "#626b7b", fontWeight: 700, textTransform: "uppercase" }}>Cost Rate</div>
                                <span style={{ fontSize: "1.18rem", fontWeight: 850, color: inCartQty > 0 ? "var(--snd-primary)" : "var(--snd-ink)", minWidth: 0, whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis", letterSpacing: "-0.02em" }}>
                                    {currency}{costRate.toFixed(2)}
                                </span>
                            </div>
                            <div style={{ display: "flex", alignItems: "center", gap: "4px", flexShrink: 0 }}>
                                <button
                                    type="button"
                                    disabled={inCartQty === 0}
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        if (cartItem) {
                                            if (inCartQty > 1) {
                                                this.handleChangeQty(prod.id, inCartQty - 1);
                                            } else {
                                                this.handleClickDelete(prod.id);
                                            }
                                        }
                                    }}
                                    style={{ width: "32px", height: "32px", borderRadius: "9px", border: inCartQty > 0 ? "1px solid var(--snd-border-strong)" : "1px solid var(--snd-border)", background: inCartQty > 0 ? "#fff" : "var(--snd-surface-soft)", color: inCartQty > 0 ? "#505469" : "var(--snd-border-strong)", cursor: inCartQty > 0 ? "pointer" : "default", fontSize: "1rem", fontWeight: 800, display: "flex", alignItems: "center", justifyContent: "center", padding: 0, boxSizing: "border-box", transition: "all 0.15s ease", boxShadow: inCartQty > 0 ? "0 2px 6px rgba(0,0,0,0.06)" : "none" }}
                                    title="Decrease quantity"
                                >-</button>
                                {inCartQty > 0 && (
                                    <span style={{ minWidth: "24px", textAlign: "center", fontSize: "0.95rem", fontWeight: 800, color: "var(--snd-primary)" }}>
                                        {inCartQty}
                                    </span>
                                )}
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        this.addProductToCart(prod);
                                    }}
                                    style={{ width: "32px", height: "32px", borderRadius: "9px", border: "none", background: "var(--snd-button-primary)", color: "var(--snd-button-primary-text)", cursor: "pointer", fontSize: "1rem", fontWeight: 800, display: "flex", alignItems: "center", justifyContent: "center", padding: 0, boxSizing: "border-box", boxShadow: "0 3px 8px var(--snd-primary-ring)", transition: "all 0.15s ease" }}
                                    title="Add to purchase cart"
                                >+</button>
                            </div>
                        </div>
                    </div>
                </div>
            );
        };

        return (
            <div style={{ width: "100%", height: "100%", display: "flex", background: "var(--snd-surface-soft)", overflow: "hidden" }}>
                <div style={{ display: "grid", gridTemplateColumns: "minmax(0, 1fr) 410px", gridTemplateRows: "1fr", flex: 1, height: "100%", overflow: "hidden" }}>

                    {/* ══════════════════════════════════════════════════════════════════════
                        LEFT PANEL: PRODUCT CATALOG (IDENTICAL TO POS CART SCREEN)
                        ══════════════════════════════════════════════════════════════════════ */}
                    <div style={{ display: "flex", flexDirection: "column", gap: "10px", padding: "10px 12px", borderRight: "1px solid var(--snd-border)", background: "var(--snd-surface-soft)", minWidth: 0, overflow: "hidden", height: "100%", boxSizing: "border-box" }}>

                        {/* Top Bar: Items Count + Quick Search Box */}
                        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: "10px", background: "#ffffff", padding: "10px 14px", borderRadius: "14px", border: "1px solid var(--snd-border)", boxShadow: "0 2px 8px rgba(15, 23, 42, 0.03)", flexShrink: 0 }}>
                            <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                                <div style={{ width: "34px", height: "34px", borderRadius: "10px", background: "var(--snd-primary)", display: "flex", alignItems: "center", justifyContent: "center", color: "var(--snd-button-primary-text)", fontSize: "0.95rem", boxShadow: "0 3px 8px var(--snd-primary-ring)" }}>
                                    <i className="fas fa-boxes"></i>
                                </div>
                                <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                                    <span style={{ fontSize: "0.98rem", fontWeight: 800, color: "#1a1a2e" }}>
                                        Purchase Catalog
                                    </span>
                                    <span style={{ background: "var(--snd-primary-soft)", color: "var(--snd-primary-deep)", fontSize: "0.74rem", fontWeight: 700, padding: "2px 8px", borderRadius: "12px", border: "1px solid var(--snd-border-strong)" }}>
                                        {filteredProducts.length} items
                                    </span>
                                </div>
                            </div>

                            {/* Search Box */}
                            <div style={{ display: "flex", alignItems: "center", gap: "6px", flexShrink: 0 }}>
                                <div
                                    style={{
                                        display: "flex",
                                        gap: "8px",
                                        alignItems: "center",
                                        background: "#ffffff",
                                        border: searchFocused ? "1.5px solid var(--snd-primary)" : "1.5px solid var(--snd-border-strong)",
                                        boxShadow: searchFocused ? "0 0 0 3px var(--snd-primary-ring), 0 2px 8px rgba(0,0,0,0.04)" : "0 1px 3px rgba(0,0,0,0.02)",
                                        borderRadius: "12px",
                                        padding: "6px 14px",
                                        transition: "all 0.22s cubic-bezier(0.4, 0, 0.2, 1)",
                                        width: searchFocused || search ? "300px" : "260px",
                                        maxWidth: "100%",
                                        boxSizing: "border-box",
                                    }}
                                >
                                    <i className="fas fa-search" style={{ color: searchFocused ? "var(--snd-primary-deep)" : "var(--snd-muted)", fontSize: "0.82rem", flexShrink: 0 }}></i>
                                    <input
                                        type="text"
                                        value={search}
                                        placeholder="Search products or barcode..."
                                        onFocus={() => this.setState({ searchFocused: true })}
                                        onBlur={() => this.setState({ searchFocused: false })}
                                        onChange={(e) => {
                                            const val = e.target.value;
                                            this.setState({ search: val, barcode: val });
                                            this.loadProducts(val);
                                        }}
                                        onKeyDown={(e) => {
                                            if (e.key === "Enter") {
                                                this.handleScanBarcode(e);
                                            }
                                        }}
                                        style={{ border: "none", background: "transparent", outline: "none", fontSize: "0.84rem", width: "100%", minWidth: 0, color: "#1a1a2e", fontWeight: 500 }}
                                    />
                                    {search && (
                                        <i
                                            className="fas fa-times-circle"
                                            onClick={() => {
                                                this.setState({ search: "", barcode: "" });
                                                this.loadProducts("");
                                            }}
                                            style={{ cursor: "pointer", color: "#626b7b", fontSize: "0.85rem", flexShrink: 0 }}
                                            title="Clear search"
                                        />
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Category Pills Filter Bar */}
                        <div style={{
                            background: "#ffffff",
                            borderRadius: "14px",
                            border: "1px solid var(--snd-border)",
                            padding: "10px 14px",
                            boxShadow: "0 2px 8px rgba(15, 23, 42, 0.03)",
                            display: "flex",
                            flexWrap: "wrap",
                            alignItems: "center",
                            gap: "8px 10px",
                            flexShrink: 0,
                            maxHeight: "160px",
                            overflowY: "auto",
                            boxSizing: "border-box",
                        }}>
                            <button
                                type="button"
                                onClick={() => this.setState({ activeCategory: "all" })}
                                style={{
                                    display: "inline-flex",
                                    alignItems: "center",
                                    gap: "7px",
                                    padding: "7px 14px",
                                    borderRadius: "10px",
                                    border: activeCategory === "all" ? "1.5px solid var(--snd-primary)" : "1px solid var(--snd-border)",
                                    background: activeCategory === "all" ? "var(--snd-primary)" : "#ffffff",
                                    color: activeCategory === "all" ? "var(--snd-button-primary-text)" : "var(--snd-ink-soft)",
                                    fontWeight: activeCategory === "all" ? 700 : 600,
                                    fontSize: "0.81rem",
                                    cursor: "pointer",
                                    boxShadow: activeCategory === "all" ? "0 3px 10px var(--snd-primary-ring)" : "none",
                                    transition: "all 0.16s ease",
                                }}
                            >
                                <i className="fas fa-border-all" style={{ fontSize: "0.85rem", color: activeCategory === "all" ? "var(--snd-button-primary-text)" : "var(--snd-primary)" }}></i>
                                <span>All</span>
                                <span style={{
                                    fontSize: "0.68rem",
                                    fontWeight: 700,
                                    padding: "2px 7px",
                                    borderRadius: "8px",
                                    background: activeCategory === "all" ? "rgba(255, 255, 255, 0.24)" : "var(--snd-surface-soft)",
                                    color: activeCategory === "all" ? "#ffffff" : "#505469",
                                }}>
                                    {products.length}
                                </span>
                            </button>

                            {categories.filter((cat) => products.some((product) => String(product.category_id) === String(cat.id))).map((cat) => {
                                const isActive = String(activeCategory) === String(cat.id);
                                const count = products.filter(p => String(p.category_id) === String(cat.id)).length;
                                return (
                                    <button
                                        key={cat.id}
                                        type="button"
                                        onClick={() => this.setState({ activeCategory: cat.id })}
                                        style={{
                                            display: "inline-flex",
                                            alignItems: "center",
                                            gap: "7px",
                                            padding: "7px 14px",
                                            borderRadius: "10px",
                                            border: isActive ? "1.5px solid var(--snd-primary)" : "1px solid var(--snd-border)",
                                            background: isActive ? "var(--snd-primary)" : "#ffffff",
                                            color: isActive ? "var(--snd-button-primary-text)" : "var(--snd-ink-soft)",
                                            fontWeight: isActive ? 700 : 600,
                                            fontSize: "0.81rem",
                                            cursor: "pointer",
                                            boxShadow: isActive ? "0 3px 10px var(--snd-primary-ring)" : "none",
                                            transition: "all 0.16s ease",
                                        }}
                                    >
                                        <i className={getCategoryIcon(cat.name)} style={{ fontSize: "0.85rem", color: isActive ? "var(--snd-button-primary-text)" : "var(--snd-primary)" }}></i>
                                        <span>{cat.name}</span>
                                        <span style={{
                                            fontSize: "0.68rem",
                                            fontWeight: 700,
                                            padding: "2px 7px",
                                            borderRadius: "8px",
                                            background: isActive ? "rgba(255, 255, 255, 0.24)" : "var(--snd-surface-soft)",
                                            color: isActive ? "#ffffff" : "#505469",
                                        }}>
                                            {count}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>

                        {/* Product Cards Grid Area */}
                        <div style={{ flex: 1, minHeight: 0, overflowY: "auto", overflowX: "hidden", padding: "4px 2px", boxSizing: "border-box" }}>
                            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(225px, 1fr))", gap: "14px", width: "100%", boxSizing: "border-box" }}>
                                {filteredProducts.length === 0 ? (
                                    <div style={{ gridColumn: "1 / -1", background: "#fff", borderRadius: "16px", padding: "50px 20px", textAlign: "center", color: "#626b7b", border: "1px solid var(--snd-border)" }}>
                                        <div style={{ width: "48px", height: "48px", borderRadius: "50%", background: "var(--snd-surface-soft)", display: "inline-flex", alignItems: "center", justifyContent: "center", color: "#626b7b", fontSize: "1.3rem", marginBottom: "10px" }}>
                                            <i className="fas fa-box-open"></i>
                                        </div>
                                        <div style={{ fontWeight: 800, fontSize: "0.95rem", color: "#1a1a2e", marginBottom: "4px" }}>
                                            {search ? `No products matching "${search}"` : "No products found in this category"}
                                        </div>
                                        <div style={{ fontSize: "0.76rem", color: "#626b7b" }}>
                                            Select another category or clear the search query.
                                        </div>
                                    </div>
                                ) : (
                                    filteredProducts.map((p, idx) => renderProductCard(p, idx))
                                )}
                            </div>
                        </div>
                    </div>

                    {/* ══════════════════════════════════════════════════════════════════════
                        RIGHT PANEL: PURCHASE ORDER SUMMARY (MATCHING IMAGE 1 EXACTLY)
                        ══════════════════════════════════════════════════════════════════════ */}
                    <div style={{ width: "410px", minWidth: "410px", background: "#ffffff", display: "flex", flexDirection: "column", height: "100%", overflow: "hidden", borderLeft: "1px solid var(--snd-border)", boxShadow: "-4px 0 20px rgba(15,23,42,0.03)" }}>

                        {/* Top Header: Order Title & Reset */}
                        <div style={{ padding: "14px 18px", borderBottom: "1px solid var(--snd-surface-soft)", display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                            <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                                <span style={{ fontSize: "1.05rem", fontWeight: 800, color: "#1a1a2e", letterSpacing: "-0.01em" }}>
                                    Purchase Order #-
                                </span>
                            </div>
                            <button
                                type="button"
                                onClick={this.handleEmptyCart}
                                disabled={cart.length === 0}
                                style={{ border: "none", background: "transparent", cursor: cart.length === 0 ? "default" : "pointer", color: cart.length === 0 ? "var(--snd-border-strong)" : "#626b7b", fontSize: "0.95rem" }}
                                title="Reset / Clear Cart"
                            >
                                <i className="fas fa-sync-alt"></i>
                            </button>
                        </div>

                        {/* Supplier Selector Section (Identical to CUSTOMER box in Image 1) */}
                        <div style={{ padding: "12px 16px", borderBottom: "1px solid var(--snd-surface-soft)", background: "#fbfcfd" }}>
                            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "8px" }}>
                                <div style={{ minHeight: "32px", display: "flex", alignItems: "center", gap: "8px", fontSize: "0.74rem", fontWeight: 800, color: "var(--snd-ink-soft)", textTransform: "uppercase", letterSpacing: "0.06em" }}>
                                    <span style={{ width: "28px", height: "28px", flex: "0 0 28px", borderRadius: "8px", background: "var(--snd-primary)", color: "#ffffff", display: "inline-flex", alignItems: "center", justifyContent: "center", fontSize: "0.8rem", boxShadow: "0 3px 8px var(--snd-primary-ring)" }}>
                                        <i className="fas fa-truck-loading"></i>
                                    </span>
                                    <span>Supplier</span>
                                </div>
                                <button
                                    type="button"
                                    onClick={this.showAddSupplierModal}
                                    style={{
                                        background: "var(--snd-button-primary)",
                                        color: "var(--snd-button-primary-text)",
                                        border: "none",
                                        height: "32px",
                                        boxSizing: "border-box",
                                        borderRadius: "8px",
                                        padding: "0 11px",
                                        fontSize: "0.74rem",
                                        fontWeight: 700,
                                        cursor: "pointer",
                                        display: "inline-flex",
                                        alignItems: "center",
                                        gap: "4px",
                                        boxShadow: "0 2px 5px var(--snd-primary-ring)"
                                    }}
                                >
                                    <i className="fas fa-plus"></i> Add New
                                </button>
                            </div>
                            <select
                                value={supplier_id}
                                onChange={(e) => this.setState({ supplier_id: e.target.value })}
                                style={{
                                    width: "100%",
                                    minHeight: "40px",
                                    boxSizing: "border-box",
                                    padding: "8px 12px",
                                    fontSize: "0.85rem",
                                    border: "1.5px solid var(--snd-border-strong)",
                                    borderRadius: "10px",
                                    background: "#ffffff",
                                    color: "#1a1a2e",
                                    fontWeight: 600,
                                    outline: "none"
                                }}
                            >
                                <option value="">Search / Select Supplier...</option>
                                {suppliers.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.first_name} {s.last_name} {s.phone ? `(${s.phone})` : ""}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Branch & Counter Selector (Branch-Wise Purchasing Option) */}
                        <div style={{ padding: "10px 16px", borderBottom: "1px solid var(--snd-surface-soft)", background: "#fbfcfd", display: "grid", gridTemplateColumns: "1fr 1fr", gap: "8px" }}>
                            <div>
                                <label style={{ fontSize: "0.72rem", fontWeight: 800, color: "#505469", marginBottom: "3px", display: "flex", alignItems: "center", gap: "4px" }}>
                                    <i className="fas fa-code-branch text-primary"></i> Branch
                                </label>
                                <select
                                    value={branch_id}
                                    onChange={(e) => this.handleBranchChange(e.target.value)}
                                    style={{
                                        width: "100%",
                                        minHeight: "38px",
                                        boxSizing: "border-box",
                                        padding: "6px 8px",
                                        fontSize: "0.8rem",
                                        border: "1.5px solid var(--snd-border-strong)",
                                        borderRadius: "8px",
                                        background: "#ffffff",
                                        color: "#1a1a2e",
                                        fontWeight: 600,
                                        outline: "none"
                                    }}
                                >
                                    {branches.map(b => (
                                        <option key={b.id} value={b.id}>{b.name}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label style={{ fontSize: "0.72rem", fontWeight: 800, color: "#505469", marginBottom: "3px", display: "flex", alignItems: "center", gap: "4px" }}>
                                    <i className="fas fa-desktop text-primary"></i> Counter
                                </label>
                                <select
                                    value={counter_id}
                                    onChange={(e) => this.setState({ counter_id: e.target.value })}
                                    style={{
                                        width: "100%",
                                        minHeight: "38px",
                                        boxSizing: "border-box",
                                        padding: "6px 8px",
                                        fontSize: "0.8rem",
                                        border: "1.5px solid var(--snd-border-strong)",
                                        borderRadius: "8px",
                                        background: "#ffffff",
                                        color: "#1a1a2e",
                                        fontWeight: 600,
                                        outline: "none"
                                    }}
                                >
                                    {activeCounters.length === 0 ? (
                                        <option value="">No counter</option>
                                    ) : (
                                        activeCounters.map(c => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))
                                    )}
                                </select>
                            </div>
                        </div>

                        {/* Ordered Items List Section */}
                        <div style={{ padding: "10px 16px 6px", display: "flex", alignItems: "center", justifyContent: "space-between", borderBottom: "1px solid var(--snd-surface-soft)" }}>
                            <span style={{ fontSize: "0.88rem", fontWeight: 800, color: "#1a1a2e" }}>Ordered Items</span>
                            <span style={{ fontSize: "0.74rem", color: "#626b7b", fontWeight: 700 }}>Total Items: {totalItemsCount}</span>
                        </div>

                        <div style={{ flex: 1, overflowY: "auto", padding: "8px 12px" }} className="pos-order-items">
                            {cart.length === 0 ? (
                                <div style={{ height: "100%", display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", color: "#626b7b", padding: "30px 10px" }}>
                                    <i className="fas fa-shopping-cart" style={{ fontSize: "2.4rem", color: "var(--snd-border)", marginBottom: "10px" }}></i>
                                    <div style={{ fontSize: "0.88rem", fontWeight: 700, color: "#626b7b" }}>Cart is empty</div>
                                    <div style={{ fontSize: "0.75rem", color: "var(--snd-border-strong)", marginTop: "4px", textAlign: "center" }}>
                                        Click products or scan barcode to add items
                                    </div>
                                </div>
                            ) : (
                                cart.map((item) => {
                                    const qty = parseFloat(item.pivot?.quantity) || 1;
                                    const costRate = parseFloat(item.pivot?.purchase_price) || 0;
                                    const lineTotal = qty * costRate;

                                    return (
                                        <div
                                            key={item.id}
                                            style={{
                                                padding: "10px 12px",
                                                borderRadius: "12px",
                                                border: "1px solid var(--snd-border)",
                                                background: "#ffffff",
                                                marginBottom: "8px",
                                                boxShadow: "0 1px 3px rgba(0,0,0,0.02)",
                                                display: "flex",
                                                flexDirection: "column",
                                                gap: "6px"
                                            }}
                                        >
                                            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", gap: "6px" }}>
                                                <div style={{ flex: 1, minWidth: 0 }}>
                                                    <div style={{ fontSize: "0.88rem", fontWeight: 800, color: "#1a1a2e", whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }} title={item.name}>
                                                        {item.name}
                                                    </div>
                                                    {item.barcode && (
                                                        <div style={{ fontSize: "0.68rem", color: "#626b7b", fontFamily: "monospace" }}>
                                                            {item.barcode}
                                                        </div>
                                                    )}
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() => this.handleClickDelete(item.id)}
                                                    style={{ border: "none", background: "transparent", color: "#626b7b", cursor: "pointer", fontSize: "0.85rem", padding: "2px" }}
                                                    title="Remove item"
                                                >
                                                    <i className="fas fa-times"></i>
                                                </button>
                                            </div>

                                            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: "8px", paddingTop: "4px", borderTop: "1px dashed var(--snd-surface-soft)" }}>
                                                {/* Editable Unit Cost */}
                                                <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                                    <span style={{ fontSize: "0.72rem", color: "#626b7b", fontWeight: 700 }}>Rate:</span>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        value={costRate}
                                                        onChange={(e) => this.handleChangePrice(item.id, e.target.value)}
                                                        style={{
                                                            width: "65px",
                                                            padding: "2px 5px",
                                                            fontSize: "0.78rem",
                                                            fontWeight: 700,
                                                            border: "1px solid var(--snd-border-strong)",
                                                            borderRadius: "6px",
                                                            textAlign: "right",
                                                            color: "var(--snd-primary-deep)",
                                                            outline: "none"
                                                        }}
                                                        title="Editable Purchase Rate"
                                                    />
                                                </div>

                                                {/* Quantity controls */}
                                                <div style={{ display: "flex", alignItems: "center", gap: "3px" }}>
                                                    <button
                                                        type="button"
                                                        onClick={() => this.handleChangeQty(item.id, Math.max(1, qty - 1))}
                                                        style={{ width: "28px", height: "28px", flex: "0 0 28px", borderRadius: "8px", border: "1px solid var(--snd-border-strong)", background: "#ffffff", color: "var(--snd-ink-soft)", cursor: "pointer", fontWeight: 800, fontSize: "0.9rem", display: "flex", alignItems: "center", justifyContent: "center", padding: 0, boxSizing: "border-box" }}
                                                    >-</button>
                                                    <input
                                                        type="number"
                                                        min="1"
                                                        value={qty}
                                                        onChange={(e) => this.handleChangeQty(item.id, e.target.value)}
                                                        style={{ width: "38px", height: "28px", boxSizing: "border-box", textAlign: "center", fontSize: "0.8rem", fontWeight: 800, color: "var(--snd-ink)", border: "1px solid var(--snd-border-strong)", borderRadius: "8px", padding: 0, outline: "none" }}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => this.handleChangeQty(item.id, qty + 1)}
                                                        style={{ width: "28px", height: "28px", flex: "0 0 28px", borderRadius: "8px", border: "none", background: "var(--snd-button-primary)", color: "var(--snd-button-primary-text)", cursor: "pointer", fontWeight: 800, fontSize: "0.9rem", display: "flex", alignItems: "center", justifyContent: "center", padding: 0, boxSizing: "border-box", boxShadow: "0 2px 5px var(--snd-primary-ring)" }}
                                                    >+</button>
                                                </div>

                                                {/* Line total */}
                                                <div style={{ fontSize: "0.86rem", fontWeight: 800, color: "#1a1a2e", minWidth: "60px", textAlign: "right" }}>
                                                    {currency}{lineTotal.toFixed(2)}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })
                            )}
                        </div>

                        {/* Calculation Totals (Matching Bottom of Image 1) */}
                        <div style={{ padding: "12px 18px", borderTop: "1px solid var(--snd-border)", background: "#fbfcfd" }}>
                            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "6px" }}>
                                <span style={{ fontSize: "0.84rem", color: "#626b7b", fontWeight: 600 }}>Sub Total</span>
                                <span style={{ fontSize: "0.88rem", color: "#1a1a2e", fontWeight: 700 }}>{currency}{subtotal.toFixed(2)}</span>
                            </div>

                            {/* Tax Section with Tax % and Tax amount side-by-side matching sale screen */}
                            <div style={{ marginBottom: "10px" }}>
                                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", fontSize: "0.82rem", color: "#626b7b", marginBottom: "5px" }}>
                                    <span style={{ fontWeight: 600 }}>Tax</span>
                                    <span style={{ fontWeight: 700, color: "#1a1a2e" }}>{currency}{totalTax.toFixed(2)}</span>
                                </div>
                                <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "6px" }}>
                                    <input
                                        type="number"
                                        min="0"
                                        step="any"
                                        placeholder="Tax %"
                                        value={taxPercent}
                                        onChange={this.handleTaxPercentChange}
                                        style={{
                                            width: "100%",
                                            padding: "7px 10px",
                                            borderRadius: "8px",
                                            border: "1.5px solid var(--snd-border-strong)",
                                            fontSize: "0.82rem",
                                            outline: "none",
                                            boxSizing: "border-box"
                                        }}
                                    />
                                    <input
                                        type="number"
                                        min="0"
                                        step="any"
                                        placeholder="Tax amount"
                                        value={taxAmount}
                                        onChange={this.handleTaxAmountChange}
                                        style={{
                                            width: "100%",
                                            padding: "7px 10px",
                                            borderRadius: "8px",
                                            border: "1.5px solid var(--snd-border-strong)",
                                            fontSize: "0.82rem",
                                            outline: "none",
                                            boxSizing: "border-box"
                                        }}
                                    />
                                </div>
                            </div>

                            {/* Discount Section with Discount % and Discount amount side-by-side matching sale screen */}
                            <div style={{ marginBottom: "10px" }}>
                                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", fontSize: "0.82rem", color: "#626b7b", marginBottom: "5px" }}>
                                    <span style={{ fontWeight: 600 }}>Discount</span>
                                    <span style={{ fontWeight: 700, color: "#1f7a3d" }}>-{currency}{totalDiscount.toFixed(2)}</span>
                                </div>
                                <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "6px" }}>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="any"
                                        placeholder="Discount %"
                                        value={discountPercent}
                                        onChange={this.handleDiscountPercentChange}
                                        style={{
                                            width: "100%",
                                            padding: "7px 10px",
                                            borderRadius: "8px",
                                            border: "1.5px solid var(--snd-border-strong)",
                                            fontSize: "0.82rem",
                                            outline: "none",
                                            boxSizing: "border-box"
                                        }}
                                    />
                                    <input
                                        type="number"
                                        min="0"
                                        max={subtotal}
                                        step="any"
                                        placeholder="Discount amount"
                                        value={discountAmount}
                                        onChange={this.handleDiscountAmountChange}
                                        style={{
                                            width: "100%",
                                            padding: "7px 10px",
                                            borderRadius: "8px",
                                            border: "1.5px solid var(--snd-border-strong)",
                                            fontSize: "0.82rem",
                                            outline: "none",
                                            boxSizing: "border-box"
                                        }}
                                    />
                                </div>
                            </div>

                            {/* Grand Total: Amount to Pay */}
                            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", paddingTop: "8px", borderTop: "1.5px solid var(--snd-border)", marginBottom: "14px" }}>
                                <span style={{ fontSize: "1.05rem", fontWeight: 800, color: "#1a1a2e" }}>Amount to Pay</span>
                                <span style={{ fontSize: "1.35rem", fontWeight: 900, color: "var(--snd-primary-deep)" }}>{currency}{grandTotal.toFixed(2)}</span>
                            </div>

                            {/* Big Primary Action: Place an Order */}
                            <button
                                type="button"
                                onClick={this.handleClickSubmit}
                                disabled={cart.length === 0 || loading || !supplier_id}
                                style={{
                                    width: "100%",
                                    padding: "13px 0",
                                    borderRadius: "12px",
                                    border: "none",
                                    background: (cart.length === 0 || !supplier_id) ? "#626b7b" : "linear-gradient(135deg, var(--snd-primary-deep) 0%, var(--snd-primary-hover) 100%)",
                                    color: "var(--snd-button-primary-text)",
                                    fontSize: "0.98rem",
                                    fontWeight: 800,
                                    cursor: (cart.length === 0 || !supplier_id) ? "not-allowed" : "pointer",
                                    boxShadow: (cart.length === 0 || !supplier_id) ? "none" : "0 4px 14px var(--snd-primary-ring)",
                                    transition: "all 0.18s ease",
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                    gap: "8px",
                                    marginBottom: "10px"
                                }}
                            >
                                {loading ? (
                                    <>
                                        <i className="fas fa-circle-notch fa-spin"></i>
                                        <span>Processing...</span>
                                    </>
                                ) : (
                                    <span>Place an Order</span>
                                )}
                            </button>

                            {/* Bottom Secondary Action Buttons: Print, Invoice, Clear */}
                            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: "8px" }}>
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (lastPurchase) {
                                            this.printReceipt(lastPurchase);
                                        } else {
                                            Swal.fire({ icon: "info", title: "No receipt yet", text: "Complete a purchase first to print receipt." });
                                        }
                                    }}
                                    style={{
                                        padding: "8px 0",
                                        borderRadius: "9px",
                                        border: "1px solid var(--snd-border-strong)",
                                        background: "#ffffff",
                                        color: "#505469",
                                        fontSize: "0.78rem",
                                        fontWeight: 700,
                                        cursor: "pointer",
                                        display: "flex",
                                        alignItems: "center",
                                        justifyContent: "center",
                                        gap: "5px"
                                    }}
                                >
                                    <i className="fas fa-print" style={{ fontSize: "0.74rem" }}></i> Print
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (lastPurchase) {
                                            window.location.href = `/admin/purchases/${lastPurchase.id}`;
                                        } else {
                                            window.location.href = '/admin/purchases';
                                        }
                                    }}
                                    style={{
                                        padding: "8px 0",
                                        borderRadius: "9px",
                                        border: "1px solid var(--snd-border-strong)",
                                        background: "#ffffff",
                                        color: "#505469",
                                        fontSize: "0.78rem",
                                        fontWeight: 700,
                                        cursor: "pointer",
                                        display: "flex",
                                        alignItems: "center",
                                        justifyContent: "center",
                                        gap: "5px"
                                    }}
                                >
                                    <i className="fas fa-file-invoice" style={{ fontSize: "0.74rem" }}></i> Invoices
                                </button>
                                <button
                                    type="button"
                                    onClick={this.handleEmptyCart}
                                    disabled={cart.length === 0}
                                    style={{
                                        padding: "8px 0",
                                        borderRadius: "9px",
                                        border: "1px solid var(--snd-border-strong)",
                                        background: "#ffffff",
                                        color: cart.length === 0 ? "var(--snd-border-strong)" : "#c9342d",
                                        fontSize: "0.78rem",
                                        fontWeight: 700,
                                        cursor: cart.length === 0 ? "default" : "pointer",
                                        display: "flex",
                                        alignItems: "center",
                                        justifyContent: "center",
                                        gap: "5px"
                                    }}
                                >
                                    <i className="fas fa-trash-alt" style={{ fontSize: "0.74rem" }}></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        );
    }
}

function round2(num) {
    return Math.round((num + Number.EPSILON) * 100) / 100;
}

export default Purchase;

// Mount React component
const rootEl = document.getElementById("purchase");
if (rootEl) {
    const root = createRoot(rootEl);
    root.render(<Purchase />);
}
