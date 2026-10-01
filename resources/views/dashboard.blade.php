@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h1>Dashboard</h1>
<p class="lede">
  Stock position, open orders and the dealer ledger for a pipes-and-fittings distributor.
  Everything below is queried live from the database on each request.
</p>

<div class="kpis">
  <div class="kpi">
    <div class="label">Stock value</div>
    <div class="value">PKR {{ number_format($stockValue) }}</div>
    <div class="sub">at cost, across {{ $skuCount }} SKUs</div>
  </div>
  <div class="kpi">
    <div class="label">Needs reorder</div>
    <div class="value">{{ $lowStock->count() }}</div>
    <div class="sub">{{ $lowStock->count() === 1 ? 'item at or below' : 'items at or below' }} reorder level</div>
  </div>
  <div class="kpi">
    <div class="label">Open orders</div>
    <div class="value">{{ $openOrders }}</div>
    <div class="sub">not yet delivered</div>
  </div>
  <div class="kpi">
    <div class="label">Outstanding</div>
    <div class="value">PKR {{ number_format($outstanding) }}</div>
    <div class="sub">across {{ $dealerCount }} dealers</div>
  </div>
</div>

<div class="panel">
  <h2>Order pipeline</h2>
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>Stage</th>
          <th class="num">Orders</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($byStatus as $stage => $count)
          <tr>
            <td><span class="pill pill-{{ $stage }}">{{ strtoupper($stage) }}</span></td>
            <td class="num">{{ $count }}</td>
            <td><a href="{{ route('orders.index', ['status' => $stage]) }}">View</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <h2>Below reorder level</h2>
  @if ($lowStock->isEmpty())
    <p style="padding:18px 20px;margin:0;color:var(--muted)">Nothing needs reordering right now.</p>
  @else
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Product</th>
          <th>Category</th>
          <th class="num">In stock</th>
          <th class="num">Reorder at</th>
          <th class="num">Short by</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($lowStock as $p)
          <tr>
            <td class="sku">{{ $p->sku }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->category }}</td>
            <td class="num">{{ number_format($p->stock) }} {{ $p->unit }}</td>
            <td class="num">{{ number_format($p->reorder_level) }}</td>
            <td class="num"><span class="pill flag-low">{{ number_format($p->reorder_level - $p->stock) }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

<div class="panel">
  <h2>Recent orders</h2>
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>Reference</th>
          <th>Dealer</th>
          <th>Placed</th>
          <th>Status</th>
          <th class="num">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($recent as $order)
          <tr>
            <td><a href="{{ route('orders.show', $order) }}" class="sku" style="color:var(--orange);font-weight:600">{{ $order->reference }}</a></td>
            <td>{{ $order->dealer->name }}</td>
            <td>{{ $order->placed_on->format('d M Y') }}</td>
            <td><span class="pill pill-{{ $order->status }}">{{ strtoupper($order->status) }}</span></td>
            <td class="num">PKR {{ number_format($order->total) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
