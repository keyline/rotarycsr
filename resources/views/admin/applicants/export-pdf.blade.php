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
        .media-section { border-top: 2px solid #d49b2a; margin-top: 14px; padding-top: 8px; }
        .media-title { color: #0b3763; font-size: 13px; font-weight: bold; margin: 0 0 8px; }
        .media-item { margin-bottom: 12px; page-break-inside: avoid; }
        .media-name { color: #374151; font-weight: bold; margin-bottom: 5px; }
        .media-image { border: 1px solid #d1d5db; display: block; max-height: 200px; max-width: 200px; object-fit: contain; }
        .media-link { color: #17458f; word-break: break-all; }
    </style>
</head>
<body>
    <h1>Rotary CSR Awards 2026</h1>
    <div class="meta">Selected applicant records · Exported {{ now()->format('d M Y, h:i A') }}</div>

    @foreach ($records as $record)
        @php
            $fields = $record['fields'];
            [$statusLabel, $statusClass] = match (true) {
                $record['application_status'] !== 'submitted' => ['Application pending', 'badge-pending-application'],
                $record['review_status'] === 'approved' => ['Approved', 'badge-approved'],
                $record['review_status'] === 'rejected' => ['Rejected', 'badge-rejected'],
                $record['review_status'] === 'blacklisted' => ['Blacklisted', 'badge-blacklisted'],
                default => ['Pending review', 'badge-pending-review'],
            };
        @endphp
        <section class="record">
            <table class="record-header">
                <tr>
                    <td class="record-title">{{ $fields['Name'] ?: 'Applicant' }} · {{ $fields['Applicant Type'] }}</td>
                    <td class="record-status"><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                </tr>
            </table>
            <table>
                @foreach ($fields as $label => $value)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="{{ $value === '' ? 'empty' : '' }}">{!! $value === '' ? '—' : nl2br(e($value)) !!}</td>
                    </tr>
                @endforeach
            </table>

            @if ($record['media'] !== [])
                <div class="media-section">
                    <div class="media-title">Supporting Documents / Proof</div>
                    @foreach ($record['media'] as $media)
                        <div class="media-item">
                            <div class="media-name">{{ $media['name'] }} ({{ strtoupper($media['type']) }})</div>
                            @if ($media['type'] === 'image' && $media['data_uri'] !== '')
                                <a href="{{ $media['url'] }}" target="_blank">
                                    <img src="{{ $media['data_uri'] }}" alt="{{ $media['name'] }}" class="media-image">
                                </a>
                            @else
                                <a href="{{ $media['url'] }}" target="_blank" class="media-link">{{ $media['url'] }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach
</body>
</html>
