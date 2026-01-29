(function (global) {
    function qs(sel, root = document) {
        return root.querySelector(sel);
    }

    function qsa(sel, root = document) {
        return Array.from(root.querySelectorAll(sel));
    }

    function ensureArray(d) {
        return Array.isArray(d) ? d : (d?.data ?? (typeof d === 'object' ? Object.values(d) : []));
    }

    function unwrap(obj) {
        return (obj && typeof obj === 'object' && 'data' in obj) ? obj.data : obj;
    }

    function byIdKey(o, key) {
        return o?.[key] ?? o?.id ?? o?.uuid ?? o?.[key || 'id'];
    }

    function getCSRF(explicit) {
        if (explicit) return explicit;
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function defaultSkeleton() {
        return `
<article class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5 shadow-soft dark:shadow-softDark">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <span class="size-12 rounded-xl skel"></span>
      <div class="w-40 space-y-2">
        <div class="h-4 skel"></div>
        <div class="h-3 w-24 skel"></div>
      </div>
    </div>
    <div class="h-8 w-24 rounded-lg skel"></div>
  </div>
  <div class="mt-4 grid grid-cols-2 gap-3">
    <div class="h-16 rounded-xl skel"></div>
    <div class="h-16 rounded-xl skel"></div>
  </div>
</article>`;
    }

    function showGridOverlay(grid) {
        grid?.classList.add('grid-loading');
    }

    function hideGridOverlay(grid) {
        grid?.classList.remove('grid-loading');
    }

    function clearFormErrors(form, formErrorSel) {
        if (!form) return;
        const g = formErrorSel ? qs(formErrorSel) : qs('[data-form-error]', form);
        if (g) {
            g.classList.add('hidden');
            g.textContent = '';
        }
        qsa('.field-error', form).forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        qsa('input,select,textarea', form).forEach(el => el.classList.remove('ring-2', 'ring-red-500/40', 'border-red-500'));
    }

    function showFieldError(inputEl, errEl, msg) {
        if (errEl) {
            errEl.textContent = msg || 'Campo inválido';
            errEl.classList.remove('hidden');
        }
        inputEl?.classList.add('ring-2', 'ring-red-500/40', 'border-red-500');
    }

    function map422ToForm(form, errors) {
        if (!form || !errors) return;
        Object.entries(errors).forEach(([field, msgs]) => {
            const input = form.querySelector(`[name="${field}"]`);
            const errEl = input ? (input.closest('label,div,fieldset')?.querySelector('.field-error') || qs(`[data-error-for="${field}"]`, form)) : null;
            showFieldError(input, errEl, msgs?.[0] || 'Campo inválido');
        });
    }

    function fillFormAuto(form, rec) {
        if (!form || !rec) return;
        const map = new Map();
        qsa('[name]', form).forEach(el => map.set(el.name, (map.get(el.name) || []).concat(el)));
        for (const [name, els] of map.entries()) {
            const v = rec[name];
            els.forEach(el => {
                if (el.type === 'radio') {
                    el.checked = String(el.value) === String(v ?? '');
                } else if (el.type === 'checkbox') {
                    el.checked = !!v && (v === true || v === '1' || v === 1);
                } else {
                    el.value = (v ?? '');
                }
            });
        }
    }

    function CrudLite(cfg) {
        // ---- config obrigatória
        const routes = cfg.routes || {};
        const grid = typeof cfg.selectors?.grid === 'string' ? qs(cfg.selectors.grid) : cfg.selectors?.grid;
        const modal = typeof cfg.selectors?.modal === 'string' ? qs(cfg.selectors.modal) : cfg.selectors?.modal;
        const form = typeof cfg.selectors?.form === 'string' ? qs(cfg.selectors.form) : cfg.selectors?.form;
        const titleEl = cfg.selectors?.title ? qs(cfg.selectors.title) : null;
        const overlay = cfg.selectors?.overlay ? qs(cfg.selectors.overlay) : null;
        const openers = cfg.selectors?.openers ? qsa(cfg.selectors.openers) : [];
        const btnClose = cfg.selectors?.btnClose ? qs(cfg.selectors.btnClose) : qs('[data-crud-close]', modal);
        const btnCancel = cfg.selectors?.btnCancel ? qs(cfg.selectors.btnCancel) : null;
        const fab = cfg.selectors?.fab ? qs(cfg.selectors.fab) : null;
        const menu = cfg.selectors?.menu ? qs(cfg.selectors.menu) : null;

        const idKey = cfg.idKey || 'id';
        const cacheKey = cfg.cacheKey || `crud_${cfg.key || 'x'}_cache_v1`;
        const csrf = getCSRF(cfg.csrf);

        // hooks
        const parseIndex = cfg.parseIndex || (json => ensureArray(json));
        const parseShow = cfg.parseShow || (json => unwrap(json));
        const card = cfg.template || (() => '');
        const onModeChange = cfg.onModeChange || function () {
        };
        const onOpen = cfg.onOpen || function () {
        };
        const onAfterSave = cfg.onAfterSave || function () {
        };
        const onBeforeSubmit = cfg.onBeforeSubmit || function (fd) {
            return fd;
        };
        const onBeforeOpenCreate = cfg.onBeforeOpenCreate || function () {
        };
        const onAfterRender = cfg.onAfterRender || function () {
        };
        const skeleton = cfg.skeleton || defaultSkeleton;

        const fillForm = cfg.fillForm || fillFormAuto;
        const clearForm = cfg.clearForm || function (f) {
            f?.reset();
        };
        const formErrorSel = cfg.selectors?.formError || null;

        let mode = 'create';
        let currentId = null;
        let suppressUntil = 0;

        function readCache() {
            try {
                return JSON.parse(localStorage.getItem(cacheKey)) || null;
            } catch {
                return null;
            }
        }

        function writeCache(list) {
            try {
                localStorage.setItem(cacheKey, JSON.stringify({list, t: Date.now()}));
            } catch {
            }
        }

        // -------- Modal
        function openModal(m = 'create', data = null) {
            mode = m;
            onModeChange(m, form, titleEl, data);
            if (data) fillForm(form, data); else clearForm(form);
            if ((m === 'edit' || m === 'show') && form && !form.querySelector('[name="id"]')) {
                // opcional: não força hidden id.
            }
            modal?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden', 'ui-modal-open');
            onOpen(m, form, data);
        }

        function closeModal() {
            modal?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden', 'ui-modal-open');
        }

        function openCreate(e) {
            e?.preventDefault();
            e?.stopPropagation();
            currentId = null;
            if (menu) closeMenu();
            onBeforeOpenCreate();
            openModal('create');
        }

        openers.forEach(b => b.addEventListener('click', openCreate));
        fab?.addEventListener('click', openCreate, {passive: false});
        btnClose?.addEventListener('click', closeModal);
        btnCancel?.addEventListener('click', closeModal);
        overlay?.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal?.classList.contains('hidden')) closeModal();
        });

        // -------- List / grid
        (function prime() {
            const cached = readCache();
            if (cached?.list?.length) {
                grid.innerHTML = cached.list.map(card).join('');
                showGridOverlay(grid);
                onAfterRender(cached.list, true);
            } else {
                grid.innerHTML = skeleton().repeat(cfg.skeletonCount || 6);
            }
        })();

        async function loadList() {
            try {
                const res = await fetch(routes.index, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) throw new Error('Falha ao carregar');
                const arr = parseIndex(await res.json());

                grid.innerHTML = arr.length ? arr.map(card).join('') : (cfg.emptyHtml || `<div class="text-sm text-neutral-500">Nenhum registro.</div>`);
                writeCache(arr);
                onAfterRender(arr, false);
            } catch (e) {
                console.error(e);
            } finally {
                hideGridOverlay(grid);
            }
        }

        // -------- Menu (opcional)
        function closeMenu() {
            menu?.classList.add('hidden');
            menuForId = null;
        }

        function openMenuFor(cardEl, anchorBtn) {
            if (!menu) return;
            menuForId = cardEl.dataset.id;
            const r = anchorBtn.getBoundingClientRect();
            const top = r.bottom + window.scrollY + 6;
            const left = Math.min(window.scrollX + r.left, window.scrollX + window.innerWidth - 200);
            menu.style.top = `${top}px`;
            menu.style.left = `${left}px`;
            menu.classList.remove('hidden');
        }

        let menuForId = null;
        window.addEventListener('scroll', closeMenu, {passive: true});
        window.addEventListener('resize', closeMenu, {passive: true});
        document.addEventListener('click', (e) => {
            if (!menu?.classList.contains('hidden') && !e.target.closest(cfg.selectors?.menu || '#__no_menu') && !e.target.closest('[data-action="more"]')) closeMenu();
        });
        menu?.addEventListener('click', onMenuClick);

        async function onMenuClick(e) {
            const b = e.target.closest('[data-menu-action]');
            if (!b || !menuForId) return;
            const act = b.dataset.menuAction;
            const id = menuForId;
            closeMenu();
            if (act === 'edit' || act === 'show') {
                await openFromShow(act, id);
                return;
            }
            if (act === 'delete') {
                await doDelete(id);
                // remove card do grid
                const el = [...grid.querySelectorAll('[data-id]')].find(n => n.dataset.id == id);
                el?.remove();
                return;
            }
            if (typeof cfg.onMenuAction === 'function') cfg.onMenuAction(act, id, {openModal, closeModal, loadList});
        }

        async function openFromShow(act, id) {
            try {
                const res = await fetch(routes.show.replace(':id', id), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw 0;
                const rec = parseShow(await res.json());
                currentId = id;
                openModal(act === 'edit' ? 'edit' : 'show', rec);
            } catch {
                alert('Erro ao carregar registro');
            }
        }

        // -------- Delegação no grid
        grid.addEventListener('click', async (e) => {
            const cardEl = e.target.closest('[data-id]');
            if (!cardEl) return;
            const id = cardEl.dataset.id;
            const actBtn = e.target.closest('[data-action]');
            if (actBtn) {
                e.preventDefault();
                suppressUntil = Date.now() + 350;
                const act = actBtn.dataset.action;
                if (act === 'more') {
                    openMenuFor(cardEl, actBtn);
                    return;
                }
                if (act === 'edit' || act === 'show') {
                    await openFromShow(act, id);
                    return;
                }
                if (act === 'delete') {
                    await doDelete(id);
                    cardEl.remove();
                    return;
                }
                if (typeof cfg.onAction === 'function') cfg.onAction(act, id, {openModal, closeModal, loadList});
                return;
            }
            if (Date.now() < suppressUntil) return;
            await openFromShow('show', id);
        });

        // -------- Submit
        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearFormErrors(form, formErrorSel);
            let fd = new FormData(form);
            fd = onBeforeSubmit(fd, mode) || fd;

            const id = currentId || form.querySelector('[name="id"]')?.value?.trim();
            const isEdit = !!id;
            let url = isEdit ? routes.update.replace(':id', id) : routes.store;
            if (isEdit) fd.append('_method', 'PUT');

            try {
                showGridOverlay(grid);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    body: fd
                });
                if (!res.ok) {
                    let data = null;
                    try {
                        data = await res.json();
                    } catch {
                    }
                    if (res.status === 422 && data?.errors) {
                        map422ToForm(form, data.errors);
                        const g = formErrorSel ? qs(formErrorSel) : qs('[data-form-error]', form);
                        if (data?.message && g) {
                            g.textContent = data.message;
                            g.classList.remove('hidden');
                        }
                        return;
                    }
                    const g = formErrorSel ? qs(formErrorSel) : qs('[data-form-error]', form);
                    if (g) {
                        g.textContent = data?.message || 'Erro ao salvar';
                        g.classList.remove('hidden');
                    }
                    throw new Error(data?.message || 'Erro ao salvar');
                }
                closeModal();
                currentId = null;
                await loadList();
                if (typeof onAfterSave === 'function') {
                    let data = null;
                    try {
                        data = await res.json();
                    } catch {
                    }
                    onAfterSave(data);
                }
            } catch (err) {
                console.error(err);
                alert(err.message || 'Falha ao salvar');
            } finally {
                hideGridOverlay(grid);
            }
        });

        // -------- Delete
        async function doDelete(rawId) {
            const id = (rawId ?? '').toString().trim() || currentId;
            if (!id) {
                alert('ID inválido');
                return;
            }
            if (!(cfg.confirmDelete ? cfg.confirmDelete(id) : confirm('Excluir este registro?'))) return;
            const url = routes.destroy.replace(':id', encodeURIComponent(id));
            const fd = new FormData();
            fd.append('_method', 'DELETE');
            fd.append('id', id);
            const res = await fetch(url, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: fd
            });
            if (!res.ok) throw new Error('Falha ao excluir');
        }

        // -------- Boot load
        window.addEventListener('DOMContentLoaded', () => loadList().catch(() => {
        }));

        // Public API
        return {
            reload: loadList,
            openCreate,
            openModal,
            closeModal,
            delete: doDelete,
        };
    }

    global.CrudLite = CrudLite;

    global.CrudLite.utils = {
        ensureArray: ensureArray,
        unwrap: unwrap
    };
})(window);

