<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * FASE 8 (11/11/2026) — fluxo de login por HTTP.
 *
 * A suíte nunca tinha testado o POST /login: a evidência de "login funcional"
 * era a navegação HTTP 200 + Livewire::actingAs — cega ao JS. O P0 (dashboard.js
 * a interceptar os forms) passou despercebido por isso. Este teste tranca a
 * porta: o POST /login tem de chegar ao Fortify.
 *
 * O fluxo de browser completo (render → submit → redirect) continua a depender
 * de validação visual, que o ambiente não tem (sem tooling de browser).
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_de_login_200(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_login_com_credenciais_validas_redireciona(): void
    {
        $user = User::factory()->create(['password' => Hash::make('segredo-123')]);
        $user->assignRole('admin');

        $resposta = $this->post('/login', [
            'email' => $user->email,
            'password' => 'segredo-123',
        ]);

        $resposta->assertStatus(302);
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_errada_volta_com_erros(): void
    {
        $user = User::factory()->create(['password' => Hash::make('segredo-123')]);

        $resposta = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'errada',
        ]);

        $resposta->assertStatus(302)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registo_publico_nao_existe(): void
    {
        // Reestruturação (restrutura.md): não há cadastro público — Fortify
        // sem Features::registration(). A página de login também não pode
        // prometer link para /register.
        $this->get('/register')->assertStatus(404);
        $this->post('/register', [
            'name' => 'X',
            'email' => 'x@example.com',
            'password' => 'segredo-123',
        ])->assertStatus(404);
    }
}
