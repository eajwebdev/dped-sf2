<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF9 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        @page { margin: 12mm 10mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #111; margin: 0; }
        .card { page-break-after: always; }
        .card:last-child { page-break-after: auto; }
        h1 { font-size: 12px; text-align: center; margin: 0; letter-spacing: .5px; }
        .muted { color: #444; }
        .center { text-align: center; }
        .right { text-align: right; }
        .head { text-align: center; margin-bottom: 6px; }
        .head .agency { font-size: 9px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 0.6px solid #333; padding: 2px 3px; }
        .grid th { background: #f0f0f0; font-size: 7.5px; text-transform: uppercase; }
        .section-title { background: #e5e5e5; font-weight: bold; padding: 2px 4px; border: 0.6px solid #333; margin-top: 8px; text-transform: uppercase; font-size: 8px; }
        .info td { padding: 1.5px 3px; }
        .num { text-align: center; }
        .sub { font-weight: bold; }
        .foot { margin-top: 10px; font-size: 7.5px; }
        .sign { display: inline-block; width: 32%; text-align: center; margin-top: 16px; }
        .sign .line { border-top: 0.6px solid #333; margin-top: 14px; padding-top: 2px; }
    </style>
</head>
<body>
@php
    $school = $section->school;
    $sy = $schoolYear;
@endphp

@foreach ($learners as $L)
    <div class="card">
        <div class="head">
            <div class="muted">Republic of the Philippines</div>
            <div class="agency">Department of Education</div>
            @if ($school)<div class="muted">{{ $school->division ? 'Division of '.$school->division : '' }} {{ $school->region ? ' · '.$school->region : '' }}</div>@endif
            <h1>Learner's Progress Report Card</h1>
            <div class="muted">SF 9 — {{ $isShs ? 'SHS' : 'JHS' }}{{ $sy ? ' · S.Y. '.$sy->name : '' }}</div>
        </div>

        <table class="info">
            <tr>
                <td style="width:60%"><b>Name:</b> {{ $L['name'] }}</td>
                <td><b>LRN:</b> {{ $L['lrn'] ?: '—' }}</td>
            </tr>
            <tr>
                <td>
                    <b>School:</b> {{ $school->name ?? '—' }}
                </td>
                <td>
                    <b>Age:</b> {{ $L['age'] ?? '—' }} &nbsp; <b>Sex:</b> {{ $L['sex'] ?: '—' }}
                </td>
            </tr>
            <tr>
                <td><b>Grade:</b> {{ $section->gradeLevel->name }} &nbsp; <b>Section:</b> {{ $section->name }}</td>
                <td><b>Adviser:</b> {{ optional($section->adviser)->full_name ?? '—' }}</td>
            </tr>
        </table>

        {{-- ===== Report on Learning Progress ===== --}}
        <div class="section-title">Report on Learning Progress and Achievement</div>
        <table class="grid">
            <thead>
                @if ($isShs)
                    <tr>
                        <th rowspan="2" style="text-align:left">Learning Areas</th>
                        <th colspan="3">1st Semester</th>
                        <th colspan="3">2nd Semester</th>
                    </tr>
                    <tr>
                        <th>Q1</th><th>Q2</th><th>Final</th>
                        <th>Q1</th><th>Q2</th><th>Final</th>
                    </tr>
                @else
                    <tr>
                        <th style="text-align:left">Learning Areas</th>
                        <th>1</th><th>2</th><th>3</th><th>4</th>
                        <th>Final</th><th>Remarks</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @forelse ($L['subjects'] as $row)
                    <tr>
                        <td class="sub">{{ $row['subject'] }}</td>
                        @if ($isShs)
                            <td class="num">{{ $row['q'][1] !== null ? (int) round($row['q'][1]) : '' }}</td>
                            <td class="num">{{ $row['q'][2] !== null ? (int) round($row['q'][2]) : '' }}</td>
                            <td class="num sub">{{ $row['sem1'] ?? '' }}</td>
                            <td class="num">{{ $row['q'][3] !== null ? (int) round($row['q'][3]) : '' }}</td>
                            <td class="num">{{ $row['q'][4] !== null ? (int) round($row['q'][4]) : '' }}</td>
                            <td class="num sub">{{ $row['sem2'] ?? '' }}</td>
                        @else
                            @foreach ([1,2,3,4] as $p)
                                <td class="num">{{ $row['q'][$p] !== null ? (int) round($row['q'][$p]) : '' }}</td>
                            @endforeach
                            <td class="num sub">{{ $row['final'] ?? '' }}</td>
                            <td class="num">{{ $row['remark'] }}</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $isShs ? 7 : 7 }}" class="center muted">No learning areas set.</td></tr>
                @endforelse

                {{-- General average --}}
                @if ($isShs)
                    <tr>
                        <td class="sub right">General Average</td>
                        <td></td><td></td><td class="num sub">{{ $L['generalAverage']['sem1'] ?? '' }}</td>
                        <td></td><td></td><td class="num sub">{{ $L['generalAverage']['sem2'] ?? '' }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="sub right" colspan="5">General Average</td>
                        <td class="num sub">{{ $L['generalAverage']['final'] ?? '' }}</td>
                        <td class="num">{{ $L['generalAverage']['remark'] ?? '' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- ===== Report on Observed Values ===== --}}
        <div class="section-title">Report on Learner's Observed Values</div>
        <table class="grid">
            <thead>
                <tr>
                    <th style="text-align:left">Core Values</th>
                    @foreach ($periodLabels as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($L['values'] as $v)
                    <tr>
                        <td class="sub">{{ $v['label'] }}</td>
                        @foreach ([1,2,3,4] as $p)
                            <td class="num">{{ $v['marks'][$p] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="muted" style="font-size:7px; margin-top:2px">AO = Always Observed · SO = Sometimes Observed · RO = Rarely Observed · NO = Not Observed</div>

        {{-- ===== Attendance Record ===== --}}
        <div class="section-title">Attendance Record</div>
        <table class="grid">
            <thead>
                <tr>
                    <th style="text-align:left">&nbsp;</th>
                    @foreach ($months as $m)
                        <th>{{ $m['label'] }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="sub">No. of School Days</td>
                    @foreach ($months as $m)
                        <td class="num">{{ $L['attendance'][$m['key']]['days'] ?: '' }}</td>
                    @endforeach
                    <td class="num sub">{{ $L['attendance']['total']['days'] ?: '' }}</td>
                </tr>
                <tr>
                    <td class="sub">Days Present</td>
                    @foreach ($months as $m)
                        <td class="num">{{ $L['attendance'][$m['key']]['present'] ?: '' }}</td>
                    @endforeach
                    <td class="num sub">{{ $L['attendance']['total']['present'] ?: '' }}</td>
                </tr>
                <tr>
                    <td class="sub">Days Absent</td>
                    @foreach ($months as $m)
                        <td class="num">{{ $L['attendance'][$m['key']]['absent'] ?: '' }}</td>
                    @endforeach
                    <td class="num sub">{{ $L['attendance']['total']['absent'] ?: '' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="foot">
            <div class="sign">
                <div class="line">Parent / Guardian's Signature</div>
            </div>
            <div class="sign">
                <div class="line">{{ optional($section->adviser)->full_name ?? '' }}<br>Class Adviser</div>
            </div>
            <div class="sign">
                <div class="line">{{ $schoolHead ?? '' }}<br>Principal</div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
