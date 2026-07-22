<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SF10 — {{ $section->gradeLevel->name }} {{ $section->name }}</title>
    <style>
        @page { margin: 7mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; margin: 0; }

        /* One SF10-ES per learner. */
        .rec { page-break-after: always; }
        .rec.tail { page-break-after: auto; }

        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; }

        .hdr td { vertical-align: middle; }
        .hdr .agency { text-align: center; line-height: 1.25; }
        .hdr .agency .r { font-size: 8px; }
        .hdr .agency .d { font-weight: bold; font-size: 9px; }
        .hdr .agency .t { font-weight: bold; font-size: 9px; margin-top: 2px; }
        .hdr .agency .f { font-style: italic; font-size: 7.5px; }
        .hdr .logo { width: 46px; text-align: center; }
        .hdr .logo img { width: 40px; height: 40px; }
        .sf-tag { font-weight: bold; font-size: 8.5px; }

        .band {
            background: #e5e7eb; border: 0.6px solid #333; font-weight: bold;
            text-transform: uppercase; text-align: center; padding: 2px; font-size: 8px;
            letter-spacing: .3px; margin-top: 5px;
        }

        .info th, .info td { border: 0.5px solid #333; padding: 2px 3px; }
        .info .lbl { font-size: 7px; color: #333; }
        .info .val { font-weight: bold; min-height: 10px; }

        .box th, .box td { border: 0.5px solid #333; padding: 1px 2px; }
        .box th { font-weight: bold; text-align: center; font-size: 7px; text-transform: uppercase; background: #f3f4f6; }
        .box td.area { text-align: left; padding-left: 3px; }
        .num { text-align: center; }
        .b { font-weight: bold; }
        .center { text-align: center; }
        .muted { color: #555; }

        .meta td { padding: 1px 3px; font-size: 7.5px; }
        .fill { border-bottom: 0.5px solid #333; }
        .chip { display: inline-block; width: 8px; height: 8px; border: 0.6px solid #333; vertical-align: middle; margin-right: 2px; }
        .foot { margin-top: 4px; font-size: 7px; font-style: italic; text-align: right; }
    </style>
</head>
<body>
@php
    $depedLogo = public_path('DepED-Logo.png');
    $sy = $schoolYear;
    // DepEd School ID + region/division live on the school record; district is a
    // print-time field the adviser/oversight passes in.
    $schoolName = $school?->name ?? '';
    $depedId = $school?->school_id ?? '';
    $region = $school?->region ?? '';
    $division = $school?->division ?? '';
    $grade = $section->gradeLevel->name;
    $secName = $section->name;
    $adviser = optional($section->adviser)->full_name ?? '';
@endphp

@foreach ($learners as $L)
    <div class="rec @if ($loop->last) tail @endif">
        {{-- ===== Header ===== --}}
        <div class="sf-tag">SF10-ES</div>
        <table class="hdr">
            <tr>
                <td class="logo">
                    @if (is_file($depedLogo))<img src="{{ $depedLogo }}" alt="DepEd">@endif
                </td>
                <td class="agency">
                    <div class="r">Republic of the Philippines</div>
                    <div class="d">Department of Education</div>
                    <div class="t">Learner Permanent Academic Record for Elementary School (SF10-ES)</div>
                    <div class="f">(Formerly Form 137)</div>
                </td>
                <td class="logo">&nbsp;</td>
            </tr>
        </table>

        {{-- ===== Learner's Personal Information ===== --}}
        <div class="band">Learner's Personal Information</div>
        <table class="info">
            <tr>
                <td style="width:34%"><div class="lbl">LAST NAME</div><div class="val">{{ $L['lastName'] }}</div></td>
                <td style="width:30%"><div class="lbl">FIRST NAME</div><div class="val">{{ $L['firstName'] }}</div></td>
                <td style="width:12%"><div class="lbl">NAME EXTN. (Jr, I, II)</div><div class="val">{{ $L['suffix'] }}</div></td>
                <td style="width:24%"><div class="lbl">MIDDLE NAME</div><div class="val">{{ $L['middleName'] }}</div></td>
            </tr>
            <tr>
                <td colspan="2"><div class="lbl">Learner Reference Number (LRN)</div><div class="val">{{ $L['lrn'] }}</div></td>
                <td><div class="lbl">Birthdate (mm/dd/yyyy)</div><div class="val">{{ optional($L['birthdate'])->format('m/d/Y') }}</div></td>
                <td><div class="lbl">Sex</div><div class="val">{{ $L['sex'] }}</div></td>
            </tr>
        </table>

        {{-- ===== Eligibility for Elementary School Enrollment (fillable) ===== --}}
        <div class="band">Eligibility for Elementary School Enrollment</div>
        <table class="meta" style="border:0.5px solid #333;">
            <tr>
                <td style="width:100%">
                    <span class="b">Credential Presented for Grade 1:</span>
                    <span class="chip"></span>Kinder Progress Report
                    <span class="chip" style="margin-left:8px"></span>ECCD Checklist
                    <span class="chip" style="margin-left:8px"></span>Kindergarten Certificate of Completion
                </td>
            </tr>
            <tr>
                <td>
                    Name of School: <span class="fill" style="display:inline-block; min-width:150px">&nbsp;</span>
                    &nbsp;&nbsp;School ID: <span class="fill" style="display:inline-block; min-width:70px">&nbsp;</span>
                    &nbsp;&nbsp;Address: <span class="fill" style="display:inline-block; min-width:120px">&nbsp;</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="b">Other Credential Presented:</span>
                    PEPT Passer Rating: <span class="fill" style="display:inline-block; min-width:45px">&nbsp;</span>
                    &nbsp;Date of Examination/Assessment: <span class="fill" style="display:inline-block; min-width:70px">&nbsp;</span>
                    &nbsp;Others: <span class="fill" style="display:inline-block; min-width:70px">&nbsp;</span>
                </td>
            </tr>
        </table>

        {{-- ===== Scholastic Record ===== --}}
        <div class="band">Scholastic Record</div>
        <table class="meta" style="border:0.5px solid #333; border-bottom:0;">
            <tr>
                <td style="width:52%">School: <span class="b">{{ $schoolName }}</span></td>
                <td style="width:24%">School ID: <span class="b">{{ $depedId }}</span></td>
                <td style="width:24%">Region: <span class="b">{{ $region }}</span></td>
            </tr>
            <tr>
                <td>District: <span class="b">{{ $district ?? '' }}</span></td>
                <td>Division: <span class="b">{{ $division }}</span></td>
                <td>School Year: <span class="b">{{ $sy?->name }}</span></td>
            </tr>
            <tr>
                <td>Classified as Grade: <span class="b">{{ $grade }}</span></td>
                <td>Section: <span class="b">{{ $secName }}</span></td>
                <td>Adviser/Teacher: <span class="b">{{ $adviser }}</span></td>
            </tr>
        </table>

        <table class="box">
            <thead>
                <tr>
                    <th rowspan="2" style="width:40%">Learning Areas</th>
                    <th colspan="4">Quarterly Rating</th>
                    <th rowspan="2" style="width:11%">Final Rating</th>
                    <th rowspan="2" style="width:17%">Remarks</th>
                </tr>
                <tr>
                    <th style="width:8%">1</th>
                    <th style="width:8%">2</th>
                    <th style="width:8%">3</th>
                    <th style="width:8%">4</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($L['subjects'] as $row)
                    <tr>
                        <td class="area">{{ $row['subject'] }}</td>
                        <td class="num">{{ $row['q'][1] }}</td>
                        <td class="num">{{ $row['q'][2] }}</td>
                        <td class="num">{{ $row['q'][3] }}</td>
                        <td class="num">{{ $row['q'][4] }}</td>
                        <td class="num b">{{ $row['final'] }}</td>
                        <td class="center">{{ $row['remark'] }}</td>
                    </tr>
                @empty
                    <tr><td class="area muted" colspan="7">No learning areas set up for this class yet.</td></tr>
                @endforelse
                <tr>
                    <td class="area b">General Average</td>
                    <td class="num" colspan="4"></td>
                    <td class="num b">{{ $L['generalAverage'] }}</td>
                    <td class="center b">{{ $L['generalRemark'] }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ===== Remedial Classes (fillable) ===== --}}
        <table class="box" style="margin-top:3px">
            <thead>
                <tr>
                    <th colspan="5" style="text-align:left; padding-left:3px">
                        Remedial Classes &nbsp;·&nbsp; Conducted from: __________ to __________
                    </th>
                </tr>
                <tr>
                    <th style="width:40%">Learning Areas</th>
                    <th>Final Rating</th>
                    <th>Remedial Class Mark</th>
                    <th>Recomputed Final Grade</th>
                    <th style="width:17%">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
            </tbody>
        </table>

        <div class="foot">Revised 2025 based on DepEd Order No. 10, s. 2024</div>
    </div>
@endforeach
</body>
</html>
