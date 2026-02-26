<?php

namespace App\Services\Billing;

use App\Models\Auth\User;
use Illuminate\Http\Client\Factory as HttpFactory;

class AsaasService
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    private function client()
    {
        $baseUrl = config('asaas.sandbox') ? config('asaas.sandbox_url') : config('asaas.production_url');
        $token = config('asaas.sandbox') ? config('asaas.sandbox_token') : config('asaas.production_token');

        return $this->http->baseUrl(rtrim($baseUrl, '/').'/'.config('asaas.version'))
            ->acceptJson()
            ->withHeader('access_token', (string) $token)
            ->timeout(20);
    }

    public function ensureCustomer(User $user): string
    {
        if ($user->asaas_customer_id) {
            return $user->asaas_customer_id;
        }

        $response = $this->client()->post('/customers', [
            'name' => $user->name,
            'email' => $user->email,
            'externalReference' => $user->id,
        ])->throw()->json();

        return (string) ($response['id'] ?? '');
    }

    public function createPixPayment(string $customerId, array $payload): array
    {
        $payment = $this->client()->post('/payments', [
            'customer' => $customerId,
            'billingType' => 'PIX',
            'value' => $payload['amount'],
            'dueDate' => $payload['due_date'],
            'description' => $payload['description'],
            'externalReference' => $payload['external_reference'],
        ])->throw()->json();

        $pix = $this->client()->get('/payments/'.$payment['id'].'/pixQrCode')->throw()->json();

        return [
            'payment' => $payment,
            'pix' => $pix,
        ];
    }
}
