@php
    use App\Models\Attendance;

    $days = $dayColumns;
    $maxDayColumns = $data['maxColumns'] ?? 25;
    $daySlots = array_values(array_pad(array_slice($days, 0, $maxDayColumns), $maxDayColumns, null));
    $section = $data['section'];
    $sy = $data['schoolYear'];
    $sum = $data['summary'];
    $school = $school ?? \App\Support\ReportSchool::for($section);
    $adviser = $section->adviser?->full_name;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF2 - {{ $section->gradeLevel->name }} {{ $section->name }} - {{ $data['monthLabel'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #fff; color: #000; }
        .sheet { background: #fff; padding: 0; text-transform: uppercase; }
        .head-row { width: 48%; margin: 0 auto; }
        .head-row td { vertical-align: middle; }
        .logo { width: 56px; }
        .logo img.seal { height: 58px; }
        .logo img.deped { height: 48px; }
        .title { text-align: center; }
        .title h1 { font-size: 15px; font-weight: bold; margin: 0; }
        .title .sub { font-size: 7px; font-style: italic; margin: 1px 0 0; white-space: nowrap; }

        .meta { width: 100%; border-collapse: collapse; font-size: 7.2px; table-layout: fixed; margin-top: 5px; }
        .meta td { padding: 1px 3px; height: 22px; white-space: nowrap; vertical-align: middle; }
        .meta .lbl { border: 0; font-weight: normal; text-align: right; }
        .meta .val { border: 1px solid #000; font-size: 11px; font-weight: normal; text-align: center; }

        table.grid { border-collapse: collapse; width: 100%; table-layout: fixed; margin-top: 0; }
        .grid th, .grid td { border: 1px solid #000; font-size: 6.4px; text-align: center; padding: 0 1px; height: 15px; line-height: 1.08; }
        .grid th { font-weight: bold; }
        .grid td.name { text-align: left; padding-left: 2px; font-size: 6.8px; white-space: nowrap; }
        .grid .num { width: 3%; }
        .grid .col-name { width: 18%; }
        .grid .day { width: 1.55%; }
        .grid .tot { width: 5%; }
        .grid .remarks-col { width: 100px; }
        .grid td.remarks { text-align: left; }
        .grid .date-band { height: 13px; font-size: 6px; }
        .grid .side-head { font-size: 8px; line-height: 1.15; }
        .grid .remarks-head { font-size: 6px; line-height: 1.05; padding: 0 3px; vertical-align: middle; }
        .grid .remarks-head span { display: block; padding: 0 3px; font-size: 4.6px; line-height: 1.05; word-break: normal; overflow-wrap: break-word; }
        .grid .totrow td { font-weight: bold; background: #f7f7f7; }
        .grid td.name.totlabel { text-align: center; font-style: italic; }
        .absent { color: #b91c1c; font-weight: bold; }
        .tardy { color: #b45309; font-weight: bold; }

        .foot { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 3px; font-size: 5.6px; font-weight: normal; }
        .foot > tbody > tr > td { vertical-align: top; padding: 0 4px; }
        .foot .guidelines { width: 50%; }
        .foot .reasons-cell { width: 28%; }
        .foot .summary-cell { width: 22%; padding-right: 0; }
        .foot p { margin: 0 0 2px; line-height: 1.2; }
        .foot b { font-weight: bold; }
        .formulas { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 3px 0 4px; }
        .formulas td { border: 0.5px solid #d9d9d9; padding: 1px 2px; height: 13px; vertical-align: middle; }
        .formulas .eq { width: 38%; }
        .formulas .frac { width: 42%; text-align: center; border-bottom: 1px solid #000; }
        .formulas .times { width: 8%; text-align: center; }
        .formulas .blank { width: 12%; }
        .reasons-box { width: 100%; height: 248px; border-collapse: collapse; table-layout: fixed; font-size: 5.6px; border: 1.5px solid #000; }
        .reasons-box td { border: 1px solid #000; padding: 2px 3px; vertical-align: top; line-height: 1.04; }
        .reasons-box .section-title { font-weight: bold; }
        .generated-date { height: 13px; margin-top: 28px; text-align: center; font-size: 5px; line-height: 13px; }
        .lis { border-top: 1.5px solid #000; margin-top: 0; padding-top: 2px; text-align: center; line-height: 8px; }

        .sumtab { border-collapse: collapse; width: 100%; font-size: 5.6px; table-layout: fixed; border: 1.5px solid #000; }
        .sumtab th, .sumtab td { border: 1px solid #000; padding: 1px 2px; text-align: center; height: 14px; font-weight: normal; }
        .sumtab th { font-weight: bold; }
        .sumtab td.l { text-align: left; }
        .sumtab .label { width: 58%; font-style: italic; }
        .sumtab .small-head { font-weight: bold; text-align: left; }
        .sumtab .tall td { height: 22px; }
        .cert { margin-top: 21px; font-size: 8px; font-style: italic; text-transform: none; }
        .attested { margin-top: 28px; font-size: 8px; font-style: italic; text-transform: none; }
        .sig { text-align: center; margin: 27px 4px 0; }
        .sig .name { font-size: 8px; font-weight: normal; border-bottom: 1.5px solid #000; padding: 0 8px 1px; display: block; min-width: 120px; }
        .sig .cap { font-size: 5.4px; font-style: normal; }

        @page { size: A4 landscape; margin: 5mm; }
    </style>
</head>
<body>
    <div class="sheet">
        @php
            $schoolLogo = \App\Support\ReportSchool::logoPath($school);
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
                <td class="logo" style="text-align:right; vertical-align:top">
                    @if (file_exists($depedLogo))<img class="deped" src="{{ $depedLogo }}" alt="DepEd">@endif
                </td>
            </tr>
        </table>

        <table class="meta">
            <colgroup>
                <col style="width:19%"><col style="width:7%">
                <col style="width:7%"><col style="width:15%">
                <col style="width:13%"><col style="width:14%">
                <col style="width:6%"><col style="width:19%">
            </colgroup>
            <tr>
                <td class="lbl">School ID</td>
                <td class="val">{{ $school?->school_id ?? '' }}</td>
                <td class="lbl">School Year</td>
                <td class="val">{{ $sy->name }}</td>
                <td class="lbl">Report for the Month of</td>
                <td class="val" colspan="3">{{ strtoupper($data['month']->format('F')) }}</td>
            </tr>
            <tr>
                <td class="lbl">Name of School</td>
                <td colspan="3" class="val">{{ $school?->name ?? config('app.name') }}</td>
                <td class="lbl">Grade Level</td>
                <td class="val">{{ $section->gradeLevel->name }}</td>
                <td class="lbl">Section</td>
                <td class="val">{{ $section->name }}</td>
            </tr>
        </table>

        <table class="grid">
            <colgroup>
                <col class="num" style="width:3%"><col class="col-name" style="width:18%">
                @foreach ($daySlots as $slot)<col class="day" style="width:1.55%">@endforeach
                <col class="tot" style="width:5%"><col class="tot" style="width:5%"><col class="remarks-col" style="width:25.25%">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="3">No.</th>
                    <th rowspan="3" class="col-name">NAME<br>(Last Name, First Name, Middle Name)</th>
                    <th colspan="{{ $maxDayColumns }}" class="date-band">(1st row for date)</th>
                    <th colspan="2" rowspan="2" class="side-head">Total for the<br>Month</th>
                    <th rowspan="3" class="remarks-head">REMARKS<br><span>(If DROPPED OUT, state reason, please refer to legend number 2. If TRANSFERRED IN/OUT, write the name of School.)</span></th>
                </tr>
                <tr>
                    @foreach ($daySlots as $slot)<th class="day">{{ $slot['day'] ?? '' }}</th>@endforeach
                </tr>
                <tr>
                    @foreach ($daySlots as $slot)<th class="day">{{ isset($slot['letter']) ? strtoupper($slot['letter']) : '' }}</th>@endforeach
                    <th class="tot">ABSENT</th>
                    <th class="tot">PRESENT</th>
                </tr>
            </thead>
            <tbody>
                @include('reports.sf2.partials.rows', ['rows' => $data['males'], 'label' => 'MALE', 'totals' => $data['dailyTotals'], 'genderKey' => 'male', 'days' => $daySlots])
                @include('reports.sf2.partials.rows', ['rows' => $data['females'], 'label' => 'FEMALE', 'totals' => $data['dailyTotals'], 'genderKey' => 'female', 'days' => $daySlots])

                <tr class="totrow">
                    <td class="num">{{ count($data['males']) + count($data['females']) }}</td>
                    <td class="name totlabel">Combined TOTAL Per Day</td>
                    @foreach ($daySlots as $slot)
                        <td class="day">{{ isset($slot['date']) ? ($data['dailyTotals'][$slot['date']]['combined'] ?? 0) : '' }}</td>
                    @endforeach
                    <td class="tot">&nbsp;</td>
                    <td class="tot">{{ collect($data['males'])->sum('present') + collect($data['females'])->sum('present') }}</td>
                    <td class="remarks">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <table class="foot">
            <tr>
                <td class="guidelines">
                    <p><b>GUIDELINES:</b></p>
                    <p>1. The attendance shall be accomplished daily. Refer to the codes for checking learners' attendance.</p>
                    <p>2. Dates shall be written in the columns after Learner's Name.</p>
                    <p>3. To compute the following:</p>
                    <table class="formulas">
                        <tr>
                            <td class="eq">a. Percentage of Enrolment =</td>
                            <td class="frac">Registered Learners as of end of the month</td>
                            <td class="times" rowspan="2">x 100</td>
                            <td class="blank" rowspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td class="frac">Enrolment as of 1st Friday of the school year</td>
                        </tr>
                        <tr>
                            <td class="eq">b. Average Daily Attendance =</td>
                            <td class="frac">Total Daily Attendance</td>
                            <td class="times">&nbsp;</td>
                            <td class="blank">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td class="frac">Number of School Days in reporting month</td>
                            <td class="times">&nbsp;</td>
                            <td class="blank">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="eq">c. Percentage of Attendance for the month =</td>
                            <td class="frac">Average daily attendance</td>
                            <td class="times" rowspan="2">x 100</td>
                            <td class="blank" rowspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td class="frac">Registered Learners as of end of the month</td>
                        </tr>
                    </table>
                    <p>4. Every end of the month, the class adviser will submit this form to the office of the principal for recording of summary table into School Form 4. Once signed by the principal, this form should be returned to the adviser.</p>
                    <p>5. The adviser will provide neccessary interventions including but not limited to home visitation to learner/s who were absent for 5 consecutive days and/or those at risk of dropping out.</p>
                    <p>6. Attendance performance of learners will be reflected in Form 137 and Form 138 every grading period.</p>
                    <p style="margin-left:8px"><i>*Beginning of School Year cut-off report is every 1st Friday of the School Year</i></p>
                </td>

                <td class="reasons-cell">
                    <table class="reasons-box">
                        <tr><td><b>1. CODES FOR CHECKING ATTENDANCE</b></td></tr>
                        <tr><td><b>(blank)</b> - Present; <b class="absent">(x)</b>- Absent; Tardy (half shaded= Upper for Late Commer, Lower for Cutting Classes)</td></tr>
                        <tr><td><b>2. REASONS/CAUSES FOR NLS</b></td></tr>
                        <tr><td><span class="section-title">a. Domestic-Related Factors</span><br>a.1. Had to take care of siblings<br>a.2. Early marriage/pregnancy<br>a.3. Parents' attitude toward schooling<br>a.4. Family problems</td></tr>
                        <tr><td><span class="section-title">b. Individual-Related Factors</span><br>b.1. Illness<br>b.2. Overage<br>b.3. Death<br>b.4. Drug Abuse<br>b.5. Poor academic performance<br>b.6. Lack of interest/Distractions<br>b.7. Hunger/Malnutrition</td></tr>
                        <tr><td><span class="section-title">c. School-Related Factors</span><br>c.1. Teacher Factor<br>c.2. Physical condition of classroom<br>c.3. Peer influence</td></tr>
                        <tr><td><span class="section-title">d. Geographic/Environmental</span><br>d.1. Distance between home and school<br>d.2. Armed conflict (incl. Tribal wars &amp; clanfeuds)<br>d.3. Calamities/Disasters</td></tr>
                        <tr><td><span class="section-title">e. Financial-Related</span><br>e.1. Child labor, work</td></tr>
                        <tr><td><span class="section-title">f. Others (Specify)</span></td></tr>
                    </table>
                    <div class="generated-date">Generated on: {{ now()->format('l, F j, Y') }}</div>
                    <div class="lis">Generated thru LIS</div>
                </td>

                <td class="summary-cell">
                    @php
                        $pct = fn ($v) => round((float) $v * 100).'%';
                        $whole = fn ($v) => (string) round((float) $v);
                    @endphp
                    <table class="sumtab">
                        <tr>
                            <td class="small-head" colspan="2"><b>Month :</b> {{ strtoupper($data['month']->format('F')) }}</td>
                            <td class="small-head" colspan="2"><b>No. of Days of Classes:</b> {{ $sum['classDays'] }}</td>
                            <th colspan="3">Summary</th>
                        </tr>
                        <tr><td colspan="4">&nbsp;</td><th>M</th><th>F</th><th>TOTAL</th></tr>
                        <tr><td class="label" colspan="4">* Enrolment as of (1st Friday of June)</td><td>{{ $sum['enrolment']['male'] }}</td><td>{{ $sum['enrolment']['female'] }}</td><td>{{ $sum['enrolment']['total'] }}</td></tr>
                        <tr class="tall"><td class="label" colspan="4">Late enrolment during the month<br>(beyond cut-off)</td><td>{{ $sum['lateEnrolment']['male'] }}</td><td>{{ $sum['lateEnrolment']['female'] }}</td><td>{{ $sum['lateEnrolment']['total'] }}</td></tr>
                        <tr class="tall"><td class="label" colspan="4">Registered Learners as of<br>end of month</td><td>{{ $sum['registered']['male'] }}</td><td>{{ $sum['registered']['female'] }}</td><td>{{ $sum['registered']['total'] }}</td></tr>
                        <tr class="tall"><td class="label" colspan="4">Percentage of Enrolment as of<br>end of month</td><td>{{ $pct($sum['percentEnrolment']['male']) }}</td><td>{{ $pct($sum['percentEnrolment']['female']) }}</td><td>{{ $pct($sum['percentEnrolment']['total']) }}</td></tr>
                        <tr><td class="label" colspan="4">Average Daily Attendance</td><td>{{ $whole($sum['avgDaily']['male']) }}</td><td>{{ $whole($sum['avgDaily']['female']) }}</td><td>{{ $whole($sum['avgDaily']['total']) }}</td></tr>
                        <tr><td class="label" colspan="4">Percentage of Attendance for the month</td><td>{{ $pct($sum['percentAttendance']['male']) }}</td><td>{{ $pct($sum['percentAttendance']['female']) }}</td><td>{{ $pct($sum['percentAttendance']['total']) }}</td></tr>
                        <tr><td class="label" colspan="4">Number of students absent for 5 consecutive days</td><td>{{ $sum['absent5']['male'] }}</td><td>{{ $sum['absent5']['female'] }}</td><td>{{ $sum['absent5']['total'] }}</td></tr>
                        <tr><td class="label" colspan="4">Dropped out</td><td>{{ $sum['nls']['male'] }}</td><td>{{ $sum['nls']['female'] }}</td><td>{{ $sum['nls']['total'] }}</td></tr>
                        <tr><td class="label" colspan="4">Transferred out</td><td>{{ $sum['transferredOut']['male'] }}</td><td>{{ $sum['transferredOut']['female'] }}</td><td>{{ $sum['transferredOut']['total'] }}</td></tr>
                        <tr><td class="label" colspan="4">Transferred in</td><td>{{ $sum['transferredIn']['male'] }}</td><td>{{ $sum['transferredIn']['female'] }}</td><td>{{ $sum['transferredIn']['total'] }}</td></tr>
                    </table>

                    <p class="cert"><i>I certify that this is a true and correct report.</i></p>
                    <div class="sig">
                        <span class="name">{{ $adviser ?? '' }}</span>
                        <div class="cap">(Signature of Adviser over Printed Name)</div>
                    </div>
                    <p class="attested"><i>Attested by:</i></p>
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
