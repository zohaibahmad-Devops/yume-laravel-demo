@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h1>Dashboard</h1>
<p class="lede">
  Stock position, open orders and the dealer ledger for a pipes-and-fittings distributor.
  Everything below is queried live on each request.
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
    <div class="sub">{{ Str::plural('item', $lowStock->count()) }} at or below reorder level</div>
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

<div class="cols2">
  <div class="panel">
    <h2>
      Order pipeline
      <a class="side" href="{{ route('orders.index') }}">All orders &rarr;</a>
    </h2>
    <div class="wrapper">
      <table>
        <thead>
          <tr>
            <th>Stage</th>
            <th class="num">Orders</th>
            <th style="min-width:120px">Share</th>
          </tr>
        </thead>
        <tbody>
          @php $totalOrders = max($byStatus->sum(), 1); @endphp
          @foreach ($byStatus as $stage => $count)
            <tr>
              <td><a href="{{ route('orders.index', ['status' => $stage]) }}" style="text-decoration:none"><span class="pill pill-{{ $stage }}">{{ strtoupper($stage) }}</span></a></td>
              <td class="num">{{ $count }}</td>
              <td><span class="meter"><i style="width:{{ round($count / $totalOrders * 100) }}%"></i></span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <h2>
      Latest activity
      <span class="side" style="color:var(--muted);font-weight:400">audit trail</span>
    </h2>
    @if ($activity->isEmpty())
      <p class="empty">Nothing recorded yet.</p>
    @else
      <ul class="trail">
        @foreach ($activity as $event)
          <li class="{{ $loop->first ? 'now' : '' }}">
            <div class="when">{{ $event->created_at->format('d M Y, H:i') }}</div>
            <div class="what">
              <a class="ref" href="{{ route('orders.show', $event->order) }}">{{ $event->order->reference }}</a>
              @if ($event->from_status)
                &middot; {{ $event->from_status }} &rarr; {{ $event->to_status }}
              @else
                &middot; created as {{ $event->to_status }}
              @endif
            </div>
            <div class="who">by {{ $event->actor }}</div>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</div>

<div class="panel">
  <h2>
    Below reorder level
    <a class="side" href="{{ route('stock.index', ['low' => 1]) }}">Reorder list &rarr;</a>
  </h2>
  @if ($lowStock->isEmpty())
    <p class="empty">Nothing needs reordering right now.</p>
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
            <td><a class="ref" href="{{ route('stock.show', $p) }}">{{ $p->sku }}</a></td>
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
  <h2>
    Recent orders
    <a class="side" href="{{ route('reports.index') }}">Reports &rarr;</a>
  </h2>
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
            <td><a class="ref" href="{{ route('orders.show', $order) }}">{{ $order->reference }}</a></td>
            <td><a href="{{ route('dealers.show', $order->dealer) }}" style="text-decoration:none">{{ $order->dealer->name }}</a></td>
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
