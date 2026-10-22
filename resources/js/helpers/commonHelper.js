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
            .thermal-receipt {
                width: 100%; max-width: 70mm; margin: 0 auto; box-sizing: border-box;
                font-family: "Segoe UI", Tahoma, Arial, Helvetica, sans-serif;
                font-size: 12px; line-height: 1.35; font-weight: 600; color: #000; background: #fff;
                padding: 4px 6px; word-break: break-word;
            }
            .thermal-receipt * { font-weight: 600 !important; box-sizing: border-box; }

            .receipt-logo-wrapper { margin: 0 0 2px; text-align: center; line-height: 0; }
            .receipt-business-logo { display: block; width: 48mm; max-width: 100%; height: auto; max-height: 24mm; object-fit: contain; margin: 0 auto; }
            .receipt-header { text-align: center; margin: 0 0 2px; }
            .receipt-store-name { font-size: 15px; text-transform: uppercase; color: #000; margin-top: 2px; line-height: 1.25; }
            .receipt-address-line { font-size: 10.5px; line-height: 1.35; color: #000; margin-top: 2px; }

            .receipt-divider { border-top: 1.5px dashed #000; height: 0; margin: 4px 0; }
            .receipt-divider-double { border-top: 2px solid #000; height: 0; margin: 5px 0; }

            .meta-grid { font-size: 12px; margin: 2px 0; }
            .meta-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; padding: 1px 0; }
            .meta-row .meta-label { color: #000; flex: 0 0 auto; }
            .meta-row .meta-value { text-align: right; color: #000; flex: 1 1 auto; min-width: 0; word-break: break-word; }

            .receipt-table-head { display: grid; grid-template-columns: minmax(0,1fr) 26px 54px 60px; column-gap: 3px; font-size: 10.5px; text-align: right; color: #000; padding-bottom: 1px; white-space: nowrap; }
            .receipt-table-head .col-item { text-align: left; }
            .receipt-item-block { margin: 3px 0; font-size: 12px; }
            .receipt-item-name { word-break: break-word; color: #000; margin-bottom: 1px; font-size: 12.5px; }
            .receipt-item-row { display: grid; grid-template-columns: minmax(0,1fr) 26px 54px 60px; column-gap: 3px; text-align: right; font-size: 12px; color: #000; white-space: nowrap; }

            .receipt-summary { font-size: 12px; margin-top: 2px; }
            .summary-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; padding: 1.5px 0; color: #000; }
            .summary-row > span:first-child { flex: 1 1 auto; min-width: 0; word-break: break-word; }
            .summary-row > span:last-child { flex: 0 0 auto; white-space: nowrap; text-align: right; }
            .summary-row.grand-total { font-size: 15px; padding: 3px 0; }

            .terms-block { font-size: 10px; text-align: left; margin-top: 4px; line-height: 1.35; color: #000; }
            .terms-title { font-size: 10.5px; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.3px; color: #000; }

            .srb-footer { text-align: center; padding: 2px 0 0; margin-top: 4px; }
            .srb-label { font-size: 10.5px; color: #000; }
            .srb-invoice-id { font-size: 11.5px; margin: 1px 0 4px; word-break: break-all; color: #000; }
            .srb-row { display: flex; align-items: center; justify-content: center; gap: 2.5mm; }
            .srb-logo-img { width: 28mm; height: 28mm; object-fit: contain; display: block; }
            .srb-qr { width: 28mm; height: 28mm; display: block; }
            .srb-verify { font-size: 10.5px; margin-top: 3px; color: #000; }

            .receipt-footer { text-align: center; margin-top: 6px; font-size: 11px; color: #000; }
            .thanks-title { font-size: 12px; margin-bottom: 2px; color: #000; }
            .thanks-sub { font-size: 11px; color: #000; }
            .receipt-notice { font-size: 10px; margin-top: 2px; color: #000; }
            .receipt-software { font-size: 9.5px; margin-top: 4px; color: #000; line-height: 1.3; word-break: break-word; }
        `;
    },

    getThermalPrintStyles() {
        return `
            @page {
                size: auto;
                margin: 0;
            }
            *, *:before, *:after {
                box-sizing: border-box !important;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                height: auto !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body {
                width: 70mm !important;
                max-width: 70mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                font-family: "Segoe UI", Tahoma, Arial, Helvetica, sans-serif !important;
                font-size: 12px !important;
                line-height: 1.35 !important;
                font-weight: 600 !important;
                color: #000 !important;
                overflow-x: hidden !important;
            }
            ${this.getReceiptStyles()}
            .thermal-receipt {
                width: 100% !important;
                max-width: 70mm !important;
                margin: 0 auto !important;
                padding: 0 4.5mm 4mm 1.5mm !important;
                box-sizing: border-box !important;
            }
        `;
    },
    /** Page ki height content ke barabar set karta hai taake slip dusre page par na jaye. */
    fitPrintPage(win) {
    // Page height ab printer ke paper size se aati hai, yahan kuch nahi karna.
    },
};

// Bind to window object for global availability in inline scripts and blade templates
if (typeof window !== "undefined") {
    window.CommonHelper = CommonHelper;
}
