<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\PushNotification;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'endpoint'    => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth'   => 'required|string',
        ]);

        try {
            $request->user()->updatePushSubscription(
                $data['endpoint'],
                $data['keys']['p256dh'],
                $data['keys']['auth']
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Push subscribe error: '.$e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function showForm()
    {
        return view('app.push.index');
    }

    public function send(Request $request)
    {
        $user = auth()->user();
        $title = $request->input('title', 'Título Padrão');
        $body = $request->input('body', 'Conteúdo da notificação');

        $user->notify(new PushNotification($title, $body));

        return back()->with('success', 'Notificação enviada com sucesso!');
    }

    public function page()
    {
        return view('app.push.debug');
    }

    public function sender(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
            'p256dh'   => 'required|string',
            'auth'     => 'required|string',
            'title'    => 'nullable|string',
            'body'     => 'nullable|string',
            'url'      => 'nullable|string',
        ]);

        $auth = [
            'VAPID' => [
                'subject'    => config('webpush.vapid.subject') ?: url('/'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);
        $sub = Subscription::create([
            'endpoint' => $request->endpoint,
            'keys'     => ['p256dh' => $request->p256dh, 'auth' => $request->auth],
        ]);

        $payload = json_encode([
            'title' => $request->input('title', 'Teste do servidor'),
            'body'  => $request->input('body',  'Enviado só para ESTE dispositivo'),
            'data'  => ['url' => $request->input('url', '/')],
            'icon'  => '/laravelpwa/icons/icon-192x192.png',
        ]);

        $webPush->queueNotification($sub, $payload);

        $reports = [];
        foreach ($webPush->flush() as $report) {
            $reports[] = [
                'endpoint' => $report->getRequest()->getUri()->__toString(),
                'success'  => $report->isSuccess(),
                'reason'   => $report->isSuccess() ? null : $report->getReason(),
                'status'   => $report->getResponse() ? $report->getResponse()->getStatusCode() : null,
            ];
        }

        return response()->json(['ok' => true, 'reports' => $reports]);
    }
}
