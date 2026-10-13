@extends('layouts.app')
@section('title', 'Suppliers')

@section('content')
<h1>Suppliers</h1>
<p class="lede">
  Who the stock is bought from, what is currently on order with each of them, and how long
  they say they take. The quoted lead time is what the expected date on every purchase
  order is calculated from.
</p>

<div class="kpis">
  <div class="kpi"><b>{{ $suppliers->count() }}</b><span>Suppliers on the book</span></div>
  <div class="kpi"><b>PKR {{ number_format($onOrder) }}</b><span>Committed and not yet received</span></div>
</div>

<div class="panel">
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>Supplier</th>
          <th>City</th>
          <th>Phone</th>
          <th class="num">Lead time</th>
          <th class="num">Purchase orders</th>
          <th class="num">On order now</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($suppliers as $s)
          <tr>
            <td><a class="ref" href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
            <td>{{ $s->city }}</td>
            <td class="num" style="text-align:left">{{ $s->phone }}</td>
            <td class="num">{{ $s->lead_time_days }} days</td>
            <td class="num">{{ $s->purchase_orders_count }}</td>
            <td class="num">PKR {{ number_format($s->onOrderValue()) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<p style="color:var(--muted);font-size:14px">
  &ldquo;On order now&rdquo; counts only purchase orders that are Ordered or Part received.
  Once everything has arrived the order stops being a commitment and becomes history.
</p>
@endsection
