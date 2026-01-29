<!DOCTYPE html>
<html lang="pt-BR">

<!-- Include:head -->
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>Finanças Cliqis</title>

    <link rel="manifest" href="{{ asset('laravelpwa/manifest.json') }}">
    <meta name="theme-color" content="#f1f1f1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    @stack('styles')

    <style>
        /* ========== VARIABLE ========== */
        :root {
            --color-text: #00bfa6 !important;
            --safe-top: env(safe-area-inset-top);
            --safe-right: env(safe-area-inset-right);
            --safe-bottom: env(safe-area-inset-bottom);
            --safe-left: env(safe-area-inset-left);
        }

        .bg-color {
            background-color: var(--color-text);
            background: var(--color-text);
            color: #fff;
        }

        .text-color {
            color: var(--color-text);
        }

        .border-color {
            color: var(--color-text);
        }

        /* ========== MOBILE ========== */
        @media (max-width: 480px) {
            body{
                margin: 0;
                font-family: 'Arial', sans-serif;
                background: #f1f1f1;
                /* corrige altura em mobile e reserva espaço da barra */
                min-height: 100dvh;
                padding: 0;
                display: flex;
                justify-content: center;
                /* empurra conteúdo longe da barra superior do iOS */
                padding-top: var(--safe-top);
                padding-left: var(--safe-left);
                padding-right: var(--safe-right);
            }


            /* Modal sempre no topo (Bootstrap refs: backdrop 1050, modal 1055) */
            /* Sheet de confirmação (sempre acima de tudo) */
            .x-confirm{
                position:fixed; inset:0; display:none;
                background:rgba(0,0,0,.45); z-index:99999;
            }
            .x-confirm.show{ display:flex; align-items:flex-end; justify-content:center; }
            .x-sheet{
                width:min(560px,94vw);
                margin:0 0 calc(env(safe-area-inset-bottom) + 12px) 0;
                background:#fff; border-radius:16px; overflow:hidden;
                box-shadow:0 20px 40px rgba(0,0,0,.2);
            }
            .x-head{ display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #eee; }
            .x-body{ padding:16px; }
            .x-actions{ display:flex; gap:8px; justify-content:flex-end; padding:12px 16px; border-top:1px solid #eee; }
            .x-close{ border:0; background:transparent; font-size:22px; line-height:1; cursor:pointer; }

            /* quando qualquer modal estiver aberto, desabilita clique por trás */
            body.modal-open #transactionList{ pointer-events:none; }
            body.modal-open .create-btn,
            body.modal-open .create-other,
            body.modal-open .create-other-2,
            body.modal-open .create-other-3{ pointer-events:none; opacity:.35; }

            /* (opcional) garante que nenhum z-index de terceiros passe na frente */
            .create-btn,.create-other,.create-other-2,.create-other-3{ z-index:1040 !important; }

            .swipe-list {
                list-style: none;
                margin: 0;
                padding: 0
            }

            .swipe-item {
                position: relative;
                overflow: hidden;
                background: #fff;
                border-bottom: 1px solid #eee;
                touch-action: pan-y;
                user-select: none;
                margin: auto 3px 12px 3px;
                border-radius: 12px;
                box-shadow: 1px 1px 5px rgba(103, 103, 103, 0.35) !important;
            }

            .swipe-content {
                position: relative;
                z-index: 2;
                background: #fff;
                padding: 14px 16px;
                transform: translateX(0);
                transition: transform 750ms ease;
                /*will-change: transform;*/
            }

            .swipe-delete-btn, .swipe-edit-btn {
                position: absolute;
                top: 0;
                bottom: 0;
                width: 96px;
                border: 0;
                color: #fff;
                z-index: 5;
                pointer-events: none;
                transition: transform 750ms ease;
            }

            .swipe-delete-btn {
                right: 0;
                background: #dc3545;
                transform: translateX(100%)
            }

            .swipe-edit-btn {
                left: 0;
                background: #3498db;
                transform: translateX(-100%)
            }

            .swipe-item.open-left .swipe-delete-btn {
                transform: translateX(0);
                pointer-events: auto;
            }

            .swipe-item.open-right .swipe-edit-btn {
                transform: translateX(0);
                pointer-events: auto;
            }

            .swipe-item.open-left .swipe-content {
                transform: translateX(-96px)
            }

            .swipe-item.open-right .swipe-content {
                transform: translateX(96px)
            }

            .swipe-item.open-left .swipe-content,
            .swipe-item.open-right .swipe-content {
                pointer-events: none;
            }

            .tx-title {
                font-size: 14px;
                letter-spacing: .25px;
                font-weight: 600;
            }

            .tx-date {
                letter-spacing: .75px;
                color: #b5b5b5;
                display: block;
                font-size: 12px;
            }

            .tx-amount {
                font-size: 12px;
                letter-spacing: 1px;
                font-weight: 600;
            }

            .tx-line {
                display: flex;
                justify-content: space-between;
                gap: 12px
            }

            .app-container{
                width: 100%;
                max-width: 400px;
                /* evita corte em devices altos */
                min-height: 100dvh;
                background: #f1f1f1;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                /* deixa espaço pro nav + safe bottom */
                padding: 24px 24px calc(24px + var(--safe-bottom));
            }

            .scroll-content {
                overflow-y: auto;
                padding-bottom: 80px;
                -webkit-overflow-scrolling: touch;
            }
            .scroll-content::-webkit-scrollbar {
                display: none;
            }

            .header h1 {
                font-weight: 600;
                font-size: 1.4rem;
                margin-bottom: 16px;
            }

            h6 {
                font-size: 18px !important;
                letter-spacing: 0.5px !important;
                margin: 10px 0 15px !important;
            }

            .balance-box {
                background-color: white;
                border-radius: 16px;
                padding: 16px;
                margin-bottom: 24px;
            }
            .balance-box span {
                color: #555;
                font-size: 0.9rem;
                display: block;
            }
            .balance-box strong {
                font-size: 2rem;
                font-weight: bold;
                color: #000;
            }

            .icons-carousel {
                overflow-x: auto;
                padding: 0 16px 24px;
                display: flex;
                gap: 26px;
                scroll-snap-type: x mandatory;
            }
            .icons-carousel::-webkit-scrollbar {
                display: none;
            }

            .icon-button {
                flex: 0 0 auto;
                scroll-snap-align: start;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: #000;
                font-size: 0.8rem;
            }
            .icon-button i {
                background-color: #00bfa6;
                color: #fff;
                border-radius: 50%;
                padding: 28px;
                margin-bottom: 6px;
                width: 40px;
                height: 40px;
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 18px;
            }

            .price-default {
                font-size: 12.5px !important;
                letter-spacing: .5px !important;
                color: #151515 !important;
            }

            .recent-transactions h2, .next-payments h2, .card-invoice h2, #calendar-results h2, .card-invoice-title {
                font-size: 16px;
                font-weight: 600;
                letter-spacing: 0.75px;
                margin-bottom: 17.5px;
            }
            .transaction-card {
                background-color: white;
                border-radius: 16px;
                padding: 12px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 12px;
            }
            .transaction-info {
                display: flex;
                align-items: center;
            }
            .transaction-info .icon {
                background-color: #00bfa6;
                color: white;
                border-radius: 50%;
                padding: 10px;
                margin-right: 10px;
                width: 36px;
                height: 36px;
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 0.9rem;
            }
            .transaction-info .details {
                font-size: 0.9rem;
                line-height: 1.1;
            }
            .transaction-info .details span {
                display: block;
                font-size: 0.75rem;
                color: #666;
            }
            .transaction-amount {
                font-weight: bold;
                color: #000;
            }

            .nav-link-atalho {
                color: #000;
                text-decoration: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .bottom-nav{
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;                       /* cola no fundo */
                height: 70px;

                display: flex;
                align-items: center;
                justify-content: space-around;

                background: #00bfa6;
                border-top-left-radius: 24px;
                border-top-right-radius: 24px;

                padding-top: max(15px, env(safe-area-inset-bottom));
                padding-top: max(15px, constant(safe-area-inset-bottom));

                padding-bottom: max(15px, env(safe-area-inset-bottom));
                padding-bottom: max(15px, constant(safe-area-inset-bottom));

                z-index: 1000;
            }

            .bottom-nav-link {
                display: flex;
                align-items: center;
                flex-direction: column;
                text-decoration: none;
            }

            .bottom-nav-link span {
                letter-spacing: 1.25px;
                font-weight: 600;
                font-size: 10px;
                margin-top: 5px;
                color: #fff;
            }

            .bottom-nav i{
                color:#fff;
                font-size: 16.5px;
            }

            .flatpickr-calendar {
                border-radius: 16px !important;
                width: 100% !important;
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
                border: 1px solid #e5e7eb !important;
            }

            .flatpickr-day {
                border-radius: 8px !important;
            }

            .flatpickr-day.selected {
                background-color: #00bfa6 !important;
                color: #fff !important;
            }

            .flatpickr-day.tem-evento::after {
                content: '';
                display: block;
                width: 6px;
                height: 6px;
                margin: 0 auto;
                margin-top: 2px;
                background-color: #999;
                border-radius: 50%;
            }
            .flatpickr-current-month {
                display: flex !important;
                align-items: center !important;
                justify-content: space-around !important;
            }
            .flatpickr-day.selected,
            .flatpickr-day.startRange,
            .flatpickr-day.endRange,
            .flatpickr-day.selected.inRange,
            .flatpickr-day.startRange.inRange,
            .flatpickr-day.endRange.inRange,
            .flatpickr-day.selected:focus,
            .flatpickr-day.startRange:focus,
            .flatpickr-day.endRange:focus,
            .flatpickr-day.selected:hover,
            .flatpickr-day.startRange:hover,
            .flatpickr-day.endRange:hover,
            .flatpickr-day.selected.prevMonthDay,
            .flatpickr-day.startRange.prevMonthDay,
            .flatpickr-day.endRange.prevMonthDay,
            .flatpickr-day.selected.nextMonthDay,
            .flatpickr-day.startRange.nextMonthDay,
            .flatpickr-day.endRange.nextMonthDay {
                background: #00bfa6;
                box-shadow: none;
                color: #fff;
                border-color: #00bfa6;
            }
            .flatpickr-current-month .flatpickr-monthDropdown-months,
            .flatpickr-current-month input.cur-year {
                font-size: 14px;
            }
            .flatpickr-next-month,
            .flatpickr-prev-month {
                fill: #00bfa6 !important;
            }

            input::placeholder {
                color: #b4b4b4 !important;
            }
            .form-check-input {
                height: .75em !important;
                width: .75em !important;
            }
            .form-check-label {
                font-size: 13.5px !important;
                color: #949494 !important;
            }
            .form-check-input:checked {
                background-color: var(--color-text) !important;
                border-color: var(--color-text) !important;
            }

            .create-btn {
                background: var(--color-text);
                border: 1px solid var(--color-text);
                box-shadow: 1px 1px 10px rgba(0,0,0,.5);
                padding: 16px 20px;
                position: fixed;
                bottom: 10%;
                right: 7.5%;
                border-radius: 100%;
                z-index: 9999;
            }

            .create-other {
                background: dodgerblue;
                border: none;
                padding: 10px 16px;
                bottom: 20%;
                right: 8%;
            }

            .create-other-2 {
                background: #ffbf1e;
                border: none;
                padding: 10px 16px;
                bottom: 26.5%;
                right: 8%;
            }

            .create-other-3 {
                background: #ff5a1e;
                border: none;
                padding: 10px 16px;
                bottom: 33%;
                right: 8%;
            }

            .custom-modal {
                position: fixed;
                bottom: 0; left: 0; right: 0;
                padding-bottom: var(--safe-bottom);
                height: auto;
                background: #fff;
                border-top-left-radius: 20px;
                border-top-right-radius: 20px;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.2);
                transform: translateY(100%);
                transition: transform 0.3s ease-in-out;
                z-index: 9999;
            }

            .custom-modal-content {
                padding: 20px 20px calc(20px + var(--safe-bottom));
                height: 100%;
                overflow-y: auto;
                position: relative;
            }

            .custom-modal.show {
                transform: translateY(0);
            }

            .close-btn {
                position: absolute;
                top: 10px;
                right: 20px;
                font-size: 24px;
                cursor: pointer;
            }
        }

        :root {
            --primary:    #00bfa6;
            --bg-input:   #f0f2f5;
            --bg-card:    #ffffff;
            --text-dark:  #333333;
            --radius:     8px;
            --spacing:    16px;
            --font-base:  'Inter', sans-serif;
        }

        .custom-modal {
            transform: translateY(100%);
            transition: transform 0.3s ease-in-out;
        }
        .custom-modal.show {
            transform: translateY(0);
        }

        .custom-modal-content {
            padding: var(--spacing);
            font-family: var(--font-base);
            color: var(--text-dark);
            max-height: 60vh;
            overflow-y: auto;
            position: relative;
        }

        .custom-modal-content form {
            margin-top: 30px;
        }

        .close-btn {
            top: 12px;
            right: 16px;
            font-size: 1.5rem;
        }

        /* form-group geral */
        .custom-modal-content .form-group {
            margin-bottom: var(--spacing);
        }
        .custom-modal-content label {
            margin-bottom: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* inputs padrão */
        .custom-modal-content .form-control,
        .custom-modal-content .form-control-sm {
            background: var(--bg-input);
            border: none;
            border-radius: var(--radius);
            height: 36px;
            padding: 0 1rem;
            font-size: 0.95rem;
            color: var(--text-dark);
            box-shadow: none;
        }
        .custom-modal-content .form-control:focus,
        .custom-modal-content .form-control-sm:focus {
            box-shadow: 0 0 0 2px var(--primary);
        }

        /* select2 override */
        .custom-modal-content .select2-container--default .select2-selection--single {
            background: var(--bg-input);
            border: none;
            border-radius: var(--radius);
            height: 44px;
            padding: 0 0.75rem;
        }
        .custom-modal-content .select2-container--default .select2-selection--single:focus {
            box-shadow: 0 0 0 2px var(--primary);
        }

        /* input[type=color] */
        .custom-modal-content input[type="color"] {
            padding: 0;
            height: 44px;
            width: 44px;
            border: none;
            border-radius: var(--radius);
        }

        /* generic select override */
        .custom-modal-content select.form-control {
            background: var(--bg-input);
            border: none;
            border-radius: var(--radius);
            height: 44px;
            padding: 0 1rem;
            font-size: 0.95rem;
            color: var(--text-dark);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='8'><path fill='%23333' d='M7 8L0 0h14z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 0.75rem;
        }
        .custom-modal-content select.form-control:focus {
            box-shadow: 0 0 0 2px var(--primary);
            outline: none;
        }
        .custom-modal-content option {
            background: var(--bg-card);
            color: var(--text-dark);
        }

        /* range */
        .custom-modal-content output {
            display: block;
            text-align: right;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .custom-modal-content .form-range {
            width: 100%;
            height: 4px;
            accent-color: var(--primary);
        }

        /* botão salvar */
        .custom-modal-content button[type="submit"],
        .custom-modal-content .btn-save {
            width: 100%;
            padding: 0.75rem;
            margin-top: var(--spacing);
            background: var(--primary);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background 0.2s;
        }
        .custom-modal-content button[type="submit"]:hover,
        .custom-modal-content .btn-save:hover {
            background: #00a38c;
        }

        /* ========== LOGIN ========== */
        .login-container {
            display: flex;
            width: 100%;
            min-height: 100dvh;
            align-items: center;
            justify-content: center;
            background: var(--bg-card) !important;
            padding: calc(var(--spacing) + var(--safe-top)) var(--spacing) calc(var(--spacing) + var(--safe-bottom));
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--bg-card) !important;
            border-radius: var(--radius);
            padding: var(--spacing);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .login-card h2 {
            margin-bottom: var(--spacing);
            color: var(--text-dark);
            text-align: center;
            font-size: 1.5rem;
        }

        .login-card .form-label {
            font-weight: 600;
            color: var(--text-dark);
        }

        .login-card .form-control {
            background: var(--bg-input);
            border: none;
            border-radius: var(--radius);
            height: 44px;
            padding: 0 1rem;
            box-shadow: none;
        }

        .login-card .form-control:focus {
            box-shadow: 0 0 0 2px var(--primary);
        }

        .login-card .btn-primary {
            background: var(--primary);
            border: none;
            height: 44px;
            border-radius: var(--radius);
            font-weight: 600;
        }

        .login-card .fs-7 {
            font-size: 0.75rem;
        }

        .login-card .form-check-label {
            color: var(--text-dark);
        }


        /* ========== DESKTOP LAYOUT ========== */
        @media (min-width: 481px) {
            body {
                margin: 0;
                display: flex;
                height: 100vh;
                font-family: var(--font-base);
                background: var(--bg-card);
            }

            .app-container {
                display: flex;
                width: 100%;
            }

            .sidebar {
                width: 240px;
                background: var(--color-text);
                color: #fff;
                padding: 24px;
                display: flex;
                flex-direction: column;
                position: fixed;
                top: 0; bottom: 0; left: 0;
            }
            .sidebar .logo {
                font-size: 1.4rem;
                font-weight: 600;
                margin-bottom: 2rem;
            }
            .sidebar nav a {
                display: flex;
                align-items: center;
                padding: 0.75rem 1rem;
                color: #fff;
                text-decoration: none;
                border-radius: var(--radius);
                margin-bottom: 0.5rem;
                transition: background 0.2s;
            }
            .sidebar nav a.active,
            .sidebar nav a:hover {
                background: rgba(255,255,255,0.1);
            }
            .sidebar nav a i {
                margin-right: 0.75rem;
                font-size: 1.1rem;
            }

            .content-area {
                margin-left: 240px;
                flex: 1;
                overflow-y: auto;
                padding: 24px;
                background: var(--bg-card);
            }

            /* esconde componentes mobile */
            .bottom-nav,
            .create-btn,
            .icons-carousel {
                display: none !important;
            }
        }

        .dash-amounts {
            letter-spacing: .75px !important;
            font-size: 12.5px !important;
        }


        /* Loading Content */
        .shimmer {
            --sk-height: 22px;
            --sk-radius: 5px;
            --sk-bg: rgba(143,143,143,.35);
            --sk-shine: rgba(255,255,255,.7);
            --sk-speed: 1.8s;
            width: 100%;
            position: relative;
            display: grid;
            grid-template-areas: "stack";
        }
        .shimmer > * { grid-area: stack; }

        .shimmer.is-loading { min-height: var(--sk-height); }
        .shimmer.is-loading > * { opacity: 0; }
        .shimmer.is-loading::before,
        .shimmer.is-loading::after{
            content: "";
            position: absolute; inset: 0;
            border-radius: var(--sk-radius);
            grid-area: stack;
            pointer-events: none;
        }
        .shimmer.is-loading::before { background: var(--sk-bg); }
        .shimmer.is-loading::after  {
            background: linear-gradient(90deg, transparent, var(--sk-shine), transparent);
            background-size: 200% 100%;
            animation: shimmer var(--sk-speed) linear infinite;
        }

        .shimmer.is-loaded > * { opacity: 1; transition: opacity .18s ease; }
        .shimmer.is-loaded::before,
        .shimmer.is-loaded::after { display: none; }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .shimmer--lg { --sk-height: 36px; --sk-radius: 18px; }
        .shimmer--xl { --sk-height: 60px; --sk-radius: 22px; }
        .shimmer--pill { --sk-radius: 999px; }
    </style>
</head>

<body>

<div class="app-container">
    <!-- Include:head -->
    @auth
        @include('layouts.partials.sidenav')
    @endauth

    <main id="app-main" class="content-area scroll-content" data-skeleton="tx-list">
        @yield('content')

        @auth
            <div class="bottom-nav">
                <a href="{{route('dashboard')}}" class="bottom-nav-link" data-nav>
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="{{ route('transaction-view.index') }}" class="bottom-nav-link" data-nav>
                    <i class="fa-solid fa-cart-plus"></i>
                    <span>Transações</span>
                </a>
                <a href="{{ route('push.debug') }}" class="bottom-nav-link" data-nav>
                    <i class="fa-solid fa-arrow-up-right-dots"></i>
                    <span>Projeções</span>
                </a>
                <a href="{{route('user-view.index')}}" class="bottom-nav-link" data-nav>
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
            </div>
        @endauth
    </main>
</div>

<script>
    (function(){
        const ua = navigator.userAgent || '';
        const isIOSLike =
            (/(iPad|iPhone|iPod)/.test(ua) && !window.MSStream) ||
            (navigator.platform === 'MacIntel' && (navigator.maxTouchPoints || 0) > 1);

        window.AUTH = @json(auth()->check());
        window.PUSH_CFG = {
            vapidKeyUrl: "{{ url('/vapid-public-key') }}",
            subscribeUrl: "{{ url('/push/subscribe') }}",
            swUrl: "{{ asset('sw.js') }}?v={{ filemtime(public_path('sw.js')) }}",
            loginPath: "{{ route('login') }}",
            isIOS: isIOSLike
        };
    })();
</script>

@php
    $pushRegisterPath = public_path('assets/js/push-register.js');
@endphp
<script src="{{ asset('assets/js/push-register.js') }}?v={{ file_exists($pushRegisterPath) ? filemtime($pushRegisterPath) : time() }}" defer></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script>
    window.__SPA_LITE__ = true;
</script>

<script src="{{asset('assets/js/cache/app-nav.js')}}"></script>
<script src="{{asset('assets/js/cache/http.js')}}"></script>
<script src="{{asset('assets/js/cache/storage.js')}}"></script>

@php
    $installPath = public_path('assets/js/install.js');
@endphp

<script src="{{ asset('assets/js/install.js') }}?v={{ file_exists($installPath) ? filemtime($installPath) : time() }}" defer></script>

<div id="net-banner"
     style="display:none;position:fixed;left:50%;transform:translateX(-50%);bottom:85px;z-index:1200;background:#222;color:#fff;padding:6px 10px;border-radius:8px;font-size:12px;">
    Conexão lenta — exibindo dados em cache…
</div>


@stack('scripts')

</body>

</html>
