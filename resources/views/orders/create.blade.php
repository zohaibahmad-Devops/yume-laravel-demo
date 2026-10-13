@extends('layouts.app')
@section('title', 'New order')

@section('content')
<h1>New order</h1>
<p class="lede">
  The question this form answers most often is not what the dealer wants, it is whether
  they can have it. So the remaining credit is shown against every dealer before anything
  is chosen, and an order that would take them past the agreed limit is refused rather
  than queued for somebody to notice later.
</p>

<form method="post" action="{{ route('orders.store') }}">
  @csrf

  <div class="panel">
    <h2>Dealer</h2>
    <div class="wrapper">
      <table>
        <thead>
          <tr>
            <th style="width:46px"></th>
            <th>Dealer</th><th>City</th>
            <th class="num">Outstanding</th><th class="num">Credit limit</th>
            <th class="num">Credit left</th><th style="min-width:120px">Used</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($dealers as $row)
            @php
              $d = $row['model'];
              $limit = (float) $d->credit_limit;
              $pct = $limit > 0 ? min(100, round($row['outstanding'] / $limit * 100)) : 0;
            @endphp
            <tr>
              <td>
                <input type="radio" name="dealer_id" value="{{ $d->id }}" required
                       id="dealer{{ $d->id }}" @checked(old('dealer_id', $dealerId) == $d->id)>
              </td>
              <td><label for="dealer{{ $d->id }}">{{ $d->name }}</label></td>
              <td>{{ $d->city }}</td>
              <td class="num">PKR {{ number_format($row['outstanding']) }}</td>
              <td class="num">PKR {{ number_format($limit) }}</td>
              <td class="num {{ $row['headroom'] <= 0 ? 'flag-low' : 'flag-ok' }}">
                PKR {{ number_format(max(0, $row['headroom'])) }}
              </td>
              <td>
                <span class="meter"><i style="width:{{ $pct }}%"></i></span>
                <span style="font-size:12.5px;color:var(--muted)">{{ $pct }}%</span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <h2>Lines</h2>
    <p style="color:var(--muted);font-size:14px;margin-top:0">
      <b>Free</b> is what is left after stock already promised to orders that have not been
      dispatched. It is the only figure a new order should ever be checked against &mdash;
      the quantity on the shelf is not available if somebody else has already been promised it.
    </p>
    <div class="wrapper">
      <table>
        <thead>
          <tr>
            <th>Product</th><th>Category</th>
            <th class="num">In stock</th><th class="num">Committed</th><th class="num">Free</th>
            <th class="num">Unit price</th><th class="num" style="min-width:110px">Quantity</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($products as $i => $p)
            @php $free = $p->free(); @endphp
            <tr>
              <td>
                {{ $p->name }}<div class="sku">{{ $p->sku }}</div>
                <input type="hidden" name="lines[{{ $i }}][product_id]" value="{{ $p->id }}">
              </td>
              <td>{{ $p->category }}</td>
              <td class="num">{{ $p->stock }}</td>
              <td class="num">{{ $p->committed() }}</td>
              <td class="num {{ $free <= 0 ? 'flag-low' : 'flag-ok' }}">{{ $free }}</td>
              <td class="num">PKR {{ number_format((float) $p->price) }}</td>
              <td class="num">
                <input type="number" name="lines[{{ $i }}][quantity]" min="0" placeholder="0" style="width:90px"
                       aria-label="Quantity of {{ $p->name }}">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <p>
    <button class="btn btn-primary" type="submit">Place the order</button>
    <a class="btn btn-ghost" href="{{ route('orders.index') }}">Cancel</a>
  </p>
  <p style="color:var(--muted);font-size:14px">
    Placing an order does not move stock. Nothing leaves the warehouse until the order is
    dispatched, which is a separate step with its own check.
  </p>
</form>
@endsection
