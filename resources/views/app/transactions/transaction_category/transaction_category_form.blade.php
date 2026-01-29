<div class="row">
    <x-input col="12" set="" type="text" title="Categoria" id="name" name="name"
             value="{{ old('name', $transactionCategory->name ?? '') }}" placeholder="Salário, aluguel .."
             disabled=""></x-input>
</div>

<div class="row">
    <x-input col="6" set="" type="color" title="Cor" id="color" name="color"
             value="{{ old('color', $transactionCategory->color ?? '') }}" disabled=""></x-input>
</div>

@php
    $iconValue = old('icon', $transactionCategory->icon ?? 'fas fa-tags');
@endphp

<div class="row position-relative" id="iconPickerWrap">
    <label class="form-label">Ícone</label>
    <div class="d-flex align-items-center gap-2">
        <input type="hidden" id="iconInput" class="form-control" value="{{ $iconValue }}" readonly
               style="max-width:220px">
        <button type="button" id="iconBtn" class="btn btn-light border"><i class="{{ $iconValue }}"></i></button>
        <i id="iconPreview" class="{{ $iconValue }}" style="font-size:22px;margin-left:8px"></i>
        <input type="hidden" name="icon" id="icon" value="{{ $iconValue }}">
    </div>

    <!-- Dropdown -->
    <div id="iconDropdown" class="icon-dd d-none">
        <input id="iconSearch" class="form-control form-control-sm mb-2" placeholder="Buscar ícone...">
        <div id="iconGrid" class="icon-grid"></div>
    </div>
</div>


<div class="row mt-4">
    <label for="type">Tipo</label>
    <x-input-check col="12" set="" id="entrada" value="entrada" name="type" title="Entrada" checked="1"
                   disabled=""></x-input-check>
    <x-input-check col="12" set="" id="despesa" value="despesa" name="type" title="Despesa" checked=""
                   disabled=""></x-input-check>
    <x-input-check col="12" set="" id="investimento" value="investimento" name="type" title="Investimento" checked=""
                   disabled=""></x-input-check>
</div>

<input type="hidden" name="has_limit" id="has_limit" value="0">

<div class="row d-none" id="limitSwitchContainer">
    <label for="limitSwitch">Esta categoria terá um limite?</label>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="limitSwitch">
    </div>
</div>

<div class="row d-none" id="rangeContainer">
    <x-input-price col="6" title="Valor" id="amount" name="amount"/>
</div>


