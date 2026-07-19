@php
    // The section's school, falling back to the logged-in teacher's school so
    // the form always carries the branding of the school they belong to.
    $school = $school ?? \App\Support\ReportSchool::for($section);

    // Every grid column, so the sex band rows span the full table.
    $totalCols = 12;

    $blocks = [
        ['label' => 'MALE', 'rows' => $males],
        ['label' => 'FEMALE', 'rows' => $females],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF8 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #fff; color: #000; }
        .sheet { background: #fff; padding: 0; }

        .tag { font-size: 9px; font-weight: bold; }

        /* Narrow centered band so both seals sit right beside the title rather
           than out at the page corners. Matches SF1/SF2 exactly. */
        .head-row { width: 72%; margin: 0 auto; border-collapse: collapse; }
        .head-row td { vertical-align: middle; }
        .logo { width: 56px; }
        .logo img.seal { height: 58px; }
        .logo img.deped { height: 48px; }
        .title { text-align: center; }
        .title .dept { font-size: 11px; margin: 0; }
        .title h1 { font-size: 12.5px; font-weight: bold; margin: 1px 0 0; }
        .title .sub { font-size: 9px; margin: 1px 0 0; }

        .meta { width: 100%; font-size: 9px; border-collapse: collapse; margin-top: 6px; }
        .meta td { padding: 2px 3px; white-space: nowrap; }
        .val { border-bottom: 1px solid #000; display: inline-block; min-width: 55px; text-align: center; font-weight: bold; padding: 0 6px; }

        table.grid { border-collapse: collapse; width: 100%; margin-top: 5px; table-layout: fixed; }
        .grid th, .grid td { border: 1px solid #000; font-size: 7px; text-align: center; padding: 1px; height: 13px; word-wrap: break-word; }
        .grid th { font-weight: bold; background: #f0f0f0; }
        .grid th.band, .grid td.band { background: #d9d9d9; font-size: 8px; font-weight: bold; letter-spacing: 1px; text-align: left; padding-left: 4px; }
        .grid td.l { text-align: left; padding-left: 2px; }

        /* Column widths from the official worksheet's own column widths. */
        .c-no { width: 3.6%; } .c-lrn { width: 8.3%; } .c-name { width: 20.3%; }
        .c-bday { width: 9.3%; } .c-age { width: 4.8%; } .c-weight { width: 7%; }
        .c-height { width: 6.3%; } .c-hsq { width: 6%; } .c-bmi { width: 5.9%; }
        .c-bmicat { width: 7.2%; } .c-hfa { width: 7.3%; } .c-remarks { width: 14%; }

        /* Summary table: SEX | five nutritional-status columns + total | four HFA columns + total. */
        .sumhead { font-size: 9px; font-weight: bold; letter-spacing: 1px; margin: 10px 0 2px; }
        table.sumtab { border-collapse: collapse; width: 100%; table-layout: fixed; }
        .sumtab th, .sumtab td { border: 1px solid #000; font-size: 7px; text-align: center; padding: 1px 2px; height: 14px; word-wrap: break-word; }
        .sumtab th { font-weight: bold; background: #f0f0f0; }
        .sumtab td.l { text-align: left; padding-left: 4px; font-weight: bold; }

        .foot { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 8px; table-layout: fixed; }
        .foot td { vertical-align: bottom; padding: 0 6px; }
        .foot .line { border-bottom: 1px solid #000; text-align: center; font-weight: bold; padding: 0 4px 1px; min-height: 11px; }
        .foot .cap { font-size: 7.5px; padding-top: 1px; }

        .sfrt { text-align: right; font-size: 8px; font-style: italic; margin-top: 14px; }

        @page { size: a4 portrait; margin: 5mm; }
    </style>
</head>
<body>
    <div class="sheet">
        {{-- ===== Title ===== --}}
        @php
            // DomPDF needs local filesystem paths, not URLs. School's uploaded
            // logo (stored directly in public/) first, the bundled seal as fallback.
            $schoolLogo = \App\Support\ReportSchool::logoPath($school);
            $depedLogo = public_path('DepED-Logo.png');
        @endphp
        <div class="tag">SF 8</div>
        <table class="head-row">
            <tr>
                <td class="logo">
                    @if (file_exists($schoolLogo))<img class="seal" src="{{ $schoolLogo }}" alt="School logo">@endif
                </td>
                <td class="title">
                    <p class="dept">Department of Education</p>
                    <h1>School Form 8 Learner's Basic Health and Nutrition Report (SF8)</h1>
                    <p class="sub">(For All Grade Levels)</p>
                </td>
                <td class="logo" style="text-align:right">
                    @if (file_exists($depedLogo))<img class="deped" src="{{ $depedLogo }}" alt="DepEd">@endif
                </td>
            </tr>
        </table>

        {{-- ===== Meta: two rows, exactly as the form lays them out ===== --}}
        <table class="meta">
            <tr>
                <td><span class="lbl">School Name</span> <span class="val" style="min-width:170px">{{ $school?->name ?? config('app.name') }}</span></td>
                <td><span class="lbl">District</span> <span class="val">{{ $district ?? '' }}</span></td>
                <td><span class="lbl">Division</span> <span class="val">{{ $school?->division ?? '' }}</span></td>
                <td><span class="lbl">Region</span> <span class="val">{{ $school?->region ?? '' }}</span></td>
            </tr>
            <tr>
                <td><span class="lbl">School ID</span> <span class="val">{{ $school?->school_id ?? '' }}</span></td>
                <td>
                    <span class="lbl">Grade</span> <span class="val">{{ $section->gradeLevel->name }}</span>
                    <span class="lbl" style="padding-left:10px">Section</span> <span class="val">{{ $section->name }}</span>
                </td>
                <td><span class="lbl">Track/Strand (SHS)</span> <span class="val">{{ $track ?? '' }}</span></td>
                <td><span class="lbl">School Year</span> <span class="val">{{ $schoolYear?->name }}</span></td>
            </tr>
        </table>

        {{-- ===== Roster: measurement columns stay blank for the weighing session ===== --}}
        <table class="grid">
            <colgroup>
                <col class="c-no"><col class="c-lrn"><col class="c-name"><col class="c-bday">
                <col class="c-age"><col class="c-weight"><col class="c-height"><col class="c-hsq">
                <col class="c-bmi"><col class="c-bmicat"><col class="c-hfa"><col class="c-remarks">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">LRN</th>
                    <th rowspan="2">Learner's Name<br>(Last Name, First Name, Name Extension, Middle Name)</th>
                    <th rowspan="2">Birthdate<br>(MM/DD/YYYY)</th>
                    <th rowspan="2">Age</th>
                    <th rowspan="2">Weight<br>(kg)</th>
                    <th rowspan="2">Height<br>(m)</th>
                    <th rowspan="2">Height&sup2;<br>(m&sup2;)</th>
                    <th colspan="2">Nutritional Status</th>
                    <th rowspan="2">Height for Age (HFA)</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr>
                    <th>BMI<br>(kg/m&sup2;)</th>
                    <th>BMI Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($blocks as $block)
                    <tr><td class="band" colspan="{{ $totalCols }}">{{ $block['label'] }}</td></tr>
                    @forelse ($block['rows'] as $row)
                        <tr>
                            <td>{{ $row['no'] }}</td>
                            <td>{{ $row['lrn'] }}</td>
                            <td class="l">{{ $row['name'] }}</td>
                            <td>{{ $row['birthdate'] }}</td>
                            <td>{{ $row['age'] }}</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $totalCols }}" style="font-style:italic">No {{ strtolower($block['label']) }} learners on the register.</td></tr>
                    @endforelse
                @endforeach
            </tbody>
        </table>

        {{-- ===== Summary table: counts are tallied by hand after the assessment ===== --}}
        <div class="sumhead">SUMMARY TABLE</div>
        <table class="sumtab">
            <colgroup>
                <col style="width:11%">
                <col style="width:9.5%"><col style="width:8%"><col style="width:8%"><col style="width:9%"><col style="width:8%"><col style="width:8%">
                <col style="width:9.5%"><col style="width:8%"><col style="width:8%"><col style="width:7%"><col style="width:6%">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">SEX</th>
                    <th colspan="6">Nutritional Status<br>Summary Table</th>
                    <th colspan="5">Height for Age (HFA)<br>Summary Table</th>
                </tr>
                <tr>
                    <th>Severely Wasted</th>
                    <th>Wasted</th>
                    <th>Normal</th>
                    <th>Overweight</th>
                    <th>Obese</th>
                    <th>TOTAL</th>
                    <th>Severely Stunted</th>
                    <th>Stunted</th>
                    <th>Normal</th>
                    <th>Tall</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="l">MALE</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td class="l">FEMALE</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td class="l">TOTAL</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            </tbody>
        </table>

        {{-- ===== Signatories: four blocks across, exactly as the form ends ===== --}}
        <table class="foot">
            <tr>
                <td style="width:22%">
                    <div class="cap">Date of Assessment:</div>
                    <div class="line">@if ($assessmentDate){{ $assessmentDate->format('m/d/Y') }}@else&nbsp;@endif</div>
                </td>
                <td style="width:26%">
                    <div class="cap">Conducted/Assessed By:</div>
                    <div class="line">@if (filled($assessedBy ?? null)){{ $assessedBy }}@else&nbsp;@endif</div>
                </td>
                <td style="width:26%">
                    <div class="cap">Certified Correct By:</div>
                    <div class="line">@if (filled($certifiedBy ?? null)){{ $certifiedBy }}@else&nbsp;@endif</div>
                </td>
                <td style="width:26%">
                    <div class="cap">Reviewed By:</div>
                    <div class="line">@if (filled($reviewedBy ?? null)){{ $reviewedBy }}@else&nbsp;@endif</div>
                </td>
            </tr>
        </table>

        <div class="sfrt">SFRT 2017</div>
    </div>
</body>
</html>
