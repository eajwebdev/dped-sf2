@php
    $editing = $student->exists;
    $action = $editing ? route('admin.students.update', $student) : route('admin.students.store');
@endphp

<x-card class="max-w-3xl">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div>
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Identity</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input label="LRN" name="lrn" :value="$student->lrn" required hint="12-digit Learner Reference Number" />
                <x-form.select label="Status" name="status" :value="$student->status" required>
                    @foreach (['active','transferred','dropped','graduated','inactive'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $student->status) === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-form.input label="First Name" name="first_name" :value="$student->first_name" required />
                <x-form.input label="Middle Name" name="middle_name" :value="$student->middle_name" />
                <x-form.input label="Last Name" name="last_name" :value="$student->last_name" required />
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-form.input label="Suffix" name="suffix" :value="$student->suffix" hint="Jr., III (optional)" />
                <x-form.select label="Gender" name="gender" :value="$student->gender" required placeholder="— Select —">
                    <option value="Male" @selected(old('gender', $student->gender) === 'Male')>Male</option>
                    <option value="Female" @selected(old('gender', $student->gender) === 'Female')>Female</option>
                </x-form.select>
                <x-form.input label="Birthdate" name="birthdate" type="date" :value="$student->birthdate?->toDateString()" />
            </div>
        </div>

        {{-- SF1 (School Register) profile — every field below is a column on the official form. --}}
        <div class="border-t border-gray-200 dark:border-white/10 pt-5">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">SF1 Learner Profile</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input label="Birth Place (Province)" name="birth_place" :value="$student->birth_place" />
                <x-form.input label="Mother Tongue" name="mother_tongue" :value="$student->mother_tongue" />
                <x-form.input label="IP (Ethnic Group)" name="ethnic_group" :value="$student->ethnic_group" hint="Leave blank if not IP" />
                <x-form.input label="Religion" name="religion" :value="$student->religion" />
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-white/10 pt-5">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Address</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input label="House # / Street / Sitio / Purok" name="address_street" :value="$student->address_street" />
                <x-form.input label="Barangay" name="address_barangay" :value="$student->address_barangay" />
                <x-form.input label="Municipality / City" name="address_municipality" :value="$student->address_municipality" />
                <x-form.input label="Province" name="address_province" :value="$student->address_province" />
            </div>
            <div class="mt-4">
                <x-form.input label="Address (single line)" name="address" :value="$student->address"
                              hint="Legacy field — used on SF1 only when the four parts above are blank" />
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-white/10 pt-5">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Parents & Guardian</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input label="Father's Name" name="father_name" :value="$student->father_name"
                              hint="First name only if the family name matches the learner" />
                <x-form.input label="Mother's Maiden Name" name="mother_name" :value="$student->mother_name"
                              hint="First, middle & last name" />
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-form.input label="Guardian Name" name="guardian_name" :value="$student->guardian_name" hint="If not a parent" />
                <x-form.input label="Relationship" name="guardian_relationship" :value="$student->guardian_relationship" />
                <x-form.input label="Contact Number" name="guardian_contact" :value="$student->guardian_contact" />
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-white/10 pt-5">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Photo</h3>
            <div class="flex items-center gap-4">
                @if ($student->photo_path)
                    <img src="{{ Storage::url($student->photo_path) }}" class="h-16 w-16 rounded-lg object-cover" alt="">
                @endif
                <input type="file" name="photo" accept="image/*"
                       class="text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
            </div>
            @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">{{ $editing ? 'Update' : 'Create' }} Student</button>
            <a href="{{ route('admin.students.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Cancel</a>
        </div>
    </form>
</x-card>
