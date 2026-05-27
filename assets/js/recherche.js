const input    = document.getElementById('recherche-input');
const compteur = document.getElementById('recherche-compteur');
const articles = document.querySelectorAll('#catalogue-liste .annonce');
const total    = articles.length;

function filtrer() {
    const recherche = input.value.toLowerCase();

    let visibles = 0;
    let premiereVisibleVue = false;
    articles.forEach(article => {
        const match = article.dataset.titre.toLowerCase().includes(recherche);
        article.hidden = !match;

        const hr = article.previousElementSibling;
        if (hr && hr.tagName === 'HR') {
            hr.hidden = !match || !premiereVisibleVue;
        }

        if (match) {
            visibles++;
            premiereVisibleVue = true;
        }
    });

    compteur.textContent = visibles + ' / ' + total + ' annonces';
}

input.addEventListener('input', filtrer);
filtrer();
