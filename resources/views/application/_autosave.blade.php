<script>
(function () {
    const form = document.getElementById('wizard-form');
    const statusEl = document.getElementById('autosave-status');
    const toastEl = document.getElementById('autosave-toast');
    if (!form) return;

    const stepKey = @json($stepKey);
    let timer = null;
    let toastTimer = null;

    function collect() {
        const data = {};
        new FormData(form).forEach((value, key) => {
            if (key === '_token' || key === '_method') return;
            data[key] = value;
        });
        return data;
    }

    function showToast() {
        if (!toastEl) return;
        clearTimeout(toastTimer);
        toastEl.classList.add('is-visible');
        toastTimer = setTimeout(() => toastEl.classList.remove('is-visible'), 1600);
    }

    function save() {
        if (statusEl) statusEl.textContent = 'Saving…';

        fetch(@json(route('application.autosave')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ step_key: stepKey, data: collect() }),
        })
            .then((r) => r.ok ? r.json() : Promise.reject(r))
            .then(() => {
                if (statusEl) statusEl.textContent = '';
                showToast();
            })
            .catch(() => {
                if (statusEl) statusEl.textContent = 'Could not save — check your connection';
            });
    }

    form.addEventListener('input', () => {
        if (statusEl) statusEl.textContent = 'Unsaved changes…';
        clearTimeout(timer);
        timer = setTimeout(save, 900);
    });

    form.addEventListener('change', (e) => {
        if (e.target.type === 'radio' || e.target.tagName === 'SELECT') {
            clearTimeout(timer);
            save();
        }
    });
})();
</script>
