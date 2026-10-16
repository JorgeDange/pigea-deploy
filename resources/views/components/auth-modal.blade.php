<div class="main-register-container">
    <div class="reg-overlay close-reg-form"></div>
    <div class="main-register-holder">
        <div class="main-register-wrap fl-wrap">
            <div class="main-register_bg">
                <div class="bg-wrap">
                    <div class="bg par-elem "  data-bg="{{ asset('images/bg/1.jpg') }}"></div>
                    <div class="overlay"></div>
                </div>
                <div class="mg_logo"><img src="{{ asset('images/logo2.png') }}" alt="PIGEA"></div>
            </div>
            <div class="main-register tabs-act fl-wrap">
                <ul class="tabs-menu">
                    <li class="current"><a href="#tab-1"><i class="fal fa-sign-in-alt"></i> Entrar</a></li>
                    <li><a href="#tab-2"><i class="fal fa-user-plus"></i> Registar</a></li>
                </ul>
                <div class="close-modal close-reg-form"><i class="fal fa-times"></i></div>
                <!--tabs -->
                <div id="tabs-container">
                    <div class="tab">
                        <!--tab -->
                        <div id="tab-1" class="tab-content first-tab">
                            <div class="custom-form">
                                <form method="post" name="registerform" action="{{ route('login.store') }}">
                                    @csrf
                                    <label>Utilizador ou Email <span>*</span> </label>
                                    <input name="email" type="text" onClick="this.select()" value="{{ old('email') }}">
                                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                                    <label>Palavra-passe <span>*</span> </label>
                                    <input name="password" type="password" onClick="this.select()" value="">
                                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                                    <div class="filter-tags">
                                        <input id="check-a" type="checkbox" name="remember" value="1" checked>
                                        <label for="check-a">Manter sessão iniciada</label>
                                    </div>
                                    <div class="lost_password">
                                        <a href="{{ route('password.request') }}">Esqueceu a palavra-passe?</a>
                                    </div>
                                    <div class="clearfix"></div>
                                    <button type="submit" class="log-submit-btn color-bg"><span>Entrar</span></button>
                                </form>
                            </div>
                        </div>
                        <!--tab end -->
                        <!--tab -->
                        <div class="tab">
                            <div id="tab-2" class="tab-content">
                                <div class="custom-form">
                                    <form method="post" name="registerform" class="main-register-form" id="main-register-form2" action="{{ route('register') }}">
                                        @csrf
                                        <label>Nome Completo <span>*</span> </label>
                                        <input name="name" type="text" onClick="this.select()" value="{{ old('name') }}">
                                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                                        <label>Email <span>*</span></label>
                                        <input name="email" type="text" onClick="this.select()" value="{{ old('email') }}">
                                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                                        <label>Palavra-passe <span>*</span></label>
                                        <input name="password" type="password" onClick="this.select()" value="">
                                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                                        <button type="submit" class="log-submit-btn color-bg"><span>Registar</span></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!--tab end -->
                    </div>
                    <!--tabs end -->
                    <div class="log-separator fl-wrap"><span>ou</span></div>
                    <div class="soc-log  fl-wrap">
                        <p>Para entrar ou registar mais rápido, use a sua conta social.</p>
                        <a href="#"><i class="fab fa-facebook-f"></i>Continuar com Facebook</a>
                            <a href="{{ route('company.register') }}"><i class="fas fa-building"></i>Registar a sua empresa</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