@push('styles')
    <style>
        .form-switch {
            padding-left: 50px;
            padding-bottom: 15px;
        }

        .form-check.form-switch .form-check-input {
            -webkit-appearance: none !important;
            appearance: none !important;
            width: 44px !important;
            height: 22px !important;
            background-color: #f1f1f1 !important;
            background-image: none !important;
            border-radius: 14px !important;
            position: relative !important;
            cursor: pointer !important;
            transition: background-color .3s !important;
        }

        /* Bolinha */
        .form-check.form-switch .form-check-input::after {
            content: "" !important;
            width: 16px !important;
            height: 16px !important;
            background-color: #fff !important;
            border-radius: 50% !important;
            position: absolute !important;
            left: 2px !important;
            top: 2px !important;
            transition: left .3s !important;
        }

        /* Estado ON */
        .form-check.form-switch .form-check-input:checked {
            background-color: #00c779 !important; /* fundo ON */
        }

        /* Move bolinha pra direita */
        .form-check.form-switch .form-check-input:checked::after {
            left: calc(100% - 26px) !important;
        }
    </style>

    <style>
        #iconPickerWrap {
            overflow: visible !important;
        }

        #iconPreview {
            display: none !important;
        }

        .icon-dd {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 10px;
            margin-top: 6px;
            z-index: 11000;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .08);
            max-height: 260px;
            overflow: auto;
        }

        .icon-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 8px;
        }

        .icon-item i {
            font-size: 16px;
        }

        @media (max-width: 480px) {
            .icon-grid {
                grid-template-columns: repeat(10, 1fr);
            }
        }

        .icon-dd {
            position: absolute;
            bottom: 100%; /* abre pra cima */
            top: auto;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 6px; /* espaço entre o input e o dropdown */
            z-index: 11000;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .08);
            max-height: 200px;
            overflow: auto;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{asset('assets/js/common/mask_price_input.js')}}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const limitSwitchCont = document.getElementById('limitSwitchContainer');
            const limitSwitch = document.getElementById('limitSwitch');
            const hasLimitInput = document.getElementById('has_limit');
            const rangeContainer = document.getElementById('rangeContainer');

            typeRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.id == 'despesa' || radio.id == 'investimento') {
                        limitSwitchCont.classList.remove('d-none');
                    } else {
                        limitSwitchCont.classList.add('d-none');
                        rangeContainer.classList.add('d-none');
                        limitSwitch.checked = false;
                        hasLimitInput.value = '0';
                    }
                });
            });

            limitSwitch.addEventListener('change', () => {
                hasLimitInput.value = limitSwitch.checked ? '1' : '0';
                rangeContainer.classList.toggle('d-none', !limitSwitch.checked);
            });
        });
    </script>

    <script>
        (function () {
            const ICONS = [
                'tags', 'address-card', 'wallet', 'cart-shopping', 'bag-shopping', 'basket-shopping', 'money-bill', 'money-check',
                'sack-dollar', 'arrow-trend-up', 'arrow-trend-down', 'chart-line', 'piggy-bank', 'utensils', 'house', 'wifi',
                'bolt', 'car', 'gas-pump', 'bus', 'train', 'ticket', 'hospital', 'stethoscope', 'gamepad', 'gift', 'dumbbell',
                'plane', 'hotel', 'music', 'film', 'book', 'graduation-cap', 'shirt', 'paw', 'leaf', 'bicycle', 'motorcycle',
                'broom', 'soap', 'trash', 'screwdriver-wrench', 'briefcase', 'clipboard-list', 'file-invoice-dollar', 'heart',
                'calendar', 'calendar-days', 'calendar-check', 'bell', 'bell-slash', 'clock', 'stopwatch', 'hourglass-half',
                'envelope', 'envelope-open', 'paper-plane', 'phone', 'phone-flip', 'mobile-screen', 'desktop', 'laptop',
                'camera', 'camera-retro', 'image', 'images', 'map', 'map-marker', 'map-location', 'location-dot',
                'clipboard', 'check', 'xmark', 'plus', 'minus', 'circle', 'circle-check', 'circle-xmark', 'circle-info',
                'star', 'star-half', 'star-half-stroke', 'star-of-life', 'trophy', 'medal', 'crown',
                'shopping-cart', 'store', 'store-alt', 'shop', 'warehouse', 'box', 'boxes', 'truck', 'shipping-fast',
                'user', 'user-plus', 'user-minus', 'user-check', 'users', 'user-group', 'id-card', 'id-badge',
                'lock', 'lock-open', 'key', 'shield', 'shield-halved', 'shield-check',
                'cloud', 'cloud-download', 'cloud-upload', 'download', 'upload', 'share', 'share-nodes',
                'comment', 'comments', 'comment-dots', 'message', 'messages', 'quote-left', 'quote-right',
                'globe', 'earth-americas', 'flag', 'flag-checkered',
                'thermometer', 'fire', 'water', 'droplet', 'wind', 'snowflake', 'sun', 'moon',
                'lightbulb', 'battery-full', 'battery-half', 'battery-empty',
                'microphone', 'microphone-slash', 'volume-up', 'volume-down', 'volume-off',
                'file', 'file-alt', 'file-pdf', 'file-excel', 'file-word', 'file-image', 'file-audio', 'file-video', 'file-archive',
                'folder', 'folder-open', 'folder-plus', 'folder-minus',
                'bars', 'ellipsis-h', 'ellipsis-v', 'filter', 'search', 'search-plus', 'search-minus',
                'sign-in-alt', 'sign-out-alt', 'door-open', 'door-closed', 'home', 'anchor', 'compass', 'road',
                'truck-pickup', 'motorcycle', 'helicopter', 'rocket', 'subway', 'ship', 'ferry', 'taxi',
                'first-aid', 'bandage', 'pills', 'syringe', 'kit-medical', 'dna', 'vial', 'vials',
                'tv', 'radio', 'satellite', 'plug', 'server', 'database',
                'handshake', 'thumbs-up', 'thumbs-down', 'hand-holding', 'hand-holding-heart', 'hand-holding-usd',
                'coins', 'credit-card', 'landmark', 'balance-scale', 'gavel', 'cog', 'cogs', 'sliders-h', 'toggle-on', 'toggle-off', 'spinner', 'circle-notch', 'sync', 'ban', 'question', 'info', 'exclamation', 'exclamation-triangle', 'exclamation-circle', 'info-circle', 'question-circle', 'check-square', 'square-check', 'minus-square', 'plus-square', 'square', 'dot-circle', 'bullseye',
                'arrow-up', 'arrow-down', 'arrow-left', 'arrow-right', 'external-link-alt', 'exchange-alt', 'arrows-alt', 'expand', 'compress', 'arrows-alt-h', 'arrows-alt-v', 'angle-left', 'angle-right', 'angle-up', 'angle-down', 'chevron-left', 'chevron-right', 'chevron-up', 'chevron-down', 'caret-left', 'caret-right', 'caret-up', 'caret-down', 'long-arrow-alt-left', 'long-arrow-alt-right', 'long-arrow-alt-up', 'long-arrow-alt-down', 'location-arrow',
                'list', 'list-ul', 'list-ol', 'table', 'columns', 'align-left', 'align-center', 'align-right', 'align-justify', 'indent', 'outdent', 'paragraph', 'link', 'unlink', 'paperclip', 'copy', 'cut', 'paste', 'highlighter', 'pen', 'edit', 'pen-nib', 'pencil-alt', 'eraser', 'ruler', 'ruler-combined', 'crop', 'brush', 'paint-roller', 'palette',
                'book-open', 'bookmark', 'book-reader', 'address-book', 'file-lines', 'file-code', 'file-csv', 'file-powerpoint', 'file-zipper', 'file-archive', 'file-download', 'file-upload', 'file-signature', 'file-invoice', 'folder-closed', 'folder-open', 'folder-tree', 'envelope-square', 'inbox', 'box-archive',
                'money-bill-wave', 'money-bill-alt', 'money-bills', 'money-check-alt', 'percent', 'calculator', 'receipt', 'cash-register', 'chart-bar', 'chart-pie', 'chart-area', 'barcode', 'qrcode', 'cart-plus', 'cart-arrow-down', 'tag',
                'at', 'hashtag', 'bullhorn', 'concierge-bell', 'share-square', 'share-alt', 'comment-alt', 'sms',
                'code', 'code-branch', 'terminal', 'bug', 'cube', 'cubes', 'server', 'network-wired', 'microchip', 'memory', 'hdd', 'keyboard', 'laptop-code', 'tablet-alt', 'mobile-alt', 'shield-alt', 'unlock-alt',
                'heartbeat', 'user-md', 'notes-medical', 'briefcase-medical', 'ambulance', 'hospital-alt', 'clinic-medical',
                'walking', 'running', 'swimmer', 'hiking', 'truck-monster', 'truck-moving', 'tractor', 'tram', 'plane-departure', 'plane-arrival', 'parachute-box', 'suitcase-rolling', 'map-pin', 'map-signs', 'route', 'road', 'location-arrow',
                'bed', 'bath', 'shower', 'toilet', 'sink', 'mug-hot', 'wine-glass', 'wine-bottle', 'beer', 'umbrella', 'fan', 'thermometer-half', 'thermometer-full', 'temperature-high', 'temperature-low',
                'pizza-slice', 'hamburger', 'hotdog', 'bacon', 'egg', 'cheese', 'fish', 'drumstick-bite', 'carrot', 'apple-alt', 'lemon', 'pepper-hot', 'ice-cream', 'cookie', 'bread-slice', 'coffee',
                'seedling', 'tree', 'mountain', 'campground', 'recycle', 'globe-africa', 'globe-asia', 'globe-europe',
                'user-tie', 'user-cog', 'user-edit', 'user-clock', 'user-shield', 'user-secret', 'user-graduate', 'user-ninja', 'user-astronaut', 'users-cog', 'users-slash', 'child', 'baby', 'female', 'male', 'smile', 'meh', 'frown', 'surprise', 'smile-beam', 'tired',
                'box-open', 'boxes', 'clipboard-check', 'clipboard-user', 'dolly', 'dolly-flatbed', 'truck-loading',
                'unlock', 'fingerprint', 'id-card-alt', 'id-card-clip',
                'play', 'pause', 'stop', 'backward', 'forward', 'step-backward', 'step-forward', 'fast-backward', 'fast-forward', 'volume-mute', 'headphones', 'headset', 'microphone-alt', 'podcast', 'record-vinyl', 'video', 'video-slash',
                'calendar-plus', 'calendar-minus', 'calendar-times', 'calendar-week', 'calendar-alt', 'history', 'hourglass-start', 'hourglass-end',
                'futbol', 'basketball-ball', 'volleyball-ball', 'baseball-ball', 'golf-ball', 'table-tennis', 'swimmer', 'running', 'biking',
                'school', 'chalkboard', 'chalkboard-teacher', 'building', 'university', 'landmark', 'business-time',
                'scroll', 'file-contract', 'balance-scale-left', 'balance-scale-right', 'handshake-alt-slash',
                'compass', 'globe', 'map-marked-alt', 'map-marked', 'map-pin', 'street-view',
                'thumbtack', 'hands-helping', 'hand-point-up', 'hand-point-down', 'hand-point-left', 'hand-point-right', 'hand-pointer', 'hand-paper', 'hand-rock', 'hand-peace', 'hand-scissors',
                'ghost', 'bomb', 'magnet', 'lightbulb', 'lightbulb-slash', 'moon', 'sun', 'spray-can', 'wrench', 'screwdriver', 'flask', 'vial'
            ];

            function detectPrefix(cls) {
                if (!cls) return 'fas';
                const first = cls.trim().split(/\s+/)[0];
                return first.startsWith('fa-') ? first : (first === 'fas' ? 'fas' : 'fa-solid');
            }

            const input = document.getElementById('iconInput');
            const hidden = document.getElementById('icon');
            const preview = document.getElementById('iconPreview');
            const btn = document.getElementById('iconBtn');
            const dd = document.getElementById('iconDropdown');
            const grid = document.getElementById('iconGrid');
            const search = document.getElementById('iconSearch');
            const wrap = document.getElementById('iconPickerWrap');

            const currentPrefix = detectPrefix(hidden.value);

            function render(list) {
                grid.innerHTML = '';
                list.forEach(name => {
                    const div = document.createElement('div');
                    div.className = 'icon-item';
                    const i = document.createElement('i');
                    i.className = `${currentPrefix} fa-${name}`;
                    div.appendChild(i);
                    div.dataset.value = i.className;
                    div.onclick = () => {
                        const val = div.dataset.value;
                        hidden.value = val;
                        input.value = val;
                        preview.className = val;
                        btn.querySelector('i').className = val;
                        close();
                    };
                    grid.appendChild(div);
                });
            }

            function open() {
                dd.classList.remove('d-none');
                render(ICONS);
                search.value = '';
                search.focus();

                const rect = btn.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                dd.style.bottom = '';
                dd.style.top = '';
                if (spaceBelow < 280) {
                    dd.style.bottom = '100%';
                    dd.style.marginBottom = '6px';
                } else {
                    dd.style.top = '100%';
                    dd.style.marginTop = '6px';
                }
            }

            function close() {
                dd.classList.add('d-none');
            }

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                dd.classList.toggle('d-none');
                if (!dd.classList.contains('d-none')) open();
            });

            input.addEventListener('click', open);

            document.addEventListener('click', (e) => {
                if (!wrap.contains(e.target)) close();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });

            search.addEventListener('input', () => {
                const q = search.value.trim().toLowerCase();
                render(ICONS.filter(n => n.includes(q)));
            });
        })();
    </script>
@endpush
