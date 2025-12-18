<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Panel Auditor') | CrowdUp</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-zinc-950 text-zinc-100 font-sans min-h-screen">
    <div class="relative isolate overflow-hidden bg-zinc-950">
        <div class="absolute -left-24 top-0 h-72 w-72 rounded-full bg-purple-700/30 blur-2xl"></div>
        <div class="absolute right-0 top-24 h-72 w-72 rounded-full bg-fuchsia-600/25 blur-2xl"></div>
    </div>

    @php
        $backUrl = trim($__env->yieldContent('back_url', route('auditor.dashboard')));
        $backLabel = trim($__env->yieldContent('back_label', 'Volver al panel'));
    @endphp
    <header class="sticky top-0 z-30 border-b border-white/10 bg-zinc-950/80 backdrop-blur-xl">
        <div class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="/images/brand/mark.png" alt="CrowdUp" class="h-8 w-8" />
                    <span class="text-xl font-extrabold tracking-tight">Crowd<span class="text-purple-300">Up</span> Audit</span>
                </a>
                @if ($backUrl)
                    <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm text-zinc-300 hover:text-white">
                        <span aria-hidden="true">&larr;</span> {{ $backLabel }}
                    </a>
                @endif
                
            </div>
            <div class="flex items-center gap-3 text-xs leading-tight">
                <span class="font-semibold text-white">{{ Auth::user()->nombre_completo ?? Auth::user()->name }}</span>
                <span class="text-zinc-400 uppercase tracking-wide">AUDITOR</span>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-semibold text-white hover:border-indigo-400/60 hover:bg-white/10">
                        Salir
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-full px-0 pt-0 pb-6 space-y-8">
        <div class="gap-0 lg:grid lg:grid-cols-[280px_1fr] lg:min-h-[calc(100vh-64px)] lg:overflow-hidden admin-shell">
            <aside class="lg:sticky lg:top-0 admin-sidebar">
                @include('auditor.partials.modules', ['active' => trim($__env->yieldContent('active')) ?: 'general'])
            </aside>

            <div class="admin-sidebar-backdrop" data-admin-toggle></div>

            <div class="space-y-8 lg:overflow-y-auto lg:h-full lg:pr-2 admin-scroll admin-main">
                <section class="admin-auditor-content">
                    @yield('content')
                </section>
            </div>
        </div>
    </main>

    <!-- Chat n8n unificado para panel auditor -->
    <div id="n8n-chat"></div>
    <link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />
    <style>
        :root{
            --chat--color--primary:#4f46e5;
            --chat--color--primary-shade-50:#4338ca;
            --chat--color--primary--shade-100:#3730a3;
            --chat--color--secondary:#22c55e;
            --chat--color-secondary-shade-50:#16a34a;
            --chat--color-dark:#0b1220;
            --chat--color-light:#0f172a;
            --chat--color-light-shade-50:#111827;
            --chat--color-light-shade-100:#1f2937;
            --chat--color-disabled:#64748b;
            --chat--color-typing:#94a3b8;
            --chat--body--background:#0b1220;
            --chat--footer--background:#0b1220;
            --chat--footer--color:#e2e8f0;
            --chat--header--background:linear-gradient(135deg,#4f46e5,#22c55e);
            --chat--header--color:#f8fafc;
            --chat--message--bot--background:#111827;
            --chat--message--bot--color:#e5e7eb;
            --chat--message--user--background:#22c55e;
            --chat--message--user--color:#0b1220;
            --chat--message--pre--background:#0b1220;
            --chat--window--border:1px solid #1f2937;
            --chat--window--border-radius:18px;
            --chat--window--width:420px;
            --chat--window--height:620px;
            --chat--toggle--background:#4f46e5;
            --chat--toggle--hover--background:#4338ca;
            --chat--toggle--active--background:#3730a3;
            --chat--toggle--color:#f8fafc;
            --chat--button--background:#4f46e5;
            --chat--button--hover--background:#4338ca;
            --chat--button--color:#e2e8f0;
            --chat--input--background:#0f172a;
            --chat--input--text-color:#e2e8f0;
            --chat--color-white:#f8fafc;
        }
    </style>
    <script type="module">
        import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

        createChat({
            webhookUrl: 'https://crowfunding.app.n8n.cloud/webhook/ec74f167-1a5d-4b2b-b8f8-f28aa730c9d3/chat',
            webhookConfig: { method: 'POST', headers: {} },
            target: '#n8n-chat',
            mode: 'window',
            chatInputKey: 'chatInput',
            chatSessionKey: 'sessionId',
            loadPreviousSession: true,
            metadata: {},
            showWelcomeScreen: false,
            defaultLanguage: 'en',
            initialMessages: [
                'Hola, bienvenido a CrowdUp',
                '¿Cómo te puedo ayudar hoy?'
            ],
            i18n: {
                en: {
                    title: 'Asistente Guia',
                    subtitle: "Disponible para ayudarte 24/7",
                    footer: '',
                    getStarted: 'Nueva conversacion',
                    inputPlaceholder: 'Escribe tu pregunta',
                },
            },
            enableStreaming: false,
        });
    </script>

    @stack('scripts')
</body>
</html>
