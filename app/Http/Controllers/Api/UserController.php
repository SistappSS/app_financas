<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdditionalUser;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public $user;
    public $additionalUser;

    public function __construct(User $user, AdditionalUser $additionalUser)
    {
        $this->user         = $user;
        $this->additionalUser = $additionalUser;
    }

    public function index(Request $request)
    {
        $authUser = $request->user();
        $ownerId  = AdditionalUser::ownerIdFor(); // sempre o principal

        // dono + todos adicionais
        $accountUsers = User::select('users.id','users.name','users.email','users.is_active','users.image')
            ->where(function ($q) use ($ownerId) {
                $q->where('users.id', $ownerId)
                    ->orWhereIn('users.id', function ($sub) use ($ownerId) {
                        $sub->select('linked_user_id')
                            ->from('additional_users')
                            ->where('user_id', $ownerId);
                    });
            })
            ->orderBy('users.name')
            ->get();

        // remove o próprio da lista
        $users = $accountUsers
            ->where('id', '!=', $authUser->id)
            ->values();

        return $request->wantsJson()
            ? response()->json($users)
            : view('app.users.user_index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','email','max:255','unique:users,email'],
            'password'  => ['required','confirmed','string','min:6'],
            'is_active' => ['nullable','boolean'],
            // NÃO validamos image aqui; processamos manualmente
        ]);

        $imagemBase64 = $this->processImage($request);

        $ownerId = AdditionalUser::ownerIdFor();

        $newUser = null;

        DB::transaction(function () use (&$newUser, $data, $ownerId, $imagemBase64) {
            $newUser = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
                'image'     => $imagemBase64,
            ]);

            $newUser->assignRole('additional_user');

            AdditionalUser::firstOrCreate([
                'user_id'        => $ownerId,
                'linked_user_id' => $newUser->id,
            ]);
        });

        return response()->json([
            'id'        => $newUser->id,
            'name'      => $newUser->name,
            'email'     => $newUser->email,
            'is_active' => (bool) $newUser->is_active,
            'image'     => $newUser->image,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'      => ['sometimes','required','string','max:255'],
            'email'     => ['sometimes','required','email','max:255','unique:users,email,' . $user->id],
            'password'  => ['nullable','confirmed','string','min:6'],
            'is_active' => ['sometimes','boolean'],
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (array_key_exists('is_active', $data)) {
            $user->is_active = $data['is_active'];
        }

        $imagemBase64 = $this->processImage($request);
        if ($imagemBase64 !== null) {
            $user->image = $imagemBase64;
        }

        $user->save();

        return response()->json([
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'is_active' => (bool) $user->is_active,
            'image'     => $user->image,
        ]);
    }

    protected function processImage(Request $request): ?string
    {
        // 1) Arquivo enviado (caso do avatar principal ou se em algum lugar ainda usar file)
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                $imageData = file_get_contents($file->getRealPath());
                $image     = imagecreatefromstring($imageData);

                if ($image === false) {
                    return null;
                }

                $w = 250;
                $h = 250;
                $resizedImage = imagescale($image, $w, $h);

                ob_start();
                imagejpeg($resizedImage);
                $rawImage = ob_get_clean();

                imagedestroy($resizedImage);
                imagedestroy($image);

                return base64_encode($rawImage);
            }
        }

        // 2) String/base64 no campo "image" (vou fazer o form mandar assim)
        $raw = $request->input('image');

        if (is_string($raw) && $raw !== '') {
            // se vier "data:image/png;base64,XXXX"
            if (str_starts_with($raw, 'data:image')) {
                $parts = explode(',', $raw, 2);
                $raw   = $parts[1] ?? '';
            }

            $bin = base64_decode($raw, true);
            if ($bin === false) {
                return null;
            }

            $image = imagecreatefromstring($bin);
            if ($image === false) {
                return null;
            }

            $w = 250;
            $h = 250;
            $resizedImage = imagescale($image, $w, $h);

            ob_start();
            imagejpeg($resizedImage);
            $rawImage = ob_get_clean();

            imagedestroy($resizedImage);
            imagedestroy($image);

            return base64_encode($rawImage);
        }

        return null;
    }

    public function show(string $id) {}
    public function edit($id) { return view('app.users.user_edit'); }
    public function destroy(string $id) {}
}
