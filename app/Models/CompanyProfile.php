<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ficha privada da empresa do painel (`company_profiles`).
 *
 * Separada do `directory` (empresas públicas do portal) porque guarda dados
 * internos — NIF, contactos — e uma empresa pode existir no painel sem constar
 * do diretório. Campos exactos do formulário `painel-empresa-perfil.html`.
 */
class CompanyProfile extends Model
{
    protected $fillable = [
        'user_id', 'name', 'nif', 'sector', 'company_type', 'province',
        'phone', 'email', 'website', 'address', 'description', 'logo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
