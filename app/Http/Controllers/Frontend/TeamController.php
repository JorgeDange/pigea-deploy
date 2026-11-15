<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\Institucional;
use Illuminate\Contracts\View\View;

/**
 * Página pública da Equipa PIGEA — os membros vêm do painel admin,
 * agrupados por secção hierárquica (Institucional::SECCOES_EQUIPA).
 * Secções sem membros ativos não são renderizadas.
 */
class TeamController extends Controller
{
    public function index(): View
    {
        $membros = TeamMember::query()
            ->where('ativo', true)
            ->orderBy('ordem')->orderBy('id')
            ->get();

        // Agrupar e manter a ordem canônica das secções (não a dos registos).
        $porSecao = $membros->groupBy('cargo_select');
        $seccoes = [];
        foreach (Institucional::SECCOES_EQUIPA as $chave => $rotulo) {
            $lista = $porSecao->get($chave, collect());
            if ($lista->isNotEmpty()) {
                $seccoes[$chave] = ['rotulo' => $rotulo, 'membros' => $lista];
            }
        }

        return view('equipa', [
            'seccoes' => $seccoes,
            'total' => $membros->count(),
        ]);
    }
}
