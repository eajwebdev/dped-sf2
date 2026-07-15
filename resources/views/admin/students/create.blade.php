<x-admin-layout title="New Student">
    <x-slot name="breadcrumbs"><a href="{{ route('admin.students.index') }}" class="hover:underline">Students</a> / New</x-slot>
    @include('admin.students.form')
</x-admin-layout>
