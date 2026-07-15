# 🎨 Styles & Components Reference

Quick copy-paste reference for common UI patterns used in the modernized app.

---

## 🔘 Button Examples

### Primary Button (Main Actions)
```blade
<button class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
    </svg>
    Create Item
</button>
```

### Secondary Button
```blade
<button class="rounded-lg bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
    Cancel
</button>
```

### Icon-Only Button (Actions)
```blade
<!-- View -->
<button class="inline-flex items-center justify-center p-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors" title="View">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
</button>

<!-- Edit -->
<button class="inline-flex items-center justify-center p-2.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors" title="Edit">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
</button>

<!-- Delete - Use <x-delete-confirm-btn> instead -->
```

---

## 🏷️ Badge Examples

### Active/Success Badge
```blade
<span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
    Active
</span>
```

### Inactive Badge
```blade
<span class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
    <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>
    Inactive
</span>
```

### Status Badges (Use Component)
```blade
<!-- Using component -->
<x-badge variant="green">Active</x-badge>
<x-badge variant="red">Inactive</x-badge>
<x-badge variant="blue">Pending</x-badge>
<x-badge variant="amber">Warning</x-badge>

<!-- Without dot -->
<x-badge variant="gray" :dotted="false">Neutral</x-badge>
```

---

## 📊 Table Examples

### Table Container
```blade
<div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <!-- content -->
        </table>
    </div>
</div>
```

### Table Header
```blade
<thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
    <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
        <th class="px-6 py-4">Column Name</th>
        <th class="px-6 py-4">Another</th>
        <th class="px-6 py-4 text-right">Actions</th>
    </tr>
</thead>
```

### Table Row
```blade
<tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
    @forelse ($items as $item)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                {{ $item->name }}
            </td>
            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                {{ $item->description }}
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                    <!-- action buttons -->
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center">
                    <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No items found</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Try adjusting your filters</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
```

---

## 🎪 Modal Examples

### Create/Edit Modal
```blade
<div x-show="createOpen" x-cloak 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0"
     @keydown.escape="createOpen = false"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" 
     @click.self="createOpen = false">
    
    <div x-show="createOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95" 
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 scale-100" 
         x-transition:leave-end="opacity-0 scale-95"
         class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-gray-800 shadow-2xl">
        
        <!-- Header -->
        <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-5 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-700">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Modal Title</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Subtitle or description</p>
            </div>
            <button @click="createOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-6 overflow-y-auto">
            <!-- Form content here -->
        </div>
    </div>
</div>
```

---

## 🔍 Search Input

### With Icon
```blade
<div class="relative">
    <input type="search" name="q" placeholder="Search…"
           class="w-full sm:w-64 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm pl-4 pr-10 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
    <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
</div>
```

---

## 📝 Form Inputs

### Text Input
```blade
<input type="text" placeholder="Enter value…"
       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
```

### Select Dropdown
```blade
<select class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
    <option>Select option</option>
</select>
```

### Textarea
```blade
<textarea placeholder="Enter text…"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none"></textarea>
```

### File Input
```blade
<input type="file" accept=".xlsx,.xls,.csv"
       class="block w-full text-sm
         file:mr-4 file:py-2.5 file:px-4
         file:rounded-lg file:border-0
         file:text-sm file:font-semibold
         file:bg-indigo-600 file:text-white
         hover:file:bg-indigo-700
         file:cursor-pointer file:transition-colors
         dark:file:bg-indigo-500">
```

---

## 🎯 Toolbar/Header Section

### With Search + Buttons
```blade
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <!-- Search/Filters -->
    <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative">
            <input type="search" name="q" value="{{ $search ?? '' }}" 
                   placeholder="Search…"
                   class="w-full sm:w-64 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm pl-4 pr-10 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button type="submit" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            Search
        </button>
    </form>
    
    <!-- Action Buttons -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <svg class="h-4 w-4"><!-- icon --></svg>
            Export
        </button>
        <button @click="createOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            New
        </button>
    </div>
</div>
```

