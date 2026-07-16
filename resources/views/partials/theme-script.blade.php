{{-- Sets dark/light class before first paint to avoid a flash. Stored in localStorage('eaj-theme'). --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('eaj-theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        } catch (e) {}
    })();
</script>
