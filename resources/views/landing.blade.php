<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
    <head>
        @include('partials.head', ['title' => 'LifeOS — The Visual Autonomous Command Center'])
    </head>
    <body class="min-h-screen bg-zinc-950 font-sans text-zinc-100 antialiased selection:bg-amber-400 selection:text-zinc-950">
        {{-- Subtle Grid & Radial Glow --}}
        <div class="pointer-events-none fixed inset-0 -z-10 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.15),rgba(255,255,255,0))]"></div>
        <div class="pointer-events-none fixed inset-0 -z-10 bg-[linear-gradient(to_right,#1f293708_1px,transparent_1px),linear-gradient(to_bottom,#1f293708_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>

        {{-- Sticky Glass Nav --}}
        <header class="sticky top-0 z-50 border-b border-zinc-800/80 bg-zinc-950/80 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3.5">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-amber-400 font-black text-zinc-950 shadow-md shadow-amber-400/20">⚡</span>
                    <span class="font-black tracking-tight text-white">LifeOS</span>
                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 font-mono text-[10px] text-zinc-400">v2.4</span>
                </a>

                <div class="hidden items-center gap-6 text-xs font-semibold text-zinc-400 md:flex">
                    <a href="#system" class="hover:text-white transition">System Matrix</a>
                    <a href="#comparison" class="hover:text-white transition">Comparison Infographic</a>
                    <a href="#automation" class="hover:text-white transition">Automation Engine</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-400 px-4 py-2 text-xs font-bold text-zinc-950 hover:bg-amber-300 transition">
                            <span>Open Dashboard</span>
                            <span>→</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-zinc-800 bg-zinc-900 px-3.5 py-2 text-xs font-semibold text-zinc-300 hover:text-white transition">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl bg-amber-400 px-4 py-2 text-xs font-bold text-zinc-950 hover:bg-amber-300 transition">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero Infographic Header --}}
        <section class="px-6 pt-16 pb-12 text-center lg:pt-24 lg:pb-16">
            <div class="mx-auto max-w-5xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-xs font-medium text-emerald-400">
                    <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Self-Hosted • Zero Monthly Subscriptions • Private SQLite/MySQL</span>
                </div>

                <h1 class="mt-6 text-4xl font-black tracking-tight text-white sm:text-6xl lg:text-7xl">
                    Command Your Life with <br />
                    <span class="bg-gradient-to-r from-amber-400 via-orange-400 to-indigo-400 bg-clip-text text-transparent">
                        Visual Intelligence.
                    </span>
                </h1>

                <p class="mx-auto mt-4 max-w-2xl text-sm text-zinc-400 sm:text-base">
                    A streamlined, high-speed single dashboard that replaces 7 fragmented productivity tools with an automated Telegram briefing companion.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3.5">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-xl bg-amber-400 px-6 py-3 text-xs font-bold text-zinc-950 hover:bg-amber-300 transition shadow-lg shadow-amber-500/20">
                            Launch Dashboard →
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl bg-amber-400 px-6 py-3 text-xs font-bold text-zinc-950 hover:bg-amber-300 transition shadow-lg shadow-amber-500/20">
                            Get Started Free →
                        </a>
                    @endauth
                    <a href="#comparison" class="rounded-xl border border-zinc-800 bg-zinc-900/90 px-5 py-3 text-xs font-semibold text-zinc-300 hover:border-zinc-700 transition">
                        View System Comparison ↓
                    </a>
                </div>
            </div>
        </section>

        {{-- Infographic 1: System Ecosystem Matrix (7 Core Modules) --}}
        <section id="system" class="px-6 py-12">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row border-b border-zinc-800/80 pb-5">
                    <div>
                        <span class="font-mono text-xs font-bold uppercase tracking-widest text-amber-400">Architecture Matrix</span>
                        <h2 class="text-2xl font-black text-white sm:text-3xl">7 Unified Engine Modules</h2>
                    </div>
                    <span class="rounded-full bg-zinc-900 px-3 py-1 font-mono text-xs text-zinc-400 border border-zinc-800">
                        ⚡ Real-time Laravel 13 & Livewire v4
                    </span>
                </div>

                {{-- Interactive Infographic Cards Grid --}}
                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Card 1 --}}
                    <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 p-5 transition hover:border-indigo-500/50 hover:bg-zinc-900/80">
                        <div class="flex items-center justify-between">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-500/15 text-xl">🎯</span>
                            <span class="rounded bg-indigo-500/10 px-2 py-0.5 font-mono text-[10px] font-bold text-indigo-400">01. GOALS</span>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-white">Target Tracking</h3>
                        <div class="mt-3 space-y-1.5 text-xs text-zinc-400">
                            <p class="flex items-center gap-2"><span>📊</span> Auto-calculating milestone %</p>
                            <p class="flex items-center gap-2"><span>⏳</span> Target deadline countdowns</p>
                        </div>
                    </div>

                    {{-- Card 2 --}}
                    <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 p-5 transition hover:border-sky-500/50 hover:bg-zinc-900/80">
                        <div class="flex items-center justify-between">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-sky-500/15 text-xl">📅</span>
                            <span class="rounded bg-sky-500/10 px-2 py-0.5 font-mono text-[10px] font-bold text-sky-400">02. MEETINGS</span>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-white">Smart Schedule</h3>
                        <div class="mt-3 space-y-1.5 text-xs text-zinc-400">
                            <p class="flex items-center gap-2"><span>⏱</span> Per-minute reminder cron</p>
                            <p class="flex items-center gap-2"><span>📍</span> Direct location / meeting links</p>
                        </div>
                    </div>

                    {{-- Card 3 --}}
                    <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 p-5 transition hover:border-emerald-500/50 hover:bg-zinc-900/80">
                        <div class="flex items-center justify-between">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-500/15 text-xl">💰</span>
                            <span class="rounded bg-emerald-500/10 px-2 py-0.5 font-mono text-[10px] font-bold text-emerald-400">03. MONEY</span>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-white">Multi-Wallet & Budget</h3>
                        <div class="mt-3 space-y-1.5 text-xs text-zinc-400">
                            <p class="flex items-center gap-2"><span>🏦</span> Dynamic multi-wallet balances</p>
                            <p class="flex items-center gap-2"><span>🚨</span> 80% & 100% budget alerts</p>
                        </div>
                    </div>

                    {{-- Card 4 --}}
                    <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 p-5 transition hover:border-rose-500/50 hover:bg-zinc-900/80">
                        <div class="flex items-center justify-between">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-rose-500/15 text-xl">🔥</span>
                            <span class="rounded bg-rose-500/10 px-2 py-0.5 font-mono text-[10px] font-bold text-rose-400">04. TASKS</span>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-white">Tasks & Habit Streaks</h3>
                        <div class="mt-3 space-y-1.5 text-xs text-zinc-400">
                            <p class="flex items-center gap-2"><span>⚡</span> Urgent/High priority matrix</p>
                            <p class="flex items-center gap-2"><span>🔥</span> Unbroken daily streak engine</p>
                        </div>
                    </div>

                    {{-- Card 5 --}}
                    <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 p-5 transition hover:border-amber-500/50 hover:bg-zinc-900/80">
                        <div class="flex items-center justify-between">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-amber-500/15 text-xl">💳</span>
                            <span class="rounded bg-amber-500/10 px-2 py-0.5 font-mono text-[10px] font-bold text-amber-400">05. VAULT</span>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-white">Subscription Vault</h3>
                        <div class="mt-3 space-y-1.5 text-xs text-zinc-400">
                            <p class="flex items-center gap-2"><span>💸</span> Recurring SaaS burn monitor</p>
                            <p class="flex items-center gap-2"><span>🔔</span> 3-day renewal warning pings</p>
                        </div>
                    </div>

                    {{-- Card 6 --}}
                    <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 p-5 transition hover:border-purple-500/50 hover:bg-zinc-900/80">
                        <div class="flex items-center justify-between">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-purple-500/15 text-xl">🧠</span>
                            <span class="rounded bg-purple-500/10 px-2 py-0.5 font-mono text-[10px] font-bold text-purple-400">06. REVIEWS</span>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-white">Weekly Review Wizard</h3>
                        <div class="mt-3 space-y-1.5 text-xs text-zinc-400">
                            <p class="flex items-center gap-2"><span>✍️</span> Sunday reflection synthesis</p>
                            <p class="flex items-center gap-2"><span>💡</span> 1-click Brain Dump to Task</p>
                        </div>
                    </div>

                    {{-- Card 7 (Wide Featured) --}}
                    <div id="automation" class="scroll-mt-24 sm:col-span-2 rounded-2xl border border-amber-500/30 bg-gradient-to-r from-amber-500/15 via-zinc-900 to-zinc-900 p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-amber-400/20 text-xl">🤖</span>
                                <span class="rounded bg-amber-400/20 px-2 py-0.5 font-mono text-[10px] font-bold text-amber-300">07. AUTOMATION HUB</span>
                            </div>
                            <h3 class="mt-3 text-base font-bold text-white">Telegram Morning Briefing Companion</h3>
                            <p class="mt-2 text-xs text-zinc-300 leading-relaxed">
                                Sends an executive pulse to your phone daily at 07:30 AM: today's meetings, top 3 urgent tasks, unlogged habits, and real-time net wallet balances.
                            </p>
                        </div>
                        <div class="mt-4 flex items-center justify-between pt-2 border-t border-zinc-800/80 text-[11px] font-mono">
                            <span class="text-zinc-400">Bot: <span class="text-amber-300">@nizar_lifeos_bot</span></span>
                            <span class="text-emerald-400">● 100% Automated</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Infographic 2: High-Impact Visual Comparison (Pros & Cons) --}}
        <section id="comparison" class="border-t border-zinc-800/80 bg-zinc-900/30 px-6 py-16">
            <div class="mx-auto max-w-6xl">
                <div class="text-center">
                    <span class="font-mono text-xs font-bold uppercase tracking-widest text-indigo-400">Trade-Off Analysis</span>
                    <h2 class="mt-2 text-2xl font-black text-white sm:text-4xl">Why LifeOS vs Traditional SaaS App Stacks?</h2>
                    <p class="mt-2 text-xs text-zinc-400 sm:text-sm">An honest, concise comparison between fragmented tools and a unified personal OS.</p>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- Column 1: WITH LifeOS (The Advantage) --}}
                    <div class="relative overflow-hidden rounded-3xl border border-emerald-500/40 bg-zinc-950 p-6 sm:p-8 shadow-xl shadow-emerald-950/20">
                        <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/20 font-bold text-emerald-400">✓</span>
                                <div>
                                    <h3 class="text-lg font-bold text-white">WITH LifeOS</h3>
                                    <p class="text-[11px] text-emerald-400">Unified Command Center</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-emerald-500/10 px-3 py-1 font-mono text-xs font-bold text-emerald-400">PROS</span>
                        </div>

                        {{-- Pros Visual Points --}}
                        <div class="mt-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-emerald-400 font-bold">✓</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">Zero App Context Switching</p>
                                    <p class="text-[11px] text-zinc-400">Tasks, money, calendar & habits live on one cohesive screen.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-emerald-400 font-bold">✓</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">100% Data Privacy & Self-Hosted</p>
                                    <p class="text-[11px] text-zinc-400">Runs locally on Laragon/Docker/Home Server. No third-party data tracking.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-emerald-400 font-bold">✓</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">07:30 AM Push Automation</p>
                                    <p class="text-[11px] text-zinc-400">Your day finds you on Telegram without needing to open the app first.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-emerald-400 font-bold">✓</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">Save RM 200+/month on SaaS</p>
                                    <p class="text-[11px] text-zinc-400">Replaces Notion, Todoist, Habitica, YNAB, and Calendly subscriptions.</p>
                                </div>
                            </div>
                        </div>

                        {{-- LifeOS Minor Trade-off (Transparency) --}}
                        <div class="mt-6 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4">
                            <p class="text-[11px] font-bold text-amber-400">⚠️ Trade-off to Consider:</p>
                            <p class="mt-1 text-[11px] text-zinc-400">
                                Requires basic local setup (PHP 8.4 & MySQL/Docker) or leaving Laragon/scheduler active for cron alerts.
                            </p>
                        </div>
                    </div>

                    {{-- Column 2: WITHOUT LifeOS (The Fragmented Mess) --}}
                    <div class="relative overflow-hidden rounded-3xl border border-rose-500/30 bg-zinc-950 p-6 sm:p-8 shadow-xl shadow-rose-950/10">
                        <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-xl bg-rose-500/20 font-bold text-rose-400">✕</span>
                                <div>
                                    <h3 class="text-lg font-bold text-white">WITHOUT LifeOS</h3>
                                    <p class="text-[11px] text-rose-400">Multiple Fragmented Apps</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-rose-500/10 px-3 py-1 font-mono text-xs font-bold text-rose-400">CONS</span>
                        </div>

                        {{-- Cons Visual Points --}}
                        <div class="mt-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-rose-400 font-bold">✕</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">Painful Tool Fragmentation</p>
                                    <p class="text-[11px] text-zinc-400">Checking 5 different apps every morning causes cognitive fatigue.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-rose-400 font-bold">✕</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">SaaS Subscription Drain</p>
                                    <p class="text-[11px] text-zinc-400">Paying $8-$15 monthly per service accumulates thousands every year.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-rose-400 font-bold">✕</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">Isolated Data Silos</p>
                                    <p class="text-[11px] text-zinc-400">Your task app doesn't know about your budget or calendar meetings.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-rose-400 font-bold">✕</span>
                                <div>
                                    <p class="text-xs font-bold text-zinc-200">Cloud Privacy Risks</p>
                                    <p class="text-[11px] text-zinc-400">Your intimate habits and financial data stored on proprietary corporate servers.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-zinc-800 bg-zinc-900/60 p-4">
                            <p class="text-[11px] font-bold text-emerald-400">💡 Only Advantage of SaaS:</p>
                            <p class="mt-1 text-[11px] text-zinc-400">
                                Zero server maintenance—hosted on company infrastructure at the expense of privacy and monthly fees.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Infographic 3: 3-Step Daily Automation Flowchart --}}
        <section class="px-6 py-16">
            <div class="mx-auto max-w-5xl text-center">
                <span class="font-mono text-xs font-bold uppercase tracking-widest text-amber-400">How It Operates</span>
                <h2 class="mt-2 text-2xl font-black text-white sm:text-3xl">The Automated 24-Hour Loop</h2>

                <div class="mt-10 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-6 text-left">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-2xl font-black text-amber-400">01</span>
                            <span class="text-xs">🌅</span>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-white">07:30 AM Morning Pulse</h4>
                        <p class="mt-1 text-xs text-zinc-400">Telegram Bot aggregates meetings, top 3 tasks, and wallet balance.</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-6 text-left">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-2xl font-black text-emerald-400">02</span>
                            <span class="text-xs">⚡</span>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-white">Instant Reactive Actions</h4>
                        <p class="mt-1 text-xs text-zinc-400">1-click habit check-ins and live expense logs without full-page reloads.</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/60 p-6 text-left">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-2xl font-black text-purple-400">03</span>
                            <span class="text-xs">🌙</span>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-white">Evening Reviews & Alerts</h4>
                        <p class="mt-1 text-xs text-zinc-400">Threshold alerts warn on 80% budget limit and Sunday review wizard activates.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Bottom CTA --}}
        <section class="border-t border-zinc-800/80 bg-zinc-950 px-6 py-16 text-center">
            <div class="mx-auto max-w-2xl">
                <h2 class="text-3xl font-black text-white sm:text-4xl">Ready to take control?</h2>
                <p class="mt-3 text-xs text-zinc-400 sm:text-sm">Experience the speed of an integrated, private operating system.</p>
                <div class="mt-6 flex justify-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-xl bg-amber-400 px-6 py-3 text-xs font-bold text-zinc-950 hover:bg-amber-300 transition shadow-lg shadow-amber-500/20">
                            Launch Your Dashboard →
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl bg-amber-400 px-6 py-3 text-xs font-bold text-zinc-950 hover:bg-amber-300 transition shadow-lg shadow-amber-500/20">
                            Access LifeOS Now →
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        {{-- Compact Footer --}}
        <footer class="border-t border-zinc-900 bg-zinc-950 px-6 py-8 text-center text-xs text-zinc-600">
            <p>© {{ date('Y') }} LifeOS. Autonomous Personal Operating System.</p>
        </footer>
    </body>
</html>
