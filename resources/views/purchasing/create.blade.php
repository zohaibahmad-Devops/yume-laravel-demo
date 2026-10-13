@extends('layouts.app')
@section('title', 'Raise a purchase order')

@section('content')
<h1>Raise a purchase order</h1>
<p class="lede">
  The screen opens on what is actually short rather than on a blank form, because that
  is the list a buyer is working from. The expected date comes from the supplier's own
  lead time, so the &ldquo;late&rdquo; column elsewhere means the same thing every time.
</p>

<form method="post" action="{{ route('purchasing.store') }}">
  @csrf

  <div class="panel">
    <h2>Supplier</h2>
    <select name="supplier_id" required aria-label="Supplier" style="min-width:280px">
      <option value="">Choose a supplier&hellip;</option>
      @foreach ($suppliers as $s)
        <option value="{{ $s->id }}" @selected(old('supplier_id', $supplierId) == $s->id)>
          {{ $s->name }} &mdash; {{ $s->city }} ({{ $s->lead_time_days }} day lead time)
        </option>
      @endforeach
    </select>
  </div>

  @if ($lowProducts->isNotEmpty())
    <div class="panel">
      <h2>At or below reorder level</h2>
      <p style="color:var(--muted);font-size:14px;margin-top:0">
        These are the lines the system would flag to a buyer this morning.
      </p>
      <div class="wrapper">
        <table>
          <thead>
            <tr>
              <th>Product</th><th>Category</th>
              <th class="num">In stock</th><th class="num">Reorder at</th>
              <th class="num">Unit cost</th><th class="num" style="min-width:110px">Order</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($lowProducts as $i => $p)
              <tr>
                <td>
                  {{ $p->name }}<div class="sku">{{ $p->sku }}</div>
                  <input type="hidden" name="lines[{{ $i }}][product_id]" value="{{ $p->id }}">
                </td>
                <td>{{ $p->category }}</td>
                <td class="num flag-low">{{ $p->stock }}</td>
                <td class="num">{{ $p->reorder_level }}</td>
                <td class="num">PKR {{ number_format((float) $p->cost) }}</td>
                <td class="num">
                  <input type="number" name="lines[{{ $i }}][quantity]" min="0" style="width:90px"
                         value="{{ max(0, $p->reorder_level * 2 - $p->stock) }}"
                         aria-label="Quantity to order of {{ $p->name }}">
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p style="color:var(--muted);font-size:13.5px">
        The suggested quantity brings the line back to twice its reorder level. Change it,
        or set it to zero to leave the line out.
      </p>
    </div>
  @endif

  <div class="panel">
    <h2>Anything else</h2>
    <div class="wrapper">
      <table>
        <thead>
          <tr><th>Product</th><th>Category</th><th class="num">In stock</th><th class="num">Unit cost</th><th class="num" style="min-width:110px">Order</th></tr>
        </thead>
        <tbody>
          @foreach ($products as $j => $p)
            @continue($lowProducts->contains('id', $p->id))
            @php $k = $lowProducts->count() + $j; @endphp
            <tr>
              <td>
                {{ $p->name }}<div class="sku">{{ $p->sku }}</div>
                <input type="hidden" name="lines[{{ $k }}][product_id]" value="{{ $p->id }}">
              </td>
              <td>{{ $p->category }}</td>
              <td class="num">{{ $p->stock }}</td>
              <td class="num">PKR {{ number_format((float) $p->cost) }}</td>
              <td class="num">
                <input type="number" name="lines[{{ $k }}][quantity]" min="0" placeholder="0" style="width:90px"
                       aria-label="Quantity to order of {{ $p->name }}">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <p>
    <button class="btn btn-primary" type="submit">Raise the order</button>
    <a class="btn btn-ghost" href="{{ route('purchasing.index') }}">Cancel</a>
  </p>
</form>
@endsection
