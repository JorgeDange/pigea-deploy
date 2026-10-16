<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela `admin/utilizadores.html`.
 *
 * - **Perfil** = role Spatie (`user`/`company`/`editor`/`admin`), rótulo do protótipo.
 * - **Estado** = coluna `users.status` (`active`/`pending`/`blocked` →
 *   Ativa/Pendente/Bloqueada); o botão *Bloquear* do protótipo alterna entre
 *   `blocked` e `active`.
 * - **Último acesso** = `users.last_login_at`, registoado no evento `Login`
 *   (a tabela `sessions` apaga sessões expiradas e não servia como fonte).
 */
class UsersTable extends Component
{
    use WithPagination;

    public string $perfil = '';

    public string $estado = '';

    public string $busca = '';

    /** Rótulo do filtro → role. */
    protected const PERFIS = [
        'Utilizador' => 'user',
        'Empresa' => 'company',
        'Editor' => 'editor',
        'Admin' => 'admin',
    ];

    /** Rótulo do filtro → valor de `users.status`. */
    protected const ESTADOS = [
        'Ativo' => 'active',
        'Pendente' => 'pending',
        'Bloqueado' => 'blocked',
    ];

    /** Valor de `users.status` → [classe do badge, rótulo] (classe do protótipo). */
    protected const BADGES = [
        'active' => ['active', 'Ativa'],
        'pending' => ['pending', 'Pendente'],
        'blocked' => ['rejected', 'Bloqueada'],
    ];

    /** Role → rótulo da coluna "Perfil". */
    protected const ROTULOS = [
        'user' => 'Utilizador',
        'company' => 'Empresa',
        'editor' => 'Editor',
        'admin' => 'Admin',
    ];

    public function updated(string $propriedade): void
    {
        if (in_array($propriedade, ['perfil', 'estado', 'busca'], true)) {
            $this->resetPage();
        }
    }

    /** Eliminar: apaga o utilizador (o conteúdo fica sem autor — FK nullOnDelete). */
    public function eliminar(int $id): void
    {
        Gate::authorize('user.manage'); // permissão granular (só admin — Fase 13)

        if ($id === auth()->id()) {
            return; // não deixa apagar a própria sessão
        }

        User::find($id)?->delete();
    }

    /** Bloquear/desbloquear: alterna `users.status` entre `blocked` e `active`. */
    public function bloquear(int $id): void
    {
        Gate::authorize('user.manage'); // permissão granular (só admin — Fase 13)

        if ($id === auth()->id()) {
            return; // não deixa bloquear a própria sessão
        }

        $utilizador = User::find($id);

        if ($utilizador === null) {
            return;
        }

        $utilizador->forceFill([
            'status' => ($utilizador->status ?? 'active') === 'blocked' ? 'active' : 'blocked',
        ])->save();
    }

    public function render()
    {
        $query = User::query()->with('roles');

        if (($rotulo = $this->perfil) !== '') {
            $papel = self::PERFIS[$rotulo] ?? null;

            $papel === null
                ? $query->whereIn('id', [])
                : $query->whereHas('roles', fn ($q) => $q->where('name', $papel));
        }

        if (($estado = self::ESTADOS[$this->estado] ?? '') !== '') {
            $query->where('status', $estado);
        }

        if (trim($this->busca) !== '') {
            $termo = '%'.trim($this->busca).'%';
            $query->where(fn ($q) => $q->where('name', 'like', $termo)->orWhere('email', 'like', $termo));
        }

        return view('livewire.admin.users-table', [
            'utilizadores' => $query->orderByDesc('id')->paginate(6),
            'opcoesPerfil' => array_keys(self::PERFIS),
            'opcoesEstado' => array_keys(self::ESTADOS),
            'rotulos' => self::ROTULOS,
            'badges' => self::BADGES,
        ]);
    }
}
