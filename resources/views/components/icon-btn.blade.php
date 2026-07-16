@props(['icon', 'href' => null, 'onclick' => null, 'title' => '', 'size' => 'md', 'color' => 'gray'])

@php
  $sizeClass = match($size) {
    'sm' => 'w-4 h-4',
    'md' => 'w-5 h-5',
    'lg' => 'w-6 h-6',
  };
  $colorClass = match($color) {
    'blue' => 'text-blue-600 hover:text-blue-700 dark:text-blue-400',
    'red' => 'text-red-600 hover:text-red-700 dark:text-red-400',
    'green' => 'text-green-600 hover:text-green-700 dark:text-green-400',
    'gray' => 'text-gray-600 hover:text-gray-700 dark:text-gray-400',
  };
  $icons = [
    'edit' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
    'view' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    'plus' => 'M12 5v14m7-7H5',
    'check' => 'M5 13l4 4L19 7',
    'close' => 'M6 18L18 6M6 6l12 12',
    'download' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
    'upload' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
  ];
@endphp

<{{ $href ? 'a' : 'button' }} {{ $attributes->merge([
  'href' => $href,
  'onclick' => $onclick,
  'class' => $colorClass.' p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-navy-700/50 transition',
  'title' => $title,
  'type' => $href ? null : 'button',
]) }}>
  @if (isset($icons[$icon]))
    <svg class="{{ $sizeClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}" />
    </svg>
  @else
    {{ $slot }}
  @endif
</{{ $href ? 'a' : 'button' }}>
