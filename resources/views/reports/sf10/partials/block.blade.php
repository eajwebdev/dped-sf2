{{--
    One scholastic-record block: a 4-line header (School / District / Grade /
    Adviser), the Learning Areas table, and the Remedial Classes table.

    Params:
      caps       bool   header reads "LEARNING AREAS" (true) or "Learning Areas"
      tle        bool   the 7th standard row prints TLE instead of EPP (template)
      rows       ?array filled learner rows [{label, indent, q, final, remark}] — or null
      labels     ?array [[label, indent], …] for an unfilled standard block — or null
      blankTall  bool   render a tall block of empty generic rows (bottom blocks)
      ga, gaRemark      general-average value + remark (filled block only)
      school … adviser  header field values (blank string when unfilled)
--}}
@php
    $caps = $caps ?? false;
    $tle = $tle ?? false;
    $rows = $rows ?? null;
    $labels = $labels ?? null;
    $blankTall = $blankTall ?? false;
    $ga = $ga ?? null;
    $gaRemark = $gaRemark ?? '';
    $uw = 'display:inline-block;border-bottom:0.5px solid #000;height:7px;vertical-align:bottom;';
@endphp

<table class="meta">
    <tr>
        <td style="width:64%">School: <span style="{{ $uw }}min-width:78px" class="fillv"><b>{{ $school }}</b></span></td>
        <td>School ID: <span style="{{ $uw }}min-width:42px"><b>{{ $schoolId }}</b></span></td>
    </tr>
    <tr>
        <td>District: <span style="{{ $uw }}min-width:44px"><b>{{ $district }}</b></span>
            &nbsp;Division: <span style="{{ $uw }}min-width:44px"><b>{{ $division }}</b></span></td>
        <td>Region: <span style="{{ $uw }}min-width:40px"><b>{{ $region }}</b></span></td>
    </tr>
    <tr>
        <td>Classified as Grade: <span style="{{ $uw }}min-width:30px"><b>{{ $grade }}</b></span>
            &nbsp;Section: <span style="{{ $uw }}min-width:44px"><b>{{ $section }}</b></span></td>
        <td>School Year: <span style="{{ $uw }}min-width:44px"><b>{{ $sy }}</b></span></td>
    </tr>
    <tr>
        <td>Name of Adviser/Teacher: <span style="{{ $uw }}min-width:60px"><b>{{ $adviser }}</b></span></td>
        <td>Signature: <span style="{{ $uw }}min-width:44px">&nbsp;</span></td>
    </tr>
</table>

<table class="la">
    <thead>
        <tr>
            <th rowspan="2" style="width:45%">{{ $caps ? 'LEARNING AREAS' : 'Learning Areas' }}</th>
            <th colspan="4">Quarterly Rating</th>
            <th rowspan="2" style="width:16.5%">Final Rating</th>
            <th rowspan="2" style="width:16.5%">Remarks</th>
        </tr>
        <tr>
            <th style="width:5.5%">1</th>
            <th style="width:5.5%">2</th>
            <th style="width:5.5%">3</th>
            <th style="width:5.5%">4</th>
        </tr>
    </thead>
    <tbody>
        @if ($rows !== null)
            {{-- Filled block: learner ratings on the standard rows. --}}
            @foreach ($rows as $r)
                <tr>
                    <td class="area {{ $r['indent'] ? 'ind' : '' }}">{{ $r['label'] }}</td>
                    <td class="n">{{ $r['q'][1] }}</td>
                    <td class="n">{{ $r['q'][2] }}</td>
                    <td class="n">{{ $r['q'][3] }}</td>
                    <td class="n">{{ $r['q'][4] }}</td>
                    <td class="n b">{{ $r['final'] }}</td>
                    <td class="n">{{ $r['remark'] }}</td>
                </tr>
            @endforeach
        @elseif ($labels !== null)
            {{-- Unfilled standard block (EPP→TLE variant), printed blank. --}}
            @foreach ($labels as [$label, $indent])
                <tr>
                    <td class="area {{ $indent ? 'ind' : '' }}">{{ $label }}</td>
                    <td class="n"></td><td class="n"></td><td class="n"></td><td class="n"></td>
                    <td class="n"></td><td class="n"></td>
                </tr>
            @endforeach
        @else
            {{-- Tall generic blank block (bottom blocks). --}}
            @for ($i = 0; $i < 16; $i++)
                <tr>
                    <td class="area">&nbsp;</td>
                    <td class="n"></td><td class="n"></td><td class="n"></td><td class="n"></td>
                    <td class="n"></td><td class="n"></td>
                </tr>
            @endfor
        @endif
        <tr class="rowh">
            <td class="area b">General Average</td>
            <td class="n"></td><td class="n"></td><td class="n"></td><td class="n"></td>
            <td class="n b">{{ $ga }}</td>
            <td class="n">{{ $gaRemark }}</td>
        </tr>
    </tbody>
</table>

<table class="rem">
    <tr>
        <td colspan="2" class="hd">Remedial Classes</td>
        <td colspan="3" class="hd">Date Conducted: __________ to __________</td>
    </tr>
    <tr>
        <th style="width:30%">Learning Areas</th>
        <th style="width:17%">Final Rating</th>
        <th style="width:18%">Remedial Class Mark</th>
        <th style="width:18%">Recomputed Final Grade</th>
        <th style="width:17%">Remarks</th>
    </tr>
    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
</table>
