@php
    // The section's school, falling back to the logged-in teacher's school so
    // the form always carries the branding of the school they belong to.
    $school = $school ?? $section->school ?? auth()->user()?->school;
    $pageCount = $bookPages->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF3 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #fff; color: #000; }
        .sheet { background: #fff; padding: 0; page-break-after: always; }
        .sheet:last-child { page-break-after: auto; }

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

        .c-no { width: 2.2%; }
        .c-name { width: 15%; }
        .c-remarks { width: 10%; }
        /* The 8 book pairs share the rest evenly. */

        .foot { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 7.5px; table-layout: fixed; }
        .foot > tbody > tr > td { vertical-align: top; padding: 0 8px; }
        .foot .c1 { width: 36%; } .foot .c2 { width: 40%; } .foot .c3 { width: 24%; }
        .foot p { margin: 0 0 3px; }
        .foot b { font-weight: bold; }

        .sig { text-align: center; margin-top: 14px; }
        .sig .name { font-weight: bold; border-bottom: 1px solid #000; padding: 0 10px 1px; display: inline-block; min-width: 150px; }
        .sig .cap { font-size: 7px; font-style: italic; }
        .pageno { text-align: right; font-size: 7.5px; font-style: italic; margin-top: 4px; }

        @page { size: legal landscape; margin: 5mm; }
    </style>
</head>
<body>
@foreach ($bookPages as $pageBooks)
    @php $pageBooks = collect($pageBooks)->values(); @endphp
    <div class="sheet">
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
                    <h1>School Form 3 (SF3) Books Issued and Returned</h1>
                    <p class="sub">(This replaces Form 1 &amp; Inventory of Textbooks)</p>
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
                <td><span class="lbl">School Name</span> <span class="val" style="min-width:180px">{{ $school?->name ?? config('app.name') }}</span></td>
                <td><span class="lbl">School Year</span> <span class="val">{{ $schoolYear?->name }}</span></td>
                <td><span class="lbl">Grade Level</span> <span class="val">{{ $section->gradeLevel->name }}</span></td>
                <td><span class="lbl">Section</span> <span class="val">{{ $section->name }}</span></td>
            </tr>
        </table>

        {{-- ===== Grid: name + up to 8 book pairs + remarks ===== --}}
        @php $totalCols = 2 + max(1, $pageBooks->count()) * 2 + 1; @endphp
        <table class="grid">
            <colgroup>
                <col class="c-no"><col class="c-name">
                @for ($i = 0; $i < max(1, $pageBooks->count()) * 2; $i++)<col>@endfor
                <col class="c-remarks">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="3">No.</th>
                    <th rowspan="3">LEARNER'S NAME<br>(Last Name, First Name, Middle Name)</th>
                    @forelse ($pageBooks as $book)
                        <th colspan="2">{{ $book->subject_area }}<br>{{ $book->title }}</th>
                    @empty
                        <th colspan="2">Subject Area &amp; Title</th>
                    @endforelse
                    <th rowspan="3">REMARKS / ACTION TAKEN<br><span style="font-weight:normal;font-size:6px">(refer to legend below)</span></th>
                </tr>
                <tr>
                    @for ($i = 0; $i < max(1, $pageBooks->count()); $i++)<th colspan="2">Date</th>@endfor
                </tr>
                <tr>
                    @for ($i = 0; $i < max(1, $pageBooks->count()); $i++)<th>Issued</th><th>Returned</th>@endfor
                </tr>
            </thead>
            <tbody>
                @foreach ([['label' => 'MALE', 'rows' => $males, 'key' => 'male'],
                           ['label' => 'FEMALE', 'rows' => $females, 'key' => 'female']] as $block)
                    @forelse ($block['rows'] as $row)
                        <tr>
                            <td>{{ $row['no'] }}</td>
                            <td class="l">{{ $row['name'] }}</td>
                            @forelse ($pageBooks as $book)
                                <td>{{ $row['cells'][$book->id]['issued'] }}</td>
                                <td>{{ $row['cells'][$book->id]['returned'] }}</td>
                            @empty
                                <td>&nbsp;</td><td>&nbsp;</td>
                            @endforelse
                            <td class="l">{{ $row['remarks'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $totalCols }}" style="font-style:italic">No {{ strtolower($block['label']) }} learners on the register.</td></tr>
                    @endforelse

                    <tr class="totrow">
                        <td>{{ $totals[$block['key']]['learners'] }}</td>
                        <td class="l">TOTAL FOR {{ $block['label'] }} | TOTAL COPIES</td>
                        @forelse ($pageBooks as $book)
                            <td>{{ $totals[$block['key']]['issued'][$book->id] ?? 0 }}</td>
                            <td>{{ $totals[$block['key']]['returned'][$book->id] ?? 0 }}</td>
                        @empty
                            <td>&nbsp;</td><td>&nbsp;</td>
                        @endforelse
                        <td>&nbsp;</td>
                    </tr>
                @endforeach

                <tr class="totrow">
                    <td>{{ $totals['all']['learners'] }}</td>
                    <td class="l">TOTAL LEARNERS | TOTAL COPIES</td>
                    @forelse ($pageBooks as $book)
                        <td>{{ $totals['all']['issued'][$book->id] ?? 0 }}</td>
                        <td>{{ $totals['all']['returned'][$book->id] ?? 0 }}</td>
                    @empty
                        <td>&nbsp;</td><td>&nbsp;</td>
                    @endforelse
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        {{-- ===== Footer: guidelines | codes | signature ===== --}}
        <table class="foot">
            <tr>
                <td class="c1">
                    <p><b>GUIDELINES:</b></p>
                    <p>1. Title of Books Issued to each learner must be recorded by the class adviser.</p>
                    <p>2. The Date of Issuance and the Date of Return shall be reflected in the form.</p>
                    <p>3. The Total Number of Copies issued at BoSY shall be reflected in the form.</p>
                    <p>4. The Total Number of Copies of Books Returned at the EoSY shall be reflected in the form.</p>
                    <p>5. All textbooks being used must be included. Additional copies of this form may be used if needed.</p>
                </td>
                <td class="c2">
                    <p><b>In case of lost/unreturned books, please provide information with the following code:</b></p>
                    <p>A. In Column Date Returned, codes are: <b>FM</b>=Force Majeure, <b>TDO</b>=Transferred/Dropout, <b>NEG</b>=Negligence</p>
                    <p>B. In Column Remarks/Action Taken, codes are:
                        <b>LLTR</b>=Secured Letter from Learner duly signed by parent/guardian (for code FM),
                        <b>TLTR</b>=Teacher prepared letter/report duly noted by School Head for submission to School Property Custodian (for code TDO),
                        <b>PTL</b>=Paid by the Learner (for code NEG).
                        References: DO#23, s.2001, DO#25, s.2003, DO#14, s.2012.</p>
                </td>
                <td class="c3">
                    <p><b>Prepared By:</b></p>
                    <div class="sig">
                        <span class="name">{{ $adviser ?? '' }}</span>
                        <div class="cap">(Signature over printed name)</div>
                    </div>
                    <p style="margin-top:8px">Date BoSY: ____________ &nbsp; Date EoSY: ____________</p>
                    @if (filled($schoolHead ?? null))
                        <p style="margin-top:6px">Noted by:</p>
                        <div class="sig">
                            <span class="name">{{ $schoolHead }}</span>
                            <div class="cap">(School Head)</div>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <p class="pageno">School Form 3: Page {{ $loop->iteration }} of {{ $pageCount }}</p>
    </div>
@endforeach
</body>
</html>
