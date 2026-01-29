<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enviar Notificação Push</title>
</head>
<body>
<div style="max-width:400px;margin:1rem auto">
    @if(session('success'))
        <p>Notificação enviada com sucesso!</p>
    @endif

        <a href="{{ route('push.debug') }}" class="bottom-nav-link" data-nav>
            <i class="fa-solid fa-arrow-up-right-dots"></i>
            <span>DEBUG</span>
        </a>

        <button id="enable-notifications">Ativar Notificações</button>

        <h4>Enviar Notificação Push</h4>
    <form method="POST" action="{{ url('/push/teste') }}">
        @csrf
        <div>
            <label>Título</label><br>
            <input type="text" name="title" placeholder="Título da notificação">
        </div>
        <div style="margin-top:1rem">
            <label>Mensagem</label><br>
            <textarea name="body" placeholder="Mensagem..."></textarea>
        </div>
        <button style="margin-top:1rem">Enviar</button>
    </form>
</div>

<script src="{{ asset('assets/js/push-register.js') }}"></script>
</body>
</html>
