@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white/80 shadow-sm backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-slate-900">
                            {{ config('app.name') }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white transition-colors hover:bg-blue-700">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('filament.admin.auth.login') }}"
                                class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white transition-colors hover:bg-blue-700">
                                Entrar
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav> <!-- Hero Section -->
        <section class="px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="animate-fade-in-up text-center">
                    <h1
                        class="mb-6 text-4xl font-bold leading-tight text-slate-900 md:text-6xl">
                        Laravel Filament 4
                        <span
                            class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Starter
                            Kit</span>
                    </h1>
                    <p
                        class="mx-auto mb-8 max-w-3xl text-xl leading-relaxed text-slate-600 md:text-2xl">
                        Um kit completo e poderoso para iniciar sua aplicação
                        Laravel com Filament Admin Panel,
                        repleto de recursos pré-construídos e melhores práticas.
                    </p>
                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        @guest
                            <a href="{{ route('filament.admin.auth.login') }}"
                                class="btn-primary inline-flex items-center justify-center">
                                <x-phosphor-rocket-launch
                                    class="mr-2 size-6" />
                                Começar Agora
                            </a>
                        @else
                            <a href="{{ route('filament.admin.pages.dashboard') }}"
                                class="btn-primary inline-flex items-center justify-center">
                                <x-phosphor-house
                                    class="mr-2 size-6" />
                                Ir para Dashboard
                            </a>
                        @endguest
                        <a href="#features"
                            class="btn-secondary inline-flex items-center justify-center">
                            <x-phosphor-list
                                class="mr-2 size-6" />
                            Ver Recursos
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="features" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-slate-900 md:text-4xl">
                        Recursos Principais
                    </h2>
                    <p class="mx-auto max-w-2xl text-xl text-slate-600">
                        Tudo que você precisa para construir aplicações modernas e
                        robustas
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Authentication -->
                    <div
                        class="rounded-xl bg-slate-50 p-8 transition-shadow hover:shadow-lg">
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                            <x-phosphor-shield-check
                                class="size-6 text-blue-600" />
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-slate-900">
                            Autenticação Completa</h3>
                        <p class="text-slate-600">
                            Sistema de autenticação multi-níveis com LDAP,
                            email/senha, recuperação de senha e controle de acesso
                            baseado em funções.
                        </p>
                    </div>

                    <!-- Admin Panel -->
                    <div
                        class="rounded-xl bg-slate-50 p-8 transition-shadow hover:shadow-lg">
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100">
                            <x-phosphor-layout
                                class="size-6 text-purple-600" />
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-slate-900">Painel
                            Admin Filament</h3>
                        <p class="text-slate-600">
                            Interface administrativa moderna e responsiva com
                            dashboard personalizável, gestão de usuários e sistema
                            de permissões.
                        </p>
                    </div>

                    <!-- Developer Tools -->
                    <div
                        class="rounded-xl bg-slate-50 p-8 transition-shadow hover:shadow-lg">
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                            <x-phosphor-code
                                class="size-6 text-green-600" />
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-slate-900">
                            Ferramentas de Dev</h3>
                        <p class="text-slate-600">
                            Laravel Debugbar, PHPStan, PHP CS Fixer, testes
                            automatizados e ferramentas de monitoramento integradas.
                        </p>
                    </div>

                    <!-- Performance -->
                    <div
                        class="rounded-xl bg-slate-50 p-8 transition-shadow hover:shadow-lg">
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100">
                            <x-phosphor-lightning
                                class="size-6 text-orange-600" />
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-slate-900">Alta
                            Performance</h3>
                        <p class="text-slate-600">
                            Configurações otimizadas, cache inteligente, autoloader
                            configurado e recursos de segurança built-in.
                        </p>
                    </div>

                    <!-- Activity Logging -->
                    <div
                        class="rounded-xl bg-slate-50 p-8 transition-shadow hover:shadow-lg">
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
                            <x-phosphor-pulse
                                class="size-6 text-indigo-600" />
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-slate-900">Log de
                            Atividades</h3>
                        <p class="text-slate-600">
                            Sistema completo de auditoria e logging de atividades
                            para rastreamento de ações dos usuários.
                        </p>
                    </div>

                    <!-- Modern UI -->
                    <div
                        class="rounded-xl bg-slate-50 p-8 transition-shadow hover:shadow-lg">
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-pink-100">
                            <x-phosphor-palette
                                class="size-6 text-pink-600" />
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-slate-900">
                            Interface Moderna</h3>
                        <p class="text-slate-600">
                            Tailwind CSS, Phosphor Icons, design responsivo e
                            componentes Blade reutilizáveis para uma experiência
                            excepcional.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tech Stack -->
        <section class="bg-slate-50 px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-slate-900 md:text-4xl">
                        Stack Tecnológico
                    </h2>
                    <p class="text-xl text-slate-600">
                        Construído com as melhores tecnologias modernas
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-8 md:grid-cols-4 lg:grid-cols-6">
                    <div class="text-center">
                        <div
                            class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-lg bg-white shadow-sm">
                            <x-phosphor-code
                                class="size-6 text-red-500" />
                        </div>
                        <p class="font-semibold text-slate-900">PHP 8.2+</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-lg bg-white shadow-sm">
                            <x-phosphor-gear
                                class="size-6 text-red-600" />
                        </div>
                        <p class="font-semibold text-slate-900">Laravel 12</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-lg bg-white shadow-sm">
                            <x-phosphor-layout
                                class="size-6 text-orange-500" />
                        </div>
                        <p class="font-semibold text-slate-900">Filament 4</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-lg bg-white shadow-sm">
                            <x-phosphor-palette
                                class="size-6 text-blue-500" />
                        </div>
                        <p class="font-semibold text-slate-900">Tailwind CSS</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-lg bg-white shadow-sm">
                            <x-phosphor-database
                                class="size-6 text-green-600" />
                        </div>
                        <p class="font-semibold text-slate-900">MySQL/Postgres</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-lg bg-white shadow-sm">
                            <x-phosphor-cloud
                                class="size-6 text-red-500" />
                        </div>
                        <p class="font-semibold text-slate-900">Redis</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Start -->
        <section class="bg-white px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <h2 class="mb-8 text-3xl font-bold text-slate-900 md:text-4xl">
                    Pronto para começar?
                </h2>
                <p class="mb-8 text-xl text-slate-600">
                    Configure seu projeto em menos de 5 minutos com nossa
                    documentação completa
                </p>

                <div class="mb-8 rounded-lg bg-slate-900 p-6 text-left">
                    <div class="mb-4 flex items-center justify-between">
                        <span
                            class="font-mono text-sm text-slate-400">Terminal</span>
                        <button onclick="copyToClipboard()"
                            class="text-slate-400 transition-colors hover:text-white">
                            <x-phosphor-copy
                                class="size-6" />
                        </button>
                    </div>
                    <pre class="overflow-x-auto font-mono text-sm text-green-400"><code id="install-code">git clone [repository-url] meu-projeto
cd meu-projeto
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed</code></pre>
                </div>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-lg font-semibold text-white transition-colors hover:bg-blue-700">
                            <x-phosphor-sign-in
                                class="mr-2 size-6" />
                            Entrar no Sistema
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-lg font-semibold text-white transition-colors hover:bg-blue-700">
                            <x-phosphor-house
                                class="mr-2 size-6" />
                            Acessar Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 px-4 py-12 text-slate-300 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-8 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <h3 class="mb-4 text-lg font-bold text-white">
                            {{ config('app.name') }}</h3>
                        <p class="mb-4 text-slate-400">
                            Starter kit Laravel com Filament para acelerar o
                            desenvolvimento de suas aplicações.
                        </p>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold text-white">Recursos</h4>
                        <ul class="space-y-2">
                            <li><span class="text-slate-400">Documentação
                                    Completa</span></li>
                            <li><span class="text-slate-400">Guia de Deploy</span>
                            </li>
                            <li><span class="text-slate-400">API Development</span>
                            </li>
                            <li><span class="text-slate-400">Docker Support</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold text-white">Tecnologias</h4>
                        <ul class="space-y-2">
                            <li><span class="text-slate-400">Laravel 12</span></li>
                            <li><span class="text-slate-400">Filament 3</span></li>
                            <li><span class="text-slate-400">PHP 8.2+</span></li>
                            <li><span class="text-slate-400">Tailwind CSS</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 border-t border-slate-700 pt-8 text-center">
                    <p class="text-slate-400">
                        &copy; {{ date('Y') }} {{ config('app.name') }}.
                        Construído com Laravel e Filament.
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function copyToClipboard() {
            const code = document.getElementById('install-code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                // Opcional: mostrar feedback visual
                const button = event.target.closest('button');
                const icon = button.querySelector('i');
                icon.className = 'ph ph-check';
                setTimeout(() => {
                    icon.className = 'ph ph-copy';
                }, 2000);
            });
        }

        // Smooth scroll para anchors
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this
                    .getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
@endsection
