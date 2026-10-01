<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice {{ $order->reference }} &mdash; Mehran Distributors</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600;700&display=swap">
<style>
:root{
  --navy:#112d4f; --orange:#f16523; --line:#dfe4ea; --muted:#5c6269; --surface:#f5f7fa;
  --sans:"Open Sans",-apple-system,Arial,sans-serif;
  --display:"Montserrat","Open Sans",Arial,sans-serif;
}
*{box-sizing:border-box}
body{margin:0;background:var(--surface);color:var(--navy);font:15px/1.6 var(--sans)}

.bar{background:var(--navy);color:#cfe0f2;font-size:13px}
.bar .in{max-width:820px;margin:0 auto;padding:12px 24px;display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.bar a{color:#ffc9a8;font-weight:600;text-decoration:none}
.bar button{margin-left:auto;font:600 13px var(--sans);background:var(--orange);color:#fff;border:0;
  border-radius:999px;padding:8px 20px;cursor:pointer}
@media(max-width:620px){.bar button{margin-left:0}}

.sheet{max-width:820px;margin:26px auto 60px;background:#fff;border:1px solid var(--line);
  border-radius:12px;padding:44px 48px}
@media(max-width:620px){.sheet{padding:28px 22px;margin:16px 12px 40px}}

.top{display:flex;justify-content:space-between;gap:28px;flex-wrap:wrap;
  padding-bottom:26px;border-bottom:3px solid var(--navy)}
.logo{display:flex;align-items:center;gap:11px;font-family:var(--display);font-weight:700;font-size:21px;
  letter-spacing:-.02em}
.logo .tile{width:34px;height:34px;border-radius:9px;background:var(--orange);display:grid;place-items:center;
  color:#fff;font-size:17px}
.issuer{font-size:13px;color:var(--muted);margin-top:8px;line-height:1.55}
.title{text-align:right}
.title h1{font-family:var(--display);font-size:30px;letter-spacing:-.03em;margin:0 0 4px}
.title .ref{font-family:ui-monospace,Menlo,monospace;font-size:15px;color:var(--orange);font-weight:700}
.title .meta{font-size:13px;color:var(--muted);margin-top:6px}
@media(max-width:620px){.title{text-align:left}}

.parties{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:26px;margin:26px 0 30px}
.party h3{font-family:var(--display);font-size:10.5px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;
  color:var(--muted);margin:0 0 7px}
.party .name{font-weight:700;font-size:16px}
.party .rest{font-size:13.5px;color:var(--muted);line-height:1.6}

table{width:100%;border-collapse:collapse;font-size:14px}
th{font-family:var(--display);font-size:10.5px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;
  color:var(--muted);text-align:left;padding:10px 12px;border-bottom:2px solid var(--line)}
td{padding:12px;border-bottom:1px solid var(--line);vertical-align:top}
.num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.sku{font-family:ui-monospace,Menlo,monospace;font-size:12.5px;color:var(--muted);display:block}

.totals{margin-top:22px;margin-left:auto;width:min(340px,100%)}
.totals div{display:flex;justify-content:space-between;gap:20px;padding:8px 12px;font-size:14.5px}
.totals .grand{border-top:2px solid var(--navy);margin-top:6px;padding-top:12px;
  font-family:var(--display);font-weight:700;font-size:18px}
.totals .muted{color:var(--muted)}

.stamp{display:inline-block;font-family:var(--display);font-size:11px;font-weight:700;letter-spacing:.12em;
  padding:6px 14px;border-radius:999px;margin-top:4px}
.s-New,.s-Confirmed,.s-Packed{background:#fef3c7;color:#92400e}
.s-Dispatched{background:#dbeafe;color:#1e40af}
.s-Delivered{background:#dcfce7;color:#166534}

.foot{margin-top:34px;padding-top:20px;border-top:1px solid var(--line);font-size:12.5px;color:var(--muted);
  line-height:1.65}

@media print{
  @page{size:A4;margin:14mm}
  body{background:#fff;font-size:12pt}
  .bar{display:none}
  .sheet{max-width:none;margin:0;border:0;border-radius:0;padding:0}
}
</style>
</head>
<body>

<div class="bar">
  <div class="in">
    <a href="{{ route('orders.show', $order) }}">&larr; Back to {{ $order->reference }}</a>
    <span style="opacity:.5">&middot;</span>
    <span>Print or save as PDF &mdash; the stylesheet switches to A4</span>
    <button type="button" onclick="window.print()">Print</button>
  </div>
</div>

<div class="sheet">

  <div class="top">
    <div>
      <div class="logo"><span class="tile">M</span> Mehran Distributors</div>
      <div class="issuer">
        Pipes, fittings, valves &amp; cables<br>
        Adiala Road, Rawalpindi, Punjab<br>
        NTN 1234567-8
      </div>
    </div>
    <div class="title">
      <h1>INVOICE</h1>
      <div class="ref">{{ $order->reference }}</div>
      <div class="meta">
        Issued {{ $order->placed_on->format('d F Y') }}<br>
        Due {{ $order->placed_on->copy()->addDays(30)->format('d F Y') }}
      </div>
      <span class="stamp s-{{ $order->status }}">{{ strtoupper($order->status) }}</span>
    </div>
  </div>

  <div class="parties">
    <div class="party">
      <h3>Billed to</h3>
      <div class="name">{{ $order->dealer->name }}</div>
      <div class="rest">
        {{ $order->dealer->city }}, Punjab<br>
        {{ $order->dealer->phone }}<br>
        Credit limit PKR {{ number_format($order->dealer->credit_limit) }}
      </div>
    </div>
    <div class="party">
      <h3>Payment</h3>
      <div class="name">30 days net</div>
      <div class="rest">
        Bank transfer or cheque<br>
        Quote {{ $order->reference }} as the reference<br>
        Outstanding with us: PKR {{ number_format($order->dealer->outstanding()) }}
      </div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="num">Qty</th>
        <th class="num">Unit price</th>
        <th class="num">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($order->items as $item)
        <tr>
          <td>
            {{ $item->product->name }}
            <span class="sku">{{ $item->product->sku }} &middot; {{ $item->product->category }}</span>
          </td>
          <td class="num">{{ number_format($item->quantity) }} {{ $item->product->unit }}</td>
          <td class="num">{{ number_format($item->unit_price, 2) }}</td>
          <td class="num">{{ number_format($item->lineTotal(), 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  @php
    $subtotal = (float) $order->total;
    $tax = round($subtotal * 0.18, 2);
  @endphp

  <div class="totals">
    <div><span class="muted">Subtotal</span><span>PKR {{ number_format($subtotal, 2) }}</span></div>
    <div><span class="muted">Sales tax @ 18%</span><span>PKR {{ number_format($tax, 2) }}</span></div>
    <div class="grand"><span>Total due</span><span>PKR {{ number_format($subtotal + $tax, 2) }}</span></div>
  </div>

  <div class="foot">
    Goods remain the property of Mehran Distributors until paid for in full.
    Claims for shortage or damage must be made within seven days of delivery.
    <br><br>
    <strong>This is a demonstration document.</strong> Mehran Distributors is not a real company,
    and the tax figure is illustrative. The invoice is generated from the same order
    records as the rest of this application &mdash; nothing here is typed in by hand.
  </div>

</div>

</body>
</html>
