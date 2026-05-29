(() => {
    document.querySelectorAll('[data-export-section]').forEach((section) => {
        const datasetNode = section.querySelector('[data-export-dataset]');
        const form = section.querySelector('[data-export-form]');
        const count = section.querySelector('[data-export-count]');
        const downloadLinks = Array.from(section.querySelectorAll('[data-export-download]'));
        if (!datasetNode || !form || !count) return;

        let items = [];
        try {
            items = JSON.parse(datasetNode.textContent || '[]');
        } catch {
            return;
        }

        const multiNames = (section.dataset.multiFields || '')
            .split(',')
            .map((name) => name.trim())
            .filter(Boolean);
        const maxField = section.dataset.maxField || '';
        const maxKind = section.dataset.maxKind || '';
        const singular = section.dataset.countSingular || 'élément';
        const plural = section.dataset.countPlural || singular + 's';

        function valeursSelectionnees(name) {
            return Array.from(form.querySelectorAll(`input[type="hidden"][name="${name}[]"]`))
                .map((input) => input.value)
                .filter(Boolean);
        }

        function parseMax(value) {
            const normalized = value.trim().replace(/\s+/g, '').replace(',', '.');
            if (normalized === '' || !/^\d+(?:\.\d{1,2})?$/.test(normalized)) {
                return null;
            }

            return Number.parseFloat(normalized);
        }

        function filtresCourants() {
            const filters = {};
            multiNames.forEach((name) => {
                filters[name] = valeursSelectionnees(name);
            });
            filters.max = maxField ? parseMax(form.elements[maxField]?.value || '') : null;

            return filters;
        }

        function valeurMaxFiltrable(item) {
            if (maxKind === 'montant') {
                const brut = item.prix_courant ?? item.loyer ?? null;
                if (brut === null || brut === '') return null;
                const montant = Number.parseFloat(String(brut));
                return Number.isNaN(montant) ? null : montant;
            }

            if (maxKind === 'surface') {
                const surface = Number.parseFloat(String(item.surface_m2 ?? ''));
                return Number.isNaN(surface) ? null : surface;
            }

            return null;
        }

        function correspond(item, filters) {
            for (const name of multiNames) {
                if (filters[name].length > 0 && !filters[name].includes(String(item[name] ?? ''))) {
                    return false;
                }
            }

            if (filters.max !== null) {
                const valeur = valeurMaxFiltrable(item);
                if (valeur === null || valeur > filters.max) {
                    return false;
                }
            }

            return true;
        }

        function mettreAJourLiens(total) {
            downloadLinks.forEach((link) => {
                const params = new URLSearchParams(new FormData(form));
                params.set('download', '1');
                params.set('format', link.dataset.format || '');

                link.href = '?' + params.toString();
                link.classList.toggle('pointer-events-none', total === 0);
                link.classList.toggle('opacity-40', total === 0);
                link.setAttribute('aria-disabled', total === 0 ? 'true' : 'false');
            });
        }

        function mettreAJour() {
            const filters = filtresCourants();
            const total = items.filter((item) => correspond(item, filters)).length;
            count.textContent = `${total} ${total > 1 ? plural : singular}`;
            mettreAJourLiens(total);
        }

        form.addEventListener('input', mettreAJour);
        form.addEventListener('change', mettreAJour);
        section.addEventListener('multipicker:change', mettreAJour);

        mettreAJour();
    });
})();
