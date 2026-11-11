<?php

namespace App\Support;

use App\Models\Company;
use App\Models\News;
use App\Models\Project;
use App\Models\User;

/**
 * Notificações do painel admin (fonte única para o sino/modal e para a
 * lista de atividade recente no dashboard).
 *
 * Cada item inclui um `href` — clicável.
 *
 * @return list<array{icone: string, titulo: string, texto: string, tempo: ?string, href: string}>
 */
class NotificacoesPainel
{
    public static function obter(): array
    {
        $itens = [];

        if ($empresa = Company::pendente()->latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-building',
                'titulo' => 'Empresa à espera de aprovação',
                'texto' => ($empresa->nome_empresa ?? $empresa->nif).' submeteu registo no diretório.',
                'tempo' => $empresa->created_at?->diffForHumans(),
                'href' => route('admin.empresas'),
            ];
        }

        if ($tarefa = News::where('status', 'em_revisao')->latest('updated_at')->first()) {
            $itens[] = [
                'icone' => 'fa-newspaper',
                'titulo' => 'Notícia à espera de revisão',
                'texto' => '«'.$tarefa->title.'» enviada para revisão.',
                'tempo' => $tarefa->updated_at?->diffForHumans(),
                'href' => route('admin.noticia-editar', ['noticia' => $tarefa->id]),
            ];
        }

        if ($publicada = News::where('status', 'published')->latest('published_at')->first()) {
            $itens[] = [
                'icone' => 'fa-newspaper',
                'titulo' => 'Notícia publicada',
                'texto' => '«'.$publicada->title.'» já está no ar.',
                'tempo' => $publicada->published_at?->diffForHumans(),
                'href' => route('news.show', ['slug' => $publicada->slug]),
            ];
        }

        if ($utilizador = User::latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-user-plus',
                'titulo' => 'Novo utilizador',
                'texto' => ($utilizador->name ?? $utilizador->email).' criou conta.',
                'tempo' => $utilizador->created_at?->diffForHumans(),
                'href' => route('admin.utilizadores'),
            ];
        }

        if ($projeto = Project::latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-handshake',
                'titulo' => 'Nova oportunidade',
                'texto' => '«'.$projeto->title.'» publicado em projetos.',
                'tempo' => $projeto->created_at?->diffForHumans(),
                'href' => route('admin.projetos'),
            ];
        }

        return $itens;
    }
}
