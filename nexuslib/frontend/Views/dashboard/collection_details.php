<div id="collection-details-view" class="w-full hidden animate-fade-in">
    
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-700">
        <div class="w-full">
            <button id="btn-mock-back" class="text-slate-400 hover:text-white flex items-center mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver
            </button>
            <h2 class="text-3xl font-bold text-white flex items-center space-x-3">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                <span id="details-collection-title">Tesis de Sistemas</span>
            </h2>
        </div>
        
        <div class="flex space-x-3">
            <button id="btn-mock-add-book" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition border border-slate-700" title="Agregar libros">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
            <button id="btn-edit-collection" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition border border-slate-700" title="Cambiar nombre">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            </button>
            <button id="btn-delete-collection" class="p-2.5 bg-red-900 bg-opacity-30 hover:bg-red-600 text-red-500 hover:text-white rounded-lg transition border border-red-900 border-opacity-50" title="Eliminar colección">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
    </div>

    <div id="collection-books-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
        <!-- Collection items will be injected here -->
    </div>
</div>

<script>
    const COLLECTION_ITEMS_API = '/nexuslib/gateway-service/public/index.php/api/user-library/collections/items';

    async function loadCollectionItems(collectionId) {
        if (!collectionId) return;
        try { sessionStorage.setItem('nexus_current_collection_id', String(collectionId)); } catch (e) { }
        const grid = document.getElementById('collection-books-grid');
        if (!grid) return;

        try {
            const resp = await fetch(`${COLLECTION_ITEMS_API}?collection_id=${encodeURIComponent(collectionId)}`, { method: 'GET' });
            const json = await resp.json().catch(() => ({}));
            const items = Array.isArray(json?.data) ? json.data : [];

            if (items.length === 0) {
                grid.innerHTML = '<div class="col-span-full rounded-lg border border-slate-700 bg-slate-800/60 p-6 text-center text-gray-300">No hay libros en esta colección.</div>';
                return;
            }

            grid.innerHTML = items.map((libro) => {
                const origenRaw = String(libro?.origen || 'Desconocido').trim();
                const tituloRaw = String(libro?.titulo || 'Sin título').trim();
                const codigoRaw = String(libro?.codigo || '').trim();
                const portadaRaw = String(libro?.portada_url || '').trim();
                const colors = window.colorMap?.[origenRaw] || { hover: 'hover:border-slate-500', shadow: 'shadow-slate-500/10', border: 'border-slate-500', text: 'text-slate-300', badgeBg: 'bg-slate-800', bg: 'bg-slate-800' };
                const title = escapeHtml(tituloRaw);
                const origin = escapeHtml(origenRaw);
                const portada = escapeHtml(portadaRaw);
                const codigoJs = codigoRaw.replace(/\\/g,'\\\\').replace(/'/g, "\\'");
                const hasCover = portadaRaw !== '';
                const coverHtml = hasCover
                    ? `<img src="${portada}" alt="Portada de ${title}" class="w-20 h-28 rounded flex-shrink-0 shadow-md object-cover">`
                    : (origenRaw === 'Inventario UPT'
                        ? `<img src="/nexuslib/frontend/public/images/logo-upt.png" alt="Portada UPT" class="w-20 h-28 rounded flex-shrink-0 shadow-md object-cover">`
                        : `<div class="w-20 h-28 bg-slate-800 border-2 border-slate-700 rounded-lg flex flex-col items-center justify-center text-gray-500 flex-shrink-0 shadow-md">
                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span class="text-[10px] font-semibold uppercase tracking-wider">Sin Portada</span>
                        </div>`
                    );

                // Build details href similarly to saved_books.php
                const isPhysical = origenRaw === 'Inventario UPT' || origenRaw === 'Biblioteca UPT';
                const detailsView = isPhysical ? 'details/details_physical' : 'details/details_digital';
                const detailsHref = `${window.location.pathname}?view=${encodeURIComponent(detailsView)}&id=${encodeURIComponent(codigoRaw)}&titulo=${encodeURIComponent(tituloRaw)}&origen=${encodeURIComponent(origenRaw)}`;

                return `
                    <div class="bg-slate-700 bg-opacity-40 border border-slate-600 rounded-lg p-5 ${colors.hover} transition-all ${colors.shadow} group h-full flex flex-col">
                        <div class="flex gap-6">
                            ${coverHtml}
                            <div class="flex-1 flex flex-col justify-center">
                                <h4 class="text-lg text-white font-semibold transition-colors">${title}</h4>
                                <span class="inline-block mt-2 w-max ${colors.badgeBg || 'bg-slate-800'} border ${colors.border} ${colors.text} text-xs px-3 py-1 rounded-full">${origin}</span>
                                <div class="flex gap-3 mt-4">
                                    <a href="${detailsHref}" class="bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg text-sm transition-colors text-white">Ver detalles</a>
                                    <button data-saved-book-id="${libro.id}" class="btn-remove-from-collection text-red-400 hover:text-red-300 text-sm font-medium flex items-center gap-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (e) {
            console.error('loadCollectionItems error', e);
            grid.innerHTML = '<div class="col-span-full rounded-lg border border-red-400/60 bg-red-500/10 p-6 text-center text-red-200">Error cargando elementos.</div>';
        }
    }

    // Delegated handler for removing item from collection
    document.addEventListener('click', async function(event) {
        const btn = event.target.closest && event.target.closest('.btn-remove-from-collection');
        if (!btn) return;
        const savedBookId = btn.getAttribute('data-saved-book-id');
        const collectionId = window.currentCollectionId || null;
        if (!collectionId || !savedBookId) return;

        try {
            const r = await fetch(COLLECTION_ITEMS_API, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ collection_id: Number(collectionId), saved_book_id: Number(savedBookId) })
            });
            const j = await r.json().catch(() => ({}));
            if (!r.ok || !j.success) throw new Error(j?.message || 'Error removing item');
            await loadCollectionItems(collectionId);
            if (typeof window.showToast === 'function') window.showToast('Libro eliminado de la colección', 'success');
        } catch (e) {
            console.error('remove from collection error', e);
            if (typeof window.showToast === 'function') window.showToast('Error al eliminar libro', 'error');
        }
    });

    // Ensure collection list refreshes when user navigates back (bfcache/pageshow)
    window.addEventListener('pageshow', function(event) {
        const collId = window.currentCollectionId || sessionStorage.getItem('nexus_current_collection_id');
        if (collId) {
            try { loadCollectionItems(Number(collId)); } catch (e) { /* ignore */ }
        }
    });

    // Fallback: refresh when tab becomes visible again
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            const collId = window.currentCollectionId || sessionStorage.getItem('nexus_current_collection_id');
            if (collId) {
                try { loadCollectionItems(Number(collId)); } catch (e) { /* ignore */ }
            }
        }
    });
</script>