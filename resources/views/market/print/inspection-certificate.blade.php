<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $inspection->certificate_number }}</title>
    <link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}">
</head>
<body class="print-page">
    <main class="official-document">
        <header>
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
            <div>Republic of the Philippines<br>Department of Health<br><strong>Municipality of Pandan</strong></div>
        </header>
        <h1>SLAUGHTERED LIVESTOCK INSPECTION CERTIFICATE</h1>
        <p class="certificate-number">{{ $inspection->certificate_number }}</p>
        <p>This certifies that the livestock described below was inspected by the Pandan Public Market sanitary inspection office.</p>
        <dl>
            <dt>Owner</dt><dd>{{ $inspection->owner_name }}</dd>
            <dt>Livestock</dt><dd>{{ $inspection->livestock_type }}</dd>
            <dt>Count</dt><dd>{{ $inspection->animal_count }}</dd>
            <dt>Breed / Sex</dt><dd>{{ $inspection->breed }} / {{ $inspection->sex }}</dd>
            <dt>Live / Carcass weight</dt><dd>{{ $inspection->live_weight }} kg / {{ $inspection->carcass_weight }} kg</dd>
            <dt>Inspection result</dt><dd>{{ $inspection->inspection_result }}</dd>
            <dt>Findings</dt><dd>{{ $inspection->findings }}</dd>
            <dt>Date inspected</dt><dd>{{ $inspection->inspected_at?->format('F d, Y g:i A') }}</dd>
            <dt>Inspector</dt><dd>{{ $inspection->inspector?->full_name }}</dd>
        </dl>
        <footer><p>Rural Sanitary Inspector</p><p>Market Administrator</p></footer>
        <button onclick="window.print()" class="button button-primary no-print">Print / Save PDF</button>
    </main>
</body>
</html>
