<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF10 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        @page { size: letter portrait; margin: 6mm 8mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 6.4px; color: #000; margin: 0; }

        .rec { page-break-after: always; }
        .rec.tail { page-break-after: auto; }

        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; }

        /* Top strip: SF10-ES tag + "Page 2 of ___" */
        .toprow td { padding: 0 0 2px; font-size: 7px; }
        .tag { font-weight: bold; }
        .pageno { text-align: right; }

        .band {
            background: #d9d9d9; border: 0.7px solid #000; font-weight: bold;
            text-transform: uppercase; text-align: center; padding: 1.5px; font-size: 7px;
        }

        /* The two half-columns of the scholastic grid. */
        .grid { border: 0.7px solid #000; border-top: 0; }
        .grid > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
        .grid .gut { border-left: 0.7px solid #000; }

        .meta td { padding: 1.5px 3px; white-space: nowrap; }
        .u { display: inline-block; border-bottom: 0.5px solid #000; min-width: 40px; height: 7px; vertical-align: bottom; }
        .u.fill { font-weight: bold; padding: 0 2px; }

        .la th, .la td { border: 0.5px solid #000; padding: 0.8px 2px; height: 10px; }
        .la th { font-weight: bold; text-align: center; font-size: 6px; }
        .la td.area { text-align: left; }
        .la td.area.ind { padding-left: 10px; font-style: italic; }
        .la td.n { text-align: center; }
        .la td.b { font-weight: bold; }
        .rowh { border-top: 0.7px solid #000; }

        .rem td, .rem th { border: 0.5px solid #000; padding: 0.8px 2px; height: 10px; font-size: 6px; }
        .rem th { font-weight: bold; text-align: center; }
        .rem .hd { text-align: left; font-weight: bold; border-bottom: 0; }

        /* Certification */
        .transfer { font-weight: bold; margin: 3px 0 1px; font-size: 6.6px; }
        .cert { border: 0.7px solid #000; margin-bottom: 3px; }
        .cert .cband { background: #d9d9d9; text-align: center; font-weight: bold; padding: 1.5px; font-size: 7px; border-bottom: 0.5px solid #000; }
        .cert .line td { padding: 2px 4px; }
        .cert .b { font-weight: bold; }
        .sig td { padding: 10px 4px 1px; text-align: center; font-size: 6px; }
        .sig .sl { border-top: 0.5px solid #000; padding-top: 1px; }
        .foot td { padding-top: 2px; font-size: 6px; }
        .foot .r { text-align: right; font-style: italic; }
    </style>
</head>
<body>
@php
    $sy = $schoolYear;
    $schoolName = $school?->name ?? '';
    $depedId = $school?->school_id ?? '';
    $region = $school?->region ?? '';
    $division = $school?->division ?? '';
    $grade = $section->gradeLevel->name;
    $secName = $section->name;
    $adviser = optional($section->adviser)->full_name ?? '';
    $district = $district ?? '';

    // Static learning-area labels for the un-filled template blocks. The first
    // (top-left) block is filled per learner; the rest print blank, exactly as
    // the official form pre-prints them (EPP on the left, TLE on the right).
    $stdLabels = fn ($tle = false) => [
        ['Filipino', false], ['English', false], ['Mathematics', false], ['Science', false],
        ['GMRC (Good Manners and Right Conduct)', false], ['Araling Panlipunan', false],
        [$tle ? 'TLE' : 'EPP', false], ['MAPEH', false],
        ['Music & Arts', true], ['Physical Education & Health', true],
        ['', false], ['', false], ['', false],
        ['*Arabic Language', false], ['*Islamic Values Education', false],
    ];
@endphp

@foreach ($learners as $L)
    <div class="rec @if ($loop->last) tail @endif">
        {{-- ===== Top strip ===== --}}
        <table class="toprow">
            <tr>
                <td class="tag">SF10-ES</td>
                <td class="pageno">Page 2 of _______</td>
            </tr>
        </table>

        <div class="band">Scholastic Record</div>

        {{-- ===== 2x2 grid of scholastic blocks ===== --}}
        <table class="grid">
            {{-- --- Top row: filled (left) + blank template (right) --- --}}
            <tr>
                <td>
                    @include('reports.sf10.partials.block', [
                        'caps' => true, 'tle' => false, 'rows' => $L['areas'],
                        'ga' => $L['generalAverage'], 'gaRemark' => $L['generalRemark'],
                        'school' => $schoolName, 'schoolId' => $depedId, 'district' => $district,
                        'division' => $division, 'region' => $region, 'grade' => $grade,
                        'section' => $secName, 'sy' => $sy?->name, 'adviser' => $adviser,
                    ])
                </td>
                <td class="gut">
                    @include('reports.sf10.partials.block', [
                        'caps' => false, 'tle' => true, 'rows' => null, 'labels' => $stdLabels(true),
                        'ga' => null, 'gaRemark' => '',
                        'school' => '', 'schoolId' => '', 'district' => '', 'division' => '',
                        'region' => '', 'grade' => '', 'section' => '', 'sy' => '', 'adviser' => '',
                    ])
                </td>
            </tr>
            {{-- --- Bottom row: two blank, tall generic blocks --- --}}
            <tr>
                <td>
                    @include('reports.sf10.partials.block', [
                        'caps' => false, 'tle' => false, 'rows' => null, 'labels' => null, 'blankTall' => true,
                        'ga' => null, 'gaRemark' => '',
                        'school' => '', 'schoolId' => '', 'district' => '', 'division' => '',
                        'region' => '', 'grade' => '', 'section' => '', 'sy' => '', 'adviser' => '',
                    ])
                </td>
                <td class="gut">
                    @include('reports.sf10.partials.block', [
                        'caps' => false, 'tle' => false, 'rows' => null, 'labels' => null, 'blankTall' => true,
                        'ga' => null, 'gaRemark' => '',
                        'school' => '', 'schoolId' => '', 'district' => '', 'division' => '',
                        'region' => '', 'grade' => '', 'section' => '', 'sy' => '', 'adviser' => '',
                    ])
                </td>
            </tr>
        </table>

        {{-- ===== Certification ===== --}}
        <div class="transfer">For Transfer Out /Elementary School Completer Only</div>
        @php
            $nextGrade = (int) preg_replace('/\D/', '', $grade);
            $nextGrade = $nextGrade > 0 ? (string) ($nextGrade + 1) : '';
            $certName = trim($L['lastName'].', '.$L['firstName'].' '.($L['middleName'] ?? ''));
        @endphp
        @foreach (range(1, 3) as $c)
            <table class="cert">
                <tr><td colspan="3" class="cband">Certification</td></tr>
                <tr class="line">
                    <td colspan="3">
                        <span class="b">I CERTIFY that this is a true record of</span>
                        <span class="u fill" style="min-width:150px">{{ $loop->first ? $certName : '' }}</span>
                        <span class="b">with LRN</span>
                        <span class="u fill" style="min-width:95px">{{ $loop->first ? $L['lrn'] : '' }}</span>
                        <span class="b">and that he/she is eligible for addmision to Grade</span>
                        <span class="u fill" style="min-width:34px">{{ $loop->first ? $nextGrade : '' }}</span> .
                    </td>
                </tr>
                <tr class="line">
                    <td colspan="3">
                        <span class="b">School Name:</span> <span class="u" style="min-width:150px">&nbsp;</span>
                        &nbsp;<span class="b">School ID</span> <span class="u" style="min-width:60px">&nbsp;</span>
                        &nbsp;<span class="b">Division</span> <span class="u" style="min-width:70px">&nbsp;</span>
                        &nbsp;<span class="b">Last School Year Attended:</span> <span class="u" style="min-width:80px">&nbsp;</span>
                    </td>
                </tr>
                <tr class="sig">
                    <td style="width:30%"><div class="sl">Date</div></td>
                    <td style="width:45%"><div class="sl">Signature of Principal/School Head over Printed Name</div></td>
                    <td style="width:25%; vertical-align:bottom; text-align:center">(Affix School Seal here)</td>
                </tr>
            </table>
        @endforeach

        <table class="foot">
            <tr>
                <td>May add Certification Box if needed</td>
                <td class="r">Revised 2025 based on DepEd Order No. 10, s. 2024</td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
