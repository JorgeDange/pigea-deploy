<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Company;
use App\Models\DirectoryDocument;
use App\Models\Professional;
use Illuminate\Database\Seeder;

/**
 * Dados de demonstração do Diretório e da Publicidade (restrutura.md §3).
 *
 * Cobrem os 3 estados (aprovado/pendente/rejeitado) para permitir testar
 * a moderação do /admin (Fase 4) e a visibilidade pública (Fase 5) — no
 * público só entram os `aprovado`.
 *
 * FASE 7: os registos passam por Eloquent `create()` — os hooks de set
 * dos modelos gravam o JSON de `areas_atuacao`/`redes_sociais` com
 * acentos literais (JSON_UNESCAPED_UNICODE), sem o que a busca por área
 * do diretório público não bate nos registos.
 */
class DirectorySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompanies();
        $this->seedProfessionals();
        $this->seedDocuments();
        $this->seedBanners();
    }

    private function seedCompanies(): void
    {
        Company::create([
            'nome_empresa' => 'AngoGeo Serviços Mineiros Lda',
            'nif' => '5417000001',
            'contactos' => '+244 923 000 001 | geral@angogeo.ao',
            'areas_atuacao' => ['Mineração', 'Geociências'],
            'redes_sociais' => ['facebook' => 'https://facebook.com/angogeo', 'linkedin' => 'https://linkedin.com/company/angogeo'],
            'status' => 'aprovado',
        ]);
        Company::create([
            'nome_empresa' => 'Cuanza Petróleo e Gás SA',
            'nif' => '5417000002',
            'contactos' => '+244 923 000 002 | info@cuanzapg.ao',
            'areas_atuacao' => ['Petróleo e Gás', 'Energia'],
            'redes_sociais' => ['linkedin' => 'https://linkedin.com/company/cuanzapg'],
            'status' => 'aprovado',
        ]);
        Company::create([
            'nome_empresa' => 'HidroKwanza Consultoria',
            'nif' => '5417000003',
            'contactos' => '+244 923 000 003 | contacto@hidrokwanza.ao',
            'areas_atuacao' => ['Água', 'Ambiente'],
            'redes_sociais' => null,
            'status' => 'aprovado',
        ]);
        Company::create([
            'nome_empresa' => 'GeoLab Laboratório Analítico (aguardando aprovação)',
            'nif' => '5417000004',
            'contactos' => '+244 923 000 004 | lab@geolab.ao',
            'areas_atuacao' => ['Geociências', 'Mineração'],
            'redes_sociais' => null,
            'status' => 'pendente',
        ]);
        Company::create([
            'nome_empresa' => 'MineraSpec Fictícia (exemplo rejeitado)',
            'nif' => '5417000005',
            'contactos' => '+244 923 000 005 | nif-invalido@example.com',
            'areas_atuacao' => ['Mineração'],
            'redes_sociais' => null,
            'status' => 'rejeitado',
        ]);
    }

    private function seedProfessionals(): void
    {
        Professional::create([
            'nome_completo' => 'Eng.ª Mário Cassoma',
            'formacao_academica' => 'Mestrado em Engenharia de Minas — UTA',
            'contactos' => '+244 924 000 001 | mario.cassoma@example.ao',
            'areas_atuacao' => ['Mineração', 'Geociências'],
            'status' => 'aprovado',
        ]);
        Professional::create([
            'nome_completo' => 'Dr.ª Isabel Ferreira',
            'formacao_academica' => 'Doutoramento em Geologia — Agostinho Neto',
            'contactos' => '+244 924 000 002 | isabel.ferreira@example.ao',
            'areas_atuacao' => ['Petróleo e Gás', 'Geociências'],
            'status' => 'aprovado',
        ]);
        Professional::create([
            'nome_completo' => 'Eng. Paulo Neto (aguardando aprovação)',
            'formacao_academica' => 'Licenciatura em Engenharia do Ambiente — ISPTEC',
            'contactos' => '+244 924 000 003 | paulo.neto@example.ao',
            'areas_atuacao' => ['Ambiente', 'Água'],
            'status' => 'pendente',
        ]);
        Professional::create([
            'nome_completo' => 'Prof. Domingos Kiala (exemplo rejeitado)',
            'formacao_academica' => 'Dados de contacto incompletos',
            'contactos' => '—',
            'areas_atuacao' => ['Geociências'],
            'status' => 'rejeitado',
        ]);
    }

    private function seedDocuments(): void
    {
        DirectoryDocument::create([
            'titulo' => 'Código Mineiro de Angola (Lei n.º 31/20)',
            'descricao' => 'Lei das atividades mineiras da República de Angola, atualizada.',
            'tipo' => 'lei',
            'link_download' => 'https://example.ao/leis/codigo-mineiro-31-20.pdf',
            'areas_atuacao' => ['Mineração'],
        ]);
        DirectoryDocument::create([
            'titulo' => 'Guia de Boas Práticas Ambientais na Exploração Mineira',
            'descricao' => 'Artigo técnico da equipa PIGEA sobre mitigação de impactes ambientais.',
            'tipo' => 'artigo',
            'link_download' => 'https://example.ao/artigos/boas-praticas-ambientais.pdf',
            'areas_atuacao' => ['Ambiente', 'Mineração'],
        ]);
        DirectoryDocument::create([
            'titulo' => 'Decreto Presidencial n.º 268/21 — Regulamento de Hidrocarbonetos',
            'descricao' => 'Regulamento das operações petrolíferas onshore e offshore.',
            'tipo' => 'documento',
            'link_download' => 'https://example.ao/documentos/dp-268-21-hidrocarbonetos.pdf',
            'areas_atuacao' => ['Petróleo e Gás'],
        ]);
        DirectoryDocument::create([
            'titulo' => 'Atlas Hidrogeológico das Províncias do Sul',
            'descricao' => 'Documento de referência sobre aquíferos do Cuanza Sul, Huíla e Namibe.',
            'tipo' => 'documento',
            'link_download' => 'https://example.ao/documentos/atlas-hidrogeologico-sul.pdf',
            'areas_atuacao' => ['Água', 'Geociências'],
        ]);
    }

    private function seedBanners(): void
    {
        Banner::create([
            'titulo' => 'Banner Topo — Minex Angola (período atual)',
            'imagem_url' => 'images/all/banner.jpg',
            'link_destino' => 'https://example.ao/minex',
            'posicao' => 'topo',
            'data_inicio' => now()->subDay()->toDateString(),
            'data_fim' => now()->addMonth()->toDateString(),
            'ativo' => true,
        ]);
        Banner::create([
            'titulo' => 'Banner Sidebar — Hidrotec',
            'imagem_url' => 'images/all/banner.jpg',
            'link_destino' => 'https://example.ao/hidrotec',
            'posicao' => 'sidebar',
            'data_inicio' => null,
            'data_fim' => null,
            'ativo' => true,
        ]);
        // FASE 18 — slot vertical na sidebar (largura da coluna, 1:1)
        Banner::create([
            'titulo' => 'Banner Lateral — GeoAqua (vertical)',
            'imagem_url' => 'images/banners/lateral-demo.jpg',
            'link_destino' => 'https://example.ao/geoaqua',
            'posicao' => 'lateral',
            'data_inicio' => null,
            'data_fim' => null,
            'ativo' => true,
        ]);
        Banner::create([
            'titulo' => 'Banner expirado (teste de período)',
            'imagem_url' => 'images/all/banner.jpg',
            'link_destino' => null,
            'posicao' => 'topo',
            'data_inicio' => now()->subMonths(2)->toDateString(),
            'data_fim' => now()->subMonth()->toDateString(),
            'ativo' => true,
        ]);
    }
}
