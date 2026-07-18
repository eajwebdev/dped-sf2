@php
    use App\Models\Attendance;
    $days = $dayColumns;
    $nDays = count($days);
    $section = $data['section'];
    $sy = $data['schoolYear'];
    $sum = $data['summary'];
    // The section's school, falling back to the logged-in teacher's school so
    // the form always carries the branding of the school they belong to.
    $school = $section->school ?? auth()->user()?->school;
    $adviser = $section->adviser?->full_name;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SF2 — {{ $section->gradeLevel->name }} {{ $section->name }} — {{ $data['monthLabel'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #fff; color: #000; }
        .toolbar { position: sticky; top: 0; z-index: 10; display: flex; gap: .5rem; align-items: center; background: #1f2937; color: #fff; padding: .6rem 1rem; }
        .toolbar a, .toolbar button { font-size: 13px; text-decoration: none; border: 0; border-radius: 6px; padding: .4rem .8rem; cursor: pointer; }
        .btn-print { background: #4f46e5; color: #fff; }
        .btn-pdf { background: #dc2626; color: #fff; }
        .btn-excel { background: #16a34a; color: #fff; }
        .btn-back { background: #374151; color: #fff; }

        .sheet { background: #fff; margin: 1rem auto; padding: 12px 16px; width: 1120px; max-width: 99%; box-shadow: 0 1px 4px rgba(0,0,0,.15); }

        /* Narrow centered band so both seals sit right beside the title rather
           than out at the page corners. */
        .head-row { width: 48%; margin: 0 auto; }
        .head-row td { vertical-align: middle; }
        .logo { width: 56px; }
        .logo img.seal { height: 58px; }
        .logo img.deped { height: 48px; }
        .title { text-align: center; }
        .title h1 { font-size: 15px; font-weight: bold; margin: 0; }
        .title .sub { font-size: 9px; font-style: italic; margin: 1px 0 0; }
        .title .sf2 { font-size: 9px; text-align: right; font-style: italic; }

        .meta { width: 100%; font-size: 10.5px; border-collapse: collapse; margin-top: 6px; }
        .meta td { padding: 3px 4px; white-space: nowrap; }
        .meta .lbl { }
        .val { border-bottom: 1px solid #000; display: inline-block; min-width: 60px; text-align: center; font-weight: bold; padding: 0 6px; }

        table.grid { border-collapse: collapse; width: 100%; margin-top: 4px; }
        .grid th, .grid td { border: 1px solid #000; font-size: 8px; text-align: center; padding: 0 1px; height: 16px; }
        .grid th { font-weight: bold; }
        .grid th.col-name { white-space: nowrap; }
        .grid .num { width: 18px; }
        .grid td.name { text-align: left; padding-left: 3px; font-size: 8.5px; white-space: nowrap; }
        .grid .day { width: 14px; }
        .grid .tot { width: 26px; }
        .grid .col-remarks { width: 130px; }
        .grid td.remarks { text-align: left; }
        .grid .totrow td { font-weight: bold; background: #f4f4f4; }
        .grid td.name.totlabel { text-align: center; font-style: italic; }
        .absent { color: #b91c1c; font-weight: bold; }
        .tardy { color: #b45309; font-weight: bold; }

        /* Footer three-column block */
        .foot { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8px; table-layout: fixed; }
        .foot > tbody > tr > td { vertical-align: top; padding: 0 8px; }
        .foot .c1 { width: 38%; }
        .foot .c2 { width: 30%; }
        .foot .c3 { width: 32%; }
        .foot b { font-weight: bold; }
        .foot p { margin: 0 0 3px; }
        .formula { display: inline-block; text-align: center; }
        .formula .frac { border-bottom: 1px solid #000; padding: 0 4px; }
        .reasons div { line-height: 1.35; }

        .sumtab { border-collapse: collapse; width: 100%; font-size: 8px; }
        .sumtab th, .sumtab td { border: 1px solid #000; padding: 1px 3px; text-align: center; height: 14px; }
        .sumtab td.l { text-align: left; }
        .sig { text-align: center; margin-top: 4px; }
        .sig .name { font-weight: bold; border-bottom: 1px solid #000; padding: 0 10px 1px; display: inline-block; min-width: 150px; }
        .sig .cap { font-size: 7.5px; font-style: italic; }

        @media print {
            @page { size: A4 landscape; margin: 5mm; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { width: auto; margin: 0; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="sheet">
        {{-- ===== Title ===== --}}
        @php
            // DomPDF needs local filesystem paths, not URLs. School's uploaded
            // logo (stored directly in public/) first, the bundled seal as fallback.
            $schoolLogo = $school?->logo_path && file_exists(public_path($school->logo_path))
                ? public_path($school->logo_path)
                : public_path('logo.png');
            $depedLogo = public_path('DepED-Logo.png');
        @endphp
        <table class="head-row">
            <tr>
                <td class="logo">
                    @if (file_exists($schoolLogo))<img class="seal" src="{{ $schoolLogo }}" alt="School logo">@endif
                </td>
                <td class="title">
                    <h1>School Form 2 (SF2) Daily Attendance Report of Learners</h1>
                    <p class="sub">(This replaces Form 1, Form 2 &amp; STS Form 4 - Absenteeism and Dropout Profile)</p>
                </td>
                <td class="logo" style="text-align:right">
                    @if (file_exists($depedLogo))<img class="deped" src="{{ $depedLogo }}" alt="DepEd">@endif
                </td>
            </tr>
        </table>

        {{-- ===== Meta ===== --}}
        <table class="meta">
            <tr>
                <td><span class="lbl">School ID</span> <span class="val">{{ $school?->school_id ?? '' }}</span></td>
                <td><span class="lbl">School Year</span> <span class="val">{{ $sy->name }}</span></td>
                <td><span class="lbl">Report for the Month of</span> <span class="val">{{ strtoupper($data['month']->format('F')) }}</span></td>
            </tr>
            <tr>
                <td><span class="lbl">Name of School</span> <span class="val" style="min-width:180px">{{ $school?->name ?? config('app.name') }}</span></td>
                <td><span class="lbl">Grade Level</span> <span class="val">{{ $section->gradeLevel->name }}</span></td>
                <td><span class="lbl">Section</span> <span class="val">{{ $section->name }}</span></td>
            </tr>
        </table>

        {{-- ===== Attendance grid: single table, exactly as the official form lays it out ===== --}}
        <table class="grid">
            <colgroup>
                <col class="num"><col class="col-name">
                @foreach ($days as $d)<col class="day">@endforeach
                @if ($nDays === 0)<col class="day">@endif
                <col class="tot"><col class="tot"><col class="col-remarks">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="3">No.</th>
                    <th rowspan="3" class="col-name">NAME<br>(Last Name, First Name, Middle Name)</th>
                    <th colspan="{{ max($nDays, 1) }}">(1<sup>st</sup> row for date)</th>
                    <th colspan="2" rowspan="2">Total for the Month</th>
                    <th rowspan="3">REMARKS<br><span style="font-weight:normal;font-size:7px">(If NLS, state reason, please refer to legend number 2. If TRANSFERRED IN/OUT, write the name of School.)</span></th>
                </tr>
                <tr>
                    @foreach ($days as $d)<th class="day">{{ $d['day'] }}</th>@endforeach
                    @if ($nDays === 0)<th class="day">&nbsp;</th>@endif
                </tr>
                <tr>
                    @foreach ($days as $d)<th class="day">{{ strtoupper($d['letter']) }}</th>@endforeach
                    @if ($nDays === 0)<th class="day">&nbsp;</th>@endif
                    <th class="tot">ABSENT</th>
                    <th class="tot">PRESENT</th>
                </tr>
            </thead>
            <tbody>
                @include('reports.sf2.partials.rows', ['rows' => $data['males'], 'label' => 'MALE', 'totals' => $data['dailyTotals'], 'genderKey' => 'male', 'days' => $days])
                @include('reports.sf2.partials.rows', ['rows' => $data['females'], 'label' => 'FEMALE', 'totals' => $data['dailyTotals'], 'genderKey' => 'female', 'days' => $days])

                <tr class="totrow">
                    <td class="num">{{ count($data['males']) + count($data['females']) }}</td>
                    <td class="name totlabel">Combined TOTAL Per Day</td>
                    @foreach ($days as $d)<td class="day">{{ $data['dailyTotals'][$d['date']]['combined'] ?? 0 }}</td>@endforeach
                    @if ($nDays === 0)<td class="day">&nbsp;</td>@endif
                    <td class="tot">&nbsp;</td>
                    <td class="tot">{{ collect($data['males'])->sum('present') + collect($data['females'])->sum('present') }}</td>
                    <td class="remarks">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        {{-- ===== Footer: guidelines | codes+reasons | summary+signatures (verbatim from the official xls) ===== --}}
        <table class="foot">
            <tr>
                {{-- Column 1 — Guidelines & formulas --}}
                <td class="c1">
                    <p><b>GUIDELINES:</b></p>
                    <p>1. The attendance shall be accomplished daily. Refer to the codes for checking learners' attendance.</p>
                    <p>2. Dates shall be written in the columns after Learner's Name.</p>
                    <p>3. To compute the following:</p>
                    <p style="margin-left:8px">a. Percentage of Enrolment =
                        <span class="formula"><span class="frac">Registered Learners as of end of the month</span><br>Enrolment as of 1st Friday of the school year</span> x 100</p>
                    <p style="margin-left:8px">b. Average Daily Attendance =
                        <span class="formula"><span class="frac">Total Daily Attendance</span><br>Number of School Days in reporting month</span></p>
                    <p style="margin-left:8px">c. Percentage of Attendance for the month =
                        <span class="formula"><span class="frac">Average daily attendance</span><br>Registered Learners as of end of the month</span> x 100</p>
                    <p>4. Every end of the month, the class adviser will submit this form to the office of the principal for recording of summary table into School Form 4. Once signed by the principal, this form should be returned to the adviser.</p>
                    <p>5. The adviser will provide neccessary interventions including but not limited to home visitation to learner/s who were absent for 5 consecutive days and/or those at risk of dropping out.</p>
                    <p>6. Attendance performance of learners will be reflected in Form 137 and Form 138 every grading period.</p>
                    <p style="margin-left:8px"><i>*Beginning of School Year cut-off report is every 1st Friday of the School Year</i></p>
                </td>

                {{-- Column 2 — Codes & reasons for NLS --}}
                <td class="c2">
                    <p><b>1. CODES FOR CHECKING ATTENDANCE</b></p>
                    <p><b>(blank)</b> - Present; <b class="absent">(x)</b>- Absent; Tardy (half shaded= Upper for Late Commer, Lower for Cutting Classes)</p>
                    <p style="margin-top:5px"><b>2. REASONS/CAUSES FOR NLS</b></p>
                    <div class="reasons">
                        <div><b>a. Domestic-Related Factors</b></div>
                        <div style="margin-left:8px">a.1. Had to take care of siblings<br>a.2. Early marriage/pregnancy<br>a.3. Parents' attitude toward schooling<br>a.4. Family problems</div>
                        <div><b>b. Individual-Related Factors</b></div>
                        <div style="margin-left:8px">b.1. Illness<br>b.2. Overage<br>b.3. Death<br>b.4. Drug Abuse<br>b.5. Poor academic performance<br>b.6. Lack of interest/Distractions<br>b.7. Hunger/Malnutrition</div>
                        <div><b>c. School-Related Factors</b></div>
                        <div style="margin-left:8px">c.1. Teacher Factor<br>c.2. Physical condition of classroom<br>c.3. Peer influence</div>
                        <div><b>d. Geographic/Environmental</b></div>
                        <div style="margin-left:8px">d.1. Distance between home and school<br>d.2. Armed conflict (incl. Tribal wars &amp; clanfeuds)<br>d.3. Calamities/Disasters</div>
                        <div><b>e. Financial-Related</b></div>
                        <div style="margin-left:8px">e.1. Child labor, work</div>
                        <div><b>f. Others (Specify)</b></div>
                    </div>
                </td>

                {{-- Column 3 — Monthly summary + signatures --}}
                <td class="c3">
                    @php
                        // The xls shows percentages as whole percents and ADA as a
                        // whole number (e.g. 100%, 94%, 16).
                        $pct = fn ($v) => round((float) $v * 100).'%';
                        $whole = fn ($v) => (string) round((float) $v);
                    @endphp
                    <table class="sumtab">
                        <tr>
                            <td class="l" rowspan="2" style="width:34%"><b>Month :</b><br>{{ strtoupper($data['month']->format('F')) }}</td>
                            <td class="l" rowspan="2" style="width:26%;text-align:center"><b>No. of Days of<br>Classes:</b> {{ $sum['classDays'] }}</td>
                            <th colspan="3"><b>Summary</b></th>
                        </tr>
                        <tr><th>M</th><th>F</th><th>TOTAL</th></tr>
                        <tr><td class="l" colspan="2">* Enrolment as of (1st Friday of the SY)</td><td>{{ $sum['enrolment']['male'] }}</td><td>{{ $sum['enrolment']['female'] }}</td><td>{{ $sum['enrolment']['total'] }}</td></tr>
                        <tr><td class="l" colspan="2">Late enrolment <b>during the month</b><br>(beyond cut-off)</td><td>{{ $sum['lateEnrolment']['male'] }}</td><td>{{ $sum['lateEnrolment']['female'] }}</td><td>{{ $sum['lateEnrolment']['total'] }}</td></tr>
                        <tr><td class="l" colspan="2">Registered Learners as of <b>end of month</b></td><td>{{ $sum['registered']['male'] }}</td><td>{{ $sum['registered']['female'] }}</td><td>{{ $sum['registered']['total'] }}</td></tr>
                        <tr><td class="l" colspan="2">Percentage of Enrolment as of <b>end of month</b></td><td>{{ $pct($sum['percentEnrolment']['male']) }}</td><td>{{ $pct($sum['percentEnrolment']['female']) }}</td><td>{{ $pct($sum['percentEnrolment']['total']) }}</td></tr>
                        <tr><td class="l" colspan="2">Average Daily Attendance</td><td>{{ $whole($sum['avgDaily']['male']) }}</td><td>{{ $whole($sum['avgDaily']['female']) }}</td><td>{{ $whole($sum['avgDaily']['total']) }}</td></tr>
                        <tr><td class="l" colspan="2">Percentage of Attendance for the month</td><td>{{ $pct($sum['percentAttendance']['male']) }}</td><td>{{ $pct($sum['percentAttendance']['female']) }}</td><td>{{ $pct($sum['percentAttendance']['total']) }}</td></tr>
                        <tr><td class="l" colspan="2">Number of students absent for 5 consecutive days</td><td>{{ $sum['absent5']['male'] }}</td><td>{{ $sum['absent5']['female'] }}</td><td>{{ $sum['absent5']['total'] }}</td></tr>
                        <tr><td class="l" colspan="2">NLS</td><td>{{ $sum['nls']['male'] }}</td><td>{{ $sum['nls']['female'] }}</td><td>{{ $sum['nls']['total'] }}</td></tr>
                        <tr><td class="l" colspan="2">Transferred out</td><td>{{ $sum['transferredOut']['male'] }}</td><td>{{ $sum['transferredOut']['female'] }}</td><td>{{ $sum['transferredOut']['total'] }}</td></tr>
                        <tr><td class="l" colspan="2">Transferred in</td><td>{{ $sum['transferredIn']['male'] }}</td><td>{{ $sum['transferredIn']['female'] }}</td><td>{{ $sum['transferredIn']['total'] }}</td></tr>
                    </table>

                    <p style="margin-top:10px"><i>I certify that this is a true and correct report.</i></p>
                    <div class="sig">
                        <span class="name">{{ $adviser ?? '' }}</span>
                        <div class="cap">(Signature of Adviser over Printed Name)</div>
                    </div>
                    <p style="margin-top:6px">Attested by:</p>
                    <div class="sig">
                        <span class="name">@if(!empty($schoolHead)){{ $schoolHead }}@else&nbsp;@endif</span>
                        <div class="cap">(Signature of School Head over Printed Name)</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
