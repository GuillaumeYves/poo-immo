(() => {
    const root = document.getElementById('modal-root');
    if (!root) return;
    const backdrop = root.querySelector('[data-modal-backdrop]');
    const panel = root.querySelector('[data-modal-panel]');
    const content = root.querySelector('[data-modal-content]');

    function show() {
        root.classList.remove('hidden');
        root.classList.add('flex');
        root.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        });
    }

    function hide() {
        backdrop.classList.add('opacity-0');
        backdrop.classList.remove('opacity-100');
        panel.classList.add('opacity-0', 'scale-95');
        panel.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => {
            root.classList.add('hidden');
            root.classList.remove('flex');
            root.setAttribute('aria-hidden', 'true');
            content.innerHTML = '';
            document.body.style.overflow = '';
        }, 200);
    }

    async function openFromUrl(url) {
        content.innerHTML = '<p class="py-8 text-center text-sm text-stone-500">Chargement…</p>';
        show();
        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'fetch', 'Accept': 'text/html' },
                credentials: 'same-origin',
            });
            content.innerHTML = await res.text();
            initFormSync(content);
            wireForm();
        } catch (err) {
            content.innerHTML = '<p class="py-8 text-center text-sm text-red-600">Erreur de chargement.</p>';
        }
    }

    function openFromTemplate(id) {
        const tmpl = document.getElementById(id);
        if (!tmpl || !('content' in tmpl)) return;
        content.innerHTML = '';
        content.appendChild(tmpl.content.cloneNode(true));
        show();
        wireForm();
    }

    function wireForm() {
        const form = content.querySelector('[data-modal-form]');
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalLabel = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            }
            try {
                const res = await fetch(form.action, {
                    method: form.method.toUpperCase() || 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json, text/html' },
                    credentials: 'same-origin',
                });
                const contentType = res.headers.get('Content-Type') || '';
                if (contentType.includes('application/json')) {
                    const data = await res.json().catch(() => ({}));
                    if (data && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                }
                const html = await res.text();
                content.innerHTML = html;
                initFormSync(content);
                wireForm();
            } catch (err) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                    submitBtn.innerHTML = originalLabel;
                }
            }
        });
    }

    function initFormSync(scope) {
        const categorieSelect = scope.querySelector('[data-annonce-categorie]');
        const transactionSelect = scope.querySelector('[data-annonce-transaction]');
        const bienModeInputs = Array.from(scope.querySelectorAll('[data-bien-mode]'));

        function refresh() {
            function syncGroup(selectEl, attr) {
                if (!selectEl) return;
                const current = selectEl.value;
                scope.querySelectorAll('[' + attr + ']').forEach((el) => {
                    const modeGroup = el.closest('[data-bien-mode-field]');
                    const hiddenByMode = modeGroup ? modeGroup.hidden : false;
                    const allowed = (el.getAttribute(attr) || '')
                        .split(',')
                        .map((v) => v.trim())
                        .filter(Boolean);
                    const matches = allowed.includes(current);
                    el.hidden = !matches;
                    el.querySelectorAll('input, select, textarea').forEach((field) => {
                        field.disabled = hiddenByMode || !matches;
                    });
                });
            }

            const bienMode = bienModeInputs.find((input) => input.checked)?.value || 'new';
            scope.querySelectorAll('[data-bien-mode-field]').forEach((el) => {
                const allowed = (el.getAttribute('data-bien-mode-field') || '')
                    .split(',')
                    .map((v) => v.trim())
                    .filter(Boolean);
                const matches = allowed.includes(bienMode);
                el.hidden = !matches;
                el.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.disabled = !matches;
                });
            });

            syncGroup(categorieSelect, 'data-categorie-field');
            syncGroup(transactionSelect, 'data-transaction-field');
        }

        categorieSelect?.addEventListener('change', refresh);
        transactionSelect?.addEventListener('change', refresh);
        bienModeInputs.forEach((input) => input.addEventListener('change', refresh));
        refresh();
    }

    document.addEventListener('click', (e) => {
        const fetcher = e.target.closest('[data-modal-open]');
        if (fetcher) {
            e.preventDefault();
            openFromUrl(fetcher.getAttribute('data-modal-open'));
            return;
        }
        const templater = e.target.closest('[data-modal-template]');
        if (templater) {
            e.preventDefault();
            openFromTemplate(templater.getAttribute('data-modal-template'));
            return;
        }
        if (e.target.closest('[data-modal-close]') || e.target === backdrop) {
            e.preventDefault();
            hide();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !root.classList.contains('hidden')) {
            hide();
        }
    });

    initFormSync(document);
})();
