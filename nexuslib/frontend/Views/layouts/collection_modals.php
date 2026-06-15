<div id="toast-quick-save" class="hidden fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl z-50 flex items-center space-x-4 transition-opacity duration-300">
    <span class="font-medium">Guardado</span>
    <div class="h-5 w-px bg-slate-600"></div>
    <button onclick="openCollectionModal(window.currentSavedBookId)" class="text-blue-400 font-bold hover:text-blue-300 transition-colors">Colecciones ></button>
</div>

<div id="collection-modal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 transition-opacity">
    <div class="bg-slate-900 rounded-2xl w-11/12 max-w-md p-6 text-white shadow-2xl border border-slate-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Guardar en colección</h3>
            <button onclick="closeCollectionModal()" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        
        <div id="modal-collections-list" class="max-h-60 overflow-y-auto mb-4 space-y-2 pr-2">
            </div>

        <hr class="border-slate-700 mb-4">

        <div class="flex space-x-2">
            <input type="text" id="new-collection-name" placeholder="Nueva colección..." class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
            <button onclick="createAndSaveCollection()" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg font-semibold transition">Crear</button>
        </div>
    </div>
</div>

<div id="toast-collection-saved" class="hidden fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl z-50 flex items-center space-x-4 transition-opacity duration-300">
    <span class="font-medium" id="toast-collection-msg">Se guardó en...</span>
    <div class="h-5 w-px bg-slate-600"></div>
    <button onclick="goToCollection()" class="text-blue-400 font-bold hover:text-blue-300 transition-colors">Ver ></button>
</div>

<!-- Bulk add modal: add saved_books to current collection excluding already present -->
<div id="bulk-add-modal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 transition-opacity">
    <div class="bg-slate-900 rounded-2xl w-11/12 max-w-2xl p-6 text-white shadow-2xl border border-slate-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Agregar libros a la colección</h3>
            <button onclick="closeBulkAddModal()" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
        </div>

        <div id="bulk-books-list" class="max-h-72 overflow-y-auto grid grid-cols-1 gap-3 mb-4">
            <!-- available books will be rendered here -->
        </div>

        <div class="flex justify-end">
            <button id="bulk-add-save-btn" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg font-semibold transition">Guardar seleccionados</button>
        </div>
    </div>
</div>
</div>

<!-- Edit collection modal -->
<div id="edit-collection-modal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-slate-900 rounded-2xl w-11/12 max-w-md p-6 text-white shadow-2xl border border-slate-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Cambiar nombre de la colección</h3>
            <button onclick="closeEditCollectionModal()" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <div class="mb-4">
            <input id="edit-collection-input" type="text" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500" placeholder="Nuevo nombre">
        </div>
        <div class="flex justify-end space-x-2">
            <button id="edit-collection-cancel" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600">Cancelar</button>
            <button id="edit-collection-save" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700">Guardar cambios</button>
        </div>
    </div>
</div>

<!-- Confirm delete modal -->
<div id="confirm-delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-slate-900 rounded-2xl w-11/12 max-w-md p-6 text-white shadow-2xl border border-slate-700">
        <div class="flex items-start gap-4 mb-4">
            <div class="text-red-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 1112 3a9 9 0 019 9z"></path></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">¿Estás seguro?</h3>
                <p class="text-sm text-slate-400">¿Deseas eliminar esta colección y todos sus enlaces?</p>
            </div>
        </div>
        <div class="flex justify-end space-x-2">
            <button id="confirm-delete-cancel" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600">Cancelar</button>
            <button id="confirm-delete-accept" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700">Eliminar colección</button>
        </div>
    </div>
</div>

