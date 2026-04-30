<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Employees Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 5px;
        }
        h3 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f0f0f0;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @php
        $defaultLogoPath = public_path('images/axiomatic_logowhite-300x158.png');
        $logoSrc = file_exists($defaultLogoPath) ? 'file://' . $defaultLogoPath : null;

        if (!empty($logoUrl)) {
            if (str_starts_with($logoUrl, 'http://') || str_starts_with($logoUrl, 'https://') || str_starts_with($logoUrl, 'data:')) {
                $logoSrc = $logoUrl;
            } else {
                $candidatePath = public_path(ltrim($logoUrl, '/'));
                if (file_exists($candidatePath)) {
                    $logoSrc = 'file://' . $candidatePath;
                }
            }
        }
    @endphp

    <div style="margin-bottom: 24px; overflow: hidden; background-color: #CE2031; height: 112px; width: 100%; padding: 0 24px; border-bottom-right-radius: 110px; text-align: center; line-height: 112px;">
        @if($logoSrc)
        <img
            src="{{ $logoSrc }}"
            alt="{{ $logoAlt ?? 'Axiomatic' }}"
            width="260"
            style="max-width: 260px; width: 100%; height: auto; vertical-align: middle; position: relative; top: -20px;"
        />
        @endif
    </div>
    <h3>Employees Report</h3>
    <p style="text-align: center; color: #666; font-size: 11px;">Generated on {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Employee Number</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Company</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
                <tr>
                    <td>{{ $emp->employee_number }}</td>
                    <td>{{ $emp->employee_display }}</td>
                    <td>{{ $emp->branch?->name ?? '-' }}</td>
                    <td>{{ $emp->branch?->company?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No employees found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Employees: {{ $employees->count() }}</p>
    </div>
</body>
</html>
