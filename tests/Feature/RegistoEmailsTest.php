<?php

namespace Tests\Feature;

use App\Livewire\Admin\CompaniesTable;
use App\Livewire\Admin\ProfessionalsTable;
use App\Livewire\Frontend\RegistoEmpresa;
use App\Livewire\Frontend\RegistoProfissional;
use App\Mail\RegistoAprovado;
use App\Mail\RegistoConfirmacao;
use App\Mail\RegistoRejeitado;
use App\Models\Company;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Emails transacionais do Diretório (mailer `geral`, From geral@pigea.ao):
 * confirmação no submit (empresa e profissional), decisão na moderação
 * do painel (aprovar/rejeitar), silêncio no restabelecer e blindagem
 * contra falhas do SMTP — a ação nunca se desfaz por causa de email.
 */
class RegistoEmailsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Confirmação no submit (público)
    // ------------------------------------------------------------------

    public function test_submissao_empresa_envia_confirmacao_para_o_email_do_candidato(): void
    {
        Mail::fake();

        Livewire::test(RegistoEmpresa::class)
            ->set('nome_empresa', 'TEST Empresa Mail')
            ->set('nif', 'MAIL0001')
            ->set('contactos', '+244 900 111 001')
            ->set('email', 'chefe@mail-empresa.ao')
            ->set('areas_atuacao', ['Água'])
            ->call('submit')
            ->assertSet('estado', 'ok');

        Mail::assertSent(RegistoConfirmacao::class, fn ($m) => $m->hasTo('chefe@mail-empresa.ao'));
        $this->assertSame('chefe@mail-empresa.ao', Company::where('nif', 'MAIL0001')->value('email'));
    }

    public function test_submissao_profissional_envia_confirmacao_para_o_email_do_candidato(): void
    {
        Mail::fake();

        Livewire::test(RegistoProfissional::class)
            ->set('nome_completo', 'TEST Prof Mail')
            ->set('formacao_academica', 'Licenciatura em Geologia')
            ->set('contactos', '+244 900 111 002')
            ->set('email', 'pro@mail-prof.ao')
            ->set('areas_atuacao', ['Geociências'])
            ->call('submit')
            ->assertSet('estado', 'ok');

        Mail::assertSent(RegistoConfirmacao::class, fn ($m) => $m->hasTo('pro@mail-prof.ao'));
        $this->assertSame('pro@mail-prof.ao', Professional::where('nome_completo', 'TEST Prof Mail')->value('email'));
    }

    // ------------------------------------------------------------------
    // Decisão na moderação (admin)
    // ------------------------------------------------------------------

    public function test_aprovar_empresa_envia_email_de_aprovado(): void
    {
        Mail::fake();

        $empresa = Company::create([
            'nome_empresa' => 'TEST Empresa Aprovar', 'nif' => 'APROVEMAIL1', 'contactos' => 'x',
            'email' => 'aprova@example.ao', 'areas_atuacao' => ['Água'], 'status' => 'pendente',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('aprovar', $empresa->id);

        $this->assertSame('aprovado', $empresa->fresh()->status);
        Mail::assertSent(RegistoAprovado::class, fn ($m) => $m->hasTo('aprova@example.ao'));
        Mail::assertNotSent(RegistoRejeitado::class);
    }

    public function test_rejeitar_profissional_envia_email_de_atualizacao(): void
    {
        Mail::fake();

        $profissional = Professional::create([
            'nome_completo' => 'TEST Prof Rejeitar', 'formacao_academica' => 'x', 'contactos' => 'x',
            'email' => 'rejeita@example.ao', 'areas_atuacao' => ['Geociências'], 'status' => 'pendente',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ProfessionalsTable::class)
            ->call('rejeitar', $profissional->id);

        $this->assertSame('rejeitado', $profissional->fresh()->status);
        Mail::assertSent(RegistoRejeitado::class, fn ($m) => $m->hasTo('rejeita@example.ao'));
        Mail::assertNotSent(RegistoAprovado::class);
    }

    public function test_restabelecer_nao_envia_email(): void
    {
        Mail::fake();

        $empresa = Company::create([
            'nome_empresa' => 'TEST Empresa Restabelecer', 'nif' => 'RESTAB1', 'contactos' => 'x',
            'email' => 'restabelecer@example.ao', 'areas_atuacao' => ['Água'], 'status' => 'rejeitado',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('restabelecer', $empresa->id);

        $this->assertSame('pendente', $empresa->fresh()->status);
        Mail::assertNothingSent();
    }

    // ------------------------------------------------------------------
    // Blindagem
    // ------------------------------------------------------------------

    public function test_aprovar_registo_antigo_sem_email_nao_rebenta_e_nao_envia(): void
    {
        Mail::fake();

        $empresa = Company::create([
            'nome_empresa' => 'TEST Empresa SemEmail', 'nif' => 'SEMEMAIL1', 'contactos' => 'x',
            'email' => null, 'areas_atuacao' => ['Água'], 'status' => 'pendente',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('aprovar', $empresa->id);

        $this->assertSame('aprovado', $empresa->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_falha_no_smtp_nao_desfaz_a_aprovacao(): void
    {
        Mail::shouldReceive('mailer')->andThrow(new \RuntimeException('smtp down'));

        $empresa = Company::create([
            'nome_empresa' => 'TEST Empresa SmtpDown', 'nif' => 'SMTPDOWN1', 'contactos' => 'x',
            'email' => 'smtpdown@example.ao', 'areas_atuacao' => ['Água'], 'status' => 'pendente',
        ]);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('aprovar', $empresa->id);

        $this->assertSame('aprovado', $empresa->fresh()->status, 'O email nunca pode desfazer a decisão do admin.');
    }
}
