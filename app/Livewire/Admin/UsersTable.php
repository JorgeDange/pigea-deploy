<?php

namespace App\Livewire\Admin;

use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela `admin/utilizadores` — Fase B (prop.md).
 *
 *  - **Perfil** = role Spatie (`editor`/`admin` na UI), "Perfil" na coluna.
 *  - **Estado** = coluna `users.status`; o botão «Desativar/Reativar» tem
 *    **efeito real** (prop.md §2.3 — o antigo «Bloquear» era cosmético):
 *    desativar mata todas as sessões da conta na hora (tabela `sessions`),
 *    o middleware `EnsureUserIsActive` desloga no pedido seguinte, e o
 *    `EnsureUserHasRole`/`role:admin,editor` continua o resto.
 *  - **Eliminar** exige confirmação em dois passos (escrever o email da
 *    conta) e é bloqueado quando a conta é o último admin, ou quando tem
 *    artigos associados (FK nullOnDelete deixaria o conteúdo órfão).
 *  - **Paginação** configurável (10/25/50/100) — resolve o «limite» de 6.
 */
class UsersTable extends Component
{
    use WithPagination;

    public string $perfil = '';

    public string $estado = '';

    public string $busca = '';

    public int $porPagina = 25;

    /** Two-step «Eliminar»: id pendente + email digitado para confirmar. */
    public ?int $aEliminar = null;

    public string $confirmarEmail = '';

    /** Erro de regra no painel (visível na própria tabela). */
    public string $erro = '';

    public string $ok = '';

    /** Opções do select «Por página» (regra: 10/25/50/100). */
    public const POR_PAGINA = [10, 25, 50, 100];

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

    /** Valor de `users.status` → [classe do badge, rótulo]. */
    protected const BADGES = [
        'active' => ['active', 'Ativo'],
        'pending' => ['pending', 'Pendente'],
        'blocked' => ['rejected', 'Desativado'],
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

        if ($propriedade === 'porPagina') {
            $this->porPagina = in_array($this->porPagina, self::POR_PAGINA, true) ? $this->porPagina : 25;
            $this->resetPage();
        }
    }

    /** Eliminar — passo 1: pede o email da conta (não apaga nada). */
    public function pedirEliminar(int $id): void
    {
        Gate::authorize('user.manage');

        $this->erro = '';
        $this->ok = '';

        if ($id === auth()->id()) {
            $this->erro = 'Não pode eliminar a sua própria conta.';
            $this->aEliminar = null;

            return;
        }

        $this->aEliminar = $id;
        $this->confirmarEmail = '';
    }

    public function cancelarEliminar(): void
    {
        $this->aEliminar = null;
        $this->confirmarEmail = '';
        $this->erro = '';
    }

    /** Eliminar — passo 2: com todas as guardas (email digitado + último admin + artigos). */
    public function eliminar(): void
    {
        Gate::authorize('user.manage');

        $id = $this->aEliminar;

        $this->erro = '';
        $this->ok = '';

        if ($id === null) {
            return;
        }

        if ($id === auth()->id()) {
            $this->erro = 'Não pode eliminar a sua própria conta.';
            $this->aEliminar = null;

            return;
        }

        $utilizador = User::find($id);

        if ($utilizador === null) {
            $this->aEliminar = null;

            return;
        }

        if (strcasecmp(trim($this->confirmarEmail), (string) $utilizador->email) !== 0) {
            $this->erro = 'O email escrito não coincide com o da conta a eliminar.';

            return;
        }

        if ($this->ehUltimoAdmin($utilizador)) {
            $this->erro = 'Não pode eliminar o último administrador existente.';

            return;
        }

        if (News::where('author_id', $utilizador->id)->exists()) {
            $this->erro = 'Este utilizador tem artigos associados. Desative a conta em vez de eliminar, ou reapontar o autor dos artigos primeiro.';

            return;
        }

        $nome = $utilizador->name;
        $utilizador->delete();

        $this->aEliminar = null;
        $this->confirmarEmail = '';
        $this->ok = 'Utilizador «'.$nome.'» eliminado.';
    }

    /**
     * Desativar/Reativar (Fase B / prop.md §2.3 e B3).
     *
     * Desativar: `status = blocked` + todas as sessões da conta apagadas
     * na hora; o middleware EnsureUserIsActive bloqueia o login seguinte.
     * O último administrador não pode ser desativado.
     */
    public function alternar(int $id): void
    {
        Gate::authorize('user.manage');

        $this->erro = '';
        $this->ok = '';

        if ($id === auth()->id()) {
            $this->erro = 'Não pode desativar a sua própria conta.';

            return;
        }

        $utilizador = User::find($id);

        if ($utilizador === null) {
            return;
        }

        $desativar = ($utilizador->status ?? 'active') !== 'blocked';

        if ($desativar && $this->ehUltimoAdmin($utilizador)) {
            $this->erro = 'Não pode desativar o último administrador existente.';

            return;
        }

        $utilizador->forceFill(['status' => $desativar ? 'blocked' : 'active'])->save();

        if ($desativar) {
            DB::table('sessions')->where('user_id', $utilizador->id)->delete();
        }

        $this->ok = $desativar ? 'Conta de «'.$utilizador->name.'» desativada.' : 'Conta de «'.$utilizador->name.'» reativada.';
    }

    /** O utilizador é o último admin vivo do sistema? */
    private function ehUltimoAdmin(User $utilizador): bool
    {
        return $utilizador->hasRole('admin') && User::role('admin')->count() <= 1;
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
            'utilizadores' => $query->orderByDesc('id')->paginate($this->porPagina),
            'opcoesPerfil' => array_keys(self::PERFIS),
            'opcoesEstado' => array_keys(self::ESTADOS),
            'opcoesPorPagina' => self::POR_PAGINA,
            'rotulos' => self::ROTULOS,
            'badges' => self::BADGES,
        ]);
    }
}
