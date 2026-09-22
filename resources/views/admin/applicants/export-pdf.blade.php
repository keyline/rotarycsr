<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Selected Applicant Records</title>
    <style>
        @page { margin: 28px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #0b3763; font-size: 20px; margin: 0 0 4px; }
        .meta { color: #6b7280; margin-bottom: 18px; }
        .record { page-break-after: always; }
        .record:last-child { page-break-after: auto; }
        .record-header { border-bottom: 2px solid #d49b2a; margin: 0 0 10px; padding-bottom: 6px; width: 100%; }
        .record-header td { background: transparent; border: 0; padding: 0; vertical-align: middle; }
        .record-header td:first-child { background: transparent; width: auto; }
        .record-title { color: #0b3763; font-size: 16px; font-weight: bold; }
        .record-status { text-align: right; width: 145px; }
        .badge { border-radius: 12px; display: inline-block; font-size: 9px; font-weight: bold; padding: 4px 9px; }
        .badge-pending-application { background: #f3f4f6; color: #4b5563; }
        .badge-pending-review { background: #fffbeb; color: #b45309; }
        .badge-approved { background: #f0fdf4; color: #15803d; }
        .badge-rejected { background: #fef2f2; color: #b91c1c; }
        .badge-blacklisted { background: #111827; color: #ffffff; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        td { border: 1px solid #d1d5db; padding: 6px 8px; vertical-align: top; word-wrap: break-word; }
        td:first-child { background: #f3f4f6; color: #374151; font-weight: bold; width: 29%; }
        .empty { color: #9ca3af; }
    </style>
</head>
<body>
    <h1>Rotary CSR Awards 2026</h1>
    <div class="meta">Selected applicant records · Exported {{ now()->format('d M Y, h:i A') }}</div>

    @foreach ($records as $record)
        @php
            [$statusLabel, $statusClass] = match (true) {
                $record['Application Status'] !== 'Submitted' => ['Application pending', 'badge-pending-application'],
                $record['Review Status'] === 'Approved' => ['Approved', 'badge-approved'],
                $record['Review Status'] === 'Rejected' => ['Rejected', 'badge-rejected'],
                $record['Review Status'] === 'Blacklisted' => ['Blacklisted', 'badge-blacklisted'],
                default => ['Pending review', 'badge-pending-review'],
            };
        @endphp
        <section class="record">
            <table class="record-header">
                <tr>
                    <td class="record-title">{{ $record['Name'] ?: 'Applicant' }} · {{ $record['Applicant Type'] }}</td>
                    <td class="record-status"><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                </tr>
            </table>
            <table>
                @foreach ($record as $label => $value)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="{{ $value === '' ? 'empty' : '' }}">{!! $value === '' ? '—' : nl2br(e($value)) !!}</td>
                    </tr>
                @endforeach
            </table>
        </section>
    @endforeach
</body>
</html>
