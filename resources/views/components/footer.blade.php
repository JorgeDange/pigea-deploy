<footer class="fl-wrap main-footer">
    <div class="container">
        <!-- footer-widget-wrap -->
        <div class="footer-widget-wrap fl-wrap">
            <div class="row">
                <!-- footer-widget -->
                <div class="col-md-3">
                    <div class="footer-widget">
                        <div class="footer-widget-content">
                            <a href="{{ route('home') }}" class="footer-logo"><img src="{{ asset('images/logo2.png') }}" alt="PIGEA"></a>
                            <p>PIGEA — Plataforma de Impulsionamento das Geociências em Angola. Centralizamos informação, promovemos a transparência e ligamos profissionais, empresas e investigadores.</p>
                            <div class="footer-social fl-wrap">
                                <ul>
                                    @include('components.social-icons')
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- footer-widget  end-->
                <!-- footer-widget -->
                <div class="col-md-2">
                    <div class="footer-widget">
                        <div class="footer-widget-title">Conteúdos</div>
                        <div class="footer-widget-content">
                            <div class="footer-list footer-box fl-wrap">
                                <ul>
                                    <li> <a href="{{ route('news.index') }}">Notícias</a></li>
                                    <li> <a href="{{ route('legislation.index') }}">Legislação</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- footer-widget  end-->
                <!-- footer-widget -->
                <div class="col-md-2">
                    <div class="footer-widget">
                        <div class="footer-widget-title">Comunidade</div>
                        <div class="footer-widget-content">
                            <div class="footer-list footer-box fl-wrap">
                                <ul>
                                    <li> <a href="{{ route('training.index') }}">Formação</a></li>
                                    <li> <a href="{{ route('events.index') }}">Eventos</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- footer-widget  end-->
                <!-- footer-widget -->
                <div class="col-md-2">
                    <div class="footer-widget">
                        <div class="footer-widget-title">Negócios</div>
                        <div class="footer-widget-content">
                            <div class="footer-list footer-box fl-wrap">
                                <ul>
                                    <li> <a href="{{ route('suppliers.index') }}">Fornecedores</a></li>
                                    <li> <a href="{{ route('directory.index') }}">Directório</a></li>
                                    <li> <a href="{{ route('news.index', ['cat' => 'oportunidades']) }}">Oportunidades</a></li>
                                    <li> <a href="{{ route('registo-empresa') }}">Registar Empresa</a></li>
                                    <li> <a href="{{ route('registo-profissional') }}">Registar Profissional</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- footer-widget  end-->
                <!-- footer-widget -->
                <div class="col-md-3">
                    <div class="footer-widget">
                        <div class="footer-widget-title">Contacto</div>
                        <div class="footer-widget-content">
                            <div class="footer-list footer-box fl-wrap">
                                <ul>
                                    {{-- Dados configuráveis no admin > Configurações > Contactos --}}
                                    @php
                                        $definicoes = \App\Models\Setting::todas();
                                        $enderecoRodape = trim((string) ($definicoes['endereco'] ?? '')) ?: 'Luanda, Angola';
                                        $emailRodape = trim((string) ($definicoes['email'] ?? '')) ?: 'geral@pigea.ao';
                                        $telefoneRodape = trim((string) ($definicoes['telefone'] ?? '')) ?: '+244 000 000 000';
                                    @endphp
                                    <li> <a href="{{ route('contacts') }}"><i class="fas fa-map-marker-alt"></i> {{ $enderecoRodape }}</a></li>
                                    <li> <a href="mailto:{{ $emailRodape }}"><i class="fas fa-envelope"></i> {{ $emailRodape }}</a></li>
                                    <li> <a href="tel:{{ preg_replace('/\s+/', '', $telefoneRodape) }}"><i class="fas fa-phone"></i> {{ $telefoneRodape }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- footer-widget  end-->
            </div>
        </div>
        <!-- footer-widget-wrap end-->
    </div>
    <div class="footer-bottom fl-wrap">
        <div class="container">
            <div class="copyright"><span>&#169; 2026 PIGEA</span> — Todos os direitos reservados. | Desenvolvido por <a href="https://qnbangola.ao/" target="_blank" rel="noopener">QNB Angola</a></div>
            <div class="to-top"> <i class="fas fa-caret-up"></i></div>
            <div class="subfooter-nav">
                <ul>
                    <li><a href="#">Termos &amp; Condições</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
