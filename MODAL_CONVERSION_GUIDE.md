# Modal + Icon Conversion Guide

## Pages Completed ✅
- **Students** — Full modal + icon conversion done

## Pattern to Apply to Remaining Pages

### 1. **Teachers** (Priority 1)
```blade
<!-- In resources/views/admin/teachers/index.blade.php -->
<x-admin-layout>
    <div x-data="{ createOpen: false, editOpen: false, editTeacher: null }">
        <!-- Toolbar -->
        <button @click="createOpen = true" ...>
            <x-icon-btn icon="plus" /> New Teacher
        </button>

        <!-- Create Modal -->
        <div x-show="createOpen" x-cloak ...>
            @include('admin.teachers.form-inline', ['teacher' => null])
        </div>

        <!-- Edit Modal -->
        <div x-show="editOpen" x-cloak ...>
            <template x-if="editTeacher">
                @include('admin.teachers.form-inline', ['teacher' => null])
            </template>
        </div>

        <!-- Table with Icons -->
        <table>
            <tbody>
                @foreach ($teachers as $t)
                    <tr>
                        ...
                        <td>
                            <a href="{{ route(..., $t) }}">
                                <x-icon-btn icon="view" />
                            </a>
                            <button @click="editTeacher = @json($t); editOpen = true">
                                <x-icon-btn icon="edit" color="blue" />
                            </button>
                            <form method="POST" action="{{ route(..., $t) }}">
                                <button type="submit">
                                    <x-icon-btn icon="delete" color="red" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
```

Create `resources/views/admin/teachers/form-inline.blade.php`:
```blade
<form method="POST" action="{{ $teacher ? route('admin.teachers.update', $teacher) : route('admin.teachers.store') }}">
    @csrf
    @if ($teacher) @method('PATCH') @endif
    
    <!-- Form fields here (copy from edit.blade.php) -->
    
    <div class="flex justify-end gap-2 pt-4 border-t">
        <button type="button" @click="editOpen = false" ...>Cancel</button>
        <button type="submit" ...>{{ $teacher ? 'Update' : 'Create' }} Teacher</button>
    </div>
</form>
```

### 2. **Subjects** (Priority 2)
Same pattern. Create modal for add/edit, use icons for view/edit/delete.

### 3. **Grade Levels** (Priority 3)
Same pattern.

### 4. **Sections** (Priority 4)
Same pattern. Add adviser dropdown in the form.

### 5. **School Years** (Priority 5)
Same pattern, but keep the lifecycle action buttons (Activate/Close/Archive) outside the modal.

## Quick Conversion Checklist

For each page:
- [ ] Read the current `index.blade.php`
- [ ] Copy the structure from the Students example above
- [ ] Create `form-inline.blade.php` by copying form inputs from `create.blade.php` or `edit.blade.php`
- [ ] Replace "New X", "Edit", "Delete" text buttons with `<x-icon-btn>` components
- [ ] Add `x-data` with `{ createOpen: false, editOpen: false, edit[Model]: null }` to the parent div
- [ ] Remove or redirect the individual create/edit route views (optional: keep them as fallbacks)
- [ ] Test the modals open/close with Alpine

## Icon Reference
- `view` — view/show
- `edit` — edit
- `delete` — delete
- `plus` — create new
- `download` — export
- `upload` — import
- `close` — close modal

## Colors
```blade
<x-icon-btn icon="edit" color="blue" />      <!-- blue-600 -->
<x-icon-btn icon="delete" color="red" />     <!-- red-600 -->
<x-icon-btn icon="view" color="gray" />      <!-- gray-600 (default) -->
<x-icon-btn icon="plus" color="green" />     <!-- green-600 -->
```

## Alpine x-data Pattern for Multiple Modals
```blade
<div x-data="{
    createOpen: false,
    editOpen: false,
    deleteOpen: false,
    edit[Model]: null
}">
```

## Folder Structure After Conversion
```
resources/views/admin/
├── students/
│   ├── index.blade.php          ← modals inline
│   ├── form-inline.blade.php    ← shared form
│   └── show.blade.php           ← keep for detail view
├── teachers/
│   ├── index.blade.php          ← modals inline
│   ├── form-inline.blade.php    ← shared form
│   └── show.blade.php           ← keep (optional)
├── subjects/
│   ├── index.blade.php
│   ├── form-inline.blade.php
├── grade-levels/
│   ├── index.blade.php
│   ├── form-inline.blade.php
├── sections/
│   ├── index.blade.php
│   └── form-inline.blade.php
└── school-years/
    ├── index.blade.php
    └── form-inline.blade.php
```

## Deletion: Keep or Remove?
- **Keep** `create.blade.php`, `edit.blade.php` — useful as fallback single-page forms
- **Optional** — redirect routes to index if you want pure modal-only UX

## Testing
After converting each page:
```bash
npm run build
php artisan test
```

All tests should pass (63/64 from before).
