<?php

namespace App\Livewire\Frontend;

use App\Models\Company;
use App\Models\DirectoryDocument;
use App\Models\Professional;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Listagem do Directório público (restrutura.md §6).
 *
 * Três abas — "Empresas", "Profissionais" e "Artigos/Documentos" — e uma
 * barra de pesquisa que filtra por nome ou área de atuação.
 *
 *  - Empresas: apenas `status = 'aprovado'` (scope `aprovado`); mostram-se
 *    Nome, Áreas de Atuação, Contactos e Redes Sociais. O NIF NUNCA é
 *    apresentado no público (restrutura.md §6 — serve só de identificação
 *    no registo e moderação).
 *  - Profissionais: apenas `status = 'aprovado'`; mostram-se Nome Completo,
 *    Formação Académica, Áreas de Atuação e Contactos (sem NIF).
 *  - Artigos/Documentos: publicados pela equipa interna, com botão
 *    "Download" que abre `link_download` (restrutura.md §1 — apenas link).
 */
class DirectoryListing extends Component
{
    use WithPagination;

    /** Aba ativa — na URL (?aba=) para que fique partilhável/retrocedível. */
    #[Url]
    public string $aba = 'empresas';

    /** Busca por nome/área (empresas e profissionais) ou título/área (documentos). */
    public string $busca = '';

    /** Rejeita `?aba=` desconhecidos — cai no default "empresas". */
    public function mount(): void
    {
        if (! in_array($this->aba, ['empresas', 'profissionais', 'documentos'], true)) {
            $this->aba = 'empresas';
        }
    }

    public function updatedAba(): void
    {
        $this->resetPage();
    }

    public function updatedBusca(): void
    {
        $this->resetPage();
    }

    /** Troca de aba pelos botões (mantém `aba` na URL). */
    public function mostrar(string $aba): void
    {
        if (in_array($aba, ['empresas', 'profissionais', 'documentos'], true)) {
            $this->aba = $aba;
            $this->resetPage();
        }
    }

    public function render()
    {
        $busca = trim($this->busca);

        $empresas = Company::aprovado()
            ->when($busca !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('nome_empresa', 'like', '%'.$busca.'%')
                // `areas_atuacao` é JSON — o like pelo texto apanha a área
                // gravada ("Mineração", "Petróleo e Gás", …).
                ->orWhere('areas_atuacao', 'like', '%'.$busca.'%')))
            ->orderBy('nome_empresa')
            ->orderBy('id')
            ->paginate(8, ['*'], 'paginaEmpresas');

        $profissionais = Professional::aprovado()
            ->when($busca !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('nome_completo', 'like', '%'.$busca.'%')
                ->orWhere('formacao_academica', 'like', '%'.$busca.'%')
                ->orWhere('areas_atuacao', 'like', '%'.$busca.'%')))
            ->orderBy('nome_completo')
            ->orderBy('id')
            ->paginate(8, ['*'], 'paginaProfissionais');

        $documentos = DirectoryDocument::query()
            ->when($busca !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('titulo', 'like', '%'.$busca.'%')
                ->orWhere('descricao', 'like', '%'.$busca.'%')
                ->orWhere('areas_atuacao', 'like', '%'.$busca.'%')))
            ->orderBy('titulo')
            ->orderBy('id')
            ->paginate(8, ['*'], 'paginaDocumentos');

        return view('livewire.frontend.directory-listing', [
            'empresas' => $empresas,
            'profissionais' => $profissionais,
            'documentos' => $documentos,
        ]);
    }
}
