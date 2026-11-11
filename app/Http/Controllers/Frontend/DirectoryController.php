<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

/**
 * Página do Directório (restrutura.md §6) — a listagem com abas
 * "Empresas" / "Artigos/Documentos" e a pesquisa vivem no componente
 * Livewire `DirectoryListing` (empresas aprovadas; NIF nunca apresentado).
 */
class DirectoryController extends Controller
{
    public function index()
    {
        return view('directory.index');
    }
}
