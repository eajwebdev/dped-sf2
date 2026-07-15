{{-- One delegated SweetAlert handler for every <form class="js-confirm"> on the page
     (js-confirm-delete is kept as an alias for the delete components). Delegation means
     it works no matter how many buttons render; the JS flag guarantees a single document
     listener even if this partial is included more than once.

     Per-form data attributes:
       data-title    — dialog heading
       data-message  — dialog body text
       data-confirm  — confirm button label (default "Yes, delete it")
       data-icon     — SweetAlert icon: warning | question | info (default "warning") --}}
<script>
    if (!window.__confirmActionBound) {
        window.__confirmActionBound = true;
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.js-confirm, .js-confirm-delete');
            if (!form || form.dataset.confirmed === '1') return;
            e.preventDefault();
            Swal.fire({
                title: form.dataset.title || 'Delete this record?',
                text: form.dataset.message || 'This action cannot be undone.',
                icon: form.dataset.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: form.dataset.icon === 'question' ? '#4f46e5' : '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: form.dataset.confirm || 'Yes, delete it',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        }, true);
    }
</script>
