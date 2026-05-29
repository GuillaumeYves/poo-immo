<section class="mb-5 flex justify-end">
    <button type="button"
            data-modal-open="?action=bien_create&fragment=1"
            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-md bg-khaki-500 text-white shadow-sm hover:bg-khaki-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 ease-soft">
        <i class="fa-solid fa-plus text-xs"></i>
        Créer un bien
    </button>
</section>

<?php if ($biens === []): ?>
    <section class="rounded-lg border border-dashed border-beige-300 bg-white p-10 text-center text-stone-500">
        <p class="text-sm">Aucun bien sans annonce pour le moment.</p>
    </section>
<?php else: ?>
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($biens as $bien): ?>
            <?php
            $iconClass = $bien['categorie'] === 'appartement' ? 'fa-building' : 'fa-house';
            $badgeBits = array_filter([$bien['categorieLibelle'], $bien['typeLibelle'] ?? null]);
            ?>
            <article class="bg-white border border-beige-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-khaki-400 transition-all duration-200 ease-soft">
                <div class="relative h-36 bg-gradient-to-br from-beige-100 to-beige-200 flex items-center justify-center">
                    <i class="fa-solid <?= $iconClass ?> text-5xl text-khaki-600/40"></i>
                    <span class="absolute top-2 left-2 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide rounded bg-white/95 text-stone-700 ring-1 ring-beige-200">
                        <?= $this->e(implode(' · ', $badgeBits)) ?>
                    </span>
                </div>

                <div class="p-4 flex flex-col gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400"><?= $this->e($bien['ville']) ?></p>
                        <h2 class="mt-1 text-base font-semibold text-stone-900"><?= $this->e($bien['titre']) ?></h2>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <?php foreach ($bien['attributs'] as $attr): ?>
                            <div class="min-w-0">
                                <dt class="text-[0.7rem] uppercase tracking-wide text-stone-400"><?= $this->e($attr[0]) ?></dt>
                                <dd class="font-medium text-stone-700 truncate"><?= $this->e($attr[1]) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>

                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
