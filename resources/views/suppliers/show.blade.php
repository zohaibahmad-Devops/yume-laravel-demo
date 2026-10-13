@extends('layouts.app')
@section('title', $supplier->name)

@section('content')
<h1>{{ $supplier->name }}</h1>
<p class="lede">{{ $supplier->city }} &middot; {{ $supplier->phone }} &middot; quoted lead time {{ $supplier->lead_time_days }} days</p>

<div class="kpis">
  <div class="kpi"><b>PKR {{ number_format($onOrder) }}</b><span>On order right now</span></div>
  <div class="kpi"><b>{{ $receivedCount }}</b><span>Orders received in full</span></div>
  <div class="kpi"><b>PKR {{ number_format($receivedValue) }}</b><span>Value received to date</span></div>
</div>

<div class="panel">
  <h2>Purchase orders</h2>
  @if ($supplier->purchaseOrders->isEmpty())
    <p class="empty">Nothing has been bought from this supplier yet.</p>
  @else
    <div class="wrapper">
      <table>
        <thead>
          <tr>
            <th>Reference</th><th>Ordered</th><th>Expected</th>
            <th class="num">Lines</th><th class="num">Value</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($supplier->purchaseOrders as $po)
            @php $late = $po->daysLate(); @endphp
            <tr>
              <td><a class="ref" href="{{ route('purchasing.show', $po) }}">{{ $po->reference }}</a></td>
              <td>{{ $po->ordered_on->format('j M Y') }}</td>
              <td>
                {{ $po->expected_on?->format('j M Y') ?? '&mdash;' }}
                @if ($late !== null && $late > 0)<span class="pill flag-low">{{ $late }}d late</span>@endif
              </td>
              <td class="num">{{ $po->items_count }}</td>
              <td class="num">PKR {{ number_format((float) $po->total) }}</td>
              <td><span class="pill">{{ $po->status }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

<p>
  <a class="btn btn-primary" href="{{ route('purchasing.create', ['supplier' => $supplier->id]) }}">
    Raise an order with {{ $supplier->name }}
  </a>
</p>
@endsection
