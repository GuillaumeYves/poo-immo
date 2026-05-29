(() => {
    const input = document.getElementById('recherche-input');
    const compteur = document.getElementById('recherche-compteur');
    const liste = document.getElementById('catalogue-liste');
    const tri = document.getElementById('tri-prix');
    const categorieField = document.querySelector('[data-catalogue-categorie]');
    const filtreTransaction = document.getElementById('filtre-transaction');
    if (!input || !compteur || !liste) return;

    function categoriesSelectionnees() {
        if (!categorieField) return [];
        return Array.from(categorieField.querySelectorAll('[data-multi-picker-badge]'))
            .map((b) => b.dataset.multiPickerBadge);
    }

    // Ordre initial figé pour pouvoir revenir au tri « par défaut ».
    const cards = Array.from(liste.querySelectorAll('.annonce-card'));
    const total = cards.length;

    function masquerOptionDefaut(select) {
        if (!select) return;

        const defaultValue = select.dataset.defaultValue;
        if (!defaultValue) return;

        const option = Array.from(select.options).find((opt) => opt.value === defaultValue);
        if (!option) return;

        option.hidden = select.value === defaultValue;
    }

    function prixDe(card) {
        const brut = parseFloat(card.dataset.prix || '');
        return Number.isNaN(brut) ? null : brut;
    }

    function trier() {
        const sens = tri ? tri.value : 'defaut';
        if (sens === 'defaut') {
            cards.forEach((card) => liste.appendChild(card));
            return;
        }

        const ordonnees = cards.slice().sort((a, b) => {
            const pa = prixDe(a);
            const pb = prixDe(b);
            // Les annonces sans prix exploitable sont reléguées en fin de liste.
            if (pa === null) return 1;
            if (pb === null) return -1;
            return sens === 'asc' ? pa - pb : pb - pa;
        });

        ordonnees.forEach((card) => liste.appendChild(card));
    }

    function filtrer() {
        const recherche = input.value.trim().toLowerCase();
        const categories = categoriesSelectionnees();
        const transaction = filtreTransaction ? filtreTransaction.value : 'tout';
        const filtreActif = recherche !== '' || categories.length > 0 || transaction !== 'tout';

        let visibles = 0;
        cards.forEach((card) => {
            const haystack = card.dataset.recherche || (card.dataset.titre || '').toLowerCase();
            const matchTexte = recherche === '' || haystack.includes(recherche);
            const matchCategorie = categories.length === 0 || categories.includes(card.dataset.categorie);
            const matchTransaction = transaction === 'tout' || card.dataset.transaction === transaction;
            const match = matchTexte && matchCategorie && matchTransaction;
            card.hidden = !match;
            if (match) visibles++;
        });

        if (!filtreActif) {
            compteur.textContent = `${total} annonce${total > 1 ? 's' : ''}`;
        } else {
            compteur.textContent = `${visibles} / ${total} annonce${total > 1 ? 's' : ''}`;
        }
    }

    input.addEventListener('input', filtrer);
    filtreTransaction?.addEventListener('change', () => {
        masquerOptionDefaut(filtreTransaction);
        filtrer();
    });
    document.addEventListener('multipicker:change', filtrer);
    tri?.addEventListener('change', () => {
        masquerOptionDefaut(tri);
        trier();
    });

    masquerOptionDefaut(filtreTransaction);
    masquerOptionDefaut(tri);
    trier();
    filtrer();
})();
