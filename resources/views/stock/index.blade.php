@extends('layouts.app')
@section('title', 'Stock')

@section('content')
<div class="head">
  <h1>Stock</h1>
  <a class="btn btn-ghost btn-sm" href="{{ route('stock.export') }}">Download CSV</a>
</div>
<p class="lede">
  Every SKU with its cost, selling price, margin and reorder flag. Open any line to see
  the movements that explain the quantity on hand.
</p>

<div class="panel">
  <div class="toolbar">
    <a href="{{ route('stock.index') }}" class="chip {{ $category === 'all' && ! $lowOnly && $q === '' ? 'on' : '' }}">All</a>
    @foreach ($categories as $c)
      <a href="{{ route('stock.index', ['category' => $c]) }}" class="chip {{ $category === $c ? 'on' : '' }}">{{ $c }}</a>
    @endforeach
    <a href="{{ route('stock.index', ['low' => 1]) }}" class="chip {{ $lowOnly ? 'on' : '' }}">Needs reorder</a>

    <form class="search" method="GET" action="{{ route('stock.index') }}">
      @if ($category !== 'all')<input type="hidden" name="category" value="{{ $category }}">@endif
      @if ($lowOnly)<input type="hidden" name="low" value="1">@endif
      <input type="search" name="q" value="{{ $q }}" placeholder="Search name or SKU" aria-label="Search stock">
      <button class="btn btn-primary btn-sm" type="submit">Search</button>
    </form>
  </div>

  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Product</th>
          <th>Category</th>
          <th class="num">Cost</th>
          <th class="num">Price</th>
          <th class="num">Margin</th>
          <th class="num">In stock</th>
          <th class="num">Value</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($products as $p)
          <tr>
            <td><a class="ref" href="{{ route('stock.show', $p) }}">{{ $p->sku }}</a></td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->category }}</td>
            <td class="num">{{ number_format($p->cost) }}</td>
            <td class="num">{{ number_format($p->price) }}</td>
            <td class="num">{{ number_format($p->marginPercent(), 1) }}%</td>
            <td class="num">{{ number_format($p->stock) }} {{ $p->unit }}</td>
            <td class="num">{{ number_format((float) $p->cost * $p->stock) }}</td>
            <td>
              @if ($p->isLow())
                <span class="pill flag-low">REORDER</span>
              @else
                <span class="pill flag-ok">OK</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="9" style="color:var(--muted)">Nothing matches that filter.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @include('partials.pagination', ['paginator' => $products])
</div>

<p style="color:var(--muted);font-size:14px">
  {{ number_format($matchCount) }} {{ Str::plural('product', $matchCount) }} matched
  &middot; total value at cost PKR {{ number_format($totalValue) }}
</p>
@endsection
