@php
    $days = $dayColumns;
    $nDays = count($days);
    $section = $data['section'];
    $sy = $data['schoolYear'];
    $sum = $data['summary'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SF2 — {{ $section->gradeLevel->name }} {{ $section->name }} — {{ $data['monthLabel'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #f3f4f6; color: #111; }
        .toolbar { position: sticky; top: 0; display: flex; gap: .5rem; align-items: center; background: #1f2937; color: #fff; padding: .6rem 1rem; }
        .toolbar a, .toolbar button { font-size: 13px; text-decoration: none; border: 0; border-radius: 6px; padding: .4rem .8rem; cursor: pointer; }
        .btn-print { background: #4f46e5; color: #fff; }
        .btn-pdf { background: #dc2626; color: #fff; }
        .btn-excel { background: #16a34a; color: #fff; }
        .btn-back { background: #374151; color: #fff; }
        .sheet { background: #fff; margin: 1rem auto; padding: 10px 14px; width: 1050px; max-width: 98%; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
        table { border-collapse: collapse; width: 100%; }
        .grid th, .grid td { border: 1px solid #000; font-size: 8.5px; text-align: center; padding: 1px 2px; }
        .grid td.name { text-align: left; white-space: nowrap; font-size: 9px; padding-left: 4px; }
        .grid .day { width: 16px; }
        .grid .totrow td { font-weight: bold; background: #f0f0f0; }
        .hdr { font-size: 8.5px; }
        .title { text-align: center; }
        .title h1 { font-size: 13px; margin: 2px 0; }
        .title p { font-size: 9px; margin: 0; }
        .meta { width: 100%; font-size: 10px; margin: 8px 0; }
        .meta td { padding: 2px 4px; }
        .meta .lbl { font-weight: bold; }
        .uline { border-bottom: 1px solid #000; display: inline-block; min-width: 120px; padding: 0 4px; }
        .tardy { color: #b45309; font-weight: bold; }
        .absent { color: #b91c1c; font-weight: bold; }
        .summary { width: 60%; font-size: 9px; margin-top: 10px; }
        .summary th, .summary td { border: 1px solid #000; padding: 2px 4px; }
        .legend { font-size: 8px; margin-top: 8px; color: #333; }
        .signs { display: flex; justify-content: space-between; margin-top: 22px; font-size: 10px; }
        .signs .sig { text-align: center; width: 45%; }
        .signs .sig .line { border-top: 1px solid #000; margin-top: 26px; padding-top: 2px; }
        @media print {
            @page { size: A4 landscape; margin: 6mm; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { width: auto; margin: 0; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    @unless ($pdf ?? false)
    <div class="toolbar no-print">
        <a class="btn-back" href="{{ route('reports.sf2.index') }}">&larr; Back</a>
        <button class="btn-print" onclick="window.print()">🖨 Print</button>
        <a class="btn-pdf" href="{{ route('reports.sf2.pdf', ['section' => $section, 'year' => $data['month']->year, 'month' => $data['month']->month]) }}">PDF</a>
        <a class="btn-excel" href="{{ route('reports.sf2.excel', ['section' => $section, 'year' => $data['month']->year, 'month' => $data['month']->month]) }}">Excel</a>
        <span style="margin-left:auto;font-size:12px;opacity:.8">{{ $section->gradeLevel->name }} {{ $section->name }} · {{ $data['monthLabel'] }}</span>
    </div>
    @endunless

    <div class="sheet">
        <div class="title">
            <h1>School Form 2 (SF2) Daily Attendance Report of Learners</h1>
            <p>(This replaced Form 1, Form 2 &amp; STS Form 4 - Absenteeism and Dropout Profile)</p>
        </div>

        <table class="meta">
            <tr>
                <td><span class="lbl">School ID:</span> <span class="uline">&nbsp;</span></td>
                <td><span class="lbl">School Year:</span> <span class="uline">{{ $sy->name }}</span></td>
                <td><span class="lbl">Report for the Month of:</span> <span class="uline">{{ $data['month']->format('F Y') }}</span></td>
            </tr>
            <tr>
                <td><span class="lbl">Name of School:</span> <span class="uline">{{ config('app.name') }}</span></td>
                <td><span class="lbl">Grade Level:</span> <span class="uline">{{ $section->gradeLevel->name }}</span></td>
                <td><span class="lbl">Section:</span> <span class="uline">{{ $section->name }}</span></td>
            </tr>
        </table>

        <table class="grid">
            <thead>
                <tr class="hdr">
                    <th rowspan="3" style="width:22px">No.</th>
                    <th rowspan="3" style="min-width:220px">LEARNER'S NAME<br>(Last Name, First Name, Middle Name)</th>
                    <th colspan="{{ max($nDays, 1) }}">Month: {{ $data['month']->format('F') }} &nbsp;|&nbsp; No. of School Days: {{ $sum['classDays'] }}</th>
                    <th colspan="2">Total for the Month</th>
                    <th rowspan="3" style="min-width:120px">REMARKS</th>
                </tr>
                <tr class="hdr">
                    @foreach ($days as $d)<th class="day">{{ $d['day'] }}</th>@endforeach
                    @if ($nDays === 0)<th>&nbsp;</th>@endif
                    <th rowspan="2" style="width:36px">ABSENT</th>
                    <th rowspan="2" style="width:36px">TARDY</th>
                </tr>
                <tr class="hdr">
                    @foreach ($days as $d)<th class="day">{{ $d['letter'] }}</th>@endforeach
                    @if ($nDays === 0)<th>&nbsp;</th>@endif
                </tr>
            </thead>
            <tbody>
                @include('reports.sf2.partials.rows', ['rows' => $data['males'], 'label' => 'MALE', 'totals' => $data['dailyTotals'], 'genderKey' => 'male', 'days' => $days])
                @include('reports.sf2.partials.rows', ['rows' => $data['females'], 'label' => 'FEMALE', 'totals' => $data['dailyTotals'], 'genderKey' => 'female', 'days' => $days])

                {{-- Combined per-day total --}}
                <tr class="totrow">
                    <td colspan="2">Combined TOTAL PER DAY</td>
                    @foreach ($days as $d)<td>{{ $data['dailyTotals'][$d['date']]['combined'] ?? 0 }}</td>@endforeach
                    @if ($nDays === 0)<td>&nbsp;</td>@endif
                    <td colspan="2">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        {{-- Monthly summary --}}
        <table class="summary">
            <tr><th colspan="4" style="text-align:center">Summary for the Month ({{ $data['monthLabel'] }})</th></tr>
            <tr><th></th><th>M</th><th>F</th><th>TOTAL</th></tr>
            <tr>
                <td style="text-align:left">Enrolment as of end of the month</td>
                <td>{{ $sum['enrolment']['male'] }}</td><td>{{ $sum['enrolment']['female'] }}</td><td>{{ $sum['enrolment']['total'] }}</td>
            </tr>
            <tr>
                <td style="text-align:left">Average Daily Attendance</td>
                <td>{{ $sum['avgDaily']['male'] }}</td><td>{{ $sum['avgDaily']['female'] }}</td><td>{{ $sum['avgDaily']['total'] }}</td>
            </tr>
            <tr>
                <td style="text-align:left">Percentage of Attendance for the month</td>
                <td>{{ $sum['percentAttendance']['male'] }}%</td><td>{{ $sum['percentAttendance']['female'] }}%</td><td>{{ $sum['percentAttendance']['total'] }}%</td>
            </tr>
            <tr>
                <td style="text-align:left">No. of Days of Classes</td>
                <td colspan="3">{{ $sum['classDays'] }}</td>
            </tr>
        </table>

        <div class="legend">
            <b>1. Codes for Checking Attendance:</b> (blank) = Present; <b class="absent">x</b> = Absent;
            <b class="tardy">/</b> = Tardy (half shaded: upper = Late Comer, lower = Cutting Classes); <b>½</b> = Half Day; <b>e</b> = Excused.
        </div>

        <div class="signs">
            <div class="sig">
                <div class="line">{{ $section->adviser?->full_name ?? '________________________' }}</div>
                <div>(Signature of Teacher over Printed Name)</div>
            </div>
            <div class="sig">
                <div class="line">________________________</div>
                <div>(Signature of School Head over Printed Name)</div>
            </div>
        </div>
        <p style="font-size:8px;text-align:right;margin-top:6px">I certify that this is a true and correct report.</p>
    </div>
</body>
</html>
