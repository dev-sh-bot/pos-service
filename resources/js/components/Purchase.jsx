import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

class Purchase extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            suppliers: [],
            search: "",
            supplier_id: "",
            purchase_date: new Date().toISOString().split('T')[0],
            status: "completed",
            notes: "",
            translations: {},
        };

        this.loadCart = this.loadCart.bind(this);
        this.loadProducts = this.loadProducts.bind(this);
        this.loadSuppliers = this.loadSuppliers.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.addProductToCart = this.addProductToCart.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleChangePrice = this.handleChangePrice.bind(this);
        this.handleClickDelete = this.handleClickDelete.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);
        this.setSupplierId = this.setSupplierId.bind(this);
        this.handleDateChange = this.handleDateChange.bind(this);
        this.handleStatusChange = this.handleStatusChange.bind(this);
        this.handleNotesChange = this.handleNotesChange.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        this.loadTranslations();
        this.loadSuppliers();
        this.loadProducts();
        this.loadCart();
    }

    loadTranslations() {
        axios
            .get(window.APP.locale_cart_url)
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
                this.setState({ translations: {} });
            });
    }

    loadSuppliers() {
        axios.get(`/admin/suppliers`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            console.log('Suppliers API Response:', res.data);
            console.log('Is Array?', Array.isArray(res.data));
            console.log('Has data property?', res.data.data);

            // Handle both array and paginated response
            const suppliers = Array.isArray(res.data) ? res.data : (res.data.data || []);
            console.log('Final suppliers:', suppliers);

            this.setState({ suppliers });
        }).catch((error) => {
            console.error("Error loading suppliers:", error);
            this.setState({ suppliers: [] });
        });
    }

    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        axios.get(`/admin/products${query}`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            const products = res.data.data || [];
            this.setState({ products });
        }).catch((error) => {
            console.error("Error loading products:", error);
            this.setState({ products: [] });
        });
    }

    loadCart() {
        axios.get("/admin/purchase-cart", {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            const cart = Array.isArray(res.data) ? res.data : [];
            this.setState({ cart });
        }).catch((error) => {
            console.error("Error loading cart:", error);
            this.setState({ cart: [] });
        });
    }

    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }

    handleSearch(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    addProductToCart(product) {
        // Check if product already in cart
        let cartItem = this.state.cart.find((c) => c.id === product.id);

        if (cartItem) {
            // Update quantity
            this.setState({
                cart: this.state.cart.map((c) => {
                    if (c.id === product.id) {
                        c.pivot.quantity = c.pivot.quantity + 1;
                    }
                    return c;
                }),
            });
        } else {
            // Add new item with purchase price
            const newProduct = {
                ...product,
                pivot: {
                    quantity: 1,
                    purchase_price: product.purchase_price || 0,
                    product_id: product.id,
                    user_id: 1,
                },
            };
            this.setState({ cart: [...this.state.cart, newProduct] });
        }

        // Sync with backend
        axios
            .post("/admin/purchase-cart", { barcode: product.barcode })
            .then((res) => {
                console.log("Added to cart:", res.data);
            })
            .catch((err) => {
                Swal.fire("Error!", err.response?.data?.message || "Failed to add product", "error");
            });
    }

    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });

        this.setState({ cart });
        if (!qty) return;

        axios
            .post("/admin/purchase-cart/change-qty", { product_id, quantity: qty })
            .then((res) => {
                console.log("Quantity updated");
            })
            .catch((err) => {
                Swal.fire("Error!", err.response?.data?.message || "Failed to update quantity", "error");
            });
    }

    handleChangePrice(product_id, price) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.purchase_price = price;
            }
            return c;
        });

        this.setState({ cart });
        if (!price) return;

        axios
            .post("/admin/purchase-cart/change-price", { product_id, purchase_price: price })
            .then((res) => {
                console.log("Price updated");
            })
            .catch((err) => {
                Swal.fire("Error!", err.response?.data?.message || "Failed to update price", "error");
            });
    }

    getTotal(cart) {
        const total = cart.map((c) => c.pivot.quantity * (c.pivot.purchase_price || 0));
        return sum(total).toFixed(2);
    }

    handleClickDelete(product_id) {
        axios
            .post("/admin/purchase-cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            })
            .catch((err) => {
                Swal.fire("Error!", err.response?.data?.message || "Failed to delete", "error");
            });
    }

    handleEmptyCart() {
        axios.post("/admin/purchase-cart/empty", { _method: "DELETE" }).then((res) => {
            this.setState({ cart: [] });
        }).catch((err) => {
            Swal.fire("Error!", err.response?.data?.message || "Failed to empty cart", "error");
        });
    }

    setSupplierId(event) {
        this.setState({ supplier_id: event.target.value });
    }

    handleDateChange(event) {
        this.setState({ purchase_date: event.target.value });
    }

    handleStatusChange(event) {
        this.setState({ status: event.target.value });
    }

    handleNotesChange(event) {
        this.setState({ notes: event.target.value });
    }

    handleClickSubmit() {
        const { supplier_id, purchase_date, status, notes, cart, suppliers } = this.state;

        // Validation
        if (!supplier_id) {
            Swal.fire("Error!", "Please select a supplier", "error");
            return;
        }

        if (cart.length === 0) {
            Swal.fire("Error!", "Please add at least one product", "error");
            return;
        }

        const total_amount = this.getTotal(cart);
        const items = cart.map(c => ({
            product_id: c.id,
            quantity: c.pivot.quantity,
            purchase_price: c.pivot.purchase_price || 0
        }));

        // Get supplier info safely
        const suppliersList = Array.isArray(suppliers) ? suppliers : [];
        const supplier = suppliersList.find(s => s.id == supplier_id);
        const supplierName = supplier ? `${supplier.first_name} ${supplier.last_name}` : 'Unknown';

        Swal.fire({
            title: "Confirm Purchase",
            customClass: { popup: "pos-purchase-dialog" },
            html: `
                <div style="text-align: left;">
                    <p><strong>Supplier:</strong> ${supplierName}</p>
                    <p><strong>Date:</strong> ${purchase_date}</p>
                    <p><strong>Total Amount:</strong> ${window.APP.currency_symbol} ${total_amount}</p>
                    <p><strong>Status:</strong> ${status}</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: "Save Purchase",
            cancelButtonText: "Cancel",
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return axios
                    .post("/admin/purchases", {
                        supplier_id,
                        purchase_date,
                        total_amount,
                        status,
                        notes,
                        items
                    })
                    .then((res) => {
                        this.loadCart();
                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err.response?.data?.message || "Failed to create purchase");
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.value) {
                Swal.fire("Success!", "Purchase created successfully!", "success");
                // Clear form
                this.setState({
                    cart: [],
                    supplier_id: "",
                    purchase_date: new Date().toISOString().split('T')[0],
                    status: "completed",
                    notes: ""
                });
            }
        });
    }

    render() {
        const { cart = [], products = [], suppliers = [], search = "", supplier_id, purchase_date, status, notes, translations = {} } = this.state;
        const suppliersList = Array.isArray(suppliers) ? suppliers : [];
        const currency = window.APP.currency_symbol || "";
        const itemCount = cart.reduce((count, item) => count + Number(item.pivot.quantity || 0), 0);

        return (
            <div className="purchase-container">
                <section className="purchase-product-column" aria-label="Purchase product catalog">
                    <div className="purchase-catalog-header"><div><span className="pos-workspace-eyebrow">Build your purchase</span><h2>Select products<span>.</span></h2><p>Choose stock to receive from your supplier.</p></div><span className="purchase-count">{products.length} products</span></div>
                    <div className="purchase-search"><i className="fas fa-search"></i><input type="search" aria-label="Search purchase products" placeholder={(translations["search_product"] || "Search products by name or barcode") + "…"} value={search} onChange={this.handleChangeSearch} onKeyDown={this.handleSearch} /><button type="button" onClick={() => this.loadProducts(search)} aria-label="Search products"><i className="fas fa-arrow-right"></i></button></div>
                    <div className="purchase-product-grid">{products.length ? products.map(product => {
                        const quantity = Number(cart.find(item => item.id === product.id)?.pivot?.quantity || 0);
                        return <article className={`purchase-product ${quantity ? "is-selected" : ""}`} key={product.id}><button type="button" onClick={() => this.addProductToCart(product)}><div className="purchase-product-visual"><i className="fas fa-box-open"></i>{product.image_url && <img src={product.image_url} alt="" onError={e => { e.currentTarget.hidden = true; }} />}{quantity > 0 && <span>{quantity} selected</span>}</div><span className="pos-workspace-eyebrow">{product.barcode || "Inventory"}</span><h3>{product.name}</h3><span className="purchase-stock">{product.quantity} currently in stock</span><div className="purchase-product-bottom"><strong>{currency} {Number(product.purchase_price || 0).toFixed(2)}</strong><span className="purchase-product-add"><i className="fas fa-plus"></i></span></div></button></article>;
                    }) : <div className="pos-empty-state"><i className="fas fa-box-open"></i><h3>No products found</h3><p>Try another product name or barcode.</p></div>}</div>
                </section>
                <aside className="purchase-sidebar-column" aria-label="Purchase details">
                    <div className="purchase-panel-heading"><div><span className="pos-workspace-eyebrow">Purchase details</span><h2>Stock intake</h2></div><span className="purchase-count">{itemCount} items</span></div>
                    <div className="purchase-form-section">
                        <label htmlFor="purchase-supplier">Supplier <span className="purchase-required">*</span></label>
                        {suppliersList.length === 0 && <div className="purchase-empty-suppliers">No suppliers found. <a href="/admin/suppliers/create">Add a supplier</a> to record a purchase.</div>}
                        <select id="purchase-supplier" className="form-control" value={supplier_id} onChange={this.setSupplierId}><option value="">Select supplier</option>{suppliersList.map(supplier => <option key={supplier.id} value={supplier.id}>{`${supplier.first_name} ${supplier.last_name}`}</option>)}</select>
                        <label htmlFor="purchase-date">Purchase date <span className="purchase-required">*</span></label><input id="purchase-date" type="date" className="form-control" value={purchase_date} onChange={this.handleDateChange} />
                    </div>
                    <div className="purchase-items-heading"><h3>Line items</h3><span>{cart.length} products</span></div>
                    <div className="purchase-line-items">{cart.length ? cart.map(item => <article className="purchase-line-item" key={item.id}><div className="purchase-line-top"><h4>{item.name}</h4><button type="button" className="pos-remove-item" title="Remove" aria-label={`Remove ${item.name}`} onClick={() => this.handleClickDelete(item.id)}><i className="fas fa-times"></i></button></div><div className="purchase-line-fields"><label>Quantity<input type="number" aria-label={`Quantity for ${item.name}`} className="form-control" value={item.pivot.quantity} min="1" onChange={event => this.handleChangeQty(item.id, event.target.value)} /></label><label>Unit cost<input type="number" aria-label={`Purchase price for ${item.name}`} className="form-control" value={item.pivot.purchase_price || 0} min="0" step="0.01" onChange={event => this.handleChangePrice(item.id, event.target.value)} /></label><div><span>Subtotal</span><strong>{currency} {(Number(item.pivot.quantity) * Number(item.pivot.purchase_price || 0)).toFixed(2)}</strong></div></div></article>) : <div className="pos-empty-order"><i className="fas fa-shopping-basket"></i><h3>No stock selected.</h3><p>Add products from the catalog to begin.</p></div>}</div>
                    {cart.length > 0 && <>
                        <div className="purchase-form-section purchase-notes"><label htmlFor="purchase-notes">Notes <span>Optional</span></label><textarea id="purchase-notes" className="form-control" placeholder="Add purchase notes…" rows="2" value={notes} onChange={this.handleNotesChange}></textarea></div>
                        <fieldset className="purchase-status"><legend>Purchase status</legend><div>{[{ value: "pending", label: "Pending", icon: "clock" }, { value: "completed", label: "Completed", icon: "check-circle" }, { value: "cancelled", label: "Cancelled", icon: "times-circle" }].map(option => <label key={option.value} className={status === option.value ? "is-selected" : ""}><input type="radio" name="status" id={`status-${option.value}`} value={option.value} checked={status === option.value} onChange={this.handleStatusChange} /><i className={`fas fa-${option.icon}`}></i><span>{option.label}</span></label>)}</div></fieldset>
                        <div className="purchase-total"><span>Total amount</span><strong><small>{currency}</small>{this.getTotal(cart)}</strong></div>
                        <div className="purchase-actions"><button type="button" className="purchase-cancel" onClick={this.handleEmptyCart}><i className="fas fa-times"></i>Cancel</button><button type="button" className="pos-primary-button" disabled={!supplier_id} onClick={this.handleClickSubmit}>Save purchase<i className="fas fa-arrow-right"></i></button></div>
                    </>}
                </aside>
            </div>
        );
    }
}

export default Purchase;

const root = document.getElementById("purchase");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Purchase />);
}
