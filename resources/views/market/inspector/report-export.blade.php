<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        h1, p { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #777; text-align: center; }
        th { color: #fff; background: #700006; }
    </style>
</head>
<body>
    <p>Department of Health<br>Office of the Municipal Health Officer<br><strong>MUNICIPALITY OF PANDAN</strong></p>
    <h1>LIST OF {{ $type }} SLAUGHTERED INSPECTED REPORT OF {{ strtoupper($month->format('F Y')) }}</h1>
    <table>
        <thead><tr><th>No.</th><th>Owner</th><th>Address</th><th>Type</th><th>Age</th><th>Weight</th><th>Count</th><th>Result</th><th>Inspection Date</th></tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td><td>{{ $row->owner_name }}</td><td>{{ $row->address }}</td><td>{{ $row->breed ?: $row->livestock_type }}</td>
                    <td>{{ $row->animal_age }}</td><td>{{ $row->live_weight }}</td><td>{{ $row->animal_count }}</td><td>{{ $row->inspection_result }}</td><td>{{ $row->scheduled_at->format('F d, Y g:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
