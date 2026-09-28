/**
 * CommonHelper - Universal Helper for POS
 * Centralizes Receipt Logo, Address, Header, Footer, and Thermal Print Styles
 */

export const CommonHelper = {
    /**
     * Get Business Logo URL
     */
    getLogoUrl() {
        return window.APP && window.APP.business_logo_url ? window.APP.business_logo_url : null;
    },

    /**
     * Get Business Name / Brand Title
     */
    getBrandName() {
        return (window.APP && window.APP.app_name) ? window.APP.app_name : "Salevince POS";
    },

    /**
     * Get Default Brand Subtitle
     */
    getBrandSubtitle() {
        return (window.APP && window.APP.app_description) ? window.APP.app_description : "SERVICES | DEALS | BILLING";
    },

    /**
     * Get Universal Printable Header HTML (Logo + Brand + Subbrand + Address + Counter)
     *
     * @param {Object} options - { branchName, branchAddress, counterName }
     * @returns {string} HTML string
     */
    /**
     * Get Universal Printable Header HTML (Store Name + Address + Counter)
     */
    getReceiptHeaderHTML(options = {}) {
        const logoUrl = this.getLogoUrl();
        const branchName = options.branchName || "Main Branch";
        const branchAddress = options.branchAddress || "";
        const branchPhone = options.branchPhone || "";

        const logoHTML = logoUrl
            ? `<div class="receipt-logo-wrapper"><img class="receipt-business-logo" src="${logoUrl}" alt="${branchName} Logo"></div>`
            : "";

        return `
            <div class="receipt-header">
                ${logoHTML}
                <div class="receipt-store-name">${branchName}</div>
                ${branchAddress || branchPhone ? `<div class="receipt-address-line">${branchAddress}${branchAddress && branchPhone ? " | " : ""}${branchPhone}</div>` : ""}
            </div>
        `;
    },

    /**
     * Get Universal Printable Footer HTML
     */
    getReceiptFooterHTML(createdAt = "") {
        const dateStr = createdAt || new Date().toLocaleString();
        const appName = (window.APP && window.APP.app_name) ? window.APP.app_name : "Salevince POS";
        return `
            <div class="receipt-footer">
                <div class="thanks-title">*** THANK YOU FOR VISITING ***</div>
                <div class="thanks-sub">${appName}</div>
                <div class="receipt-notice">Please retain this receipt for any queries.</div>
                <div class="receipt-software">Powered by ${appName} | Printed on ${dateStr}</div>
            </div>
        `;
    },

    /**
     * Get Scoped Receipt Styles for Modal / Preview without mutating body or page background
     */
    getReceiptStyles() {
        return `
            .thermal-receipt { width: 100%; max-width: 72mm; margin: 0 auto; box-sizing: border-box; font-family: "Courier New", Courier, monospace, sans-serif; font-size: 11px; line-height: 1.35; color: #000; background: #fff; padding: 4px 6px; }

            .receipt-header { text-align: center; margin-bottom: 2px; }
            .receipt-store-name { font-size: 13px; font-weight: 900; letter-spacing: 0.3px; text-transform: uppercase; color: #000; }
            .receipt-address-line { font-size: 9.5px; line-height: 1.4; color: #333; }

            .invoice-title { font-size: 11px; font-weight: 900; text-align: center; text-transform: uppercase; margin: 8px 0 2px; letter-spacing: 0.5px; color: #000; }
            .receipt-barcode svg { max-width: 100%; }
            .receipt-divider { border-top: 1px dashed #000; height: 0; margin: 4px 0; }
            .receipt-divider-double { border-top: 2px solid #000; height: 0; margin: 5px 0; }
            .receipt-title { font-size: 12px; font-weight: 900; text-align: center; letter-spacing: 1px; margin: 3px 0; color: #000; }

            .meta-grid { font-size: 10.5px; margin: 2px 0; }
            .meta-row { display: flex; justify-content: space-between; padding: 1px 0; }
            .meta-row .meta-label { color: #000; }
            .meta-row .meta-value { font-weight: 700; text-align: right; color: #000; }

            .receipt-table-head { display: grid; grid-template-columns: 1fr 30px 48px 54px; font-weight: 900; font-size: 9.5px; text-align: right; color: #000; }
            .receipt-table-head .col-item { text-align: left; }
            .receipt-item-block { margin: 3px 0; font-size: 10.5px; }
            .receipt-item-name { font-weight: 700; word-break: break-word; color: #000; }
            .receipt-item-row { display: grid; grid-template-columns: 1fr 30px 48px 54px; text-align: right; font-size: 10.5px; color: #000; }

            .receipt-summary { font-size: 11px; margin-top: 2px; }
            .summary-row { display: flex; justify-content: space-between; padding: 1px 0; font-weight: 600; color: #000; }
            .summary-row.grand-total { font-size: 14px; font-weight: 900; padding: 3px 0; color: #000; }

            .terms-block { font-size: 8.5px; text-align: left; margin-top: 6px; line-height: 1.5; color: #222; }
            .terms-title { font-weight: 800; font-size: 9.5px; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px; color: #000; }

            .receipt-footer { text-align: center; margin-top: 6px; font-size: 9.5px; color: #000; }
            .thanks-title { font-weight: 800; font-size: 11px; margin-bottom: 2px; color: #000; }
            .thanks-sub { font-size: 9.5px; font-weight: 700; color: #000; }
            .receipt-notice { font-size: 9px; margin-top: 2px; color: #333; }
            .receipt-software { font-size: 8.5px; margin-top: 4px; color: #555; }

            .srb-footer { text-align: center; padding: 4px 0; margin-top: 4px; }
            .srb-row { display: flex; align-items: center; justify-content: center; gap: 4mm; }
            .srb-logo-cell, .srb-code-cell { width: 32mm; min-width: 0; }
            .srb-logo { display: block; width: 30mm; max-height: 18mm; object-fit: contain; margin: 0 auto; }
            .srb-label, .srb-verify { font-size: 8.5px; margin-top: 2px; color: #333; }
            .srb-invoice-id { font-weight: 700; font-size: 9.5px; margin: 2px 0; word-break: break-all; color: #000; }
            .srb-qr { width: 22mm; height: 22mm; display: block; margin: 3px auto; }

            .receipt-logo-wrapper { margin-bottom: 4px; text-align: center; }
            .receipt-business-logo { display: block; max-width: 42mm; max-height: 18mm; object-fit: contain; margin: 0 auto; }

            .invoice-number { font-size: 11px; font-weight: 700; text-align: center; margin: 2px 0; letter-spacing: 0.5px; color: #000; }
        `;
    },

    /**
     * Get Universal Print CSS Style Block for Thermal (80mm) Printers (Dedicated Print Context)
     */
    getThermalPrintStyles() {
        return `
            @page { size: 80mm auto; margin: 2mm; }
            *, *:before, *:after { box-sizing: border-box; }
            html, body { margin: 0; padding: 0; background: #fff !important; }
            body { width: 72mm; margin: 2mm auto; font-family: "Courier New", Courier, monospace, sans-serif; font-size: 11px; line-height: 1.35; color: #000; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            ${this.getReceiptStyles()}
        `;
    }
};

// Bind to window object for global availability in inline scripts and blade templates
if (typeof window !== "undefined") {
    window.CommonHelper = CommonHelper;
}
