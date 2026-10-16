<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\CompanyProfile;
use App\Models\Job;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Dados de demonstração dos painéis (`painel-*`).
 *
 * Idempotente: tudo via `firstOrCreate`/`updateOrCreate` com chaves estáveis
 * (email / par user+job / título).
 *
 * Contas de demonstração para o painel: `utilizador@pigea.ao` (role `user`) e
 * `empresa@pigea.ao` (role `company`), ambas com password `password`.
 */
class PainelSeeder extends Seeder
{
    /**
     * Estados e datas das candidaturas mostradas em
     * `painel-utilizador-candidaturas.html` (6 linhas: p, a, r, p, a, r).
     */
    private const CANDIDATURAS_PROTO = [
        ['estado' => 'em-analise', 'data' => '2026-09-21'],
        ['estado' => 'aceite',     'data' => '2026-09-18'],
        ['estado' => 'rejeitada',  'data' => '2026-09-12'],
        ['estado' => 'em-analise', 'data' => '2026-09-09'],
        ['estado' => 'aceite',     'data' => '2026-09-03'],
        ['estado' => 'rejeitada',  'data' => '2026-08-28'],
    ];

    /**
     * Candidaturas recebidas (`painel-empresa-candidaturas.html`): 5 linhas
     * (p, p, a, p, r) sobre as vagas 1-4 da empresa (a 5.ª repete a vaga 1).
     * O índice `vaga` conta por `Job` da empresa ordenado por id.
     */
    private const RECEBIDAS_PROTO = [
        ['vaga' => 0, 'estado' => 'em-analise', 'data' => '2026-09-21'],
        ['vaga' => 1, 'estado' => 'em-analise', 'data' => '2026-09-20'],
        ['vaga' => 2, 'estado' => 'aceite',     'data' => '2026-09-19'],
        ['vaga' => 3, 'estado' => 'em-analise', 'data' => '2026-09-18'],
        ['vaga' => 0, 'estado' => 'rejeitada',  'data' => '2026-09-17'],
    ];

