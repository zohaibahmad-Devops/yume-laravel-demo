@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<h1>Reports</h1>
<p class="lede">
  Aggregates over the same records the rest of the application shows. The chart is
  plain SVG generated server-side &mdash; no charting library, nothing to load.
</p>

<div class="kpis">
  <div class="kpi">
    <div class="label">Order value booked</div>
    <div class="value">PKR {{ number_format($totalValue) }}</div>
    <div class="sub">all orders, all stages</div>
  </div>
  <div class="kpi">
    <div class="label">Stock at cost</div>
    <div class="value">PKR {{ number_format($stockValue) }}</div>
    <div class="sub">what is sitting in the warehouse</div>
  </div>
  <div class="kpi">
    <div class="label">Active dealers</div>
    <div class="value">{{ $dealerCount }}</div>
    <div class="sub">{{ $byDealer->count() }} have ordered</div>
  </div>
  <div class="kpi">
    <div class="label">Blended margin</div>
    @php
      $rev = $byCategory->sum('revenue');
      $cost = $byCategory->sum('cost');
      $blended = $rev > 0 ? round(($rev - $cost) / $rev * 100, 1) : 0;
    @endphp
    <div class="value">{{ number_format($blended, 1) }}%</div>
    <div class="sub">across everything sold</div>
  </div>
</div>

<div class="panel">
  <h2>Order value by month</h2>
  @if ($byMonth->isEmpty())
    <p class="empty">No orders yet.</p>
  @else
    @php
      $w = 720; $h = 230;
      $padL = 8; $padR = 8; $padT = 26; $padB = 34;
      $plotW = $w - $padL - $padR;
      $plotH = $h - $padT - $padB;
      $n = $byMonth->count();
      $slot = $plotW / max($n, 1);
      $barW = min(78, $slot * 0.52);
      $max = max($byMonth->max('value'), 1);
    @endphp
    <div class="chart">
      <svg viewBox="0 0 {{ $w }} {{ $h }}" role="img"
           aria-label="Bar chart of order value by month, highest {{ number_format($max) }} rupees">
        @for ($g = 0; $g <= 4; $g++)
          @php $y = $padT + $plotH - ($plotH * $g / 4); @endphp
          <line class="grid" x1="{{ $padL }}" y1="{{ round($y, 1) }}" x2="{{ $w - $padR }}" y2="{{ round($y, 1) }}"
                opacity="{{ $g === 0 ? 1 : 0.5 }}"/>
        @endfor

        @foreach ($byMonth as $i => $row)
          @php
            $cx = $padL + $slot * $i + $slot / 2;
            $bh = $plotH * ($row['value'] / $max);
            $y = $padT + $plotH - $bh;
            $last = $i === $n - 1;
          @endphp
          <rect class="bar {{ $last ? '' : 'dim' }}"
                x="{{ round($cx - $barW / 2, 1) }}" y="{{ round($y, 1) }}"
                width="{{ round($barW, 1) }}" height="{{ round(max($bh, 2), 1) }}" rx="4"/>
          <text class="gvalue" x="{{ round($cx, 1) }}" y="{{ round($y - 8, 1) }}" text-anchor="middle">
            {{ number_format($row['value'] / 1000, 0) }}k
          </text>
          <text class="glabel" x="{{ round($cx, 1) }}" y="{{ $padT + $plotH + 18 }}" text-anchor="middle">
            {{ strtoupper($row['label']) }}
          </text>
          <text class="glabel" x="{{ round($cx, 1) }}" y="{{ $padT + $plotH + 30 }}" text-anchor="middle" opacity=".75">
            {{ $row['orders'] }} {{ Str::plural('order', $row['orders']) }}
          </text>
        @endforeach
      </svg>
    </div>
  @endif
</div>

<div class="cols2">
  <div class="panel">
    <h2>Margin by category</h2>
    <div class="wrapper">
      <table>
        <thead>
          <tr>
            <th>Category</th>
            <th class="num">Units</th>
            <th class="num">Revenue</th>
            <th class="num">Margin</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($byCategory as $row)
            <tr>
              <td>{{ $row['category'] }}</td>
              <td class="num">{{ number_format($row['units']) }}</td>
              <td class="num">{{ number_format($row['revenue']) }}</td>
              <td class="num">
                {{ number_format($row['margin'], 1) }}%
                <span class="meter" style="margin-top:4px"><i style="width:{{ min(100, $row['margin'] * 2) }}%"></i></span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <p class="empty" style="padding-top:14px;font-size:13px">
      Margin is revenue less cost at the price each line actually sold for, not the
      current list price.
    </p>
  </div>

  <div class="panel">
    <h2>Dealers by value</h2>
    <div class="wrapper">
      <table>
        <thead>
          <tr>
            <th>Dealer</th>
            <th class="num">Orders</th>
            <th class="num">Lifetime</th>
            <th class="num">Open</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($byDealer as $row)
            <tr>
              <td><a class="ref" href="{{ route('dealers.show', $row['dealer']) }}">{{ $row['dealer']->name }}</a></td>
              <td class="num">{{ $row['orders'] }}</td>
              <td class="num">{{ number_format($row['value']) }}</td>
              <td class="num">{{ number_format($row['open']) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <p class="empty" style="padding-top:14px;font-size:13px">
      "Open" is everything not yet delivered &mdash; the figure that counts against the
      dealer's credit limit.
    </p>
  </div>
</div>
@endsection
