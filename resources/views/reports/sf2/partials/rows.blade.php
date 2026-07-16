@php $nDays = count($days); @endphp

@forelse ($rows as $row)
    <tr>
        <td class="num">{{ $row['no'] }}</td>
        <td class="name">{{ $row['name'] }}</td>
        @foreach ($days as $d)
            @php $code = $row['marks'][$d['date']] ?? ''; @endphp
            <td class="day {{ $code === '/' ? 'tardy' : ($code === 'x' ? 'absent' : '') }}">{{ $code }}</td>
        @endforeach
        <td class="tot">{{ $row['absent'] ?: '' }}</td>
        <td class="tot">{{ $row['present'] }}</td>
        <td class="remarks">&nbsp;</td>
    </tr>
@empty
    <tr>
        <td class="num">&nbsp;</td>
        <td class="name" style="color:#888">No {{ strtolower($label) }} learners enrolled.</td>
        @foreach ($days as $d)<td class="day">&nbsp;</td>@endforeach
        <td class="tot">&nbsp;</td><td class="tot">&nbsp;</td><td class="remarks">&nbsp;</td>
    </tr>
@endforelse

{{-- Gender per-day present total row --}}
<tr class="totrow">
    <td class="num">{{ count($rows) }}</td>
    <td class="name">&lt;=== {{ $label }} | TOTAL Per Day ===&gt;</td>
    @foreach ($days as $d)<td class="day">{{ $totals[$d['date']][$genderKey] ?? 0 }}</td>@endforeach
    <td class="tot">&nbsp;</td>
    <td class="tot">{{ collect($rows)->sum('present') }}</td>
    <td class="remarks">&nbsp;</td>
</tr>
