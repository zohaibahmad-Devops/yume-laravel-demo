@extends('layouts.app')
@section('title', $dealer->name)

@section('content')
<p style="margin:0 0 10px"><a href="{{ route('dealers.index') }}" style="color:var(--muted);font-size:14px;text-decoration:none">&larr; All dealers</a></p>

<h1>{{ $dealer->name }}</h1>
<p class="lede" style="margin-bottom:22px">{{ $dealer->city }}, Punjab &middot; {{ $dealer->phone }}</p>

@php
  $limit = (float) $dealer->credit_limit;
  $used = $dealer->outstanding();
  $lifetime = (float) $dealer->orders->sum('total');
  $pct = $limit > 0 ? min(100, round($used / $limit * 100)) : 0;
@endphp

<div class="kpis">
  <div class="kpi">
    <div class="label">Outstanding</div>
    <div class="value">PKR {{ number_format($used) }}</div>
    <div class="sub">{{ $pct }}% of limit used</div>
  </div>
  <div class="kpi">
    <div class="label">Credit limit</div>
    <div class="value">PKR {{ number_format($limit) }}</div>
    <div class="sub">
      @if ($used > $limit) over by PKR {{ number_format($used - $limit) }}
      @else PKR {{ number_format($limit - $used) }} still available
      @endif
    </div>
  </div>
  <div class="kpi">
    <div class="label">Lifetime value</div>
    <div class="value">PKR {{ number_format($lifetime) }}</div>
    <div class="sub">across {{ $dealer->orders->count() }} {{ Str::plural('order', $dealer->orders->count()) }}</div>
  </div>
  <div class="kpi">
    <div class="label">Average order</div>
    <div class="value">PKR {{ number_format($dealer->orders->count() ? $lifetime / $dealer->orders->count() : 0) }}</div>
    <div class="sub">all time</div>
  </div>
</div>

<div class="panel">
  <h2>Order history</h2>
  @if ($dealer->orders->isEmpty())
    <p class="empty">This dealer has not ordered yet.</p>
  @else
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>Reference</th>
          <th>Placed</th>
          <th class="num">Lines</th>
          <th>Status</th>
          <th class="num">Total</th>
          <th class="num">Counts against limit</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($dealer->orders as $order)
          <tr>
            <td><a class="ref" href="{{ route('orders.show', $order) }}">{{ $order->reference }}</a></td>
            <td>{{ $order->placed_on->format('d M Y') }}</td>
            <td class="num">{{ $order->items_count }}</td>
            <td><span class="pill pill-{{ $order->status }}">{{ strtoupper($order->status) }}</span></td>
            <td class="num">PKR {{ number_format($order->total) }}</td>
            <td class="num">{{ $order->status === 'Delivered' ? 'no' : 'yes' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection
