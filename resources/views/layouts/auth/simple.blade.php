<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="relative h-screen h-dvh w-screen overflow-hidden bg-zinc-50 text-zinc-900 antialiased selection:bg-amber-500/30 selection:text-amber-300 dark:bg-zinc-950 dark:text-zinc-100">
        {{-- Floating Top-Left Back Button --}}
        <a href="{{ route('home') }}" class="group absolute top-4 left-4 z-30 inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/80 bg-white/70 px-3 py-1.5 text-xs font-medium text-zinc-600 shadow-sm backdrop-blur-md transition-all hover:border-zinc-300 hover:bg-white hover:text-zinc-900 dark:border-zinc-800/80 dark:bg-zinc-900/60 dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:bg-zinc-900 dark:hover:text-white sm:top-6 sm:left-6" wire:navigate>
            <svg class="size-3.5 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back</span>
        </a>

        {{-- Ambient Cybernetic Glow Gradients (Fixed in Background) --}}
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            {{-- Top Amber Glow (LifeOS Primary Core) --}}
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 h-[420px] w-[600px] rounded-full bg-linear-to-b from-amber-500/15 via-amber-600/5 to-transparent blur-3xl dark:from-amber-500/20 dark:via-amber-600/8"></div>
            {{-- Bottom Emerald Subtle Glow --}}
            <div class="absolute -bottom-32 right-1/4 h-[350px] w-[450px] rounded-full bg-emerald-500/10 blur-3xl dark:bg-emerald-500/8"></div>
            {{-- Micro Grid Pattern --}}
            <div class="absolute inset-0 bg-[radial-gradient(#d4d4d8_1px,transparent_1px)] dark:bg-[radial-gradient(#27272a_1px,transparent_1px)] [background-size:24px_24px] opacity-40"></div>
        </div>

        {{-- Main Container - Strictly Centered, Zero Page Scroll --}}
        <main class="relative z-10 flex h-full w-full flex-col items-center justify-center p-3 sm:p-4">
            <div class="relative w-full max-w-[420px]">
                {{-- Card Ambient Glow --}}
                <div class="absolute -inset-1 rounded-3xl bg-linear-to-b from-amber-500/25 via-zinc-800/20 to-emerald-500/15 blur-lg opacity-70"></div>

                {{-- Glassmorphic Executive Card --}}
                <div class="relative rounded-2xl border border-zinc-200/90 bg-white/95 p-5 shadow-2xl shadow-zinc-300/40 backdrop-blur-xl dark:border-zinc-800/80 dark:bg-zinc-900/90 dark:shadow-black/70 sm:rounded-3xl sm:p-6">
                    {{-- Compact Branded Header --}}
                    <div class="mb-3 sm:mb-4 flex flex-col items-center text-center">
                        <a href="{{ route('home') }}" class="group flex items-center justify-center gap-2 transition-transform duration-200 hover:scale-105" wire:navigate>
                            <div class="relative flex size-10 items-center justify-center rounded-xl border border-amber-500/30 bg-gradient-to-br from-amber-400/20 to-amber-500/10 shadow-md shadow-amber-500/10 backdrop-blur-md">
                                <x-app-logo-icon class="size-6 drop-shadow-sm" />
                                <span class="absolute -bottom-0.5 -right-0.5 flex size-2.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex size-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-zinc-900"></span>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-base font-black tracking-tight text-zinc-900 dark:text-white">Life<span class="text-amber-500 dark:text-amber-400">OS</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- Dynamic Slot (Login, Register, etc.) --}}
                    <div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>