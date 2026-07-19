@php
    // The section's school, falling back to the logged-in teacher's school so
    // the form always carries the branding of the school they belong to.
    $school = $school ?? $section->school ?? auth()->user()?->school;
    $blocks = [
        ['label' => 'MALE', 'rows' => $males, 'totalLabel' => 'TOTAL MALE'],
        ['label' => 'FEMALE', 'rows' => $females, 'totalLabel' => 'TOTAL FEMALE'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF5 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #fff; color: #000; }

        .head-row { width: 80%; margin: 0 auto; }
        .head-row td { vertical-align: middle; }
        .logo { width: 90px; }
        .logo img.seal { height: 54px; }
        .logo img.deped { height: 46px; }
        .title { text-align: center; }
        .title h1 { font-size: 14px; font-weight: bold; margin: 0; }
        .title .sub { font-size: 8.5px; font-style: italic; margin: 1px 0 0; }

        .meta { width: 100%; font-size: 9.5px; border-collapse: collapse; margin-top: 6px; }
        .meta td { padding: 2px 4px; white-space: nowrap; }
        .val { border-bottom: 1px solid #000; display: inline-block; min-width: 55px; text-align: center; font-weight: bold; padding: 0 6px; }

        /* Two panes: learner grid left, summary/signatures right. */
        .panes { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .panes > tbody > tr > td { vertical-align: top; }
        .pane-left { width: 66%; padding-right: 6px; }
        .pane-right { width: 34%; }

        table.grid { border-collapse: collapse; width: 100%; table-layout: fixed; }
        .grid th, .grid td { border: 1px solid #000; font-size: 6.8px; text-align: center; padding: 1px; height: 14px; word-wrap: break-word; }
        .grid th { font-weight: bold; background: #f0f0f0; }
        .grid th.band { background: #d9d9d9; font-size: 8px; letter-spacing: 1px; text-align: left; padding-left: 4px; }
        .grid td.l { text-align: left; padding-left: 2px; }
        .grid .totrow td { font-weight: bold; background: #f4f4f4; }
        .c-lrn { width: 13%; } .c-name { width: 30%; } .c-ave { width: 13%; }
        .c-act { width: 12%; } .c-comp { width: 16%; } .c-inc { width: 16%; }

        table.sumtab { border-collapse: collapse; width: 100%; font-size: 7px; }
        .sumtab th, .sumtab td { border: 1px solid #000; padding: 1px 3px; text-align: center; height: 13px; }
        .sumtab td.l { text-align: left; }
        .sumtab th.hdr { background: #d9d9d9; letter-spacing: 1px; }

        .sig { text-align: center; margin-top: 12px; font-size: 7.5px; }
        .sig .name { font-weight: bold; border-bottom: 1px solid #000; padding: 0 10px 1px; display: inline-block; min-width: 140px; }
        .sig .cap { font-size: 6.5px; font-style: italic; }
        .sig .role { font-size: 7px; font-weight: bold; }

        .guidelines { font-size: 6.5px; margin-top: 10px; }
        .guidelines p { margin: 0 0 2px; }
        .pageno { text-align: right; font-size: 7.5px; font-style: italic; margin-top: 4px; }

        @page { size: legal landscape; margin: 5mm; }
    </style>
</head>
<body>
    {{-- ===== Title ===== --}}
    @php
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
                <h1>School Form 5 (SF5) Report on Promotion &amp; Level of Proficiency</h1>
                <p class="sub">(This replaced Forms 18-E1, 18-E2, 18A and List of Graduates)</p>
            </td>
            <td class="logo" style="text-align:right">
                @if (file_exists($depedLogo))<img class="deped" src="{{ $depedLogo }}" alt="DepEd">@endif
            </td>
        </tr>
    </table>

    {{-- ===== Meta ===== --}}
    <table class="meta">
        <tr>
            <td><span class="lbl">Region</span> <span class="val">{{ $school?->region ?? '' }}</span></td>
            <td><span class="lbl">Division</span> <span class="val">{{ $school?->division ?? '' }}</span></td>
            <td><span class="lbl">District</span> <span class="val">{{ $district ?? '' }}</span></td>
            <td><span class="lbl">School ID</span> <span class="val">{{ $school?->school_id ?? '' }}</span></td>
            <td><span class="lbl">School Year</span> <span class="val">{{ $schoolYear?->name }}</span></td>
            <td><span class="lbl">Curriculum</span> <span class="val">{{ $curriculum }}</span></td>
        </tr>
        <tr>
            <td colspan="3"><span class="lbl">School Name</span> <span class="val" style="min-width:200px">{{ $school?->name ?? config('app.name') }}</span></td>
            <td colspan="2"><span class="lbl">Grade Level</span> <span class="val">{{ $section->gradeLevel->name }}</span></td>
            <td><span class="lbl">Section</span> <span class="val">{{ $section->name }}</span></td>
        </tr>
    </table>

    <table class="panes">
        <tr>
            {{-- ===== Left: learner grid ===== --}}
            <td class="pane-left">
                <table class="grid">
                    <colgroup>
                        <col class="c-lrn"><col class="c-name"><col class="c-ave">
                        <col class="c-act"><col class="c-comp"><col class="c-inc">
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2">LRN</th>
                            <th rowspan="2">LEARNER'S NAME<br>(Last Name, First Name, Middle Name)</th>
                            <th rowspan="2">GENERAL AVERAGE<br><span style="font-weight:normal;font-size:5.5px">(3 decimals for honor learner, 2 for non-honor &amp; Descriptive Letter)</span></th>
                            <th rowspan="2">ACTION TAKEN:<br>PROMOTED, *IRREGULAR or RETAINED</th>
                            <th colspan="2">INCOMPLETE SUBJECT/S</th>
                        </tr>
                        <tr>
                            <th>Completed as of end of current SY</th>
                            <th>As of end of the current SY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($blocks as $block)
                            <tr><th colspan="6" class="band">{{ $block['label'] }} (A–Z)</th></tr>

                            @forelse ($block['rows'] as $row)
                                <tr>
                                    <td>{{ $row['lrn'] }}</td>
                                    <td class="l">{{ $row['name'] }}</td>
                                    <td>{{ $row['average_display'] }}</td>
                                    <td>{{ $row['action'] }}</td>
                                    <td class="l">{{ $row['completed'] }}</td>
                                    <td class="l">{{ $row['incomplete'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="font-style:italic">No {{ strtolower($block['label']) }} learners on the register.</td></tr>
                            @endforelse

                            <tr class="totrow">
                                <td colspan="5" class="l">{{ $block['totalLabel'] }}</td>
                                <td>{{ count($block['rows']) }}</td>
                            </tr>
                        @endforeach

                        <tr class="totrow">
                            <td colspan="5" class="l">COMBINED</td>
                            <td>{{ count($males) + count($females) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            {{-- ===== Right: summaries, signatures, guidelines ===== --}}
            <td class="pane-right">
                <table class="sumtab">
                    <tr><th colspan="4" class="hdr">SUMMARY TABLE</th></tr>
                    <tr><th>STATUS</th><th>MALE</th><th>FEMALE</th><th>TOTAL</th></tr>
                    @foreach ($summary['actions'] as $action => $counts)
                        <tr>
                            <td class="l"><b>{{ $action }}</b></td>
                            <td>{{ $counts['male'] }}</td>
                            <td>{{ $counts['female'] }}</td>
                            <td>{{ $counts['total'] }}</td>
                        </tr>
                    @endforeach
                </table>

                <table class="sumtab" style="margin-top:6px">
                    <tr><th colspan="4" class="hdr">LEVEL OF PROFICIENCY</th></tr>
                    <tr><th>&nbsp;</th><th>MALE</th><th>FEMALE</th><th>TOTAL</th></tr>
                    @foreach ($bands as $letter => $band)
                        <tr>
                            <td class="l"><b>{{ $band['label'] }}</b> <span style="font-size:6px">({{ $band['note'] }})</span></td>
                            <td>{{ $summary['proficiency'][$letter]['male'] }}</td>
                            <td>{{ $summary['proficiency'][$letter]['female'] }}</td>
                            <td>{{ $summary['proficiency'][$letter]['total'] }}</td>
                        </tr>
                    @endforeach
                </table>

                <div class="sig">
                    <p style="margin:0;text-align:left;font-weight:bold">PREPARED BY:</p>
                    <span class="name">{{ $adviser ?? '' }}</span>
                    <div class="role">Class Adviser</div>
                    <div class="cap">(Name and Signature)</div>
                </div>
                <div class="sig">
                    <p style="margin:0;text-align:left;font-weight:bold">CERTIFIED CORRECT &amp; SUBMITTED:</p>
                    <span class="name">@if (filled($schoolHead)){{ $schoolHead }}@else&nbsp;@endif</span>
                    <div class="role">School Head</div>
                    <div class="cap">(Name and Signature)</div>
                </div>
                <div class="sig">
                    <p style="margin:0;text-align:left;font-weight:bold">REVIEWED BY:</p>
                    <span class="name">@if (filled($reviewer)){{ $reviewer }}@else&nbsp;@endif</span>
                    <div class="role">Division Representative</div>
                    <div class="cap">(Name and Signature)</div>
                </div>

                <div class="guidelines">
                    <p><b>GUIDELINES:</b></p>
                    <p>1. For All Grade/Year Levels.</p>
                    <p>2. To be prepared by the Adviser. Final rating per subject area should be taken from the record of the subject teacher. The class adviser should make the computation of General Average.</p>
                    <p>3. On the summary table, reflect the total number of learners promoted, retained and irregular (*for Grade 7 onwards only) and the level of proficiency according to the individual general average.</p>
                    <p>4. Must tally with the total enrollment report as of End of School Year GESP/GSSP (BEIS).</p>
                    <p>5. Protocols of validation &amp; submission will remain under the discretion of the Schools Division Superintendent.</p>
                </div>

                <p class="pageno">School Form 5: Page 1 of 1</p>
            </td>
        </tr>
    </table>
</body>
</html>
