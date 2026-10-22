<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Return Receipt {{ $purchaseReturn->return_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            width: 70mm;
            padding: 5mm;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .header h1 { font-size: 15px; font-weight: bold; margin-bottom: 3px; }
        .header p { font-size: 10px; margin: 2px 0; }
        .section {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #000;
        }
        .info-row { display: flex; justify-content: space-between; margin: 2px 0; }
        .info-label { font-weight: bold; width: 45%; }
        .info-value { width: 55%; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        th { text-align: left; padding: 4px 0; border-bottom: 1px solid #000; font-size: 10px; }
        td { padding: 4px 0; font-size: 10px; }
        .item-name { width: 50%; }
        .item-qty { width: 18%; text-align: center; }
        .item-price { width: 32%; text-align: right; }
        .total-section { margin-top: 8px; padding-top: 8px; border-top: 2px solid #000; }
        .total-row { display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; }
        .grand-total { font-size: 13px; font-weight: bold; border-top: 1px solid #000; padding-top: 4px; }
        .status-badge { display: inline-block; padding: 2px 6px; border: 1px solid #000; font-weight: bold; font-size: 9px; }
        .footer { text-align: center; margin-top: 12px; padding-top: 8px; border-top: 1px dashed #000; font-size: 9px; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.name') }}</h1>
    <p><strong>PURCHASE RETURN DEBIT NOTE</strong></p>
    @if(config('settings.store_address'))<p>{{ config('settings.store_address') }}</p>@endif
    @if(config('settings.store_phone'))<p>Tel: {{ config('settings.store_phone') }}</p>@endif
</div>

<div class="section">
    <div class="info-row">
        <span class="info-label">Return #:</span>
        <span class="info-value"><strong>{{ $purchaseReturn->return_no }}</strong></span>
    </div>
    @if($purchaseReturn->purchase)
    <div class="info-row">
        <span class="info-label">Original Ref:</span>
        <span class="info-value">{{ $purchaseReturn->purchase->purchase_no ?: ('#' . $purchaseReturn->purchase->id) }}</span>
    </div>
    @endif
    @if($purchaseReturn->branch)
    <div class="info-row">
        <span class="info-label">Branch:</span>
        <span class="info-value">{{ $purchaseReturn->branch->name }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Return Date:</span>
        <span class="info-value">{{ $purchaseReturn->return_date ? $purchaseReturn->return_date->format('d/m/Y') : now()->format('d/m/Y') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Refund Method:</span>
        <span class="info-value">{{ ucfirst($purchaseReturn->refund_method) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Processed By:</span>
        <span class="info-value">{{ $purchaseReturn->user->name ?? 'Admin' }}</span>
    </div>
</div>

<div class="section">
    <div class="info-row">
        <span class="info-label">Supplier:</span>
        <span class="info-value"><strong>{{ $purchaseReturn->supplier->first_name ?? '' }} {{ $purchaseReturn->supplier->last_name ?? '' }}</strong></span>
    </div>
    @if(optional($purchaseReturn->supplier)->phone)
    <div class="info-row">
        <span class="info-label">Phone:</span>
        <span class="info-value">{{ $purchaseReturn->supplier->phone }}</span>
    </div>
    @endif
</div>

<div class="section">
    <table>
        <thead>
            <tr>
                <th class="item-name">Returned Item</th>
                <th class="item-qty">Qty</th>
                <th class="item-price">Refund</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseReturn->items as $item)
            <tr>
                <td class="item-name">
                    {{ $item->product->name ?? 'Item' }}<br>
                    <small style="font-size: 8px;">{{ $item->product->barcode ?? '' }}</small>
                </td>
                <td class="item-qty">{{ (float)$item->quantity }}</td>
                <td class="item-price">
                    {{ config('settings.currency_symbol') }}{{ number_format($item->return_price, 2) }}<br>
                    <strong>{{ config('settings.currency_symbol') }}{{ number_format($item->total_amount, 2) }}</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="total-section">
    <div class="total-row grand-total">
        <span>TOTAL REFUND:</span>
        <span>{{ config('settings.currency_symbol') }}{{ number_format($purchaseReturn->total_amount, 2) }}</span>
    </div>
    <div class="info-row" style="margin-top: 6px;">
        <span class="info-label">Returned Items:</span>
        <span class="info-value">{{ (float)$purchaseReturn->items->sum('quantity') }} pcs</span>
    </div>
</div>

@if($purchaseReturn->return_reason || $purchaseReturn->notes)
<div class="section" style="margin-top: 8px;">
    @if($purchaseReturn->return_reason)
        <p style="font-size: 9px;"><strong>Reason:</strong> {{ $purchaseReturn->return_reason }}</p>
    @endif
    @if($purchaseReturn->notes)
        <p style="font-size: 9px;"><strong>Notes:</strong> {{ $purchaseReturn->notes }}</p>
    @endif
</div>
@endif

<div class="footer">
    <p>Stock Inventory Adjusted Automatically</p>
    <p style="margin-top: 4px;">{{ now()->format('d/m/Y H:i:s') }}</p>
</div>
</body>
</html>
