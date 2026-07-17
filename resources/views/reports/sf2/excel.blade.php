@php
    $days = $dayColumns;
    $nDays = count($days);
    $section = $data['section'];
    $sy = $data['schoolYear'];
    $sum = $data['summary'];
    $school = $section->school ?? auth()->user()?->school;
@endphp
<table>
    <tr><td colspan="{{ 4 + max($nDays, 1) }}"><b>School Form 2 (SF2) — Daily Attendance Report of Learners</b></td></tr>
    <tr>
        <td colspan="2">School Year: {{ $sy->name }}</td>
        <td colspan="{{ max($nDays, 1) }}">Report for the Month of: {{ $data['month']->format('F Y') }}</td>
        <td colspan="2">School Days: {{ $sum['classDays'] }}</td>
    </tr>
    <tr>
        <td colspan="2">School: {{ $school?->name ?? config('app.name') }}</td>
        <td colspan="{{ max($nDays, 1) }}">Grade Level: {{ $section->gradeLevel->name }}</td>
        <td colspan="2">Section: {{ $section->name }}</td>
    </tr>
</table>

@foreach ([['label' => 'MALE', 'rows' => $data['males'], 'genderKey' => 'male'],
           ['label' => 'FEMALE', 'rows' => $data['females'], 'genderKey' => 'female']] as $grid)
<table>
    <thead>
        <tr>
            <th colspan="{{ 5 + max($nDays, 1) }}"><b>{{ $grid['label'] }} (A–Z)</b></th>
        </tr>
        <tr>
            <th rowspan="3">No.</th>
            <th rowspan="3">LEARNER'S NAME (Last, First, Middle)</th>
            <th colspan="{{ max($nDays, 1) }}">{{ $data['month']->format('F') }}</th>
            <th colspan="2">Total for the Month</th>
            <th rowspan="3">REMARKS</th>
        </tr>
        <tr>
            @foreach ($days as $d)<th>{{ $d['day'] }}</th>@endforeach
            @if ($nDays === 0)<th></th>@endif
            <th rowspan="2">ABSENT</th>
            <th rowspan="2">TARDY</th>
        </tr>
        <tr>
            @foreach ($days as $d)<th>{{ $d['letter'] }}</th>@endforeach
            @if ($nDays === 0)<th></th>@endif
        </tr>
    </thead>
    <tbody>
        @include('reports.sf2.partials.rows', ['rows' => $grid['rows'], 'label' => $grid['label'], 'totals' => $data['dailyTotals'], 'genderKey' => $grid['genderKey'], 'days' => $days])
        @if ($grid['genderKey'] === 'female')
        <tr>
            <td colspan="2"><b>Combined TOTAL PER DAY</b></td>
            @foreach ($days as $d)<td>{{ $data['dailyTotals'][$d['date']]['combined'] ?? 0 }}</td>@endforeach
            @if ($nDays === 0)<td></td>@endif
            <td></td><td></td><td></td>
        </tr>
        @endif
    </tbody>
</table>
@endforeach

<table>
    <tr><td colspan="4"><b>Summary for the Month</b></td></tr>
    <tr><td></td><td>M</td><td>F</td><td>TOTAL</td></tr>
    <tr><td>Enrolment</td><td>{{ $sum['enrolment']['male'] }}</td><td>{{ $sum['enrolment']['female'] }}</td><td>{{ $sum['enrolment']['total'] }}</td></tr>
    <tr><td>Average Daily Attendance</td><td>{{ $sum['avgDaily']['male'] }}</td><td>{{ $sum['avgDaily']['female'] }}</td><td>{{ $sum['avgDaily']['total'] }}</td></tr>
    <tr><td>Percentage of Attendance</td><td>{{ $sum['percentAttendance']['male'] }}%</td><td>{{ $sum['percentAttendance']['female'] }}%</td><td>{{ $sum['percentAttendance']['total'] }}%</td></tr>
</table>
