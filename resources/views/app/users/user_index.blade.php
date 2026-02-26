@extends('layouts.templates.app')

@push('styles')
    <style>
        .avatar-wrap {
            position: relative;
            display: inline-grid;
            place-items: center;
            padding-top: 10px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 9999px;
            object-fit: cover;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
        }

        .avatar-edit {
            position: absolute;
            right: 8px;
            bottom: 8px;
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            display: grid;
            place-items: center;
            background: #fff;
            color: #111;
            border: 1px solid rgba(0, 0, 0, .08);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .18);
        }

        .dark .avatar-edit {
            background: #0a0a0a;
            color: #fafafa;
            border-color: rgba(255, 255, 255, .12)
        }

        /* Badges de status */
        .badge-active {
            background: rgba(0, 191, 166, .12);
            color: #00bfa6
        }

        .badge-inactive {
            background: rgba(191, 0, 0, .12);
            color: #bf0000
        }

        /* Card do item de usuário adicional */
        .user-row {
            display: flex;
            gap: .75rem;
            align-items: flex-start
        }

        .user-row img {
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            object-fit: cover
        }
    </style>
@endpush

@section('new-content')
    <div class="flex items-center justify-between my-4">
        <a href="{{ route('dashboard') }}"
           class="inline-grid size-10 place-items-center rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800"
           aria-label="Voltar">
            <i class="fas fa-chevron-left text-neutral-700 dark:text-neutral-200"></i>
        </a>
        <h2 class="text-base md:text-lg font-semibold">Meu perfil</h2>
        <a href="{{ route('logout') }}"
           class="inline-grid size-10 place-items-center rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-red-50 dark:hover:bg-red-900/20"
           title="Sair" aria-label="Sair">
            <i class="fa-solid fa-right-from-bracket text-red-600"></i>
        </a>
    </div>

    <section class="rounded-2xl text-center border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-6 shadow-soft">
        <div class="avatar-wrap">
            {{-- input escondido só para trocar a foto rápida --}}
            <input type="file" id="avatarFile" accept="image/*" class="hidden">

            @if(auth()->user()->image)
                <img class="avatar" id="avatarImg"
                     src="data:image/jpeg;base64,{{ auth()->user()->image }}"
                     alt="{{ auth()->user()->name }}">
            @else
                <img class="avatar" id="avatarImg"
                     src="{{ asset('assets/img/user_profile/profile_example.png') }}"
                     alt="{{ auth()->user()->name }}">
            @endif

            <button type="button"
                    class="avatar-edit"
                    id="btnChangeAvatar"
                    data-user-id="{{ auth()->id() }}"
                    title="Trocar foto">
                <i class="fa-solid fa-camera-retro text-amber-600"></i>
            </button>
        </div>

        <div class="text-center mt-4">
            <h3 id="profileName" class="text-sm font-semibold tracking-wide">{{ auth()->user()->name }}</h3>
            <p id="profileEmail" class="text-xs text-neutral-500 dark:text-neutral-400">{{ auth()->user()->email }}</p>

            <div class="mt-3 flex flex-col items-center gap-2">
                <button type="button" id="btnEditProfile"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border border-neutral-200/70 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <i class="fa-solid fa-pen text-[11px]"></i>
                    Editar informações
                </button>

                {{-- 🔹 Botão de ajuda / suporte --}}
                <a href="{{ route('support.index') }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium bg-brand-600 text-white shadow-soft hover:bg-brand-700">
                    <i class="fa-solid fa-circle-question text-[11px]"></i>
                    Ajuda & suporte
                </a>
            </div>
        </div>
    </section>


    <section class="mt-4">
        <div class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold">Assinatura</h4>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        Plano {{ $subscription->plan_name }} — R$ {{ number_format($subscription->amount, 2, ',', '.') }}/mês
                    </p>
                </div>
                <span class="px-2 py-1 rounded-full text-[11px] {{ $subscriptionHasAccess ? 'badge-active' : 'badge-inactive' }}">
                    {{ $subscriptionHasAccess ? 'Acesso completo' : 'Acesso limitado' }}
                </span>
            </div>

            <div class="mt-3 text-xs">
                @if($subscriptionIsTrial)
                    <p>Período grátis até <strong>{{ optional($subscription->trial_ends_at)->format('d/m/Y H:i') }}</strong>.</p>
                @elseif($subscription->current_period_ends_at)
                    <p>Assinatura válida até <strong>{{ $subscription->current_period_ends_at->format('d/m/Y H:i') }}</strong>.</p>
                @else
                    <p>Seu período grátis encerrou. Gere um PIX para renovar seu acesso.</p>
                @endif
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="button" id="btnCheckoutPix" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700">
                    <i class="fa-solid fa-qrcode"></i>
                    Adquirir assinatura
                </button>
                <a href="#" id="subscriptionInvoiceLink" target="_blank" class="hidden text-xs underline text-brand-600">Abrir fatura</a>
            </div>

            <div id="pixResult" class="hidden mt-3 rounded-xl border border-neutral-200/70 dark:border-neutral-700 p-3">
                <p class="text-xs mb-2">PIX copia e cola:</p>
                <textarea id="pixCopyPaste" readonly class="w-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-transparent p-2 text-[11px]" rows="3"></textarea>
                <div class="mt-2 flex items-center gap-2">
                    <button type="button" id="btnCopyPix" class="px-3 py-1 rounded-lg text-xs border border-neutral-200 dark:border-neutral-700">Copiar código</button>
                    <img id="pixQrImage" class="h-24 w-24 rounded-lg border border-neutral-200 dark:border-neutral-700" alt="QR Code PIX" />
                </div>
            </div>
        </div>
    </section>

    <section class="mt-4">
        <div
            class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold">Usuários adicionais</h4>
            </div>
            <div id="userList" class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3"></div>
        </div>
    </section>

    <button id="fabUser" type="button"
            class="md:hidden fixed bottom-20 right-4 z-[80] size-14 rounded-2xl grid place-items-center text-white shadow-lg bg-brand-600 hover:bg-brand-700 active:scale-95 transition"
            aria-label="Adicionar usuário">
        <i class="fa fa-plus"></i>
    </button>

    <x-modal
        id="modalUser"
        formId="formUser"
        titleCreate="Novo usuário"
        titleEdit="Editar usuário"
        titleShow="Detalhes do usuário"
        submitLabel="Salvar"
    >
        @include('app.users.user_form')
    </x-modal>

    @push('scripts')
        <script>
            (() => {
                const list           = document.getElementById('userList');
                const modal          = document.getElementById('modalUser');
                const formEl         = document.getElementById('formUser');
                const openBtn        = document.getElementById('openModal');
                const fab            = document.getElementById('fabUser');

                const profileName    = document.getElementById('profileName');
                const profileEmail   = document.getElementById('profileEmail');
                const btnEditProfile = document.getElementById('btnEditProfile');

                const avatarImg      = document.getElementById('avatarImg');
                const avatarBtn      = document.getElementById('btnChangeAvatar');
                const avatarFile     = document.getElementById('avatarFile');

                const assetUrl       = "{{ asset('assets/img/user_profile/profile_example.png') }}";

                const loggedUserId       = "{{ auth()->id() }}";
                const loggedUserName     = @json(auth()->user()->name);
                const loggedUserEmail    = @json(auth()->user()->email);
                const loggedUserIsActive = {{ auth()->user()->is_active ? '1' : '0' }};

                const usersCache = {}; // id => user
                const btnCheckoutPix = document.getElementById('btnCheckoutPix');
                const pixResult = document.getElementById('pixResult');
                const pixCopyPaste = document.getElementById('pixCopyPaste');
                const pixQrImage = document.getElementById('pixQrImage');
                const btnCopyPix = document.getElementById('btnCopyPix');
                const subscriptionInvoiceLink = document.getElementById('subscriptionInvoiceLink');

                if (!modal || !formEl || !list) {
                    console.warn('Modal, formUser ou userList não encontrados.');
                    return;
                }

                let formMode  = 'create'; // 'create' | 'edit'
                let editingId = null;

                const newUserUrl    = "{{ route('users.store') }}";
                const baseUpdateUrl = "{{ url('users') }}"; // /users/{id}

                // ===============================
                // AVATAR (foto do usuário logado) - base64 igual ao modal
                // ===============================
                if (avatarBtn) {
                    avatarBtn.addEventListener('click', () => {
                        if (avatarFile) avatarFile.click();
                    });
                }

                if (avatarFile) {
                    avatarFile.addEventListener('change', (e) => {
                        const file = e.target.files && e.target.files[0];
                        if (!file) return;

                        const reader = new FileReader();

                        reader.onload = async () => {
                            const dataUrl = reader.result; // data:image/jpeg;base64,...

                            const fd = new FormData();
                            // manda como string base64 no campo image (igual form do modal)
                            fd.append('image', dataUrl);
                            fd.append('_method', 'PUT');

                            try {
                                const resp = await fetch(`${baseUpdateUrl}/${loggedUserId}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: fd
                                });

                                const user = await resp.json();

                                if (!resp.ok) {
                                    console.error(user);
                                    throw new Error('Erro ao atualizar foto.');
                                }

                                const imgSrc = user.image
                                    ? `data:image/jpeg;base64,${user.image}`
                                    : assetUrl;

                                if (avatarImg) avatarImg.src = imgSrc;

                                // atualiza card grande
                                if (profileName)  profileName.textContent  = user.name;
                                if (profileEmail) profileEmail.textContent = user.email;

                                // cache
                                usersCache[user.id] = user;

                            } catch (err) {
                                alert(err.message || 'Erro ao atualizar foto.');
                            } finally {
                                avatarFile.value = '';
                            }
                        };

                        reader.readAsDataURL(file);
                    });
                }

                // ===============================
                // FUNÇÕES DO MODAL
                // ===============================
                function setModeCreate() {
                    formMode  = 'create';
                    editingId = null;
                    formEl.reset();

                    const isActiveInput = formEl.querySelector('[name="is_active"]');
                    if (isActiveInput && isActiveInput.type === 'checkbox') {
                        isActiveInput.checked = true;
                    }
                }

                function setModeEdit(user) {
                    formMode  = 'edit';
                    editingId = user.id;

                    formEl.reset();

                    const nameInput       = formEl.querySelector('[name="name"]');
                    const emailInput      = formEl.querySelector('[name="email"]');
                    const isActiveInput   = formEl.querySelector('[name="is_active"]');
                    const passwordInput   = formEl.querySelector('[name="password"]');
                    const passwordConfInp = formEl.querySelector('[name="password_confirmation"]');

                    if (nameInput)  nameInput.value  = user.name  || '';
                    if (emailInput) emailInput.value = user.email || '';
                    if (isActiveInput && isActiveInput.type === 'checkbox') {
                        isActiveInput.checked = !!user.is_active;
                    }
                    if (passwordInput)   passwordInput.value   = '';
                    if (passwordConfInp) passwordConfInp.value = '';
                }

                const openModal = () => {
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden', 'ui-modal-open');
                };

                const closeModal = () => {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden', 'ui-modal-open');
                    formEl.reset();
                    formMode  = 'create';
                    editingId = null;
                };

                // abrir modal para criar
                openBtn?.addEventListener('click', () => {
                    setModeCreate();
                    openModal();
                });

                fab?.addEventListener('click', () => {
                    setModeCreate();
                    openModal();
                });

                // editar perfil logado (usa o mesmo modal)
                btnEditProfile?.addEventListener('click', () => {
                    const user = usersCache[loggedUserId] || {
                        id:        loggedUserId,
                        name:      loggedUserName,
                        email:     loggedUserEmail,
                        is_active: loggedUserIsActive === '1',
                    };
                    setModeEdit(user);
                    openModal();
                });

                // fechar por X, Cancelar ou overlay
                modal.addEventListener('click', (e) => {
                    if (
                        e.target.matches('[data-crud-overlay]') ||
                        e.target.closest('[data-crud-close]')  ||
                        e.target.closest('[data-crud-cancel]')
                    ) {
                        closeModal();
                    }
                });

                // ESC fecha
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });

                // submit (create / edit)
                formEl.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(formEl);

                    let url    = newUserUrl;
                    let method = 'POST';

                    if (formMode === 'edit' && editingId) {
                        url = `${baseUpdateUrl}/${editingId}`;
                        formData.append('_method', 'PUT');
                    }

                    try {
                        const resp    = await fetch(url, {
                            method,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        const payload = await resp.json();

                        if (!resp.ok) {
                            console.error(payload);
                            throw new Error('Erro ao salvar registro.');
                        }

                        if (formMode === 'create') {
                            storeRow(payload);
                        } else {
                            updateRow(payload);
                        }

                        closeModal();
                    } catch (err) {
                        alert(err.message || 'Erro ao salvar registro.');
                    }
                });

                // ===============================
                // LISTAGEM / CARDS
                // ===============================
                function storeRow(row) {
                    if (!list) return;

                    usersCache[row.id] = row;

                    const imgSrc      = row.image ? `data:image/jpeg;base64,${row.image}` : assetUrl;
                    const statusClass = row.is_active ? 'badge-active' : 'badge-inactive';
                    const statusText  = row.is_active ? 'Ativo' : 'Inativo';

                    const html = `
<article class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-3" data-user-id="${row.id}">
  <div class="user-row">
    <img src="${imgSrc}" alt="${row.name}">
    <div class="min-w-0 flex-1">
      <div class="flex items-center gap-2">
        <h5 class="text-sm font-semibold truncate">${row.name}</h5>
        <span class="px-2 py-0.5 text-[11px] rounded ${statusClass}">${statusText}</span>
      </div>
      <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate">${row.email}</div>
    </div>
    <button type="button"
            class="ml-2 inline-flex items-center justify-center rounded-full border border-neutral-200/70 dark:border-neutral-700 size-8 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-xs"
            data-edit-user="${row.id}"
            title="Editar">
        <i class="fa-solid fa-pen"></i>
    </button>
  </div>
</article>`;
                    list.insertAdjacentHTML('beforeend', html);
                }

                function updateRow(row) {
                    usersCache[row.id] = row;

                    const old = list.querySelector(`[data-user-id="${row.id}"]`);
                    if (old) old.remove();
                    storeRow(row);

                    // se for o usuário logado, atualiza card grande também
                    if (row.id === loggedUserId) {
                        if (profileName)  profileName.textContent  = row.name;
                        if (profileEmail) profileEmail.textContent = row.email;

                        const imgSrc = row.image ? `data:image/jpeg;base64,${row.image}` : assetUrl;
                        if (avatarImg) avatarImg.src = imgSrc;
                    }
                }

                async function loadData() {
                    try {
                        const resp = await fetch("{{ route('users.index') }}", {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!resp.ok) throw new Error('Erro ao carregar registros.');

                        const rows = await resp.json();
                        list.innerHTML = '';
                        rows.forEach(storeRow);
                    } catch (err) {
                        console.error(err);
                    }
                }



                btnCheckoutPix?.addEventListener('click', async () => {
                    try {
                        const resp = await fetch("{{ route('billing.subscription.checkout-pix') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const payload = await resp.json();

                        if (!resp.ok) throw new Error(payload.message || 'Erro ao gerar PIX');

                        pixResult?.classList.remove('hidden');
                        if (pixCopyPaste) pixCopyPaste.value = payload.pix_copy_paste || '';
                        if (pixQrImage && payload.pix_qr_code) pixQrImage.src = `data:image/png;base64,${payload.pix_qr_code}`;

                        if (subscriptionInvoiceLink && payload.invoice_url) {
                            subscriptionInvoiceLink.classList.remove('hidden');
                            subscriptionInvoiceLink.href = payload.invoice_url;
                        }
                    } catch (err) {
                        alert(err.message || 'Falha no checkout');
                    }
                });

                btnCopyPix?.addEventListener('click', async () => {
                    if (!pixCopyPaste?.value) return;
                    await navigator.clipboard.writeText(pixCopyPaste.value);
                });

                // clicar no botão editar do card
                list.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-edit-user]');
                    if (!btn) return;

                    const id   = btn.getAttribute('data-edit-user');
                    const user = usersCache[id];
                    if (!user) return;

                    setModeEdit(user);
                    openModal();
                });

                window.addEventListener('DOMContentLoaded', loadData);
            })();
        </script>
    @endpush

@endsection
