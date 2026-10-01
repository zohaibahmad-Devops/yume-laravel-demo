@extends('layouts.app')
@section('title', 'Stock')

@section('content')
<h1>Stock</h1>
<p class="lede">
  Every SKU with its cost, selling price, margin and reorder flag. Filter by category,
  or show only the lines that need reordering.
</p>

<div class="panel">
  <div class="filters">
    <a href="{{ route('stock.index') }}" class="{{ $category === 'all' && ! $lowOnly ? 'on' : '' }}">All</a>
    @foreach ($categories as $c)
      <a href="{{ route('stock.index', ['category' => $c]) }}" class="{{ $category === $c ? 'on' : '' }}">{{ $c }}</a>
    @endforeach
    <a href="{{ route('stock.index', ['low' => 1]) }}" class="{{ $lowOnly ? 'on' : '' }}">Needs reorder</a>
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
            <td class="sku">{{ $p->sku }}</td>
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
          <tr><td colspan="9" style="color:var(--muted)">No products match this filter.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<p style="color:var(--muted);font-size:14px">
  Showing {{ $products->count() }} {{ Str::plural('product', $products->count()) }} &middot;
  total value at cost PKR {{ number_format($products->sum(fn ($p) => (float) $p->cost * $p->stock)) }}
</p>
@endsection
