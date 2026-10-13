@extends('layouts.app')
@section('title', 'Purchasing')

@section('content')
<h1>Purchasing</h1>
<p class="lede">
  Where stock comes from. A purchase order changes nothing on the shelf until it is
  received &mdash; ordering something does not make it arrive, and a system that adds
  stock at order time will promise a dealer a pipe that is still on a truck.
</p>

<div class="kpis">
  <div class="kpi">
    <b>PKR {{ number_format($openValue) }}</b>
    <span>On order, not yet received</span>
  </div>
  <div class="kpi">
    <b>{{ $openCount }}</b>
    <span>Open purchase orders</span>
  </div>
  <div class="kpi">
    <b class="{{ $lateCount > 0 ? 'flag-low' : '' }}">{{ $lateCount }}</b>
    <span>Past their expected date</span>
  </div>
</div>

<div class="toolbar">
  <form class="search" method="get" action="{{ route('purchasing.index') }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Reference or supplier" aria-label="Search purchase orders">
    @if ($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
    <button class="btn btn-sm btn-ghost" type="submit">Search</button>
  </form>
  <a class="{{ $status === 'all' ? 'on' : '' }}" href="{{ route('purchasing.index', ['q' => $q]) }}">All</a>
  @foreach ($statuses as $s)
    <a class="{{ $status === $s ? 'on' : '' }}" href="{{ route('purchasing.index', ['status' => $s, 'q' => $q]) }}">{{ $s }}</a>
  @endforeach
  <a class="btn btn-primary btn-sm" style="margin-left:auto" href="{{ route('purchasing.create') }}">Raise a purchase order</a>
</div>

<div class="panel">
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>Reference</th>
          <th>Supplier</th>
          <th>Ordered</th>
          <th>Expected</th>
          <th class="num">Lines</th>
          <th class="num">Value</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($orders as $po)
          @php $late = $po->daysLate(); @endphp
          <tr>
            <td><a class="ref" href="{{ route('purchasing.show', $po) }}">{{ $po->reference }}</a></td>
            <td><a class="ref" href="{{ route('suppliers.show', $po->supplier) }}">{{ $po->supplier->name }}</a></td>
            <td>{{ $po->ordered_on->format('j M Y') }}</td>
            <td>
              {{ $po->expected_on?->format('j M Y') ?? '&mdash;' }}
              @if ($late !== null && $late > 0)
                <span class="pill flag-low">{{ $late }}d late</span>
              @endif
            </td>
            <td class="num">{{ $po->items_count }}</td>
            <td class="num">PKR {{ number_format((float) $po->total) }}</td>
            <td><span class="pill">{{ $po->status }}</span></td>
          </tr>
        @empty
          <tr><td colspan="7" class="empty">No purchase orders match that.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@include('partials.pagination', ['paginator' => $orders])
@endsection
