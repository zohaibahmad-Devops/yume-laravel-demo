@extends('layouts.app')
@section('title', $po->reference)

@section('content')
<h1>{{ $po->reference }}</h1>
<p class="lede">
  {{ $po->supplier->name }} &middot; {{ $po->supplier->city }} &middot;
  ordered {{ $po->ordered_on->format('j M Y') }}
  @if ($po->expected_on) &middot; expected {{ $po->expected_on->format('j M Y') }} @endif
</p>

@php $late = $po->daysLate(); @endphp
<div class="kpis">
  <div class="kpi"><b>{{ $po->status }}</b><span>Status, read back from the lines</span></div>
  <div class="kpi"><b>PKR {{ number_format((float) $po->total) }}</b><span>Order value at cost</span></div>
  <div class="kpi">
    <b class="{{ $late !== null && $late > 0 ? 'flag-low' : '' }}">
      {{ $late === null ? 'Closed' : ($late > 0 ? $late.' days late' : abs($late).' days to go') }}
    </b>
    <span>Against the expected date</span>
  </div>
</div>

<div class="panel">
  <h2>Lines</h2>
  @if ($po->isOpen())
    <p style="color:var(--muted);font-size:14px;margin-top:0">
      Enter what actually arrived. A partial delivery is normal and is accepted; receiving
      more than was ordered is refused, because a silent over-receipt leaves a stock figure
      nobody can explain later.
    </p>
  @endif

  <form method="post" action="{{ route('purchasing.receive', $po) }}">
    @csrf
    <div class="wrapper">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th class="num">Ordered</th>
            <th class="num">Received</th>
            <th class="num">Outstanding</th>
            <th class="num">Unit cost</th>
            <th class="num">Line total</th>
            @if ($po->isOpen())<th class="num" style="min-width:120px">Receive now</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($po->items as $item)
            <tr>
              <td>
                <a class="ref" href="{{ route('stock.show', $item->product) }}">{{ $item->product->name }}</a>
                <div class="sku">{{ $item->product->sku }}</div>
              </td>
              <td class="num">{{ $item->quantity }}</td>
              <td class="num">{{ $item->quantity_received }}</td>
              <td class="num {{ $item->outstanding() > 0 ? 'flag-low' : 'flag-ok' }}">{{ $item->outstanding() }}</td>
              <td class="num">PKR {{ number_format((float) $item->unit_cost) }}</td>
              <td class="num">PKR {{ number_format($item->lineTotal()) }}</td>
              @if ($po->isOpen())
                <td class="num">
                  @if ($item->outstanding() > 0)
                    <input type="number" name="receive[{{ $item->id }}]" min="0" max="{{ $item->outstanding() }}"
                           placeholder="0" style="width:90px" aria-label="Quantity received for {{ $item->product->name }}">
                  @else
                    <span class="flag-ok">complete</span>
                  @endif
                </td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if ($po->isOpen())
      <div style="margin-top:14px">
        <button class="btn btn-primary" type="submit">Receive into stock</button>
        <span style="color:var(--muted);font-size:13.5px;margin-left:10px">
          Writes one stock movement per line, inside a single transaction.
        </span>
      </div>
    @endif
  </form>
</div>

<div class="cols2">
  <div class="panel">
    <h2>History</h2>
    <ul class="trail">
      @foreach ($po->events as $event)
        <li>
          <b>{{ $event->from_status ? $event->from_status.' → '.$event->to_status : $event->to_status }}</b>
          @if ($event->detail) <span style="color:var(--muted)">{{ $event->detail }}</span> @endif
          <time>{{ $event->created_at->format('j M Y, H:i') }} &middot; {{ $event->actor }}</time>
        </li>
      @endforeach
    </ul>
  </div>

  <div class="panel">
    <h2>Stock written by this order</h2>
    @if ($po->movements->isEmpty())
      <p class="empty">Nothing received yet, so this order has not touched stock.</p>
    @else
      <div class="wrapper">
        <table>
          <thead><tr><th>Product</th><th class="num">Change</th><th class="num">Balance after</th><th>When</th></tr></thead>
          <tbody>
            @foreach ($po->movements->sortByDesc('id') as $m)
              <tr>
                <td>{{ $m->product->name }}</td>
                <td class="num plus">+{{ $m->delta }}</td>
                <td class="num">{{ $m->balance_after }}</td>
                <td>{{ $m->created_at->format('j M, H:i') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
