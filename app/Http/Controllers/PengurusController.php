<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengurus\StorePengurusRequest;
use App\Http\Requests\Pengurus\UpdatePengurusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PengurusController extends Controller
{

    public function index(Request $request): Response
    {
        $search = $request->string('search')
            ->trim()
            ->toString();

        $pengurus = User::query()
            ->where('role', User::ROLE_PENGURUS)
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'ilike', "%{$search}%")
                            ->orWhere('username', 'ilike', "%{$search}%");
                    });
                }
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Pengurus/Index', [
            'pengurus' => $pengurus,

            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(
        StorePengurusRequest $request
    ): RedirectResponse {
        $passwordSementara = $this->generateTemporaryPassword();

        $pengurus = User::create([
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'password' => $passwordSementara,
            'role' => User::ROLE_PENGURUS,
            'must_change_password' => true,
        ]);

        return back()->with([
            'toast' => $this->makeToast(
                message: 'Data pengurus berhasil disimpan.',
            ),

            'temporary_password' => [
                'name' => $pengurus->name,
                'username' => $pengurus->username,
                'password' => $passwordSementara,
            ],
        ]);
    }

    public function update(
        UpdatePengurusRequest $request,
        User $pengurus
    ): RedirectResponse {
        $this->ensurePengurus($pengurus);

        $pengurus->update(
            $request->validated()
        );

        return back()->with(
            'toast',
            $this->makeToast(
                message: 'Data pengurus berhasil diubah.',
            )
        );
    }

    public function destroy(
        User $pengurus
    ): RedirectResponse {
        $this->ensurePengurus($pengurus);

        $pengurus->delete();

        return back()->with(
            'toast',
            $this->makeToast(
                message: 'Data pengurus berhasil dihapus.',
            )
        );
    }


    public function resetPassword(
        User $pengurus
    ): RedirectResponse {
        $this->ensurePengurus($pengurus);

        $passwordSementara = $this->generateTemporaryPassword();

        $pengurus->update([
            'password' => $passwordSementara,
            'must_change_password' => true,
        ]);

        return back()->with([
            'toast' => $this->makeToast(
                message: 'Password pengurus berhasil direset.',
            ),

            'temporary_password' => [
                'name' => $pengurus->name,
                'username' => $pengurus->username,
                'password' => $passwordSementara,
            ],
        ]);
    }


    private function ensurePengurus(User $pengurus): void
    {
        abort_unless(
            $pengurus->isPengurus(),
            404
        );
    }


    private function generateTemporaryPassword(): string
    {
        return Str::password(
            length: 10,
            letters: true,
            numbers: true,
            symbols: false,
        );
    }

    private function makeToast(
        string $message,
        string $type = 'success'
    ): array {
        return [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'message' => $message,
        ];
    }
}