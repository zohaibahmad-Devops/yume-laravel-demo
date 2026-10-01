@extends('layouts.app')
@section('title', $order->reference)

@section('content')
<p style="margin:0 0 10px"><a href="{{ route('orders.index') }}" style="color:var(--muted);font-size:14px;text-decoration:none">&larr; All orders</a></p>

<h1>{{ $order->reference }}</h1>
<p class="lede" style="margin-bottom:18px">
  {{ $order->dealer->name }}, {{ $order->dealer->city }} &middot;
  placed {{ $order->placed_on->format('d M Y') }} &middot;
  {{ $order->dealer->phone }}
</p>

<div class="flow">
  @foreach (App\Models\Order::STATUSES as $i => $stage)
    @php
      $current = $stage === $order->status;
      $index = array_search($order->status, App\Models\Order::STATUSES, true);
    @endphp
    @if ($i > 0)<i>&rarr;</i>@endif
    <span class="{{ $current ? 'here' : ($i < $index ? 'done' : '') }}">{{ strtoupper($stage) }}</span>
  @endforeach
</div>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin:22px 0 28px">
  @if ($order->nextStatus())
    <form method="POST" action="{{ route('orders.advance', $order) }}" style="margin:0">
      @csrf
      <button class="btn btn-primary" type="submit">Move to {{ $order->nextStatus() }}</button>
    </form>
  @else
    <span class="pill pill-Delivered" style="padding:11px 20px">DELIVERED &mdash; PIPELINE COMPLETE</span>
  @endif
  <a class="btn btn-ghost" href="{{ route('stock.index') }}">Check stock</a>
</div>

<div class="kpis">
  <div class="kpi">
    <div class="label">Order total</div>
    <div class="value">PKR {{ number_format($order->total) }}</div>
    <div class="sub">{{ $order->items->count() }} {{ Str::plural('line', $order->items->count()) }}</div>
  </div>
  <div class="kpi">
    <div class="label">Dealer credit limit</div>
    <div class="value">PKR {{ number_format($order->dealer->credit_limit) }}</div>
    <div class="sub">as agreed with the dealer</div>
  </div>
  <div class="kpi">
    <div class="label">Dealer outstanding</div>
    <div class="value">PKR {{ number_format($order->dealer->outstanding()) }}</div>
    <div class="sub">
      @if ($order->dealer->outstanding() > (float) $order->dealer->credit_limit)
        over limit
      @else
        within limit
      @endif
    </div>
  </div>
</div>

<div class="panel">
  <h2>Order lines</h2>
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Product</th>
          <th class="num">Qty</th>
          <th class="num">Unit price</th>
          <th class="num">Line total</th>
          <th>Stock after</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->items as $item)
          <tr>
            <td class="sku">{{ $item->product->sku }}</td>
            <td>{{ $item->product->name }}</td>
            <td class="num">{{ number_format($item->quantity) }} {{ $item->product->unit }}</td>
            <td class="num">{{ number_format($item->unit_price) }}</td>
            <td class="num">PKR {{ number_format($item->lineTotal()) }}</td>
            <td>
              @if ($item->product->isLow())
                <span class="pill flag-low">{{ number_format($item->product->stock) }} &middot; REORDER</span>
              @else
                <span class="pill flag-ok">{{ number_format($item->product->stock) }}</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="num" style="font-weight:700;border-top:2px solid var(--line)">Total</td>
          <td class="num" style="font-weight:700;border-top:2px solid var(--line)">PKR {{ number_format($order->total) }}</td>
          <td style="border-top:2px solid var(--line)"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
