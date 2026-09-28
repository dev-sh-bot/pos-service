<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class CommonHelper
{
    /**
     * Get Business Logo URL
     */
    public static function getLogoUrl()
    {
        $logo = config('settings.business_logo');
        if (!$logo) {
            return null;
        }
        if (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://'])) {
            return $logo;
        }
        return asset('storage/' . $logo);
    }

    /**
     * Get Brand Name
     */
    public static function getBrandName()
    {
        return config('settings.app_name', config('app.name', 'Salevince POS'));
    }

    /**
     * Get Brand Subtitle
     */
    public static function getBrandSubtitle()
    {
        return config('settings.app_description', config('app.app_description', 'SERVICES | DEALS | BILLING'));
    }

    /**
     * Get Universal Printable Header HTML for Blade views
     */
    public static function getReceiptHeaderHTML($branchName = 'Main Branch', $counterName = null)
    {
        $logoUrl = self::getLogoUrl();
        $brandName = self::getBrandName();
        $brandSub = self::getBrandSubtitle();
        $address = config('settings.app_description', '');

        $logoHTML = $logoUrl
            ? '<div class="receipt-logo-wrapper"><img class="receipt-business-logo" src="' . e($logoUrl) . '" alt="Logo"></div>'
            : '';

        $counterHTML = $counterName ? '<div class="receipt-contact">Counter: ' . e($counterName) . '</div>' : '';

        return '
            <div class="receipt-header">
                ' . $logoHTML . '
                <div class="receipt-brand">' . e($brandName) . '</div>
                <div class="receipt-subbrand">' . e($brandSub) . '</div>
                <div class="receipt-address">' . e($branchName) . ($address ? ' — ' . e($address) : '') . '</div>
                ' . $counterHTML . '
            </div>
        ';
    }

    /**
     * Get Universal Receipt Footer HTML
     */
    public static function getReceiptFooterHTML($createdAt = null)
    {
        $dateStr = $createdAt ?? date('Y-m-d H:i:s');
        $brandName = self::getBrandName();
        return '
            <div class="receipt-footer">
                <div class="thanks-title">*** THANK YOU FOR VISITING ***</div>
                <div class="thanks-sub">' . e($brandName) . ' — Service POS</div>
                <div class="receipt-notice">Please retain this receipt for any queries.</div>
                <div class="receipt-software">Powered by ' . e($brandName) . ' | Printed on ' . e($dateStr) . '</div>
            </div>
        ';
    }
}
