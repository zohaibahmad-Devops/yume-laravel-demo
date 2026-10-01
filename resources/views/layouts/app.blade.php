<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Inventory') — Mehran Distributors</title>
<meta name="description" content="A working Laravel demonstration: stock, dealers and an order pipeline for a distribution business.">
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;500;600;700&display=swap">
<style>
:root{
  --navy:#112d4f; --navy-2:#0c2240; --orange:#f16523; --orange-2:#ff7c3c;
  --bg:#ffffff; --surface:#f3f6fa; --line:#e6e6e6; --muted:#535455;
  --ok:#16a34a; --warn:#d97706; --bad:#dc2626;
  --sans:"Open Sans",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
  --display:"Montserrat","Open Sans",-apple-system,Arial,sans-serif;
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--navy);font:16px/1.6 var(--sans);-webkit-font-smoothing:antialiased}
a{color:inherit}
h1,h2,h3{font-family:var(--display);font-weight:700;letter-spacing:-.025em;line-height:1.15;margin:0 0 .5em}
h1{font-size:clamp(26px,3.6vw,36px)} h2{font-size:20px} h3{font-size:16px}
.wrap{max-width:1140px;margin:0 auto;padding-left:20px;padding-right:20px}

.ribbon{background:var(--navy);color:#cfe0f2;font-size:12.5px}
.ribbon .wrap{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding-top:7px;padding-bottom:7px}
.ribbon b{color:#fff;font-weight:600}
.ribbon a{color:#ffc9a8;text-decoration:none;font-weight:600;margin-left:auto}
.ribbon a:hover{color:#fff}
@media(max-width:760px){.ribbon a{margin-left:0;width:100%}}

header{border-bottom:1px solid var(--line);background:#fff;position:sticky;top:0;z-index:10}
header .wrap{display:flex;align-items:center;gap:26px;padding-top:14px;padding-bottom:14px}
.brand{display:flex;align-items:center;gap:10px;font-family:var(--display);font-weight:700;font-size:19px;
  letter-spacing:-.02em;text-decoration:none;white-space:nowrap}
.brand .tile{width:30px;height:30px;border-radius:8px;background:var(--orange);display:grid;place-items:center;
  color:#fff;font-size:15px;flex:none}
nav{display:flex;gap:22px;font-size:15px;font-weight:600}
nav a{text-decoration:none;color:var(--muted);padding:3px 0;border-bottom:2px solid transparent}
nav a:hover{color:var(--navy)}
nav a.on{color:var(--navy);border-bottom-color:var(--orange)}

main{padding:34px 0 60px}
.lede{color:var(--muted);max-width:68ch;margin:-4px 0 26px}

.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:30px}
.kpi{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:18px 20px}
.kpi .label{font-family:var(--display);font-size:11.5px;font-weight:600;letter-spacing:.12em;
  text-transform:uppercase;color:var(--muted);margin-bottom:6px}
.kpi .value{font-family:var(--display);font-size:26px;font-weight:700;letter-spacing:-.03em}
.kpi .sub{font-size:13px;color:var(--muted);margin-top:2px}

.panel{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:26px}
.panel>h2{margin:0;padding:16px 20px;border-bottom:1px solid var(--line);font-size:16px}

table{width:100%;border-collapse:collapse;font-size:14.5px}
th{font-family:var(--display);font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;
  color:var(--muted);text-align:left;padding:11px 20px;border-bottom:1px solid var(--line);white-space:nowrap}
td{padding:12px 20px;border-bottom:1px solid var(--line)}
tr:last-child td{border-bottom:0}
tbody tr:hover{background:var(--surface)}
.num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.sku{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;color:var(--muted)}
.wrapper{overflow-x:auto}

.pill{display:inline-block;font-family:var(--display);font-size:11px;font-weight:600;letter-spacing:.08em;
  padding:4px 10px;border-radius:999px;white-space:nowrap}
.pill-New{background:#eef2ff;color:#4338ca}
.pill-Confirmed{background:#fdefe7;color:#b0470f}
.pill-Packed{background:#fef3c7;color:#92400e}
.pill-Dispatched{background:#dbeafe;color:#1e40af}
.pill-Delivered{background:#dcfce7;color:#166534}
.flag-low{background:#fee2e2;color:#991b1b}
.flag-ok{background:#f3f6fa;color:var(--muted)}

.filters{display:flex;flex-wrap:wrap;gap:8px;padding:14px 20px;border-bottom:1px solid var(--line);background:var(--surface)}
.filters a{font-family:var(--display);font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;
  padding:7px 14px;border-radius:999px;border:1px solid var(--line);background:#fff;color:var(--muted);text-decoration:none}
.filters a:hover{border-color:var(--orange);color:var(--orange)}
.filters a.on{background:var(--orange);border-color:var(--orange);color:#fff}

.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:999px;border:1px solid transparent;
  font:600 14px var(--sans);cursor:pointer;text-decoration:none;white-space:nowrap}
.btn-primary{background:var(--orange);color:#fff}
.btn-primary:hover{background:var(--orange-2)}
.btn-ghost{border-color:var(--line);color:var(--navy);background:#fff}
.btn-ghost:hover{border-color:var(--navy)}

.note{background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 18px;border-radius:12px;margin-bottom:20px;font-size:14.5px}
.flow{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:4px 0 0}
.flow span{font-family:var(--display);font-size:11.5px;font-weight:600;letter-spacing:.06em;padding:5px 12px;
  border-radius:999px;background:var(--surface);color:var(--muted)}
.flow span.done{background:var(--navy);color:#fff}
.flow span.here{background:var(--orange);color:#fff}
.flow i{color:var(--line);font-style:normal}

footer{border-top:1px solid var(--line);padding:26px 0;color:var(--muted);font-size:14px}
footer a{color:var(--orange);text-decoration:none;font-weight:600}
</style>
</head>
<body>

<div class="ribbon">
  <div class="wrap">
    <b>Demonstration</b>
    <span>&middot;</span>
    <span>Laravel 11 &middot; SQLite &middot; sample data, not a real business</span>
    <a href="https://studioyume.pages.dev" target="_blank" rel="noopener">Built by Zohaib Ahmad &rarr;</a>
  </div>
</div>

<header>
  <div class="wrap">
    <a class="brand" href="{{ route('dashboard') }}">
      <span class="tile">M</span> Mehran Distributors
    </a>
    <nav>
      <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}">Dashboard</a>
      <a href="{{ route('stock.index') }}" class="{{ request()->routeIs('stock.*') ? 'on' : '' }}">Stock</a>
      <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'on' : '' }}">Orders</a>
    </nav>
  </div>
</header>

<main>
  <div class="wrap">
    @if (session('note'))
      <p class="note">{{ session('note') }}</p>
    @endif
    @yield('content')
  </div>
</main>

<footer>
  <div class="wrap">
    A working Laravel application, not a mockup &mdash; every figure on these pages is read from the database.
    <a href="https://studioyume.pages.dev">studioyume.pages.dev</a>
  </div>
</footer>

</body>
</html>
