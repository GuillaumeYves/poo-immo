(() => {
    const list = document.getElementById('catalogue-liste');
    if (!list) return;
    const buttons = document.querySelectorAll('[data-view-toggle]');

    const storageKey = 'poo-immo:view-mode';
    const activeClasses = ['bg-khaki-500', 'text-white', 'shadow-sm'];
    const inactiveClasses = ['text-stone-500'];
    // En dessous de ce seuil (= breakpoint Tailwind `sm`), on force la vue grille.
    const petitEcran = window.matchMedia('(max-width: 639.98px)');

    let preference = localStorage.getItem(storageKey) === 'grille' ? 'grille' : 'cartouche';

    function render(mode) {
        list.setAttribute('data-view-mode', mode);
        buttons.forEach((btn) => {
            const active = btn.getAttribute('data-view-toggle') === mode;
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            activeClasses.forEach((c) => btn.classList.toggle(c, active));
            inactiveClasses.forEach((c) => btn.classList.toggle(c, !active));
        });
    }

    function update() {
        render(petitEcran.matches ? 'grille' : preference);
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            preference = btn.getAttribute('data-view-toggle');
            localStorage.setItem(storageKey, preference);
            update();
        });
    });

    petitEcran.addEventListener('change', update);
    update();
})();
