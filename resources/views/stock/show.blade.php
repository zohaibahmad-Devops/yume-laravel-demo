@extends('layouts.app')
@section('title', $product->sku)

@section('content')
<p style="margin:0 0 10px"><a href="{{ route('stock.index') }}" style="color:var(--muted);font-size:14px;text-decoration:none">&larr; All stock</a></p>

<h1>{{ $product->name }}</h1>
<p class="lede" style="margin-bottom:22px">
  <span class="sku">{{ $product->sku }}</span> &middot; {{ $product->category }} &middot;
  sold by the {{ $product->unit }}
</p>

<div class="kpis">
  <div class="kpi">
    <div class="label">On hand</div>
    <div class="value">{{ number_format($product->stock) }} {{ $product->unit }}</div>
    <div class="sub">
      @if ($product->isLow())
        {{ number_format($product->reorder_level - $product->stock) }} below reorder level
      @else
        {{ number_format($product->stock - $product->reorder_level) }} above reorder level
      @endif
    </div>
  </div>
  <div class="kpi">
    <div class="label">Value at cost</div>
    <div class="value">PKR {{ number_format((float) $product->cost * $product->stock) }}</div>
    <div class="sub">{{ number_format($product->cost) }} per {{ $product->unit }}</div>
  </div>
  <div class="kpi">
    <div class="label">Margin</div>
    <div class="value">{{ number_format($product->marginPercent(), 1) }}%</div>
    <div class="sub">sells at {{ number_format($product->price) }}</div>
  </div>
  <div class="kpi">
    <div class="label">Free to sell</div>
    <div class="value">{{ number_format($product->free()) }}</div>
    <div class="sub">{{ number_format($product->committed()) }} committed to open orders</div>
  </div>
  <div class="kpi">
    <div class="label">On order</div>
    <div class="value">{{ number_format($onOrder) }}</div>
    <div class="sub">
      @if ($onOrder > 0)
        bought and not yet received
      @else
        nothing on the way
      @endif
    </div>
  </div>
  <div class="kpi">
    <div class="label">Status</div>
    <div class="value">
      @if ($product->isLow())
        <span class="pill flag-low" style="font-size:13px;padding:6px 14px">REORDER</span>
      @else
        <span class="pill flag-ok" style="font-size:13px;padding:6px 14px">OK</span>
      @endif
    </div>
    <div class="sub">reorder at {{ number_format($product->reorder_level) }}</div>
  </div>
</div>

<div class="panel">
  <h2>Count and adjust</h2>
  <p style="color:var(--muted);font-size:14px;margin-top:0">
    When the shelf disagrees with the screen, the fix is not to type the new number over the
    old one &mdash; that destroys the only thing that makes a stock figure trustworthy. Enter
    the count you actually made and the system works out the difference, writes it to the
    ledger below, and requires a reason. An unexplained correction is the one thing this refuses.
  </p>
  <form method="post" action="{{ route('stock.adjust', $product) }}">
    @csrf
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
      <label style="display:block">
        <span style="display:block;font-size:13px;color:var(--muted);margin-bottom:4px">Counted on the shelf</span>
        <input type="number" name="counted" min="0" required style="width:150px"
               value="{{ $product->stock }}" aria-label="Counted quantity">
      </label>
      <label style="display:block">
        <span style="display:block;font-size:13px;color:var(--muted);margin-bottom:4px">Reason</span>
        <select name="reason" required style="min-width:220px" aria-label="Reason for the adjustment">
          @foreach ($reasons as $r)
            <option value="{{ $r }}">{{ $r }}</option>
          @endforeach
        </select>
      </label>
      <label style="display:block;flex:1;min-width:200px">
        <span style="display:block;font-size:13px;color:var(--muted);margin-bottom:4px">Note (optional)</span>
        <input type="text" name="note" style="width:100%" placeholder="Bay 3, two lengths cracked" aria-label="Note">
      </label>
      <button class="btn btn-primary" type="submit">Record the adjustment</button>
    </div>
  </form>
</div>

<div class="panel">
  <h2>
    Stock ledger
    <span class="side" style="color:var(--muted);font-weight:400">
      {{ $product->movements->count() }} {{ Str::plural('movement', $product->movements->count()) }}
    </span>
  </h2>
  @if ($product->movements->isEmpty())
    <p class="empty">No movements recorded.</p>
  @else
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>When</th>
          <th>Source</th>
          <th>Reason</th>
          <th>Document</th>
          <th class="num">Change</th>
          <th class="num">Balance after</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($product->movements as $m)
          <tr>
            <td class="num" style="text-align:left">{{ $m->created_at->format('d M Y, H:i') }}</td>
            <td><span class="pill">{{ $m->source() }}</span></td>
            <td>{{ $m->reason }}</td>
            <td>
              @if ($m->order)
                <a class="ref" href="{{ route('orders.show', $m->order) }}">{{ $m->order->reference }}</a>
              @elseif ($m->purchaseOrder)
                <a class="ref" href="{{ route('purchasing.show', $m->purchaseOrder) }}">{{ $m->purchaseOrder->reference }}</a>
              @else
                <span style="color:var(--muted)">&mdash;</span>
              @endif
            </td>
            <td class="num {{ $m->delta < 0 ? 'minus' : 'plus' }}">
              {{ $m->delta > 0 ? '+' : '' }}{{ number_format($m->delta) }}
            </td>
            <td class="num">{{ number_format($m->balance_after) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

<p style="color:var(--muted);font-size:14px">
  The ledger is append-only. Nothing edits a quantity directly &mdash; the figure above is
  whatever the last movement left behind, which is why it can always be explained.
</p>
@endsection
