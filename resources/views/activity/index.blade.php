@extends('layouts.app')
@section('title', 'Activity')

@section('content')
<h1>Activity</h1>
<p class="lede">
  One timeline across selling, buying and stock. Nothing on this page is calculated from a
  status column &mdash; every row is an event or a movement that was written at the moment
  it happened. That is the whole reason those tables are append-only: delete this screen and
  the data is still correct, but delete the event tables and this screen becomes a guess.
</p>

<div class="toolbar">
  <a class="{{ $kind === 'all' ? 'on' : '' }}" href="{{ route('activity.index') }}">Everything</a>
  <a class="{{ $kind === 'orders' ? 'on' : '' }}" href="{{ route('activity.index', ['kind' => 'orders']) }}">Sales orders</a>
  <a class="{{ $kind === 'purchasing' ? 'on' : '' }}" href="{{ route('activity.index', ['kind' => 'purchasing']) }}">Purchasing</a>
  <a class="{{ $kind === 'stock' ? 'on' : '' }}" href="{{ route('activity.index', ['kind' => 'stock']) }}">Stock movements</a>
</div>

<div class="panel">
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th style="min-width:130px">When</th>
          <th>Source</th>
          <th>What</th>
          <th>Detail</th>
          <th>Who</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($rows as $row)
          <tr>
            <td style="white-space:nowrap">{{ $row['at']->format('j M, H:i') }}</td>
            <td><span class="pill">{{ ucfirst($row['kind']) }}</span></td>
            <td>
              @if ($row['url'])
                <a class="ref" href="{{ $row['url'] }}">{{ $row['title'] }}</a>
              @else
                {{ $row['title'] }}
              @endif
            </td>
            <td>{{ $row['detail'] }}</td>
            <td style="color:var(--muted)">
              {{ $row['who'] ?? '' }}
              @if ($row['actor']) <span class="sku">{{ $row['actor'] }}</span> @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Nothing recorded yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<p style="color:var(--muted);font-size:14px">
  Showing the most recent 80 entries. Each one is a row in <code>order_events</code>,
  <code>purchase_order_events</code> or <code>stock_movements</code> &mdash; none of which is
  ever updated, only added to.
</p>
@endsection
