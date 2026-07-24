<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Report of Collection</title><link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}"></head>
<body class="print-page">
<article class="official-document cash-ticket-document">
    <header><img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt=""><div><p>Republic of the Philippines<br>Province of Antique<br>Municipality of Pandan</p><h2>REPORT OF COLLECTION AND CASH TICKET</h2></div></header>
    <dl>
        <dt>Report No.</dt><dd>{{ $collection->collection_number }}</dd>
        <dt>Revenue Collector</dt><dd>{{ $collection->collector?->full_name }}</dd>
        <dt>Collection Date</dt><dd>{{ $collection->collection_date->format('F d, Y') }}</dd>
        <dt>Stall Section</dt><dd>{{ $collection->stall_section }}</dd>
        <dt>Cash Ticket Range</dt><dd>{{ $collection->ticket_start ?: '—' }} – {{ $collection->ticket_end ?: '—' }}</dd>
        <dt>Tickets Collected</dt><dd>{{ number_format($collection->ticket_quantity) }}</dd>
        <dt>Total Collected</dt><dd>₱{{ number_format($collection->amount, 2) }}</dd>
        <dt>Shortage</dt><dd>₱{{ number_format($collection->shortage_amount, 2) }}</dd>
        <dt>Remarks</dt><dd>{{ $collection->remarks ?: 'Submitted for treasury verification.' }}</dd>
    </dl>
    <footer><p>Prepared by<br><strong>{{ $collection->collector?->full_name }}</strong></p><p>Received by<br><strong>Municipal Treasurer</strong></p></footer>
    <button class="button button-primary no-print" onclick="window.print()">Print Report</button>
</article>
</body>
</html>
