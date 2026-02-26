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
                <span id="subscriptionAccessBadge" class="px-2 py-1 rounded-full text-[11px] {{ $subscriptionHasAccess ? 'badge-active' : 'badge-inactive' }}">
                    {{ $subscriptionHasAccess ? 'Acesso completo' : 'Acesso limitado' }}
                </span>
            </div>

            <div class="mt-3 text-xs" id="subscriptionStatusText">
                @if($subscriptionIsSubscriber && $subscriptionSubscriberUntil)
                    <p>Assinante até <strong>{{ $subscriptionSubscriberUntil->format('d/m/Y H:i') }}</strong>.</p>
                @elseif($subscriptionIsTrial)
                    <p>Período grátis até <strong>{{ optional($subscriptionTrialEndsAt)->format('d/m/Y H:i') }}</strong>.</p>
                @elseif($subscriptionGraceUntil)
                    <p>Seu plano venceu. Renove até <strong>{{ $subscriptionGraceUntil->format('d/m/Y H:i') }}</strong> para não perder acesso.</p>
                @else
                    <p>Seu período grátis encerrou. Gere um PIX para renovar seu acesso.</p>
                @endif
            </div>

            <div id="subscriptionRenewAlert" class="{{ $subscriptionIsRenewalAlert ? '' : 'hidden' }} mt-2 text-[11px] px-2 py-1 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                Renove seu plano para não perder acesso...
            </div>

            <div id="subscriptionCheckoutActions" class="mt-3 flex flex-wrap items-center gap-2">
                <button type="button" id="btnCheckoutPix" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700">
                    <i class="fa-solid fa-qrcode"></i>
                    Adquirir assinatura
                </button>
                <a href="#" id="subscriptionInvoiceLink" target="_blank" class="hidden text-xs underline text-brand-600">Abrir fatura</a>
            </div>
            <p id="subscriptionCheckoutHint" class="hidden mt-2 text-[11px] text-neutral-500 dark:text-neutral-400"></p>

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


    <div id="modalSubscriptionDocument" class="fixed inset-0 z-[90] hidden">
        <div class="absolute inset-0 bg-black/50" data-doc-overlay></div>
        <div class="relative mx-auto mt-24 w-[92%] max-w-md rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4 shadow-xl">
            <div class="flex items-center justify-between">
                <h5 class="text-sm font-semibold">Informe CPF/CNPJ para assinatura</h5>
                <button type="button" data-doc-close class="size-8 rounded-lg border border-neutral-200/70 dark:border-neutral-700">×</button>
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">Precisamos desse dado para gerar a cobrança PIX no Asaas.</p>
            <form id="subscriptionDocumentForm" class="mt-3 space-y-3">
                <label class="block text-sm">
                    <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">CPF / CNPJ</span>
                    <input type="text" id="subscriptionDocumentInput" name="cpf_cnpj" required placeholder="000.000.000-00" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm">
                </label>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-doc-close class="px-3 py-1.5 rounded-lg text-xs border border-neutral-200 dark:border-neutral-700">Cancelar</button>
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs bg-brand-600 text-white">Salvar e continuar</button>
                </div>
            </form>
        </div>
    </div>

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
                let loggedUserCpfCnpj  = @json(auth()->user()->cpf_cnpj);

                const usersCache = {}; // id => user
                const btnCheckoutPix = document.getElementById('btnCheckoutPix');
                const pixResult = document.getElementById('pixResult');
                const pixCopyPaste = document.getElementById('pixCopyPaste');
                const pixQrImage = document.getElementById('pixQrImage');
                const btnCopyPix = document.getElementById('btnCopyPix');
                const subscriptionInvoiceLink = document.getElementById('subscriptionInvoiceLink');
                const subscriptionCheckoutActions = document.getElementById('subscriptionCheckoutActions');
                const subscriptionCheckoutHint = document.getElementById('subscriptionCheckoutHint');
                const modalSubscriptionDocument = document.getElementById('modalSubscriptionDocument');
                const subscriptionDocumentForm = document.getElementById('subscriptionDocumentForm');
                const subscriptionDocumentInput = document.getElementById('subscriptionDocumentInput');
                const subscriptionStatusText = document.getElementById('subscriptionStatusText');
                const subscriptionAccessBadge = document.getElementById('subscriptionAccessBadge');
                const subscriptionRenewAlert = document.getElementById('subscriptionRenewAlert');

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
                    const cpfCnpjInput  = formEl.querySelector('[name="cpf_cnpj"]');

                    if (nameInput)  nameInput.value  = user.name  || '';
                    if (emailInput) emailInput.value = user.email || '';
                    if (isActiveInput && isActiveInput.type === 'checkbox') {
                        isActiveInput.checked = !!user.is_active;
                    }
                    if (passwordInput)   passwordInput.value   = '';
                    if (passwordConfInp) passwordConfInp.value = '';
                    if (cpfCnpjInput) cpfCnpjInput.value = user.cpf_cnpj || '';
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
                        cpf_cnpj: loggedUserCpfCnpj || '',
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
                        loggedUserCpfCnpj = row.cpf_cnpj || null;

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
                        await refreshSubscriptionSummary();
                    } catch (err) {
                        console.error(err);
                    }
                }




                function formatDateTime(value) {
                    if (!value) return '';
                    return new Date(value).toLocaleString('pt-BR', {
                        day: '2-digit', month: '2-digit', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                }

                function renderSubscriptionSummary(summary) {
                    loggedUserCpfCnpj = summary.cpf_cnpj || loggedUserCpfCnpj;

                    if (subscriptionAccessBadge) {
                        const hasAccess = !!summary.has_access;
                        subscriptionAccessBadge.textContent = hasAccess ? 'Acesso completo' : 'Acesso limitado';
                        subscriptionAccessBadge.classList.toggle('badge-active', hasAccess);
                        subscriptionAccessBadge.classList.toggle('badge-inactive', !hasAccess);
                    }

                    if (subscriptionStatusText) {
                        if (summary.is_subscriber && summary.subscriber_until) {
                            subscriptionStatusText.innerHTML = `<p>Assinante até <strong>${formatDateTime(summary.subscriber_until)}</strong>.</p>`;
                        } else if (summary.is_trial && summary.trial_ends_at) {
                            subscriptionStatusText.innerHTML = `<p>Período grátis até <strong>${formatDateTime(summary.trial_ends_at)}</strong>.</p>`;
                        } else if (summary.grace_until) {
                            subscriptionStatusText.innerHTML = `<p>Seu plano venceu. Renove até <strong>${formatDateTime(summary.grace_until)}</strong> para não perder acesso.</p>`;
                        } else {
                            subscriptionStatusText.innerHTML = '<p>Seu período grátis encerrou. Gere um PIX para renovar seu acesso.</p>';
                        }
                    }

                    subscriptionRenewAlert?.classList.toggle('hidden', !summary.is_renewal_alert);

                    const canCheckout = !!summary.can_checkout;
                    const hasPending = !!summary.pending_payment;

                    if (btnCheckoutPix) {
                        btnCheckoutPix.classList.toggle('hidden', !canCheckout && !hasPending);
                    }

                    if (subscriptionCheckoutActions) {
                        subscriptionCheckoutActions.classList.toggle('hidden', !canCheckout && !hasPending);
                    }

                    if (subscriptionCheckoutHint) {
                        const msg = summary.checkout_block_reason || '';
                        subscriptionCheckoutHint.textContent = msg;
                        subscriptionCheckoutHint.classList.toggle('hidden', !msg || hasPending);
                    }

                    const pendingPayment = summary.pending_payment;

                    if (pendingPayment?.pix_copy_paste && pendingPayment?.pix_qr_code) {
                        pixResult?.classList.remove('hidden');
                        if (pixCopyPaste) pixCopyPaste.value = pendingPayment.pix_copy_paste;
                        if (pixQrImage) pixQrImage.src = `data:image/png;base64,${pendingPayment.pix_qr_code}`;

                        if (subscriptionInvoiceLink && pendingPayment.invoice_url) {
                            subscriptionInvoiceLink.classList.remove('hidden');
                            subscriptionInvoiceLink.href = pendingPayment.invoice_url;
                        }
                    } else {
                        pixResult?.classList.add('hidden');
                        if (pixCopyPaste) pixCopyPaste.value = '';
                        if (pixQrImage) pixQrImage.removeAttribute('src');
                        subscriptionInvoiceLink?.classList.add('hidden');
                    }
                }

                async function refreshSubscriptionSummary() {
                    const resp = await fetch("{{ route('billing.subscription.summary') }}", {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!resp.ok) return;

                    const summary = await resp.json();
                    renderSubscriptionSummary(summary);
                }


                function initSubscriptionRealtime() {
                    if (typeof EventSource === 'undefined') return;

                    const evt = new EventSource("{{ route('billing.subscription.stream') }}");

                    evt.addEventListener('subscription', (event) => {
                        try {
                            const summary = JSON.parse(event.data || '{}');
                            renderSubscriptionSummary(summary);
                        } catch (_) {}
                    });

                    evt.onerror = () => {
                        evt.close();
                        setTimeout(initSubscriptionRealtime, 4000);
                    };
                }

                const closeDocumentModal = () => {
                    modalSubscriptionDocument?.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                };

                const openDocumentModal = () => {
                    modalSubscriptionDocument?.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    if (subscriptionDocumentInput) subscriptionDocumentInput.value = loggedUserCpfCnpj || '';
                };

                modalSubscriptionDocument?.addEventListener('click', (e) => {
                    if (e.target.matches('[data-doc-overlay]') || e.target.closest('[data-doc-close]')) {
                        closeDocumentModal();
                    }
                });

                async function createPixCheckout() {
                    const resp = await fetch("{{ route('billing.subscription.checkout-pix') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const payload = await resp.json();

                    if (!resp.ok && resp.status !== 409) throw new Error(payload.message || 'Erro ao gerar PIX');

                    const paymentData = payload.payment || payload;

                    if (paymentData?.pix_copy_paste && paymentData?.pix_qr_code) {
                        pixResult?.classList.remove('hidden');
                        if (pixCopyPaste) pixCopyPaste.value = paymentData.pix_copy_paste || '';
                        if (pixQrImage && paymentData.pix_qr_code) pixQrImage.src = `data:image/png;base64,${paymentData.pix_qr_code}`;

                        if (subscriptionInvoiceLink && paymentData.invoice_url) {
                            subscriptionInvoiceLink.classList.remove('hidden');
                            subscriptionInvoiceLink.href = paymentData.invoice_url;
                        }
                    }

                    await refreshSubscriptionSummary();
                }

                subscriptionDocumentForm?.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    try {
                        const resp = await fetch("{{ route('billing.subscription.document') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ cpf_cnpj: subscriptionDocumentInput?.value || '' })
                        });

                        const payload = await resp.json();

                        if (!resp.ok) throw new Error(payload.message || 'Erro ao salvar CPF/CNPJ');

                        loggedUserCpfCnpj = payload.cpf_cnpj;
                        closeDocumentModal();
                        if (btnCheckoutPix?.classList.contains('hidden')) return;
                        await createPixCheckout();
                    } catch (err) {
                        alert(err.message || 'Falha ao salvar documento');
                    }
                });

                btnCheckoutPix?.addEventListener('click', async () => {
                    try {
                        if (!loggedUserCpfCnpj) {
                            openDocumentModal();
                            return;
                        }

                        await createPixCheckout();
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

                window.addEventListener('DOMContentLoaded', () => {
                    loadData();
                    initSubscriptionRealtime();
                });
            })();
        </script>
    @endpush

@endsection
