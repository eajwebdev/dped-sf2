<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF9 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        @page { margin: 6mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; color: #111; margin: 0; }

        /* One landscape page per learner: four panels in a 2x2 grid. */
        .card { page-break-after: always; }
        .card.tail { page-break-after: auto; }

        table { border-collapse: collapse; width: 100%; }
        .grid2 > tbody > tr > td { width: 50%; vertical-align: top; padding: 2px 7px; }
        .grid2 .divider { border-left: 0.6px solid #999; }
        .grid2 .rowtop { border-top: 0.6px solid #999; padding-top: 5px; }

        .box th, .box td { border: 0.5px solid #333; padding: 1px 2px; }
        .box th { font-weight: bold; text-align: center; font-size: 6.5px; text-transform: uppercase; }

        /* Attendance month + total headers rotated straight up (vertical),
           reading bottom-to-top, centred in their narrow columns. */
        .box th.mon { height: 50px; width: 12px; padding: 1px 0; vertical-align: middle; text-align: center; }
        .box th.mon span {
            display: inline-block; white-space: nowrap; font-size: 6.5px; font-weight: bold;
            transform: rotate(-90deg); transform-origin: 50% 50%;
        }

        .num { text-align: center; }
        .b { font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .muted { color: #444; }

        .card-title { text-align: center; font-weight: bold; font-size: 9px; letter-spacing: .2px; margin: 3px 0; }
        .section-h { text-align: center; font-weight: bold; font-size: 7.5px; text-transform: uppercase; margin: 1px 0 3px; }
        .sf-tag { font-weight: bold; font-size: 7.5px; }

        .hdr { text-align: center; line-height: 1.2; }
        .hdr .agency { font-weight: bold; }
        .fill { border-bottom: 0.5px solid #333; height: 9px; }
        .line { display: inline-block; border-bottom: 0.5px solid #333; min-width: 90px; }
        .field { margin: 2px 0; }

        .sign { margin-top: 12px; text-align: center; }
        .sign .ln { border-top: 0.5px solid #333; padding-top: 1px; }
        .legend th, .legend td { border: 0.5px solid #333; padding: 1px 3px; font-size: 6.5px; }
        .letter { font-size: 7px; line-height: 1.3; margin: 2px 1px; text-align: justify; }
    </style>
</head>
<body>
@php
    $school = $section->school;
    $sy = $schoolYear;
    $depedLogo = public_path('DepED-Logo.png');
    // Only the school's own uploaded seal is printed (the official card shows
    // just the DepEd logo at left), never the generic app fallback.
    $schoolLogo = ($school?->logo_path && is_file(public_path($school->logo_path)))
        ? public_path($school->logo_path)
        : null;
    $sfTag = 'SF 9 - '.($isShs ? 'SHS' : 'JHS');
@endphp

@foreach ($learners as $L)
    <div class="card @if ($loop->last) tail @endif">
        <table class="grid2">
            {{-- ===== Top row: attendance/transfer | report-card cover ===== --}}
            <tr>
                <td>
                    <div class="section-h">Attendance Record</div>
                    <table class="box">
                        <thead>
                            <tr>
                                <th style="text-align:left; vertical-align:bottom">&nbsp;</th>
                                @foreach ($months as $m)
                                    <th class="mon"><span>{{ strtoupper(\Carbon\Carbon::create($m['year'], $m['month'], 1)->format('F')) }}</span></th>
                                @endforeach
                                <th class="mon"><span>TOTAL</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="b">School Days</td>
                                @foreach ($months as $m)<td class="num">{{ $L['attendance'][$m['key']]['days'] ?: '' }}</td>@endforeach
                                <td class="num b">{{ $L['attendance']['total']['days'] ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="b">No. of Days Present</td>
                                @foreach ($months as $m)<td class="num">{{ $L['attendance'][$m['key']]['present'] ?: '' }}</td>@endforeach
                                <td class="num b">{{ $L['attendance']['total']['present'] ?: '' }}</td>
                            </tr>
                            <tr>
                                <td class="b">No. of Days Absent</td>
                                @foreach ($months as $m)<td class="num">{{ $L['attendance'][$m['key']]['absent'] ?: '' }}</td>@endforeach
                                <td class="num b">{{ $L['attendance']['total']['absent'] ?: '' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="section-h" style="margin-top:6px">Parent / Guardian's Signature</div>
                    <table>
                        <tr>
                            <td style="width:50%">1st Quarter <span class="line" style="min-width:70px">&nbsp;</span></td>
                            <td style="width:50%">2nd Quarter <span class="line" style="min-width:70px">&nbsp;</span></td>
                        </tr>
                        <tr>
                            <td>3rd Quarter <span class="line" style="min-width:70px">&nbsp;</span></td>
                            <td>4th Quarter <span class="line" style="min-width:70px">&nbsp;</span></td>
                        </tr>
                    </table>

                    <div class="section-h" style="margin-top:6px">Certificate of Transfer</div>
                    <div class="field">Admitted to Grade: <span class="line" style="min-width:55px">&nbsp;</span>
                        &nbsp;Section: <span class="line" style="min-width:70px">&nbsp;</span></div>
                    <div class="field">Eligibility for Admission to Grade: <span class="line" style="min-width:90px">&nbsp;</span></div>
                    <table style="margin-top:12px">
                        <tr>
                            <td style="width:50%; padding:0 8px"><div class="sign" style="margin-top:0"><div class="ln">Principal</div></div></td>
                            <td style="width:50%; padding:0 8px"><div class="sign" style="margin-top:0"><div class="ln">Teacher</div></div></td>
                        </tr>
                    </table>

                    <div class="section-h" style="margin-top:6px">Cancellation of Eligibility to Transfer</div>
                    <div class="field">Admitted in: <span class="line" style="min-width:110px">&nbsp;</span></div>
                    <div class="field">Date: <span class="line" style="min-width:110px">&nbsp;</span></div>
                    <table style="margin-top:12px">
                        <tr>
                            <td style="width:50%"></td>
                            <td style="width:50%; padding:0 8px"><div class="sign" style="margin-top:0"><div class="ln">Principal</div></div></td>
                        </tr>
                    </table>
                </td>

                <td class="divider">
                    <div class="sf-tag">{{ $sfTag }}</div>
                    <table>
                        <tr>
                            <td style="width:50px; vertical-align:top">
                                @if (file_exists($depedLogo))<img src="{{ $depedLogo }}" alt="DepEd" style="height:46px">@endif
                            </td>
                            <td class="hdr">
                                <div class="muted">Republic of the Philippines</div>
                                <div class="agency">Department of Education</div>
                                <div>{{ $school?->region ? 'Region '.$school->region : 'Region ______' }}</div>
                                <div>{{ $school?->division ? 'Division of '.$school->division : 'Division of ______' }}</div>
                                <div class="fill">&nbsp;</div>
                                <div class="muted">District</div>
                                <div class="fill">{{ $school?->name ?? '' }}</div>
                                <div class="muted">School</div>
                            </td>
                            <td style="width:50px; vertical-align:top; text-align:right">
                                @if ($schoolLogo)<img src="{{ $schoolLogo }}" alt="School" style="height:42px">@endif
                            </td>
                        </tr>
                    </table>

                    <div class="card-title">Learner's Progress Report Card</div>

                    <div class="field">Name: <span class="line" style="min-width:210px">{{ $L['name'] }}</span></div>
                    <div class="field">Learner's Reference Number: <span class="line" style="min-width:140px">{{ $L['lrn'] ?: '' }}</span></div>
                    <div class="field">Age: <span class="line" style="min-width:45px">{{ $L['age'] ?? '' }}</span>
                        &nbsp;&nbsp;Sex: <span class="line" style="min-width:95px">{{ $L['sex'] ?: '' }}</span></div>
                    <div class="field">Grade: <span class="line" style="min-width:75px">{{ $section->gradeLevel->name }}</span>
                        &nbsp;&nbsp;Section: <span class="line" style="min-width:95px">{{ $section->name }}</span></div>
                    <div class="field">School Year: <span class="line" style="min-width:150px">{{ $sy?->name }}</span></div>

                    <p class="letter"><b>Dear Parent,</b> This report card shows the ability and progress your child has made
                        in the different learning areas as well as his/her core values. The school welcomes you should you
                        desire to know more about your child's progress.</p>

                    <table style="margin-top:14px">
                        <tr>
                            <td style="width:50%; padding:0 8px">
                                <div class="sign" style="margin-top:0"><div class="ln">{{ $schoolHead ?? '' }}<br>Principal</div></div>
                            </td>
                            <td style="width:50%; padding:0 8px">
                                <div class="sign" style="margin-top:0"><div class="ln">{{ optional($section->adviser)->full_name ?? '' }}<br>Teacher</div></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- ===== Bottom row: learning progress | observed values ===== --}}
            <tr>
                <td class="rowtop">
                    <div class="section-h">Report on Learning Progress and Achievement</div>
                    <table class="box">
                        <thead>
                            @if ($isShs)
                                <tr>
                                    <th rowspan="2" style="text-align:left">Learning Areas</th>
                                    <th colspan="3">1st Semester</th>
                                    <th colspan="3">2nd Semester</th>
                                </tr>
                                <tr><th>Q1</th><th>Q2</th><th>Final</th><th>Q1</th><th>Q2</th><th>Final</th></tr>
                            @else
                                <tr>
                                    <th rowspan="2" style="text-align:left">Learning Areas</th>
                                    <th colspan="4">Quarter</th>
                                    <th rowspan="2">Final<br>Rating</th>
                                    <th rowspan="2">Remarks</th>
                                </tr>
                                <tr><th>1</th><th>2</th><th>3</th><th>4</th></tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse ($L['subjects'] as $row)
                                <tr>
                                    <td class="b">{{ $row['subject'] }}</td>
                                    @if ($isShs)
                                        <td class="num">{{ $row['q'][1] !== null ? (int) round($row['q'][1]) : '' }}</td>
                                        <td class="num">{{ $row['q'][2] !== null ? (int) round($row['q'][2]) : '' }}</td>
                                        <td class="num b">{{ $row['sem1'] ?? '' }}</td>
                                        <td class="num">{{ $row['q'][3] !== null ? (int) round($row['q'][3]) : '' }}</td>
                                        <td class="num">{{ $row['q'][4] !== null ? (int) round($row['q'][4]) : '' }}</td>
                                        <td class="num b">{{ $row['sem2'] ?? '' }}</td>
                                    @else
                                        @foreach ([1,2,3,4] as $p)
                                            <td class="num">{{ $row['q'][$p] !== null ? (int) round($row['q'][$p]) : '' }}</td>
                                        @endforeach
                                        <td class="num b">{{ $row['final'] ?? '' }}</td>
                                        <td class="num">{{ $row['remark'] }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="7" class="center muted">No learning areas set.</td></tr>
                            @endforelse

                            @if ($isShs)
                                <tr>
                                    <td class="b right">General Average</td>
                                    <td></td><td></td><td class="num b">{{ $L['generalAverage']['sem1'] ?? '' }}</td>
                                    <td></td><td></td><td class="num b">{{ $L['generalAverage']['sem2'] ?? '' }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="b right" colspan="5">General Average</td>
                                    <td class="num b">{{ $L['generalAverage']['final'] ?? '' }}</td>
                                    <td class="num">{{ $L['generalAverage']['remark'] ?? '' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <table class="legend" style="margin-top:5px; width:auto">
                        <thead><tr><th>Descriptors</th><th>Grading Scale</th><th>Remarks</th></tr></thead>
                        <tbody>
                            <tr><td>Outstanding</td><td class="center">90-100</td><td class="center">Passed</td></tr>
                            <tr><td>Very Satisfactory</td><td class="center">85-89</td><td class="center">Passed</td></tr>
                            <tr><td>Satisfactory</td><td class="center">80-84</td><td class="center">Passed</td></tr>
                            <tr><td>Fairly Satisfactory</td><td class="center">75-79</td><td class="center">Passed</td></tr>
                            <tr><td>Did Not Meet Expectations</td><td class="center">Below 75</td><td class="center">Failed</td></tr>
                        </tbody>
                    </table>
                </td>

                <td class="divider rowtop">
                    <div class="section-h">Report on Learner's Observed Values</div>
                    <table class="box">
                        <thead>
                            <tr>
                                <th rowspan="2" style="text-align:left; width:15%">Core Values</th>
                                <th rowspan="2" style="text-align:left">Behavior Statements</th>
                                <th colspan="4">Quarter</th>
                            </tr>
                            <tr><th>1</th><th>2</th><th>3</th><th>4</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($L['values'] as $v)
                                @php $rows = max(1, count($v['statements'])); @endphp
                                @forelse ($v['statements'] as $i => $st)
                                    <tr>
                                        @if ($i === 0)
                                            <td class="b" rowspan="{{ $rows }}">{{ $loop->parent->iteration }}. {{ $v['label'] }}</td>
                                        @endif
                                        <td>{{ $st['text'] }}</td>
                                        @foreach ([1,2,3,4] as $p)
                                            <td class="num">{{ $st['marks'][$p] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td class="b">{{ $loop->iteration }}. {{ $v['label'] }}</td><td colspan="5"></td></tr>
                                @endforelse
                            @endforeach
                        </tbody>
                    </table>

                    <table class="legend" style="margin-top:5px; width:auto">
                        <thead><tr><th>Marking</th><th>Non-Numerical Rating</th></tr></thead>
                        <tbody>
                            @foreach ($marks as $code => $label)
                                <tr><td class="center b">{{ $code }}</td><td>{{ $label }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