<script>
    const COLLECTIONS_API_BASE = '/nexuslib/gateway-service/public/index.php/api/user-library/collections';

    let currentCollectionId = null;
    let currentSavedBookId = null;
    // Expose saved book id globally for external flows (search/details)
    window.currentSavedBookId = window.currentSavedBookId || null;

    function closeCollectionModal() {
        const modal = document.getElementById('collection-modal');
        if (modal) modal.classList.add('hidden');
    }

    async function openCollectionModal(savedBookId = null) {
        document.getElementById('toast-quick-save')?.classList.add('hidden');
        // Preserve existing global savedBookId when called without argument
        if (savedBookId !== null && savedBookId !== undefined) {
            currentSavedBookId = Number(savedBookId);
            window.currentSavedBookId = currentSavedBookId;
        } else {
            currentSavedBookId = window.currentSavedBookId ? Number(window.currentSavedBookId) : null;
        }
        await loadUserCollections(currentSavedBookId);
        const modal = document.getElementById('collection-modal');
        if (modal) modal.classList.remove('hidden');
        const input = document.getElementById('new-collection-name');
        if (input) input.focus();
    }

    async function loadUserCollections(savedBookId = null) {
        const userUuid = (localStorage.getItem('user_uuid') || '').trim();
        if (userUuid === '') return;

        try {
            const resp = await fetch(`${COLLECTIONS_API_BASE}?user_uuid=${encodeURIComponent(userUuid)}`, { method: 'GET' });
            const json = await resp.json().catch(() => ({}));
            const items = Array.isArray(json?.data) ? json.data : [];

            // Render grid in collections view
            const grid = document.querySelector('#collections-view .grid');
            if (grid) {
                if (items.length === 0) {
                    grid.innerHTML = '<div class="col-span-full rounded-lg border border-slate-700 bg-slate-800/60 p-6 text-center text-gray-300">Aún no tienes colecciones.</div>';
                } else {
                    grid.innerHTML = items.map(col => `
                        <div data-collection-id="${col.id}" class="collection-card bg-slate-800 border border-slate-700 hover:border-blue-500 rounded-xl p-6 cursor-pointer transition flex flex-col items-center justify-center text-center group h-40">
                            <svg class="w-12 h-12 text-slate-400 group-hover:text-blue-400 transition mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            <h3 class="text-lg font-semibold text-white">${escapeHtml(col.nombre || col.name || '')}</h3>
                            <span class="text-xs text-slate-400 font-medium mt-1">${(col.total_items !== undefined && col.total_items !== null) ? col.total_items : 0} libros</span>
                        </div>
                    `).join('');
                }
            }

            // If savedBookId provided, fetch collections that already contain it
            let presentCollectionIds = [];
            if (savedBookId) {
                try {
                    const resp2 = await fetch(`${COLLECTIONS_API_BASE}/items?saved_book_id=${encodeURIComponent(savedBookId)}`, { method: 'GET' });
                    const j2 = await resp2.json().catch(() => ({}));
                    const cols = Array.isArray(j2?.data) ? j2.data : [];
                    presentCollectionIds = cols.map(c => Number(c.id));
                } catch (e) {
                    presentCollectionIds = [];
                }
            }

            // Render checkboxes in modal
            const listContainer = document.getElementById('modal-collections-list');
            if (listContainer) {
                listContainer.innerHTML = items.map(col => `
                    <label class="flex items-center space-x-3 p-3 bg-slate-800 hover:bg-slate-700 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="collections" value="${col.id}" class="w-5 h-5 rounded border-slate-500 text-blue-600 focus:ring-blue-500 bg-slate-700" ${presentCollectionIds.includes(Number(col.id)) ? 'checked' : ''}>
                        <span class="font-medium">${escapeHtml(col.nombre || col.name || '')}</span>
                    </label>
                `).join('');

                // Attach change listeners
                const inputs = listContainer.querySelectorAll('input[name="collections"]');
                inputs.forEach(inp => {
                    inp.addEventListener('change', async function(evt) {
                        const collectionId = Number(this.value);
                        const checked = this.checked;
                        if (!currentSavedBookId) {
                            if (typeof window.showToast === 'function') window.showToast('No hay libro seleccionado', 'error');
                            // revert
                            this.checked = !checked;
                            return;
                        }

                        try {
                            const url = `${COLLECTIONS_API_BASE}/items`;
                            if (checked) {
                                const r = await fetch(url, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ collection_id: collectionId, saved_book_id: Number(currentSavedBookId) })
                                });
                                const jr = await r.json().catch(() => ({}));
                                if (!r.ok || !jr.success) {
                                    throw new Error(jr?.message || 'No se pudo agregar el libro a la colección');
                                }
                            } else {
                                const r = await fetch(url, {
                                    method: 'DELETE',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ collection_id: collectionId, saved_book_id: Number(currentSavedBookId) })
                                });
                                const jr = await r.json().catch(() => ({}));
                                if (!r.ok || !jr.success) {
                                    throw new Error(jr?.message || 'No se pudo remover el libro de la colección');
                                }
                            }

                            if (typeof window.showToast === 'function') window.showToast('Operación completada', 'success');
                        } catch (e) {
                            console.error('collection checkbox change error', e);
                            // revert checkbox on error
                            this.checked = !checked;
                            if (typeof window.showToast === 'function') window.showToast('Error actualizando colección', 'error');
                        }
                    });
                });
            }

        } catch (e) {
            console.error('loadUserCollections error', e);
        }
    }

    async function createAndSaveCollection() {
        const input = document.getElementById('new-collection-name');
        if (!input) return;
        const name = input.value.trim();
        const userUuid = (localStorage.getItem('user_uuid') || '').trim();
        if (name === '' || userUuid === '') {
            if (typeof window.showToast === 'function') window.showToast('Nombre inválido o sesión no iniciada', 'error');
            return;
        }

        try {
            const resp = await fetch(COLLECTIONS_API_BASE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_uuid: userUuid, name: name })
            });
            const json = await resp.json().catch(() => ({}));
            if (!resp.ok || !json.success) {
                if (typeof window.showToast === 'function') window.showToast(json?.message || 'No se pudo crear la colección', 'error');
                return;
            }

            const newCollectionId = json.id ?? json.data?.id ?? null;

            // Scenario A: no saved book context -> simple create
            if (!window.currentSavedBookId) {
                input.value = '';
                closeCollectionModal();
                await loadUserCollections();
                const toast2 = document.getElementById('toast-collection-saved');
                if (toast2) {
                    document.getElementById('toast-collection-msg').innerText = `Colección "${name}" creada`;
                    toast2.classList.remove('hidden');
                    setTimeout(() => { toast2.classList.add('hidden'); }, 3500);
                }
                // clear contextual saved book id
                window.currentSavedBookId = null;
                currentSavedBookId = null;
                return;
            }

            // Scenario B: there's a saved book id - auto-link
            if (!newCollectionId) {
                // can't proceed to link without collection id
                input.value = '';
                closeCollectionModal();
                if (typeof window.showToast === 'function') window.showToast('Colección creada pero no se pudo vincular el libro', 'error');
                await loadUserCollections();
                return;
            }

            try {
                const linkResp = await fetch(`${COLLECTIONS_API_BASE}/items`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ collection_id: Number(newCollectionId), saved_book_id: Number(window.currentSavedBookId) })
                });
                const linkJson = await linkResp.json().catch(() => ({}));
                if (!linkResp.ok || !linkJson.success) {
                    throw new Error(linkJson?.message || 'No se pudo vincular el libro');
                }

                // success: show second toast with link action
                input.value = '';
                closeCollectionModal();
                await loadUserCollections();
                const toast2 = document.getElementById('toast-collection-saved');
                if (toast2) {
                    document.getElementById('toast-collection-msg').innerText = `Se guardó en ${name}`;
                    toast2.classList.remove('hidden');
                    setTimeout(() => { toast2.classList.add('hidden'); }, 3500);
                }
                // clear contextual saved book id
                window.currentSavedBookId = null;
                currentSavedBookId = null;
            } catch (e) {
                console.error('auto-link error', e);
                input.value = '';
                closeCollectionModal();
                if (typeof window.showToast === 'function') window.showToast('Error agregando libro a la colección', 'error');
                await loadUserCollections();
            }
        } catch (e) {
            if (typeof window.showToast === 'function') window.showToast('Error creando colección', 'error');
        }
    }

    function closeBulkAddModal() {
        const m = document.getElementById('bulk-add-modal');
        if (m) m.classList.add('hidden');
    }

    async function openBulkAddModal() {
        const collectionId = window.currentCollectionId || null;
        const userUuid = (localStorage.getItem('user_uuid') || '').trim();
        if (!collectionId || userUuid === '') {
            if (typeof window.showToast === 'function') window.showToast('No hay colección seleccionada o sesión no iniciada', 'error');
            return;
        }

        try {
            const resp = await fetch(`${COLLECTIONS_API_BASE}/available-books?user_uuid=${encodeURIComponent(userUuid)}&collection_id=${encodeURIComponent(collectionId)}`, { method: 'GET' });
            const payload = await resp.json().catch(() => ({}));
            // payload may be { success: true, data: [...] } or direct array
            let items = [];
            if (Array.isArray(payload?.data)) items = payload.data;
            else if (Array.isArray(payload?.payload)) items = payload.payload;
            else if (Array.isArray(payload)) items = payload;
            else items = [];

            const list = document.getElementById('bulk-books-list');
            if (!list) return;
            if (items.length === 0) {
                list.innerHTML = '<div class="col-span-full rounded-lg border border-slate-700 bg-slate-800/60 p-6 text-center text-gray-300">No hay libros disponibles para agregar.</div>';
            } else {
                const localColorMap = window.colorMap || {
                    'Alpha Cloud': { badgeBg: 'bg-cyan-800', text: 'text-cyan-300', border: 'border-cyan-500' },
                    'e-Libro': { badgeBg: 'bg-orange-800', text: 'text-orange-300', border: 'border-orange-500' },
                    'Inventario UPT': { badgeBg: 'bg-blue-800', text: 'text-white', border: 'border-blue-700' }
                };

                list.innerHTML = items.map(b => {
                    const titulo = String(b.titulo ?? b.title ?? b.nombre ?? '').trim();
                    const title = escapeHtml(titulo || 'Sin título');
                    const cover = String(b.portada_url ?? b.portada ?? b.cover_url ?? '').trim();
                    const origenRaw = String(b.origen ?? b.origin ?? 'Desconocido').trim();
                    const codigoVal = b.id ?? b.codigo ?? '';

                    const colors = localColorMap[origenRaw] || { badgeBg: 'bg-slate-800', text: 'text-slate-300', border: 'border-slate-700' };
                    const badgeHtml = `<span class="${colors.badgeBg} ${colors.text} ${colors.border} text-xs px-2 py-0.5 rounded-full">${escapeHtml(origenRaw)}</span>`;

                    const coverHtml = cover
                        ? `<img src="${escapeHtml(cover)}" class="w-12 h-16 object-cover rounded shadow-sm mr-3">`
                        : (origenRaw === 'Inventario UPT'
                            ? `<img src="/nexuslib/frontend/public/images/logo-upt.png" alt="Portada UPT" class="w-12 h-16 rounded object-cover shadow-sm mr-3">`
                            : `<div class="w-12 h-16 bg-slate-800 border border-slate-700 rounded mr-3 flex items-center justify-center text-gray-500 text-[10px]">No<br>Portada</div>`
                        );

                    return `
                        <label class="flex items-center justify-between bg-slate-800 p-2 rounded">
                            <div class="flex items-center">
                                ${coverHtml}
                                <div class="min-w-0">
                                    <div class="font-medium text-sm text-white truncate max-w-[36ch]">${title}</div>
                                    <div class="mt-1">${badgeHtml}</div>
                                </div>
                            </div>
                            <input type="checkbox" class="bulk-book-checkbox ml-4" value="${Number(b.id)}">
                        </label>
                    `;
                }).join('');
            }

            const modal = document.getElementById('bulk-add-modal');
            if (modal) modal.classList.remove('hidden');
        } catch (e) {
            console.error('openBulkAddModal error', e);
            if (typeof window.showToast === 'function') window.showToast('Error cargando libros', 'error');
        }
    }

    function openEditCollectionModal() {
        const titleEl = document.getElementById('details-collection-title');
        const input = document.getElementById('edit-collection-input');
        if (input) {
            input.value = titleEl ? (titleEl.innerText || '') : '';
        }
        const modal = document.getElementById('edit-collection-modal');
        if (modal) modal.classList.remove('hidden');
        setTimeout(() => { try { document.getElementById('edit-collection-input')?.focus(); } catch(_){} }, 50);
    }

    function closeEditCollectionModal() {
        const modal = document.getElementById('edit-collection-modal');
        if (modal) modal.classList.add('hidden');
    }

    function openConfirmDeleteModal() {
        const modal = document.getElementById('confirm-delete-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeConfirmDeleteModal() {
        const modal = document.getElementById('confirm-delete-modal');
        if (modal) modal.classList.add('hidden');
    }

    function escapeHtml(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    document.addEventListener('click', function(event) {
        const target = event.target;

        if (target.closest && target.closest('#btn-mock-create')) {
            openCollectionModal();
            return;
        }

        if (target.closest && target.closest('#btn-mock-add-book')) {
            openBulkAddModal();
            return;
        }

        // Delete collection (top trash icon in details view)
        if (target.closest && target.closest('#btn-delete-collection')) {
            openConfirmDeleteModal();
            return;
        }

        // Edit collection name (top pencil icon)
        if (target.closest && target.closest('#btn-edit-collection')) {
            openEditCollectionModal();
            return;
        }

        const card = target.closest && target.closest('.collection-card');
        if (card) {
            const collId = card.getAttribute('data-collection-id');
            const collName = card.querySelector('h3')?.innerText || '';
            const collView = document.getElementById('collections-view');
            const detView = document.getElementById('collection-details-view');
            const titleEl = document.getElementById('details-collection-title');
            if (collView) collView.classList.add('hidden');
            if (detView) detView.classList.remove('hidden');
            if (titleEl) titleEl.innerText = collName;
            currentCollectionId = collId ? Number(collId) : null;
            window.currentCollectionId = currentCollectionId;
            if (typeof window.loadCollectionItems === 'function' && currentCollectionId) {
                window.loadCollectionItems(Number(currentCollectionId));
            }
            return;
        }

        if (target.closest && target.closest('#btn-mock-back')) {
            const collView = document.getElementById('collections-view');
            const detView = document.getElementById('collection-details-view');
            if (detView) detView.classList.add('hidden');
            if (collView) collView.classList.remove('hidden');
            return;
        }
        
        // Save selected books from bulk modal
        if (target.closest && target.closest('#bulk-add-save-btn')) {
            (async function() {
                const collectionId = window.currentCollectionId || null;
                if (!collectionId) {
                    if (typeof window.showToast === 'function') window.showToast('No hay colección seleccionada', 'error');
                    return;
                }

                const checkboxes = Array.from(document.querySelectorAll('.bulk-book-checkbox')).filter(ch => ch.checked);
                if (checkboxes.length === 0) {
                    if (typeof window.showToast === 'function') window.showToast('Selecciona al menos un libro', 'error');
                    return;
                }

                try {
                    const url = `${COLLECTIONS_API_BASE}/items`;
                    for (const cb of checkboxes) {
                        const savedBookId = Number(cb.value);
                        const r = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ collection_id: Number(collectionId), saved_book_id: savedBookId })
                        });
                        const jr = await r.json().catch(() => ({}));
                        if (!r.ok || !jr.success) {
                            throw new Error(jr?.message || 'Error adding book to collection');
                        }
                    }

                    closeBulkAddModal();
                    if (typeof window.showToast === 'function') window.showToast('Libros agregados', 'success');
                    if (typeof window.loadCollectionItems === 'function') await window.loadCollectionItems(Number(collectionId));
                    await loadUserCollections();
                } catch (e) {
                    console.error('bulk add save error', e);
                    if (typeof window.showToast === 'function') window.showToast('Error agregando libros', 'error');
                }
            })();
            return;
        }

        // Modal buttons: edit save/cancel and confirm delete actions
        if (target.closest && target.closest('#edit-collection-save')) {
            (async function() {
                const input = document.getElementById('edit-collection-input');
                const newName = input ? String(input.value || '').trim() : '';
                const titleEl = document.getElementById('details-collection-title');
                const currentName = titleEl ? (titleEl.innerText || '') : '';
                if (newName === '' || newName === currentName.trim()) { closeEditCollectionModal(); return; }
                const collectionId = window.currentCollectionId || null;
                if (!collectionId) { closeEditCollectionModal(); if (typeof window.showToast === 'function') window.showToast('No hay colección seleccionada', 'error'); return; }
                try {
                    const r = await fetch(`${COLLECTIONS_API_BASE}/update`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: Number(collectionId), name: newName })
                    });
                    const j = await r.json().catch(() => ({}));
                    if (!r.ok || !j.success) throw new Error(j?.message || 'Error renombrando colección');
                    if (titleEl) titleEl.innerText = newName;
                    closeEditCollectionModal();
                    await loadUserCollections();
                    if (typeof window.showToast === 'function') window.showToast('Nombre actualizado', 'success');
                } catch (e) {
                    console.error('rename collection error', e);
                    closeEditCollectionModal();
                    if (typeof window.showToast === 'function') window.showToast('Error al renombrar la colección', 'error');
                }
            })();
            return;
        }

        if (target.closest && target.closest('#edit-collection-cancel')) {
            closeEditCollectionModal();
            return;
        }

        if (target.closest && target.closest('#confirm-delete-accept')) {
            (async function() {
                const collectionId = window.currentCollectionId || null;
                if (!collectionId) { closeConfirmDeleteModal(); if (typeof window.showToast === 'function') window.showToast('No se seleccionó ninguna colección', 'error'); return; }
                try {
                    const r = await fetch(COLLECTIONS_API_BASE, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: Number(collectionId) })
                    });
                    const j = await r.json().catch(() => ({}));
                    if (!r.ok || !j.success) throw new Error(j?.message || 'Error eliminando colección');
                    closeConfirmDeleteModal();
                    if (typeof window.showToast === 'function') window.showToast('Colección eliminada', 'success');
                    const backBtn = document.getElementById('btn-mock-back'); if (backBtn) backBtn.click();
                    await loadUserCollections();
                } catch (e) {
                    console.error('delete collection error', e);
                    closeConfirmDeleteModal();
                    if (typeof window.showToast === 'function') window.showToast('Error al eliminar la colección', 'error');
                }
            })();
            return;
        }

        if (target.closest && target.closest('#confirm-delete-cancel')) {
            closeConfirmDeleteModal();
            return;
        }
    });

    // Expose helper for other scripts to open modal for a specific saved book
    window.openCollectionModalForSavedBook = function(savedBookId) { openCollectionModal(savedBookId); };
    window.openCollectionModal = openCollectionModal;

    window.goToCollection = function() {
        try { localStorage.setItem('active_tab', 'collections'); } catch(e) { /* ignore */ }
        window.location.href = '/nexuslib/frontend/public/index.php?view=dashboard/saved_books&tab=collections';
    };
</script>