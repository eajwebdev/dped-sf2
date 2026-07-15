@php $nDays = count($days); @endphp

@forelse ($rows as $row)
    <tr>
        <td>{{ $row['no'] }}</td>
        <td class="name">{{ $row['name'] }}</td>
        @foreach ($days as $d)
            @php $code = $row['marks'][$d['date']] ?? ''; @endphp
            <td class="{{ $code === '/' ? 'tardy' : ($code === 'x' ? 'absent' : '') }}">{{ $code }}</td>
        @endforeach
        @if ($nDays === 0)<td>&nbsp;</td>@endif
        <td class="absent">{{ $row['absent'] ?: '' }}</td>
        <td class="tardy">{{ $row['tardy'] ?: '' }}</td>
        <td>&nbsp;</td>
    </tr>
@empty
    <tr><td>&nbsp;</td><td class="name" style="color:#888">No {{ strtolower($label) }} learners enrolled.</td>
        @for ($i = 0; $i < max($nDays, 1); $i++)<td>&nbsp;</td>@endfor
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
@endforelse

{{-- Gender per-day present total --}}
<tr class="totrow">
    <td colspan="2">{{ $label }} | TOTAL Per Day</td>
    @foreach ($days as $d)<td>{{ $totals[$d['date']][$genderKey] ?? 0 }}</td>@endforeach
    @if ($nDays === 0)<td>&nbsp;</td>@endif
    <td colspan="2">&nbsp;</td>
    <td>&nbsp;</td>
</tr>
