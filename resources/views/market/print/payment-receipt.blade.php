<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $payment->reference_number }} Receipt</title>
    <link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}">
</head>
<body class="print-page">
    <main class="official-document receipt-document">
        <header>
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
            <div>Republic of the Philippines<br>Province of Antique<br><strong>Municipality of Pandan</strong></div>
        </header>
        <h1>STALL RENTAL OFFICIAL RECEIPT</h1>
        <dl>
            <dt>Reference</dt><dd>{{ $payment->reference_number }}</dd>
            <dt>O.R. Number</dt><dd>{{ $payment->or_number ?? 'Pending assignment' }}</dd>
            <dt>Tenant</dt><dd>{{ $payment->tenant?->full_name }}</dd>
            <dt>Period</dt><dd>{{ $payment->period_month?->format('F Y') ?? '-' }}</dd>
            <dt>Amount</dt><dd>₱{{ number_format($payment->amount, 2) }}</dd>
            <dt>Payment method</dt><dd>{{ $payment->payment_method }}</dd>
            <dt>Date paid</dt><dd>{{ $payment->paid_at?->format('F d, Y g:i A') ?? 'Not yet paid' }}</dd>
            <dt>Status</dt><dd>{{ $payment->status }}</dd>
        </dl>
        <footer><p>Market Treasurer</p><p>Tenant Signature</p></footer>
        <button onclick="window.print()" class="button button-primary no-print">Print / Save PDF</button>
    </main>
</body>
</html>
