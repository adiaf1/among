<script>
    document.addEventListener('DOMContentLoaded', function () {
        const offcanvasEl = document.getElementById('masterOffcanvas');
        if (!offcanvasEl) return;

        const form = document.getElementById('masterOffcanvasForm');
        const titleEl = document.getElementById('masterOffcanvasLabel');
        const methodInput = document.getElementById('masterOffcanvasMethod');
        const actionInput = document.getElementById('masterOffcanvasActionInput');
        const modeInput = document.getElementById('masterOffcanvasModeInput');

        const storeUrl = form.dataset.storeUrl;
        const createTitle = form.dataset.createTitle;
        const editTitle = form.dataset.editTitle;
        const ignoredNames = new Set(['_token', '_method', '_form_action', '_form_mode']);

        const clearValidation = function () {
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(function (el) {
                el.remove();
            });
        };

        const resetFields = function () {
            form.querySelectorAll('[name], [data-field]').forEach(function (el) {
                const name = el.getAttribute('name') || el.dataset.field;
                if (!name || ignoredNames.has(name)) return;

                if (el.type === 'checkbox') {
                    el.checked = el.dataset.defaultChecked !== 'false';
                } else {
                    el.value = el.dataset.defaultValue !== undefined ? el.dataset.defaultValue : '';
                }
            });
        };

        const fillFields = function (record) {
            Object.keys(record).forEach(function (key) {
                const el = form.querySelector(`[name="${key}"], [data-field="${key}"]`);
                if (!el) return;

                if (el.type === 'checkbox') {
                    el.checked = Boolean(record[key]);
                } else {
                    el.value = record[key] !== null && record[key] !== undefined ? record[key] : '';
                }
            });
        };

        offcanvasEl.addEventListener('show.bs.offcanvas', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            clearValidation();

            const mode = trigger.dataset.mode || 'create';
            if (mode === 'edit') {
                const action = trigger.dataset.action;
                const record = JSON.parse(trigger.dataset.record || '{}');
                form.action = action;
                methodInput.value = 'PUT';
                actionInput.value = action;
                modeInput.value = 'edit';
                titleEl.textContent = editTitle;
                resetFields();
                fillFields(record);
            } else {
                form.action = storeUrl;
                methodInput.value = 'POST';
                actionInput.value = storeUrl;
                modeInput.value = 'create';
                titleEl.textContent = createTitle;
                resetFields();
            }
        });

        @if($errors->any())
            titleEl.textContent = modeInput.value === 'edit' ? editTitle : createTitle;
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
        @endif
    });
</script>
