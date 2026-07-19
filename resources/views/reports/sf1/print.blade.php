@php
    // The section's school, falling back to the logged-in teacher's school so
    // the form always carries the branding of the school they belong to.
    $school = $school ?? $section->school ?? auth()->user()?->school;

    // Every grid column, so header bands and full-width rows stay in step.
    $totalCols = 20;

    $blocks = [
        ['label' => 'MALE', 'rows' => $males],
        ['label' => 'FEMALE', 'rows' => $females],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF1 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #fff; color: #000; }
        .sheet { background: #fff; padding: 0; }

        .head-row { width: 80%; margin: 0 auto; }
        .head-row td { vertical-align: middle; }
        .logo { width: 90px; }
        .logo img.seal { height: 54px; }
        .logo img.deped { height: 46px; }
        .title { text-align: center; }
        .title h1 { font-size: 14px; font-weight: bold; margin: 0; }
        .title .sub { font-size: 8.5px; font-style: italic; margin: 1px 0 0; }

        .meta { width: 100%; font-size: 10px; border-collapse: collapse; margin-top: 6px; }
        .meta td { padding: 3px 4px; white-space: nowrap; }
        .val { border-bottom: 1px solid #000; display: inline-block; min-width: 55px; text-align: center; font-weight: bold; padding: 0 6px; }

        table.grid { border-collapse: collapse; width: 100%; margin-top: 4px; table-layout: fixed; }
        .grid th, .grid td { border: 1px solid #000; font-size: 6.5px; text-align: center; padding: 1px; height: 15px; word-wrap: break-word; }
        .grid th { font-weight: bold; background: #f0f0f0; }
        .grid th.band { background: #d9d9d9; font-size: 8px; letter-spacing: 1px; text-align: left; padding-left: 4px; }
        .grid td.l { text-align: left; padding-left: 2px; }
        .grid .totrow td { font-weight: bold; background: #f4f4f4; }

        /* Column widths — the name, address and remarks columns carry the most text. */
        .c-no { width: 2%; } .c-lrn { width: 5.5%; } .c-name { width: 11%; }
        .c-sex { width: 2%; } .c-bday { width: 4%; } .c-age { width: 2%; }
        .c-bplace { width: 5.5%; } .c-tongue { width: 4.5%; } .c-ethnic { width: 4.5%; } .c-religion { width: 5%; }
        .c-street { width: 6.5%; } .c-brgy { width: 5%; } .c-muni { width: 5%; } .c-prov { width: 5%; }
        .c-parent { width: 7%; } .c-guardian { width: 6%; } .c-rel { width: 3.5%; } .c-contact { width: 4.5%; }
        .c-remarks { width: 8%; }

        .foot { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 7.5px; table-layout: fixed; }
        .foot > tbody > tr > td { vertical-align: top; padding: 0 8px; }
        .foot .c1 { width: 34%; } .foot .c2 { width: 34%; } .foot .c3 { width: 32%; }
        .foot p { margin: 0 0 3px; }
        .foot b { font-weight: bold; }

        .sumtab { border-collapse: collapse; width: 100%; font-size: 7.5px; }
        .sumtab th, .sumtab td { border: 1px solid #000; padding: 1px 3px; text-align: center; height: 14px; }
        .sumtab td.l { text-align: left; }

        .legend div { line-height: 1.4; }
        .sig { text-align: center; margin-top: 6px; }
        .sig .name { font-weight: bold; border-bottom: 1px solid #000; padding: 0 10px 1px; display: inline-block; min-width: 150px; }
        .sig .cap { font-size: 7px; font-style: italic; }

        @page { size: legal landscape; margin: 5mm; }
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
                    <h1>School Form 1 (SF1) School Register</h1>
                    <p class="sub">(This replaces Form 1, Master List &amp; STS Form 2-Family Background and Profile)</p>
                </td>
                <td class="logo" style="text-align:right; vertical-align:top">
                    @if (file_exists($depedLogo))<img class="deped" src="{{ $depedLogo }}" alt="DepEd">@endif
                </td>
            </tr>
        </table>

        {{-- ===== Meta ===== --}}
        <table class="meta">
            <tr>
                <td><span class="lbl">School ID</span> <span class="val">{{ $school?->school_id ?? '' }}</span></td>
                <td><span class="lbl">Region</span> <span class="val">{{ $school?->region ?? '' }}</span></td>
                <td><span class="lbl">Division</span> <span class="val">{{ $school?->division ?? '' }}</span></td>
                <td><span class="lbl">District</span> <span class="val">{{ $district ?? '' }}</span></td>
                <td><span class="lbl">School Year</span> <span class="val">{{ $schoolYear?->name }}</span></td>
            </tr>
            <tr>
                <td colspan="2"><span class="lbl">Name of School</span> <span class="val" style="min-width:180px">{{ $school?->name ?? config('app.name') }}</span></td>
                <td><span class="lbl">Grade Level</span> <span class="val">{{ $section->gradeLevel->name }}</span></td>
                <td><span class="lbl">Section</span> <span class="val">{{ $section->name }}</span></td>
                <td><span class="lbl">Age as of</span> <span class="val">{{ $cutOff->format('m/d/Y') }}</span></td>
            </tr>
        </table>

        {{-- ===== Register: one block per sex, numbering restarts in each ===== --}}
        <table class="grid">
            <colgroup>
                <col class="c-no"><col class="c-lrn"><col class="c-name"><col class="c-sex">
                <col class="c-bday"><col class="c-age"><col class="c-bplace"><col class="c-tongue">
                <col class="c-ethnic"><col class="c-religion">
                <col class="c-street"><col class="c-brgy"><col class="c-muni"><col class="c-prov">
                <col class="c-parent"><col class="c-parent">
                <col class="c-guardian"><col class="c-rel"><col class="c-contact">
                <col class="c-remarks">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">LRN</th>
                    <th rowspan="2">NAME<br>(Last Name, First Name, Middle Name)</th>
                    <th rowspan="2">SEX<br>(M/F)</th>
                    <th rowspan="2">BIRTH DATE<br>(mm/dd/yy)</th>
                    <th rowspan="2">AGE as of<br>{{ $cutOff->format('m/d/Y') }}</th>
                    <th rowspan="2">BIRTH PLACE</th>
                    <th rowspan="2">MOTHER TONGUE</th>
                    <th rowspan="2">IP<br>(Ethnic Group)</th>
                    <th rowspan="2">RELIGION</th>
                    <th colspan="4">ADDRESS</th>
                    <th colspan="2">PARENTS</th>
                    <th colspan="3">GUARDIAN (if not parent)</th>
                    <th rowspan="2">REMARKS</th>
                </tr>
                <tr>
                    <th>House No./ Street/ Sitio/ Purok</th>
                    <th>Barangay</th>
                    <th>Municipality/ City</th>
                    <th>Province</th>
                    <th>Father's Name<br>(Last, First, Middle)</th>
                    <th>Mother's Maiden Name<br>(Last, First, Middle)</th>
                    <th>Name</th>
                    <th>Relationship</th>
                    <th>Contact Number</th>
                </tr>
            </thead>
            <tbody>
                {{-- The official form carries no band header: each sex block is
                     closed by its own TOTAL row, exactly as in SF2/SF3/SF5. --}}
                @foreach ($blocks as $block)
                    @forelse ($block['rows'] as $row)
                        <tr>
                            <td>{{ $row['no'] }}</td>
                            <td>{{ $row['lrn'] }}</td>
                            <td class="l">{{ $row['name'] }}</td>
                            <td>{{ $row['sex'] }}</td>
                            <td>{{ $row['birthdate'] }}</td>
                            <td>{{ $row['age'] }}</td>
                            <td class="l">{{ $row['birth_place'] }}</td>
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
                            <td class="l">{{ $row['remarks'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $totalCols }}" style="font-style:italic">No {{ strtolower($block['label']) }} learners on the register.</td></tr>
                    @endforelse

                    <tr class="totrow">
                        <td colspan="{{ $totalCols - 1 }}" class="l">TOTAL {{ $block['label'] }}</td>
                        <td>{{ count($block['rows']) }}</td>
                    </tr>
                @endforeach

                <tr class="totrow">
                    <td colspan="{{ $totalCols - 1 }}" class="l">COMBINED</td>
                    <td>{{ count($males) + count($females) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ===== Footer: guidelines | legend | registration summary + signatures ===== --}}
        <table class="foot">
            <tr>
                <td class="c1">
                    <p><b>GUIDELINES:</b></p>
                    <p>1. The School Register shall be accomplished at the beginning of the school year and updated as changes occur.</p>
                    <p>2. Learners shall be listed alphabetically by sex — males first, then females — with numbering restarting for each.</p>
                    <p>3. Age is reckoned as of the first Friday of June ({{ $cutOff->format('F j, Y') }}).</p>
                    <p>4. Mother's name shall be written in maiden form (Last Name, First Name, Middle Name).</p>
                    <p>5. Guardian columns are filled only when the learner is not living with either parent.</p>
                    <p>6. Enter the applicable code in REMARKS together with the information it requires, as listed in the legend.</p>
                    <p>7. This form shall be submitted to the Division Office at the beginning and end of the school year.</p>
                </td>

                <td class="c2">
                    <p><b>LEGEND — CODES FOR REMARKS</b></p>
                    <div class="legend">
                        <div><b>T/O</b> — Transferred Out: write the name of the receiving school and the date of transfer.</div>
                        <div><b>T/I</b> — Transferred In: write the name of the previous school and the date of transfer.</div>
                        <div><b>DRP</b> — Dropped: write the reason and the date the learner dropped out.</div>
                        <div><b>LE</b> — Late Enrollment: write the reason for enrolling beyond the cut-off.</div>
                        <div><b>CCT</b> — CCT Recipient: write the CCT control/reference number.</div>
                        <div><b>B/A</b> — Balik-Aral: write the number of years the learner was out of school.</div>
                        <div><b>LWD</b> — Learner With Disability: write the specific type of disability.</div>
                        <div><b>ACL</b> — Accelerated: write the grade level from which the learner was accelerated.</div>
                    </div>
                </td>

                <td class="c3">
                    <table class="sumtab">
                        <tr>
                            <th rowspan="2" style="width:34%">REGISTRATION</th>
                            <th colspan="3">Number of Learners</th>
                        </tr>
                        <tr><th>MALE</th><th>FEMALE</th><th>TOTAL</th></tr>
                        <tr>
                            <td class="l">Beginning of School Year (BoSY)</td>
                            <td>{{ $summary['male']['bosy'] }}</td>
                            <td>{{ $summary['female']['bosy'] }}</td>
                            <td>{{ $summary['total']['bosy'] }}</td>
                        </tr>
                        <tr>
                            <td class="l">End of School Year (EoSY)</td>
                            <td>{{ $summary['male']['eosy'] }}</td>
                            <td>{{ $summary['female']['eosy'] }}</td>
                            <td>{{ $summary['total']['eosy'] }}</td>
                        </tr>
                    </table>

                    <p style="margin-top:10px"><i>I certify that this is a true and correct report.</i></p>
                    <div class="sig">
                        <span class="name">{{ $adviser ?? '' }}</span>
                        <div class="cap">(Signature of Class Adviser over Printed Name)</div>
                    </div>
                    <p style="margin-top:6px">Attested by:</p>
                    <div class="sig">
                        <span class="name">@if (filled($schoolHead ?? null)){{ $schoolHead }}@else&nbsp;@endif</span>
                        <div class="cap">(Signature of School Head over Printed Name)</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
