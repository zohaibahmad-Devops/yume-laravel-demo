@extends('layouts.app')
@section('title', 'Orders')

@section('content')
<div class="head">
  <h1>Orders</h1>
  <a class="btn btn-ghost btn-sm" href="{{ route('orders.export') }}">Download CSV</a>
</div>
<p class="lede">
  Dealer orders through the five-stage pipeline. Open any order to see its lines, its
  full history, and to move it to the next stage.
</p>

<div class="panel">
  <div class="toolbar">
    <a href="{{ route('orders.index') }}" class="chip {{ $status === 'all' && $q === '' ? 'on' : '' }}">All</a>
    @foreach ($statuses as $s)
      <a href="{{ route('orders.index', ['status' => $s]) }}" class="chip {{ $status === $s ? 'on' : '' }}">{{ $s }}</a>
    @endforeach

    <form class="search" method="GET" action="{{ route('orders.index') }}">
      @if ($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
      <input type="search" name="q" value="{{ $q }}" placeholder="Search reference or dealer" aria-label="Search orders">
      <button class="btn btn-primary btn-sm" type="submit">Search</button>
    </form>
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
            <td><a class="ref" href="{{ route('orders.show', $order) }}">{{ $order->reference }}</a></td>
            <td><a href="{{ route('dealers.show', $order->dealer) }}" style="text-decoration:none">{{ $order->dealer->name }}</a></td>
            <td>{{ $order->dealer->city }}</td>
            <td>{{ $order->placed_on->format('d M Y') }}</td>
            <td class="num">{{ $order->items_count }}</td>
            <td><span class="pill pill-{{ $order->status }}">{{ strtoupper($order->status) }}</span></td>
            <td class="num">PKR {{ number_format($order->total) }}</td>
          </tr>
        @empty
          <tr><td colspan="7" style="color:var(--muted)">No orders match.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @include('partials.pagination', ['paginator' => $orders])
</div>

<p style="color:var(--muted);font-size:14px">
  {{ number_format($orders->total()) }} {{ Str::plural('order', $orders->total()) }} matched
</p>
@endsection
