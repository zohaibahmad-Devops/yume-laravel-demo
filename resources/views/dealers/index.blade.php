@extends('layouts.app')
@section('title', 'Dealers')

@section('content')
<h1>Dealers</h1>
<p class="lede">
  Who buys, how much is on the books, and how close each one is to the credit limit
  that was agreed with them.
</p>

<div class="panel">
  <h2>Credit position</h2>
  <div class="wrapper">
    <table>
      <thead>
        <tr>
          <th>Dealer</th>
          <th>City</th>
          <th>Phone</th>
          <th class="num">Orders</th>
          <th class="num">Outstanding</th>
          <th class="num">Credit limit</th>
          <th style="min-width:130px">Used</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($dealers as $dealer)
          @php
            $limit = (float) $dealer->credit_limit;
            $used = $dealer->outstanding();
            $pct = $limit > 0 ? min(100, round($used / $limit * 100)) : 0;
          @endphp
          <tr>
            <td><a class="ref" href="{{ route('dealers.show', $dealer) }}">{{ $dealer->name }}</a></td>
            <td>{{ $dealer->city }}</td>
            <td class="num" style="text-align:left">{{ $dealer->phone }}</td>
            <td class="num">{{ $dealer->orders_count }}</td>
            <td class="num">PKR {{ number_format($used) }}</td>
            <td class="num">PKR {{ number_format($limit) }}</td>
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

<p style="color:var(--muted);font-size:14px">
  Outstanding counts every order that has not been delivered yet. A delivered order has
  left the pipeline, so it stops consuming the dealer's limit.
</p>
@endsection
