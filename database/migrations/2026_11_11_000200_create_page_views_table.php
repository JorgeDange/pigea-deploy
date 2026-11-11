<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contador de visitas do portal (cms_pro.md — cartão "Visitas no Site").
 *
 * Agregado por dia (sem dados pessoais: sem IPs/cookies/user-agents); a
 * contagem acontece no middleware `CountPublicVisits`, só em páginas públicas
 * (nunca em /admin, /livewire ou assets). Resolução: 1 linha por dia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedBigInteger('visits')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