    public function run(): void
    {
        $utilizador = User::firstOrCreate(
            ['email' => 'utilizador@pigea.ao'],
            ['name' => 'Ana Manuel', 'password' => 'password', 'phone' => '+244 912 345 678', 'province' => 'luanda', 'bio' => 'Geóloga com experiência em exploração mineira e hidrogeologia.']
        );
        $utilizador->assignRole('user');
        // forceFill para o seeder voltar a preencher o perfil mesmo que a conta
        // já exista (ex.: colunas adicionadas depois da primeira seed).
        $utilizador->forceFill(['status' => 'active', 'phone' => '+244 912 345 678', 'province' => 'luanda', 'bio' => 'Geóloga com experiência em exploração mineira e hidrogeologia.'])->save();

        $empresa = User::firstOrCreate(
            ['email' => 'empresa@pigea.ao'],
            ['name' => 'Minex Angola', 'password' => 'password']
        );
        $empresa->assignRole('company');
        $empresa->forceFill(['status' => 'active'])->save();

        // Ficha privada da empresa (campos de painel-empresa-perfil.html).
        CompanyProfile::updateOrCreate(
            ['user_id' => $empresa->id],
            [
                'name' => 'Minex Angola',
                'nif' => '5000123456',
                'sector' => 'mineracao',
                'province' => 'luanda',
                'phone' => '+244 222 334 556',
                'email' => 'geral@minex.ao',
                'website' => 'https://www.minex.ao',
                'address' => 'Rua da Missão, Ingombota, Luanda',
                'description' => 'Empresa mineira angolana com projetos de exploração de ferro e diamantes.',
            ]
        );

        // As vagas da empresa são as Jobs 4-8 (5 linhas em
        // `painel-empresa-vagas`). As Jobs 1-3 ficam com outros autores: a Ana
        // candidata-se a 6 vagas (1-6) e só 3 podem ser da empresa, senão as
        // candidaturas dela encheriam a tabela "Candidaturas recebidas" (o
        // protótipo mostra lá 5 linhas de outros candidatos).
        Job::query()->whereIn('id', [4, 5, 6, 7, 8])->update(['author_id' => $empresa->id]);
        Job::query()->where('id', 1)->update(['author_id' => 8]);
        Job::query()->where('id', 2)->update(['author_id' => 9]);
        Job::query()->where('id', 3)->update(['author_id' => 10]);

        // Candidaturas do utilizador — uma por vaga, com estados/datas do protótipo.
        Application::where('user_id', $utilizador->id)->delete();
        foreach (Job::query()->orderBy('id')->limit(6)->get() as $i => $job) {
            Application::create([
                'user_id' => $utilizador->id,
                'job_id' => $job->id,
                'name' => $utilizador->name,
                'email' => $utilizador->email,
                'status' => self::CANDIDATURAS_PROTO[$i % 6]['estado'],
                'created_at' => Carbon::parse(self::CANDIDATURAS_PROTO[$i % 6]['data']),
            ]);
        }

        // Candidaturas recebidas pela empresa (5 linhas, como no protótipo).
        $candidatos = User::query()->whereNotIn('id', [$utilizador->id, $empresa->id])->orderBy('id')->limit(5)->get();
        $vagasEmpresa = Job::query()->where('author_id', $empresa->id)->orderBy('id')->get();
        Application::whereIn('user_id', $candidatos->pluck('id'))->delete();
        foreach (self::RECEBIDAS_PROTO as $i => $r) {
            $candidato = $candidatos[$i % $candidatos->count()];
            $vaga = $vagasEmpresa[$r['vaga'] % max($vagasEmpresa->count(), 1)];
            Application::create([
                'user_id' => $candidato->id,
                'job_id' => $vaga->id,
                'name' => $candidato->name,
                'email' => $candidato->email,
                'status' => $r['estado'],
                'created_at' => Carbon::parse($r['data']),
            ]);
        }

        // Vagas guardadas (3, como no protótipo).
        foreach (Job::query()->orderBy('id')->limit(3)->get() as $job) {
            SavedJob::firstOrCreate(['user_id' => $utilizador->id, 'job_id' => $job->id]);
        }

        // Notificações: 3 por ler (`unread` no protótipo) + 2 lidas.
        // Datas de `painel-utilizador-notificacoes.html`; Newsletter (18/09) e
        // Evento (17/09) vêm trocadas face ao protótipo para o cartão
        // "Notificações" do dashboard mostrar os mesmos 4 itens (latest→4).
        $notificacoes = [
            ['icon' => 'fa-paper-plane', 'title' => 'Candidatura enviada', 'text' => 'a sua candidatura foi enviada com sucesso.', 'lida' => false, 'data' => '2026-09-21 14:32'],
            ['icon' => 'fa-check', 'title' => 'Candidatura aceite', 'text' => 'a Minex Angola aceitou a sua candidatura.', 'lida' => false, 'data' => '2026-09-20 09:15'],
            ['icon' => 'fa-bell', 'title' => 'Nova vaga', 'text' => 'surgiu uma vaga que corresponde às suas áreas de interesse (Mineração).', 'lida' => false, 'data' => '2026-09-19 18:04'],
            ['icon' => 'fa-envelope', 'title' => 'Newsletter PIGEA', 'text' => 'resumo semanal das notícias das geociências enviado para o seu email.', 'lida' => true, 'data' => '2026-09-18 10:00'],
            ['icon' => 'fa-calendar-alt', 'title' => 'Evento', 'text' => 'a próxima conferência de geociências começa dentro de dias.', 'lida' => true, 'data' => '2026-09-17 07:30'],
        ];
        foreach ($notificacoes as $n) {
            // updateOrCreate repõe o estado de leitura do protótipo mesmo que
            // a demo tenha sido tocada (ex.: testes marcaram tudo como lido).
            Notification::updateOrCreate(
                ['user_id' => $utilizador->id, 'title' => $n['title']],
                [
                    'icon' => $n['icon'],
                    'text' => $n['text'],
                    'read_at' => $n['lida'] ? Carbon::parse($n['data']) : null,
                    'created_at' => Carbon::parse($n['data']),
                ]
            );
        }

        // Oportunidades de negócio da empresa (4 linhas, como no protótipo).
        $oportunidades = [
            ['title' => 'Parceria de prospecção no Cuando Cubango', 'type' => 'Parceria', 'status' => 'ativa', 'deadline' => now()->addDays(31)],
            ['title' => 'Fornecimento de equipamentos de sondagem', 'type' => 'Fornecimento', 'status' => 'ativa', 'deadline' => now()->addDays(15)],
            ['title' => 'Joint venture de exploração de manganês', 'type' => 'Joint venture', 'status' => 'pendente', 'deadline' => now()->addDays(4)],
            ['title' => 'Licenciamento de dados geofísicos', 'type' => 'Licensing', 'status' => 'expirada', 'deadline' => now()->subDays(3)],
        ];
        foreach ($oportunidades as $o) {
            Opportunity::updateOrCreate(['user_id' => $empresa->id, 'title' => $o['title']], $o);
        }
    }
}
