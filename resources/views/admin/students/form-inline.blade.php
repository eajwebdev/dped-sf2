<form method="POST" action="{{ $student ? route('admin.students.update', $student) : route('admin.students.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if ($student) @method('PATCH') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- LRN -->
        <x-form.input label="LRN" name="lrn" value="{{ $student?->lrn }}" required placeholder="12-digit ID"
                     :disabled="(bool)$student" />

        <!-- First Name -->
        <x-form.input label="First Name" name="first_name" value="{{ $student?->first_name }}" required />

        <!-- Middle Name -->
        <x-form.input label="Middle Name" name="middle_name" value="{{ $student?->middle_name }}" />

        <!-- Last Name -->
        <x-form.input label="Last Name" name="last_name" value="{{ $student?->last_name }}" required />

        <!-- Suffix -->
        <x-form.input label="Suffix" name="suffix" value="{{ $student?->suffix }}" placeholder="Jr., Sr., etc." />

        <!-- Gender -->
        <x-form.select label="Gender" name="gender" value="{{ $student?->gender }}" required>
            <option value="">— Select —</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </x-form.select>

        <!-- Birthdate -->
        <x-form.input label="Birthdate" name="birthdate" type="date" value="{{ $student?->birthdate?->toDateString() }}" />

        <!-- Status -->
        <x-form.select label="Status" name="status" value="{{ $student?->status }}" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="transferred">Transferred</option>
            <option value="dropped">Dropped</option>
            <option value="graduated">Graduated</option>
        </x-form.select>
    </div>

    <!-- Address -->
    <x-form.input label="Address" name="address" value="{{ $student?->address }}" />

    <!-- Guardian -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input label="Guardian Name" name="guardian_name" value="{{ $student?->guardian_name }}" />
        <x-form.input label="Guardian Contact" name="guardian_contact" value="{{ $student?->guardian_contact }}" />
    </div>

    <!-- Photo -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Photo</label>
        <div class="flex items-start gap-4">
            @if ($student?->photo_path)
                <img src="{{ Storage::url($student->photo_path) }}" class="h-20 w-20 rounded-lg object-cover">
            @endif
            <input type="file" name="photo" accept="image/*" class="w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-indigo-700 dark:file:bg-indigo-500/10 dark:file:text-indigo-300">
        </div>
        @if ($student?->photo_path)
            <label class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                <input type="checkbox" name="remove_photo" value="1">
                Remove current photo
            </label>
        @endif
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
        <button type="button" @click="$parent.{{ $student ? 'editOpen' : 'createOpen' }} = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
            Cancel
        </button>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            {{ $student ? 'Update' : 'Create' }} Student
        </button>
    </div>
</form>
