@extends('layouts.app')
@section('title', 'Orders')

@section('content')
<h1>Orders</h1>
<p class="lede">
  Dealer orders through the five-stage pipeline. Open any order to see its lines and
  move it to the next stage.
</p>

<div class="panel">
  <div class="filters">
    <a href="{{ route('orders.index') }}" class="{{ $status === 'all' ? 'on' : '' }}">All</a>
    @foreach ($statuses as $s)
      <a href="{{ route('orders.index', ['status' => $s]) }}" class="{{ $status === $s ? 'on' : '' }}">{{ $s }}</a>
    @endforeach
  </div>
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>Reference</th>
          <th>Dealer</th>
          <th>City</th>
          <th>Placed</th>
          <th class="num">Lines</th>
          <th>Status</th>
          <th class="num">Total</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($orders as $order)
          <tr>
            <td><a href="{{ route('orders.show', $order) }}" class="sku" style="color:var(--orange);font-weight:600">{{ $order->reference }}</a></td>
            <td>{{ $order->dealer->name }}</td>
            <td>{{ $order->dealer->city }}</td>
            <td>{{ $order->placed_on->format('d M Y') }}</td>
            <td class="num">{{ $order->items_count }}</td>
            <td><span class="pill pill-{{ $order->status }}">{{ strtoupper($order->status) }}</span></td>
            <td class="num">PKR {{ number_format($order->total) }}</td>
          </tr>
        @empty
          <tr><td colspan="7" style="color:var(--muted)">No orders at this stage.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<p style="color:var(--muted);font-size:14px">
  {{ $orders->count() }} {{ Str::plural('order', $orders->count()) }} &middot;
  PKR {{ number_format($orders->sum('total')) }}
</p>
@endsection
