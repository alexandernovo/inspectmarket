<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        h1, p { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #777; text-align: center; }
        th { color: #fff; background: #650006; }
    </style>
</head>
<body>
    <p>Republic of the Philippines<br>Office of the Municipal Treasurer<br><strong>MUNICIPALITY OF PANDAN</strong></p>
    <h1>{{ strtoupper($title) }}</h1>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) + 1 }}">No report data available.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
