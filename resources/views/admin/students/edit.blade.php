<x-admin-layout title="Edit Student">
    <x-slot name="breadcrumbs"><a href="{{ route('admin.students.index') }}" class="hover:underline">Students</a> / {{ $student->full_name }}</x-slot>
    @include('admin.students.form')
</x-admin-layout>