---

## 🌐 Responsive Patterns

### Mobile-First Flexbox
```blade
<!-- Column on mobile, row on sm+ screens -->
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
</div>
```

### Hide/Show by Breakpoint
```blade
<!-- Show only on mobile -->
<div class="sm:hidden">Mobile Only</div>

<!-- Hide on mobile, show on sm+ -->
<div class="hidden sm:block">Desktop Only</div>

<!-- Hide on mobile/tablet, show on lg+ -->
<div class="hidden lg:block">Large Desktop Only</div>
```

### Responsive Width
```blade
<div class="w-full sm:w-64">
    <!-- Full width on mobile, fixed on sm+ -->
</div>
```

### Responsive Padding
```blade
<div class="p-4 sm:p-6 lg:p-8">
    <!-- Less padding on mobile, more on larger screens -->
</div>
```

---

## 🌙 Dark Mode

Every styled element should have dark mode support:

```blade
<!-- Background -->
<div class="bg-white dark:bg-gray-800">

<!-- Text -->
<p class="text-gray-900 dark:text-white">

<!-- Border -->
<div class="border border-gray-200 dark:border-gray-700">

<!-- Hover states -->
<button class="hover:bg-gray-100 dark:hover:bg-gray-700">

<!-- Gradients -->
<div class="bg-gradient-to-r from-gray-50 dark:from-gray-800">
```

---

## ✨ Animations

### Fade + Scale (for modals)
```blade
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0 scale-95"
x-transition:enter-end="opacity-100 scale-100"
x-transition:leave="transition ease-in duration-150"
x-transition:leave-start="opacity-100 scale-100"
x-transition:leave-end="opacity-0 scale-95"
```

### Fade Only
```blade
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="transition ease-in duration-150"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0"
```

### Hover Transitions
```blade
class="transition-all duration-200 hover:shadow-lg hover:scale-105"
```

---

## 🎪 Common Layouts

### Page Layout
```blade
<x-admin-layout title="Page Title">
    <x-slot name="breadcrumbs">Admin / Page</x-slot>
    
    <div class="space-y-6">
        <!-- Toolbar -->
        <div><!-- buttons/search --></div>
        
        <!-- Table or Content -->
        <div><!-- main content --></div>
        
        <!-- Pagination -->
        <div><!-- pagination --></div>
    </div>
</x-admin-layout>
```

---

## 📋 Copy-Paste Blocks

Save time by copying these complete sections for your pages!

See `TEMPLATE_INDEX_PAGE.blade.php` for a full working example.

---

## 🎨 Color Hex Values

For reference when working with design tools:

```
Indigo:  #4F46E5, #4338CA
Emerald: #059669, #10B981
Red:     #DC2626, #EF4444
Gray:    #F9FAFB, #E5E7EB, #9CA3AF, #4B5563
```

---

## ❓ Tips

- Use `dark:` prefix for ALL color classes
- Always add transition classes to interactive elements
- Use `rounded-2xl` for modern look (not `rounded-lg`)
- Icon buttons use `p-2.5` for good touch targets
- Tables use `px-6 py-4` for spacing
- Modals use `px-6 py-5` for headers, `p-6` for content
- Keep gaps consistent: use `gap-4` or `gap-6`
- Always include titles and descriptions in modals
- Test dark mode with every change

---

## 🚀 Quick Reference

**Most Used Classes:**
- Padding: `px-6 py-4` (tables), `p-6` (cards), `px-6 py-5` (headers)
- Borders: `border border-gray-200 dark:border-gray-700`
- Text: `text-sm font-medium` (default), `text-xs` (meta)
- Rounded: `rounded-lg` (buttons), `rounded-2xl` (cards)
- Gap: `gap-2` (buttons), `gap-4` (sections)
- Hover: `hover:bg-gray-100 dark:hover:bg-gray-700`
- Focus: `focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20`

---

Ready to build! Copy what you need and adapt to your page. 🚀
