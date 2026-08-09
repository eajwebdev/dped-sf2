@php
    $school = $school ?? \App\Support\ReportSchool::for($section);
    $totalCols = 19;
    $blocks = [
        ['label' => 'MALE', 'rows' => $males],
        ['label' => 'FEMALE', 'rows' => $females],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF1 - {{ $section->gradeLevel->name }} {{ $section->name }}</title>
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
        .title h1 { font-size: 14px; font-weight: bold; margin: 0; }
        .title .sub { font-size: 8.5px; font-style: italic; margin: 1px 0 0; }

        .meta { width: 100%; border-collapse: collapse; font-size: 7.2px; table-layout: fixed; margin-top: 5px; }
        .meta td { padding: 1px 3px; height: 22px; white-space: nowrap; vertical-align: middle; }
        .meta .lbl { border: 0; font-weight: normal; text-align: right; }
        .meta .val { border: 1px solid #000; font-size: 10px; font-weight: normal; text-align: center; }

        table.grid { border-collapse: collapse; width: 100%; table-layout: fixed; margin-top: 0; }
        .grid th, .grid td { border: 1px solid #000; font-size: 5.5px; text-align: center; padding: 1px; height: 16px; line-height: 1.08; word-wrap: break-word; }
        .grid th { font-weight: bold; }
        .grid td.l { text-align: left; padding-left: 2px; }
        .grid .learner td { height: 20px; }
        .grid .totrow td { font-weight: bold; background: #f7f7f7; }

        .c-lrn { width: 7%; } .c-name { width: 10.6%; } .c-sex { width: 2.3%; }
        .c-bday { width: 4.8%; } .c-age { width: 3.5%; } .c-tongue { width: 6.4%; }
        .c-ip { width: 4%; } .c-religion { width: 4.8%; }
        .c-street { width: 6.1%; } .c-brgy { width: 4.9%; } .c-city { width: 4.9%; } .c-prov { width: 4.9%; }
        .c-father { width: 7.2%; } .c-mother { width: 7.2%; }
        .c-guardian { width: 5.5%; } .c-rel { width: 4.2%; } .c-contact { width: 5%; }
        .c-modality { width: 5%; } .c-remarks { width: 5.7%; }

        .sf1-footer { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 4px; font-size: 5.6px; text-transform: none; }
        .sf1-footer > tbody > tr > td { vertical-align: top; padding: 0 5px 0 0; }
        .sf1-footer .legend-cell { width: 51%; }
        .sf1-footer .registered-cell { width: 12%; }
        .sf1-footer .prepared-cell { width: 15%; }
        .sf1-footer .certified-cell { width: 17%; padding-right: 0; }
        .legend-title { border-top: 1px solid #000; font-size: 8px; font-weight: bold; text-align: center; padding: 2px 0; }
        .legend-grid { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 5.4px; border: 1.5px solid #000; }
        .legend-grid th, .legend-grid td { border: 1px solid #000; padding: 1px 2px; vertical-align: top; line-height: 1.08; }
        .legend-grid th { text-align: left; font-weight: bold; }
        .registered-grid { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 5.5px; border: 1.5px solid #000; }
        .registered-grid th, .registered-grid td { border: 1px solid #000; padding: 2px; height: 15px; text-align: center; vertical-align: middle; }
        .registered-grid th { font-weight: bold; }
        .sign-box { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 5.5px; }
        .sign-box td { border: 0.5px solid #d9d9d9; padding: 2px 3px; height: 16px; vertical-align: top; }
        .sign-box .label { font-weight: bold; }
        .sign-box .name { text-align: center; height: 22px; vertical-align: bottom; text-transform: uppercase; }
        .sign-box .cap { border-top: 1.5px solid #000; text-align: center; font-weight: bold; line-height: 1.05; }
        .sign-box .date { height: 16px; border-bottom: 1.5px solid #000; font-weight: bold; }
        .lis-line { border-top: 1.5px solid #000; margin-top: 7px; padding-top: 2px; text-align: center; font-size: 5.4px; }
        .sf1-generated { margin-top: 16px; font-size: 7px; text-transform: none; }

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
                    <h1>School Form 1 (SF1) School Register</h1>
                    <p class="sub">(This replaces Form 1, Master List &amp; STS Form 2-Family Background and Profile)</p>
                </td>
                <td class="logo" style="text-align:right; vertical-align:top">
                    @if (file_exists($depedLogo))<img class="deped" src="{{ $depedLogo }}" alt="DepEd">@endif
                </td>
            </tr>
        </table>

        <table class="meta">
            <colgroup>
                <col style="width:14%"><col style="width:10%">
                <col style="width:8%"><col style="width:13%">
                <col style="width:8%"><col style="width:12%">
                <col style="width:7%"><col style="width:8%">
                <col style="width:6%"><col style="width:14%">
            </colgroup>
            <tr>
                <td class="lbl">School ID</td>
                <td class="val">{{ $school?->school_id ?? '' }}</td>
                <td class="val" colspan="2">{{ $school?->region ?? '' }}</td>
                <td class="lbl">Division</td>
                <td class="val" colspan="5">{{ $school?->division ?? '' }}</td>
            </tr>
            <tr>
                <td class="lbl">School Name</td>
                <td colspan="3" class="val">{{ $school?->name ?? config('app.name') }}</td>
                <td class="lbl">School Year</td>
                <td class="val">{{ $schoolYear?->name }}</td>
                <td class="lbl">Grade Level</td>
                <td class="val">{{ $section->gradeLevel->name }}</td>
                <td class="lbl">Section</td>
                <td class="val">{{ $section->name }}</td>
            </tr>
        </table>

        <table class="grid">
            <colgroup>
                <col class="c-lrn"><col class="c-name"><col class="c-sex"><col class="c-bday"><col class="c-age">
                <col class="c-tongue"><col class="c-ip"><col class="c-religion">
                <col class="c-street"><col class="c-brgy"><col class="c-city"><col class="c-prov">
                <col class="c-father"><col class="c-mother"><col class="c-guardian"><col class="c-rel"><col class="c-contact">
                <col class="c-modality"><col class="c-remarks">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">LRN</th>
                    <th rowspan="2">NAME<br>(Last Name, First Name, Middle Name)</th>
                    <th rowspan="2">Sex<br>(M/F)</th>
                    <th rowspan="2">BIRTH DATE<br>(mm/dd/yyyy)</th>
                    <th rowspan="2">AGE as of<br>1st Friday June</th>
                    <th rowspan="2">MOTHER TONGUE<br>(Grade 1 to 3 Only)</th>
                    <th rowspan="2">IP<br>(Ethnic Group)</th>
                    <th rowspan="2">RELIGION</th>
                    <th colspan="4">ADDRESS</th>
                    <th colspan="2">PARENTS</th>
                    <th colspan="2">GUARDIAN (if Not Parent)</th>
                    <th rowspan="2">Contact Number of Parent or Guardian</th>
                    <th rowspan="2">Learning Modality</th>
                    <th rowspan="2">REMARKS<br><span style="font-weight:normal">(Please refer to the legend on last page)</span></th>
                </tr>
                <tr>
                    <th>House #/ Street/ Sitio/ Purok</th>
                    <th>Barangay</th>
                    <th>Municipality/ City</th>
                    <th>Province</th>
                    <th>Father's Name<br>(Last Name, First Name, Middle Name)</th>
                    <th>Mother's Maiden Name<br>(Last Name, First Name, Middle Name)</th>
                    <th>Name</th>
                    <th>Relationship</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($blocks as $block)
                    @forelse ($block['rows'] as $row)
                        <tr class="learner">
                            <td>{{ $row['lrn'] }}</td>
                            <td class="l">{{ $row['name'] }}</td>
                            <td>{{ $row['sex'] }}</td>
                            <td>{{ $row['birthdate'] }}</td>
                            <td>{{ $row['age'] }}</td>
                            <td class="l">{{ $row['mother_tongue'] }}</td>
                            <td class="l">{{ $row['ethnic_group'] }}</td>
                            <td class="l">{{ $row['religion'] }}</td>
                            <td class="l">{{ $row['street'] }}</td>
                            <td class="l">{{ $row['barangay'] }}</td>
                            <td class="l">{{ $row['municipality'] }}</td>
                            <td class="l">{{ $row['province'] }}</td>
                            <td class="l">{{ $row['father'] }}</td>
                            <td class="l">{{ $row['mother'] }}</td>
                            <td class="l">{{ $row['guardian'] }}</td>
                            <td class="l">{{ $row['relationship'] }}</td>
                            <td>{{ $row['contact'] }}</td>
                            <td>{{ $row['learning_modality'] }}</td>
                            <td class="l">{{ $row['remarks'] }}</td>
                        </tr>
                    @empty
                        <tr><td>&nbsp;</td><td colspan="{{ $totalCols - 1 }}" class="l" style="font-style:italic">No {{ strtolower($block['label']) }} learners on the register.</td></tr>
                    @endforelse

                    <tr class="totrow">
                        <td>{{ count($block['rows']) }}</td>
                        <td colspan="{{ $totalCols - 1 }}" class="l">&lt;=== TOTAL {{ $block['label'] }}</td>
                    </tr>
                @endforeach

                <tr class="totrow">
                    <td>{{ count($males) + count($females) }}</td>
                    <td colspan="{{ $totalCols - 1 }}" class="l">&lt;=== COMBINED</td>
                </tr>
            </tbody>
        </table>

        <table class="sf1-footer">
            <tr>
                <td class="legend-cell">
                    <div class="legend-title">List and Code of Indicators under REMARKS column</div>
                    <table class="legend-grid">
                        <colgroup>
                            <col style="width:12%"><col style="width:5%"><col style="width:32%">
                            <col style="width:13%"><col style="width:5%"><col style="width:33%">
                        </colgroup>
                        <tr>
                            <th>Indicator</th><th>Code</th><th>Required Information</th>
                            <th>Indicator</th><th>Code</th><th>Required Information</th>
                        </tr>
                        <tr>
                            <td>Transfered Out</td><td>T/O</td><td>Name of Public (P) Private (PR) School &amp; Effectivity Date</td>
                            <td>CCT Receipient</td><td>CCT</td><td>CCT Control/reference number &amp; Effectivity Date</td>
                        </tr>
                        <tr>
                            <td>Transfered In</td><td>T/I</td><td>Name of Public (P) Private (PR) School &amp; Effectivity Date</td>
                            <td>Balik Aral</td><td>B/A</td><td>Name of school last attended &amp; Year</td>
                        </tr>
                        <tr>
                            <td>Dropped</td><td>DRP</td><td>Reason and Effectivity Date</td>
                            <td>Special Needs Education</td><td>SNED</td><td>Specify</td>
                        </tr>
                        <tr>
                            <td>Late Enrollment</td><td>LE</td><td>Reason (Enrollment beyond 1st Friday of SY)</td>
                            <td>Accelerated</td><td>ACL</td><td>Specify Level &amp; Effectivity Data</td>
                        </tr>
                    </table>
                </td>
                <td class="registered-cell">
                    <table class="registered-grid">
                        <tr><th>REGISTERED</th><th>BoSY</th><th>EoSY</th></tr>
                        <tr><th>MALE</th><td>{{ $summary['male']['bosy'] }}</td><td>{{ $summary['male']['eosy'] }}</td></tr>
                        <tr><th>FEMALE</th><td>{{ $summary['female']['bosy'] }}</td><td>{{ $summary['female']['eosy'] }}</td></tr>
                        <tr><th>TOTAL</th><td>{{ $summary['total']['bosy'] }}</td><td>{{ $summary['total']['eosy'] }}</td></tr>
                    </table>
                </td>
                <td class="prepared-cell">
                    <table class="sign-box">
                        <tr><td class="label">Prepared by;</td></tr>
                        <tr><td class="name">{{ $adviser ?? '' }}</td></tr>
                        <tr><td class="cap">(Signature of Adviser over Printed Name)</td></tr>
                        <tr><td class="date">BoSY Date: <span style="float:right">EoSY Date:</span></td></tr>
                    </table>
                </td>
                <td class="certified-cell">
                    <table class="sign-box">
                        <tr><td class="label">Certified Correct:</td></tr>
                        <tr><td class="name">@if (filled($schoolHead ?? null)){{ $schoolHead }}@else&nbsp;@endif</td></tr>
                        <tr><td class="cap">(Signature of School Head over Printed Name)</td></tr>
                        <tr><td class="date">BoSY Date: <span style="float:right">EoSY Date:</span></td></tr>
                    </table>
                    <div class="lis-line">Generated thru LIS</div>
                </td>
            </tr>
        </table>
        <div class="sf1-generated">Generated on: {{ now()->format('l, F j, Y') }}</div>
    </div>
</body>
</html>
