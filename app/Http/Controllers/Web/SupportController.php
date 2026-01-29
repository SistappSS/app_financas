<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use App\Models\SupportRequestAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    // GET /support
    public function index()
    {
        $categories = [
            [
                'slug' => 'conta',
                'name' => 'Contas',
                'description' => 'Como cadastrar, editar e excluir contas.',
            ],
            [
                'slug' => 'transacoes',
                'name' => 'Transações',
                'description' => 'Como lançar receitas, despesas, recorrências e parcelas.',
            ],
            [
                'slug' => 'categoria-transacao',
                'name' => 'Categorias de transação',
                'description' => 'Como organizar suas receitas e despesas em categorias.',
            ],
            [
                'slug' => 'cartao',
                'name' => 'Cartões e faturas',
                'description' => 'Configuração de cartões, limites, fechamento e faturas.',
            ],
            [
                'slug' => 'investimentos',
                'name' => 'Investimentos',
                'description' => 'Como registrar e acompanhar seus investimentos no app.',
            ],
            [
                'slug' => 'projecoes',
                'name' => 'Projeções',
                'description' => 'Como interpretar as projeções e relatórios financeiros.',
            ],
            [
                'slug' => 'perfil',
                'name' => 'Perfil',
                'description' => 'Dados do usuário, foto, usuários adicionais e configurações.',
            ],
            [
                'slug' => 'outros',
                'name' => 'Outros',
                'description' => 'Não encontrou sua dúvida? Envie sua mensagem pra gente.',
            ],
        ];

        return view('app.support.support_index', compact('categories'));
    }


    // GET /support/{slug}
    public function article(string $slug)
    {
        // aqui só escolhemos qual view abrir
        switch ($slug) {
            case 'conta':
                return view('app.support.articles.account');

            case 'investimentos':
                return view('app.support.articles.investments');

            case 'cartao':
                return view('app.support.articles.card');

            case 'categoria-transacao':
                return view('app.support.articles.transaction-category');

            case 'transacoes':
                return view('app.support.articles.transactions');

            case 'projecoes':
                return view('app.support.articles.projections');

            case 'perfil':
                return view('app.support.articles.profile');

            case 'outros':
                return view('app.support.articles.outros');

            default:
                abort(404);
        }
    }

    // POST /support/outros
    public function storeOther(Request $request)
    {
        $validated = $request->validate([
            'message'      => ['required', 'string', 'max:5000'],
            'subject'      => ['nullable', 'string', 'max:255'],
            'images'       => ['nullable', 'array', 'max:5'], // até 5 imagens, ajuste se quiser
            'images.*'     => ['image', 'mimes:jpeg,png,jpg,webp', 'max:4096'], // 4MB cada
        ]);

        // cria o registro principal
        $supportRequest = SupportRequest::create([
            'user_id'       => auth()->id(),
            'category_slug' => 'outros',
            'subject'       => $validated['subject'] ?? null,
            'message'       => $validated['message'],
            'status'        => 'aberto',
        ]);

        // salva anexos (se houver)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('support_attachments', 'public'); // storage/app/public/support_attachments

                SupportRequestAttachment::create([
                    'support_request_id' => $supportRequest->id,
                    'path'               => $path,
                    'original_name'      => $file->getClientOriginalName(),
                    'mime_type'          => $file->getClientMimeType(),
                    'size'               => (int) round($file->getSize() / 1024),
                ]);
            }
        }

        return back()->with('success', 'Sua mensagem foi enviada. Em breve nossa equipe vai analisar.');
    }
}
