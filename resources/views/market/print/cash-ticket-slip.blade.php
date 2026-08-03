<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Cash Ticket Requisition and Issue Slip</title><link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}"></head>
<body class="print-page">
<article class="official-document cash-ticket-document">
    <header><img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt=""><div><p>Republic of the Philippines<br>Province of Antique<br>Municipality of Pandan</p><h2>REQUISITION AND ISSUE SLIP</h2></div></header>
    <dl>
        <dt>Assignment No.</dt><dd>{{ $assignment->assignment_number }}</dd>
        <dt>Collector</dt><dd>{{ $assignment->collector?->full_name }}</dd>
        <dt>Office</dt><dd>Pandan Public Market</dd>
        <dt>Date</dt><dd>{{ $assignment->assigned_date->format('F d, Y') }}</dd>
        <dt>Stall Section</dt><dd>{{ $assignment->stall_section }}</dd>
        <dt>Ticket Numbers</dt><dd>{{ number_format($assignment->ticket_start) }} – {{ number_format($assignment->ticket_end) }}</dd>
        <dt>Quantity Issued</dt><dd>{{ number_format($assignment->ticket_quantity) }} cash tickets</dd>
        <dt>Remarks</dt><dd>{{ $assignment->remarks ?: 'For daily public market fee collection.' }}</dd>
    </dl>
    <footer><p>Requested by<br><strong>{{ $assignment->collector?->full_name }}</strong></p><p>Approved / Issued by<br><strong>{{ $assignment->assigner?->full_name ?? 'Municipal Treasurer' }}</strong></p></footer>
    <button class="button button-primary no-print" onclick="window.print()">Print Slip</button>
</article>
</body>
</html>
