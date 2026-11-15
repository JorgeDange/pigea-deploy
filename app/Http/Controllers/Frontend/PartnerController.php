<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Support\Institucional;
use Illuminate\Contracts\View\View;

/**
 * Página pública dos Parceiros PIGEA — dois grupos fixos (Empresas e
 * Profissionais), conforme a estrutura da secção "Sobre Nós" definida pelo
 * cliente. Parceiros inativos não são renderizados.
 */
class PartnerController extends Controller
{
    public function index(): View
    {
        $parceiros = Partner::query()
            ->where('ativo', true)
            ->orderBy('ordem')->orderBy('id')
            ->get();

        return view('parceiros', [
            'empresas' => $parceiros->where('tipo', 'empresa')->values(),
            'profissionais' => $parceiros->where('tipo', 'profissional')->values(),
            'total' => $parceiros->count(),
        ]);
    }
}
