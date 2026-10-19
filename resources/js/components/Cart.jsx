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
        // Gateway presentation is shared with the POS workspace stylesheet.
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
                confirmButtonColor: "var(--snd-primary)",
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
                    <div style="width:68px;height:68px;border-radius:4px;background:var(--snd-primary);display:inline-flex;align-items:center;justify-content:center;color:var(--snd-ink);font-size:1.65rem;box-shadow:0 8px 24px var(--snd-primary-shadow);margin-bottom:1.2rem;">
                        <i class="fas fa-lock"></i>
                    </div>

                    <div style="font-size:1.95rem;font-weight:800;color:var(--snd-ink);margin:0 0 0.2rem;letter-spacing:-0.03em;">${CommonHelper.getBrandName()}</div>
                    <div style="font-size:0.75rem;letter-spacing:0.18em;color:var(--snd-primary);text-transform:uppercase;font-weight:700;margin-bottom:1rem;">${CommonHelper.getBrandSubtitle()}</div>

                    <div style="display:inline-flex;align-items:center;gap:0.55rem;background:var(--snd-primary-soft);border:1px solid var(--snd-primary-border);border-radius:4px;padding:0.4rem 1.1rem;margin:0 auto 1.6rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--snd-surface-soft);box-shadow:0 0 8px var(--snd-primary);"></span>
                        <span style="font-size:0.76rem;letter-spacing:0.12em;color:var(--snd-primary);text-transform:uppercase;font-weight:800;">Branch Access</span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:1.2rem;text-align:left;max-width:370px;margin:0 auto;">
                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:var(--snd-ink);margin-bottom:0.5rem;">
                                Select Branch
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-building" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.95rem;pointer-events:none;z-index:10;"></i>
                                <select id="swal-branch" style="width:100%;padding:0.85rem 2.5rem 0.85rem 2.8rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.95rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);appearance:none;-webkit-appearance:none;cursor:pointer;box-sizing:border-box;transition:all 0.2s ease;">
                                    ${branchOptions}
                                </select>
                                <i id="swal-branch-arrow" class="fas fa-chevron-down" style="position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);pointer-events:none;font-size:0.85rem;z-index:3;"></i>
                            </div>
                        </div>

                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:var(--snd-ink);margin-bottom:0.5rem;">
                                Branch Password
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-key" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.95rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-branch-pass" type="password"
                                       placeholder="Enter branch password"
                                       style="width:100%;padding:0.85rem 2.8rem 0.85rem 2.8rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.95rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);box-sizing:border-box;"
                                       autocomplete="off">
                                <button type="button" id="swal-pass-toggle" style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--snd-primary);cursor:pointer;font-size:0.95rem;padding:4px;display:flex;align-items:center;justify-content:center;z-index:2;">
                                    <i class="fas fa-eye" id="swal-pass-toggle-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`,
            showCancelButton: false,
            confirmButtonText: "Verify Branch",
            confirmButtonColor: "var(--snd-primary)",
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
                            toggleBtn.style.color = "var(--snd-primary)";
                        } else {
                            passInput.type = "password";
                            icon.className = "fas fa-eye";
                            toggleBtn.style.color = "var(--snd-primary)";
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
                confirmButtonColor: "var(--snd-primary)",
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
                    <div style="width:68px;height:68px;border-radius:4px;background:var(--snd-primary);display:inline-flex;align-items:center;justify-content:center;color:var(--snd-ink);font-size:1.65rem;box-shadow:0 8px 24px var(--snd-primary-shadow);margin-bottom:1.2rem;">
                        <i class="fas fa-lock"></i>
                    </div>

                    <div style="font-size:1.95rem;font-weight:800;color:var(--snd-ink);margin:0 0 0.2rem;letter-spacing:-0.03em;">${CommonHelper.getBrandName()}</div>
                    <div style="font-size:0.75rem;letter-spacing:0.18em;color:var(--snd-primary);text-transform:uppercase;font-weight:700;margin-bottom:1rem;">${CommonHelper.getBrandSubtitle()}</div>

                    <div style="display:inline-flex;align-items:center;gap:0.55rem;background:var(--snd-primary-soft);border:1px solid var(--snd-primary-border);border-radius:4px;padding:0.4rem 1.1rem;margin:0 auto 1.6rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--snd-surface-soft);box-shadow:0 0 8px var(--snd-primary);"></span>
                        <span style="font-size:0.76rem;letter-spacing:0.12em;color:var(--snd-primary);text-transform:uppercase;font-weight:800;">Counter Access</span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:1.2rem;text-align:left;max-width:370px;margin:0 auto;">
                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:var(--snd-ink);margin-bottom:0.5rem;">
                                Select Counter
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-cash-register" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.95rem;pointer-events:none;z-index:10;"></i>
                                <select id="swal-counter" style="width:100%;padding:0.85rem 2.5rem 0.85rem 2.8rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.95rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);appearance:none;-webkit-appearance:none;cursor:pointer;box-sizing:border-box;transition:all 0.2s ease;">
                                    ${counterOptions}
                                </select>
                                <i id="swal-counter-arrow" class="fas fa-chevron-down" style="position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);pointer-events:none;font-size:0.85rem;z-index:3;"></i>
                            </div>
                        </div>

                        <div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.84rem;font-weight:700;color:var(--snd-ink);margin-bottom:0.5rem;">
                                Counter Password
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-key" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.95rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-counter-pass" type="password"
                                       placeholder="Enter counter password"
                                       style="width:100%;padding:0.85rem 2.8rem 0.85rem 2.8rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.95rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);box-sizing:border-box;"
                                       autocomplete="off">
                                <button type="button" id="swal-pass-toggle" style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--snd-primary);cursor:pointer;font-size:0.95rem;padding:4px;display:flex;align-items:center;justify-content:center;z-index:2;">
                                    <i class="fas fa-eye" id="swal-pass-toggle-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`,
            showCancelButton: true,
            cancelButtonText: "Back to Branch",
            confirmButtonText: "Open POS",
            confirmButtonColor: "var(--snd-primary)",
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
                            toggleBtn.style.color = "var(--snd-primary)";
                        } else {
                            passInput.type = "password";
                            icon.className = "fas fa-eye";
                            toggleBtn.style.color = "var(--snd-primary)";
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
            confirmButtonColor: "var(--snd-danger)",
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
            customClass: { popup: "pos-customer-dialog" },
            title: "",
            html: `
                <div style="text-align:center;padding:0.2rem 0 0.5rem;">
                    <div style="width:60px;height:60px;border-radius:4px;background:var(--snd-surface-soft);display:inline-flex;align-items:center;justify-content:center;color:var(--snd-ink);font-size:1.5rem;box-shadow:0 8px 22px var(--snd-primary-border);margin-bottom:0.8rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div style="font-size:1.45rem;font-weight:800;color:var(--snd-ink);margin-bottom:0.2rem;letter-spacing:-0.02em;">Create New Customer</div>
                    <div style="font-size:0.8rem;color:var(--snd-muted);margin-bottom:1.2rem;font-weight:500;">Add customer details for quick POS checkout</div>

                    <div style="text-align:left;display:flex;flex-direction:column;gap:0.9rem;max-width:380px;margin:0 auto;">
                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:var(--snd-ink);margin-bottom:0.35rem;display:flex;align-items:center;justify-content:space-between;">
                                <span>First Name <span style="color:var(--snd-primary);">*</span></span>
                                <span style="font-size:0.7rem;color:var(--snd-primary);font-weight:600;">Required</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-user" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-first" type="text" placeholder="e.g. Ayesha"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.9rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:var(--snd-ink);margin-bottom:0.35rem;display:block;">
                                Last Name <span style="font-size:0.72rem;color:var(--snd-muted);font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="far fa-user" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-last" type="text" placeholder="e.g. Khan"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.9rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:var(--snd-ink);margin-bottom:0.35rem;display:block;">
                                Phone Number <span style="font-size:0.72rem;color:var(--snd-muted);font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-phone-alt" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-phone" type="text" placeholder="e.g. +92 300 1234567"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.9rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:var(--snd-ink);margin-bottom:0.35rem;display:block;">
                                Email Address <span style="font-size:0.72rem;color:var(--snd-muted);font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-envelope" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-email" type="email" placeholder="e.g. ayesha@example.com"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.9rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label style="font-weight:700;font-size:0.82rem;color:var(--snd-ink);margin-bottom:0.35rem;display:block;">
                                Address <span style="font-size:0.72rem;color:var(--snd-muted);font-weight:500;">(Optional)</span>
                            </label>
                            <div style="position:relative;width:100%;">
                                <i class="fas fa-map-marker-alt" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--snd-primary);font-size:0.9rem;pointer-events:none;z-index:2;"></i>
                                <input id="swal-cust-address" type="text" placeholder="e.g. Clifton Block 5, Karachi"
                                       style="width:100%;padding:0.75rem 1rem 0.75rem 2.7rem;border:1.5px solid var(--snd-border);border-radius:4px;font-size:0.9rem;font-weight:600;outline:none;background:var(--snd-surface-soft);color:var(--snd-ink);box-sizing:border-box;transition:all 0.2s ease;"
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
            `,

            showCancelButton: true,
            confirmButtonText: "Save & Select Customer",
            confirmButtonColor: "var(--snd-primary)",
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
                confirmButtonColor: "var(--snd-primary)",
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
            confirmButtonText: "Yes, clear", confirmButtonColor: "var(--snd-danger)",
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
                confirmButtonColor: "var(--snd-primary)"
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
                ? `<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px;border-radius:4px;font-size:0.67rem;font-weight:700;background:var(--snd-surface-soft);color:var(--snd-primary);border:1px solid var(--snd-border);"><i class="fas fa-tags" style="font-size:0.62rem;"></i> Deal</span>`
                : isService
                ? `<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px;border-radius:4px;font-size:0.67rem;font-weight:700;background:var(--snd-success-soft);color:var(--snd-primary);border:1px solid var(--snd-primary-border);"><i class="fas fa-spa" style="font-size:0.62rem;"></i> Service</span>`
                : `<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px;border-radius:4px;font-size:0.67rem;font-weight:700;background:var(--snd-surface);color:var(--snd-muted);border:1px solid var(--snd-border);"><i class="fas fa-box-open" style="font-size:0.62rem;"></i> Product</span>`;

            return `
                <tr style="border-bottom:1px solid var(--snd-surface-soft);">
                    <td style="padding: 8px 6px; text-align: left; vertical-align: middle;">
                        <div style="font-weight: 700; color:var(--snd-ink); font-size: 0.84rem; line-height: 1.3;">${item.name}</div>
                        <div style="margin-top: 3px;">${typeBadge}</div>
                    </td>
                    <td style="padding: 8px 6px; text-align: center; vertical-align: middle; font-weight: 700; color:var(--snd-ink); font-size: 0.84rem;">
                        ${qty}
                    </td>
                    <td style="padding: 8px 6px; text-align: right; vertical-align: middle; color:var(--snd-muted); font-size: 0.82rem; font-family: monospace;">
                        ${this.formatAmount(unitPrice)}
                    </td>
                    <td style="padding: 8px 6px; text-align: right; vertical-align: middle; font-weight: 800; color:var(--snd-ink); font-size: 0.85rem; font-family: monospace;">
                        ${this.formatAmount(lineTotal)}
                    </td>
                </tr>
            `;
        }).join("");

        const modalHtml = `
            <div style="text-align: left; font-family: inherit; color:var(--snd-ink);">
                <!-- Header Card -->
                <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom:1.5px solid var(--snd-border); margin-bottom: 14px;">
                    <div style="width: 44px; height: 44px; border-radius:4px; background:linear-gradient(135deg, var(--snd-surface-soft) 0%, var(--snd-surface-soft) 100%); display: flex; align-items: center; justify-content: center; color:var(--snd-ink); font-size: 1.25rem; box-shadow:0 4px 12px var(--snd-primary-shadow); flex-shrink: 0;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 1.15rem; font-weight: 800; color:var(--snd-ink); letter-spacing: -0.02em; line-height: 1.2;">
                            Order Confirmation & Payment
                        </div>
                        <div style="font-size: 0.76rem; color:var(--snd-muted); margin-top: 2px;">
                            Review purchase details below. Only Received Amount is editable.
                        </div>
                    </div>
                </div>

                <!-- Customer & Summary Details Pill -->
                <div style="background:var(--snd-surface); border:1px solid var(--snd-border); border-radius:4px; padding: 8px 12px; margin-bottom: 12px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; font-size: 0.78rem;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="color:var(--snd-muted); font-weight: 600;"><i class="fas fa-user-circle" style="color:var(--snd-primary);"></i> Customer:</span>
                        <strong style="color:var(--snd-ink);">${customerName}</strong>
                        ${customerPhone ? `<span style="color:var(--snd-muted); font-size: 0.72rem;">(${customerPhone})</span>` : ""}
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="background:var(--snd-surface-soft); color:var(--snd-primary); border:1px solid var(--snd-border); padding: 2px 7px; border-radius:4px; font-weight: 700; font-size: 0.72rem;">
                            ${totalQty} Total Items
                        </span>
                        ${activeBranch ? `<span style="background:var(--snd-surface-soft); color:var(--snd-muted); border:1px solid var(--snd-border); padding: 2px 7px; border-radius:4px; font-weight: 600; font-size: 0.72rem;">${activeBranch.name}</span>` : ""}
                    </div>
                </div>

                <!-- Selected Items List (Scrollable Table) -->
                <div style="border:1.5px solid var(--snd-border); border-radius:4px; overflow: hidden; margin-bottom: 12px; background:var(--snd-surface);">
                    <div style="background:var(--snd-surface-soft); padding: 6px 8px; border-bottom:1px solid var(--snd-border); font-size: 0.72rem; font-weight: 800; color:var(--snd-muted); text-transform: uppercase; letter-spacing: 0.04em;">
                        Selected Services & Deals (View-Only)
                    </div>
                    <div style="max-height: 170px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse:collapse; font-size: 0.8rem;">
                            <thead>
                                <tr style="background:var(--snd-surface); border-bottom:1px solid var(--snd-border); font-size: 0.72rem; color:var(--snd-muted); text-transform: uppercase;">
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
                <div style="background:var(--snd-surface); border:1px solid var(--snd-border); border-radius:4px; padding: 10px 14px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color:var(--snd-muted); margin-bottom: 5px;">
                        <span>Subtotal</span>
                        <strong style="color:var(--snd-ink); font-family: monospace;">${currency} ${this.formatAmount(saleTotals.subtotal)}</strong>
                    </div>
                    ${saleTotals.discountAmount > 0 ? `
                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color:var(--snd-primary); margin-bottom: 5px;">
                            <span>Discount ${saleTotals.discountPercent ? `(${saleTotals.discountPercent}%)` : ''}</span>
                            <strong style="font-family: monospace;">-${currency} ${this.formatAmount(saleTotals.discountAmount)}</strong>
                        </div>
                    ` : ""}
                    ${saleTotals.taxAmount > 0 ? `
                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color:var(--snd-primary); margin-bottom: 5px;">
                            <span>Sales Tax ${saleTotals.taxPercent ? `(${saleTotals.taxPercent}%)` : ''}</span>
                            <strong style="font-family: monospace;">+${currency} ${this.formatAmount(saleTotals.taxAmount)}</strong>
                        </div>
                    ` : ""}
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 6px; border-top:1.5px dashed var(--snd-border); margin-top: 6px;">
                        <span style="font-size: 0.95rem; font-weight: 800; color:var(--snd-ink);">Payable Total</span>
                        <span style="font-size: 1.15rem; font-weight: 900; color:var(--snd-primary); font-family: monospace;">${currency} ${this.formatAmount(saleTotals.total)}</span>
                    </div>
                </div>

                <!-- Editable Cash Received Input Box -->
                <div style="background:var(--snd-surface); border:2px solid var(--snd-primary); border-radius:4px; padding: 12px 14px; box-shadow:0 4px 14px var(--snd-primary-soft);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="swal-received-amount" style="font-weight: 800; font-size: 0.85rem; color:var(--snd-ink); margin: 0; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-hand-holding-usd" style="color:var(--snd-primary); font-size: 0.95rem;"></i>
                            Received Amount <span style="font-size: 0.72rem; color:var(--snd-primary); font-weight: 700; background:var(--snd-surface-soft); padding: 1px 6px; border-radius:4px; border:1px solid var(--snd-border);">EDITABLE</span>
                        </label>
                        <span style="font-size: 0.74rem; font-weight: 700; color:var(--snd-muted);">
                            Due: <b style="color:var(--snd-ink);">${currency} ${total}</b>
                        </span>
                    </div>

                    <div style="position: relative; display: flex; align-items: center;">
                        <span style="position: absolute; left: 12px; font-weight: 800; color:var(--snd-muted); font-size: 1rem; pointer-events: none;">${currency}</span>
                        <input
                            id="swal-received-amount"
                            type="number"
                            step="0.01"
                            min="${total}"
                            value="${total}"
                            style="width: 100%; box-sizing: border-box; padding: 9px 12px 9px 48px; font-size: 1.2rem; font-weight: 800; color:var(--snd-ink); background:var(--snd-surface); border:1.5px solid var(--snd-border); border-radius:4px; outline: none; transition: border-color 0.2s;"
                            autocomplete="off"
                        />
                    </div>

                    <!-- Quick Preset Buttons -->
                    <div style="display: flex; gap: 5px; margin-top: 8px; flex-wrap: wrap;">
                        <button type="button" id="swal-btn-exact" style="flex: 1; min-width: 80px; padding: 5px 8px; border:1px solid var(--snd-primary); background:var(--snd-surface-soft); color:var(--snd-primary); border-radius:4px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            Exact (${total})
                        </button>
                        <button type="button" class="swal-preset-btn" data-add="500" style="padding: 5px 9px; border:1px solid var(--snd-border); background:var(--snd-surface); color:var(--snd-ink); border-radius:4px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            +500
                        </button>
                        <button type="button" class="swal-preset-btn" data-add="1000" style="padding: 5px 9px; border:1px solid var(--snd-border); background:var(--snd-surface); color:var(--snd-ink); border-radius:4px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            +1000
                        </button>
                        <button type="button" class="swal-preset-btn" data-add="5000" style="padding: 5px 9px; border:1px solid var(--snd-border); background:var(--snd-surface); color:var(--snd-ink); border-radius:4px; font-size: 0.73rem; font-weight: 700; cursor: pointer;">
                            +5000
                        </button>
                    </div>

                    <!-- Change / Short Amount Live Calculation -->
                    <div id="swal-change-box" style="margin-top: 9px; padding: 7px 10px; border-radius:4px; background:var(--snd-success-soft); border:1px solid var(--snd-primary-border); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.78rem; font-weight: 700; color:var(--snd-primary);" id="swal-change-label">Change Due:</span>
                        <span style="font-size: 0.95rem; font-weight: 800; color:var(--snd-primary); font-family: monospace;" id="swal-change-val">${currency} 0.00</span>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            html: modalHtml,
            customClass: { popup: "pos-checkout-dialog" },
            width: 550,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle" style="margin-right: 6px;"></i> Confirm & Place Order',
            confirmButtonColor: "var(--snd-primary)",
            cancelButtonText: "Cancel",
            cancelButtonColor: "var(--snd-muted)",
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
                        changeBox.style.background = "var(--snd-success-soft)";
                        changeBox.style.borderColor = "var(--snd-primary-border)";
                        changeLabel.style.color = "var(--snd-primary)";
                        changeLabel.textContent = "Change Due:";
                        changeVal.style.color = "var(--snd-primary)";
                        changeVal.textContent = `${currency} ${diff > 0 ? diff.toFixed(2) : "0.00"}`;
                    } else {
                        changeBox.style.background = "var(--snd-danger-soft)";
                        changeBox.style.borderColor = "var(--snd-danger-border)";
                        changeLabel.style.color = "var(--snd-danger)";
                        changeLabel.textContent = "Remaining Due:";
                        changeVal.style.color = "var(--snd-danger)";
                        changeVal.textContent = `${currency} ${Math.abs(diff).toFixed(2)}`;
                    }
                };

                if (inputEl) {
                    inputEl.addEventListener("input", updateChange);
                    inputEl.addEventListener("focus", () => {
                        inputEl.style.background = "var(--snd-surface)";
                        inputEl.style.borderColor = "var(--snd-primary)";
                    });
                    inputEl.addEventListener("blur", () => {
                        inputEl.style.background = "var(--snd-surface)";
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
                confirmButtonColor: "var(--snd-primary)",
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
                        <stop offset="100%" stop-color="#5145a2" />
                    </linearGradient>
                </defs>
                <rect x="0" y="0" width="220" height="54" rx="12" fill="#f8fafc" stroke="#ded9f5" />
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
                    background: var(--snd-surface) !important;
                    border-radius: 4px !important;
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
            confirmButtonColor: "var(--snd-primary)",
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
                confirmButtonColor: "var(--snd-primary)",
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

    renderProductTile(item) {
        const key = this.getCartItemKey(item);
        const cartItem = this.state.cart.find(c => this.getCartItemKey(c) === key);
        const quantity = Number(cartItem?.pivot?.quantity || 0);
        const isProduct = (item.item_type || "product") === "product";
        const stock = Number(item.quantity || 0);
        const lowStock = isProduct && stock < Number(window.APP.warning_quantity || 0);
        const price = Number(item.price ?? item.rate ?? 0);
        const currency = window.APP.currency_symbol || "";

        return (
            <article key={key} className={`pos-catalog-card ${quantity ? "is-in-cart" : ""} ${isProduct && stock <= 0 ? "is-out-of-stock" : ""}`}>
                <button type="button" className="pos-catalog-card-select" onClick={() => this.addProductToCart(item.barcode)} title={`Add ${item.name}`}>
                    <div className="pos-catalog-card-visual">
                        {item.image_url ? <img src={item.image_url} alt="" onError={e => { e.currentTarget.hidden = true; }} /> : null}
                        <i className={`fas fa-${isProduct ? "box-open" : "spa"}`} aria-hidden="true"></i>
                        {quantity > 0 && <span className="pos-selected-count">{quantity} selected</span>}
                    </div>
                    <span className="pos-catalog-card-type">{isProduct ? "Product" : item.category_name || "Service"}</span>
                    <h3>{item.name}</h3>
                    <span className={`pos-stock-label ${lowStock || (isProduct && stock <= 0) ? "is-low" : ""}`}>
                        {isProduct ? stock > 0 ? `${stock} in stock` : "Out of stock" : "Service"}
                    </span>
                </button>
                <div className="pos-catalog-card-footer">
                    <strong>{currency} {this.formatAmount(price)}</strong>
                    <div className="pos-card-quantity">
                        {quantity > 0 && <>
                            <button type="button" aria-label={`Decrease ${item.name}`} onClick={() => quantity > 1 ? this.handleChangeQty(key, quantity - 1) : this.handleClickDelete(key)}><i className="fas fa-minus"></i></button>
                            <span>{quantity}</span>
                        </>}
                        <button type="button" className="pos-add-item" aria-label={`Add ${item.name}`} onClick={() => this.addProductToCart(item.barcode)}><i className="fas fa-plus"></i></button>
                    </div>
                </div>
            </article>
        );
    }

    renderDealCard(deal) {
        const key = this.getCartItemKey(deal);
        const quantity = Number(this.state.cart.find(c => this.getCartItemKey(c) === key)?.pivot?.quantity || 0);
        const currency = window.APP.currency_symbol || "";
        return (
            <article className={`pos-deal-card ${quantity ? "is-in-cart" : ""}`} key={key}>
                <button type="button" className="pos-deal-select" onClick={() => this.addProductToCart(deal.barcode)}>
                    <div className="pos-deal-top"><span className="pos-deal-icon"><i className="fas fa-tags"></i></span><span className="pos-deal-saving">{Number(deal.discount_percentage || 0).toFixed(0)}% OFF</span></div>
                    <span className="pos-catalog-card-type">Service package</span>
                    <h3>{deal.name}</h3>
                    {deal.description && <p>{deal.description}</p>}
                    <ul>{(deal.services || []).map((service, index) => <li key={`${service.id}-${index}`}><i className="fas fa-check"></i><span>{service.name}</span></li>)}</ul>
                </button>
                <div className="pos-catalog-card-footer">
                    <div><del>{currency} {this.formatAmount(deal.original_price)}</del><strong>{currency} {this.formatAmount(deal.price)}</strong></div>
                    <div className="pos-card-quantity">
                        {quantity > 0 && <><button type="button" aria-label={`Decrease ${deal.name}`} onClick={() => quantity > 1 ? this.handleChangeQty(key, quantity - 1) : this.handleClickDelete(key)}><i className="fas fa-minus"></i></button><span>{quantity}</span></>}
                        <button type="button" className="pos-add-item" aria-label={`Add ${deal.name}`} onClick={() => this.addProductToCart(deal.barcode)}><i className="fas fa-plus"></i></button>
                    </div>
                </div>
            </article>
        );
    }

    renderGateScreen() {
        const { branchVerified, activeBranch } = this.state;
        const step = branchVerified ? 2 : 1;
        return (
            <div className="pos-gate-screen">
                <div className="pos-gate-card">
                    <span className="pos-workspace-eyebrow">Secure workspace</span>
                    <div className="pos-gate-steps"><span className="is-active">{step > 1 ? <i className="fas fa-check"></i> : "01"}</span><span className="pos-gate-step-line"></span><span className={step === 2 ? "is-active" : ""}>02</span></div>
                    <div className="pos-gate-icon"><i className={`fas fa-${step === 1 ? "code-branch" : "desktop"}`}></i></div>
                    <h1>{step === 1 ? "Choose your branch." : "Open your counter."}</h1>
                    <p>{step === 1 ? "Select your branch and enter its password to start your session." : "Select your counter and enter its password to open the POS."}</p>
                    {activeBranch && <div className="pos-session-tag"><i className="fas fa-check-circle"></i>{activeBranch.name}</div>}
                    <button type="button" className="pos-primary-button" onClick={step === 1 ? this.showBranchModal : this.showCounterModal}>{step === 1 ? "Verify branch" : "Verify counter"}<i className="fas fa-arrow-right"></i></button>
                </div>
            </div>
        );
    }

    render() {
        const { cart, products, services, categories, deals = [], catalogMode, customers, customer_id, barcode, gateChecked, branchVerified, counterVerified, activeBranch, activeCounter, search, isSearching } = this.state;
        if (!gateChecked) return <div className="pos-loading" role="status"><i className="fas fa-circle-notch fa-spin"></i><span>Opening your workspace…</span></div>;
        if (!branchVerified || !counterVerified) return this.renderGateScreen();

        const currency = window.APP.currency_symbol || "";
        const totals = this.getSaleTotals(cart);
        const itemCount = cart.reduce((count, item) => count + Number(item.pivot.quantity), 0);
        const showProducts = window.APP?.show_products !== false;
        const showServices = !!window.APP?.show_services;
        const activeCategory = this.state.activeCategory || "all";
        const derivedCategories = categories.length ? categories : Array.from(new Map(services.filter(item => item.category_id).map(item => [item.category_id, { id: item.category_id, name: item.category_name }])).values());
        const categoryOptions = [{ key: "all", label: "All items", icon: "border-all" }, ...(showProducts ? [{ key: "product", label: "Products", icon: "box-open" }] : []), ...((showServices ? derivedCategories : []).map(category => ({ key: `category:${category.id}`, label: category.name, icon: "spa" })))];
        const catalog = [...products, ...services].filter(item => activeCategory === "all" || (activeCategory === "product" && item.item_type === "product") || (activeCategory.startsWith("category:") && item.item_type === "service" && Number(item.category_id) === Number(activeCategory.split(":")[1])));
        const filteredDeals = deals.filter(deal => !search || [deal.name, deal.barcode, ...(deal.services || []).map(service => service.name)].some(value => String(value || "").toLowerCase().includes(search.toLowerCase())));
        const groups = activeCategory === "all" && showServices ? [
            ...(showProducts && products.length ? [{ key: "products", label: "Products", items: catalog.filter(item => item.item_type === "product") }] : []),
            ...derivedCategories.map(category => ({ key: `category:${category.id}`, label: category.name, items: catalog.filter(item => item.item_type === "service" && Number(item.category_id) === Number(category.id)) })).filter(group => group.items.length),
            ...(catalog.some(item => item.item_type === "service" && (!item.category_id || !derivedCategories.some(category => Number(category.id) === Number(item.category_id)))) ? [{ key: "other-services", label: "Other services", items: catalog.filter(item => item.item_type === "service" && (!item.category_id || !derivedCategories.some(category => Number(category.id) === Number(item.category_id)))) }] : []),
        ] : [{ key: activeCategory, label: "", items: catalog }];
        const isDeals = catalogMode === "deals";

        return (
            <div className="pos-shell">
                <div className="pos-workspace-header">
                    <div><span className="pos-workspace-eyebrow">Sales workspace</span><h1>Point of sale<span>.</span></h1></div>
                    <div className="pos-session"><span><i className="fas fa-circle"></i>{activeBranch?.name || "Main branch"}<b>/</b>{activeCounter?.name || "Counter"}</span><button type="button" onClick={this.handleClearSession} title="Change branch or counter" aria-label="Change branch or counter"><i className="fas fa-exchange-alt"></i></button></div>
                </div>
                <div className="pos-layout">
                    <section className="pos-catalog" aria-label="Item catalog">
                        <div className="pos-catalog-toolbar">
                            <div className="pos-mode-switch" role="group" aria-label="Catalog type"><button type="button" className={!isDeals ? "is-active" : ""} onClick={() => this.setState({ catalogMode: "services" })}><i className="fas fa-border-all"></i>Catalog</button><button type="button" className={isDeals ? "is-active" : ""} onClick={() => this.setState({ catalogMode: "deals" })}><i className="fas fa-tags"></i>Deals<span>{deals.length}</span></button></div>
                            <span className="pos-catalog-count">{isDeals ? filteredDeals.length : catalog.length} available</span>
                        </div>
                        <div className="pos-search-row">
                            <label className="pos-search-field"><i className={`fas fa-${isSearching ? "circle-notch fa-spin" : "search"}`}></i><input type="search" aria-label="Search catalog" placeholder={isDeals ? "Search deals or services…" : "Search products or services…"} value={search} onChange={this.handleChangeSearch} onKeyDown={this.handleSeach} />{search && <button type="button" aria-label="Clear search" onClick={() => this.setState({ search: "" }, () => this.loadProducts(""))}><i className="fas fa-times"></i></button>}</label>
                            <form className="pos-barcode-field" onSubmit={this.handleScanBarcode}><i className="fas fa-barcode"></i><input aria-label="Scan or enter barcode" placeholder="Scan barcode" value={barcode} onChange={this.handleOnChangeBarcode} /><button type="submit" aria-label="Add barcode"><i className="fas fa-arrow-right"></i></button></form>
                        </div>
                        {!isDeals && <nav className="pos-category-list" aria-label="Item categories">{categoryOptions.map(category => <button type="button" key={category.key} className={activeCategory === category.key ? "is-active" : ""} onClick={() => this.setState({ activeCategory: category.key })}><i className={`fas fa-${category.icon}`}></i>{category.label}</button>)}</nav>}
                        <div className="pos-catalog-scroll" aria-busy={isSearching}>
                            {isDeals ? filteredDeals.length ? <div className="pos-deal-grid">{filteredDeals.map(deal => this.renderDealCard(deal))}</div> : <div className="pos-empty-state"><i className="fas fa-tags"></i><h3>{search ? "No matching deals" : "No deals available"}</h3><p>{search ? "Try another name or clear your search." : "Active deals will appear here."}</p>{search && <button type="button" onClick={() => this.setState({ search: "" }, () => this.loadProducts(""))}>Clear search</button>}</div> : catalog.length ? groups.map(group => <section className="pos-catalog-group" key={group.key}>{group.label && <div className="pos-group-heading"><h2>{group.label}</h2><span>{group.items.length} items</span></div>}<div className="pos-product-grid">{group.items.map(item => this.renderProductTile(item))}</div></section>) : <div className="pos-empty-state"><i className={`fas fa-${isSearching ? "circle-notch fa-spin" : "search"}`}></i><h3>{isSearching ? "Finding your items…" : "No items found"}</h3><p>Try another search or category.</p>{search && <button type="button" onClick={() => this.setState({ search: "" }, () => this.loadProducts(""))}>Clear search</button>}</div>}
                        </div>
                    </section>
                    <aside className="pos-order-panel" aria-label="Current order">
                        <div className="pos-order-heading"><div><span className="pos-workspace-eyebrow">Checkout</span><h2>Current order</h2></div><span className="pos-order-count">{itemCount} items</span></div>
                        <div className="pos-customer-wrapper"><div className="pos-field-heading"><label htmlFor="pos-customer-select">Customer</label><button type="button" onClick={this.showAddCustomerModal}><i className="fas fa-plus"></i>Add new</button></div><select id="pos-customer-select" className="form-control" value={customer_id} onChange={this.setCustomerId}><option value="">Walk-in customer</option>{(customers || []).map(customer => <option key={customer.id} value={customer.id}>{[customer.first_name, customer.last_name].filter(Boolean).join(" ")}{customer.phone ? ` · ${customer.phone}` : ""}</option>)}</select></div>
                        <div className="pos-order-list-heading"><span>Order details</span>{cart.length > 0 && <button type="button" onClick={this.handleEmptyCart}><i className="fas fa-trash-alt"></i>Clear</button>}</div>
                        <div className="pos-order-items">{cart.length ? cart.map(item => {
                            const key = this.getCartItemKey(item), quantity = Number(item.pivot.quantity), rate = Number(item.price ?? item.discounted_amount ?? item.rate ?? 0);
                            return <article className="pos-order-item" key={key}><div className="pos-order-item-top"><span className="pos-line-icon"><i className={`fas fa-${item.item_type === "deal" ? "tags" : item.item_type === "service" ? "spa" : "box-open"}`}></i></span><div><h3>{item.name}</h3><span>{item.item_type || "product"} · {currency} {this.formatAmount(rate)} each</span></div><button type="button" className="pos-remove-item" aria-label={`Remove ${item.name}`} onClick={() => this.handleClickDelete(key)}><i className="fas fa-times"></i></button></div><div className="pos-order-item-bottom"><div className="pos-quantity-control"><button type="button" aria-label={`Decrease ${item.name}`} onClick={() => this.handleChangeQty(key, Math.max(1, quantity - 1))}>−</button><input type="number" aria-label={`Quantity for ${item.name}`} min="1" step="1" value={item.pivot.quantity} onChange={event => this.handleChangeQty(key, event.target.value)} /><button type="button" aria-label={`Increase ${item.name}`} onClick={() => this.handleChangeQty(key, quantity + 1)}>+</button></div>{window.APP?.editable_item_rate ? <label className="pos-edit-rate"><span>Rate</span><input type="number" aria-label={`Rate for ${item.name}`} min="0" step="0.01" value={rate} onChange={e => { const price = e.target.value; this.setState({ cart: cart.map(cartItem => this.getCartItemKey(cartItem) === key ? { ...cartItem, price } : cartItem) }); }} /></label> : <strong>{currency} {this.formatAmount(rate * quantity)}</strong>}</div></article>;
                        }) : <div className="pos-empty-order"><i className="fas fa-shopping-basket"></i><h3>Your order starts here.</h3><p>Select an item from the catalog to begin.</p></div>}</div>
                        <div className="pos-order-summary"><div className="pos-summary-row"><span>Subtotal</span><strong>{currency} {this.formatAmount(totals.subtotal)}</strong></div>{window.APP?.tax_enabled && <div className="pos-adjustment"><div className="pos-summary-row"><label htmlFor="pos-tax-percent">Tax</label><span>{currency} {this.formatAmount(totals.taxAmount)}</span></div><div className="pos-adjustment-fields"><label><input id="pos-tax-percent" type="number" min="0" step="0.01" placeholder="Percent" value={totals.taxPercent || ""} onChange={this.setTaxPercent} /><span>%</span></label><label><input type="number" aria-label="Tax amount" min="0" step="0.01" placeholder="Amount" value={totals.taxAmount ? totals.taxAmount.toFixed(2) : ""} onChange={this.setTaxAmount} /><span>{currency}</span></label></div></div>}{window.APP?.discount_enabled && <div className="pos-adjustment"><div className="pos-summary-row"><label htmlFor="pos-discount-percent">Discount</label><span>−{currency} {this.formatAmount(totals.discountAmount)}</span></div><div className="pos-adjustment-fields"><label><input id="pos-discount-percent" type="number" min="0" step="0.01" placeholder="Percent" value={totals.discountPercent || ""} onChange={this.setDiscountPercent} /><span>%</span></label><label><input type="number" aria-label="Discount amount" min="0" step="0.01" placeholder="Amount" value={totals.discountAmount ? totals.discountAmount.toFixed(2) : ""} onChange={this.setDiscountAmount} /><span>{currency}</span></label></div></div>}<div className="pos-grand-total"><span>Total due</span><strong><small>{currency}</small>{this.formatAmount(totals.total)}</strong></div></div>
                        <div className="pos-order-actions"><button type="button" className="pos-primary-button" onClick={this.handleClickSubmit}>Place order<i className="fas fa-arrow-right"></i></button><div className="pos-secondary-actions"><button type="button" onClick={() => cart.length > 0 ? this.handleClickSubmit() : this.state.lastPlacedOrder ? this.printReceipt(this.state.lastPlacedOrder) : this.handleClickSubmit()}><i className="fas fa-print"></i>Print</button><button type="button" onClick={this.handleOpenLastInvoice}><i className="fas fa-file-invoice"></i>Invoice</button><button type="button" onClick={() => Swal.fire({ icon: "info", title: "Draft Saved", text: "Your current cart items are active and saved in your session.", timer: 2000, showConfirmButton: false })}><i className="fas fa-save"></i>Draft</button></div></div>
                    </aside>
                </div>
            </div>
        );
    }
}

export default Cart;

const cartRoot = document.getElementById("cart");
if (cartRoot) createRoot(cartRoot).render(<Cart />);
