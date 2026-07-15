<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>QR ID Cards — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; }

    .sheet-header { margin-bottom: 14px; border-bottom: 2px solid #4338ca; padding-bottom: 8px; }
    .sheet-header h1 { font-size: 14px; color: #312e81; }
    .sheet-header p { font-size: 9px; color: #6b7280; margin-top: 2px; }

    table.cards { width: 100%; border-collapse: separate; border-spacing: 6px; }
    td.card {
        width: 50%;
        border: 1px solid #c7d2fe;
        border-radius: 10px;
        padding: 0;
        vertical-align: top;
        background: #ffffff;
    }
    .card-band {
        background: #4338ca;
        color: #ffffff;
        padding: 6px 12px;
        border-radius: 9px 9px 0 0;
        font-size: 8px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .card-body { padding: 10px 12px; }
    .card-body table { width: 100%; border-collapse: collapse; }
    .qr-cell { width: 96px; vertical-align: top; }
    .qr-cell img { width: 92px; height: 92px; }
    .info-cell { vertical-align: top; padding-left: 10px; }
    .student-name { font-size: 12px; font-weight: bold; color: #111827; line-height: 1.25; }
    .lrn { font-size: 8.5px; color: #6b7280; margin-top: 3px; }
    .meta { margin-top: 8px; font-size: 9px; color: #374151; line-height: 1.6; }
    .meta strong { color: #4338ca; }
    .card-foot {
        border-top: 1px dashed #e5e7eb;
        padding: 5px 12px;
        font-size: 7.5px;
        color: #9ca3af;
    }
</style>
</head>
<body>

<div class="sheet-header">
    <h1>Student QR Attendance IDs — {{ $section->gradeLevel->name }} {{ $section->name }}</h1>
    <p>
        School Year {{ $section->schoolYear->name }}
        @if ($section->adviser) · Adviser: {{ $section->adviser->full_name }} @endif
        · Generated {{ now()->format('M d, Y g:i A') }}
    </p>
</div>

<table class="cards">
    @foreach ($cards->chunk(2) as $row)
        <tr>
            @foreach ($row as $card)
                <td class="card">
                    <div class="card-band">{{ config('app.name') }} · Attendance ID</div>
                    <div class="card-body">
                        <table>
                            <tr>
                                <td class="qr-cell"><img src="{{ $card['qr'] }}" alt="QR"></td>
                                <td class="info-cell">
                                    <div class="student-name">{{ $card['name'] }}</div>
                                    <div class="lrn">LRN: {{ $card['lrn'] }}</div>
                                    <div class="meta">
                                        <strong>SY {{ $section->schoolYear->name }}</strong><br>
                                        {{ $section->gradeLevel->name }} — {{ $section->name }}
                                        @if ($section->room)<br>Room {{ $section->room }}@endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-foot">Scan at the class portal to be marked present. Report lost cards to your adviser.</div>
                </td>
            @endforeach
            @if ($row->count() === 1)<td style="border: none;"></td>@endif
        </tr>
    @endforeach
</table>

</body>
</html>
