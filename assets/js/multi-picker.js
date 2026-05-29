(() => {
    const ALL = '__all__';
    const BADGE_CLASS = 'inline-flex max-w-full items-center gap-1 pl-2.5 pr-1 py-0.5 text-xs font-medium rounded-full bg-khaki-500/10 text-khaki-700 ring-1 ring-khaki-500/20';
    const REMOVE_CLASS = 'inline-flex h-4 w-4 items-center justify-center rounded-full text-khaki-600 hover:bg-khaki-500/20 hover:text-khaki-800 transition-colors';

    document.querySelectorAll('[data-multi-picker-field]').forEach((field) => {
        const picker = field.querySelector('[data-multi-picker]');
        const badges = field.querySelector('[data-multi-picker-badges]');
        if (!picker || !badges) return;

        const name = picker.dataset.multiPicker;

        const optionFor = (value) => Array.from(picker.options).find((o) => o.value === value) || null;
        const regularOptions = () => Array.from(picker.options).filter((o) => o.value !== '' && o.value !== ALL);
        const dejaPresent = (value) => Array.from(badges.querySelectorAll('[data-multi-picker-badge]'))
            .some((b) => b.dataset.multiPickerBadge === value);

        function updateAllOption() {
            const allOption = optionFor(ALL);
            const options = regularOptions();
            const noOptionsLeft = options.length > 0 && options.every((opt) => opt.hidden);

            if (allOption) {
                allOption.hidden = noOptionsLeft;
            }

            picker.disabled = noOptionsLeft;
            picker.classList.toggle('opacity-60', noOptionsLeft);
            picker.classList.toggle('cursor-not-allowed', noOptionsLeft);
            picker.classList.toggle('bg-beige-50', noOptionsLeft);
            picker.classList.toggle('text-stone-400', noOptionsLeft);
        }

        function makeBadge(value, label) {
            const span = document.createElement('span');
            span.dataset.multiPickerBadge = value;
            span.className = BADGE_CLASS;

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name + '[]';
            input.value = value;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.dataset.multiPickerRemove = '';
            remove.setAttribute('aria-label', 'Retirer ' + label);
            remove.className = REMOVE_CLASS;
            remove.innerHTML = '<i class="fa-solid fa-xmark text-[0.6rem]"></i>';

            const text = document.createElement('span');
            text.className = 'min-w-0 truncate';
            text.textContent = label;

            span.append(input, text, remove);
            return span;
        }

        function ajouter(value, label) {
            if (value === '' || value === ALL || dejaPresent(value)) return;
            badges.appendChild(makeBadge(value, label));
            const opt = optionFor(value);
            if (opt) opt.hidden = true;
            updateAllOption();
        }

        function emit() {
            field.dispatchEvent(new CustomEvent('multipicker:change', { bubbles: true }));
        }

        picker.addEventListener('change', () => {
            const value = picker.value;
            picker.value = '';

            if (value === ALL) {
                regularOptions().forEach((opt) => {
                    if (!opt.hidden) {
                        ajouter(opt.value, opt.textContent.trim());
                    }
                });
            } else if (value !== '') {
                const opt = optionFor(value);
                ajouter(value, opt ? opt.textContent.trim() : value);
            } else {
                return;
            }

            emit();
        });

        badges.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-multi-picker-remove]');
            if (!btn) return;
            const badge = btn.closest('[data-multi-picker-badge]');
            if (!badge) return;

            const opt = optionFor(badge.dataset.multiPickerBadge);
            if (opt) opt.hidden = false;
            badge.remove();
            updateAllOption();
            emit();
        });

        updateAllOption();
    });
})();
