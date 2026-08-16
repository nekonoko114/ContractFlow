<!DOCTYPE html>
<html lang="ja" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? '携帯電話回線・乗り換え管理' }} - ContractFlow</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            background-color: #faf8ff;
            color: #131b2e;
            -webkit-font-smoothing: antialiased;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1;
        }
        .ambient-shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #faf8ff;
        }
        ::-webkit-scrollbar-thumb {
            background: #c7c4d8;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #777587;
        }
    </style>
</head>
<body x-data="{ 
        sidebarExpanded: JSON.parse(localStorage.getItem('cf_sidebar_expanded') ?? 'true'),
        mobileOpen: false 
      }" 
      x-init="$watch('sidebarExpanded', val => localStorage.setItem('cf_sidebar_expanded', JSON.stringify(val)))"
      class="min-h-screen relative bg-[#faf8ff] text-[#131b2e] font-sans antialiased flex">

    <!-- Mobile Header (md:hidden) -->
    <header class="fixed top-0 left-0 w-full z-40 flex justify-between items-center px-4 h-16 bg-white/90 backdrop-blur-md border-b border-[#c7c4d8]/40 shadow-xs md:hidden">
        <div class="flex items-center gap-3">
            <button @click="mobileOpen = true" class="p-2 rounded-xl text-[#3525cd] hover:bg-[#eaedff] transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#3525cd] to-[#4f46e5] flex items-center justify-center text-white shadow-xs">
                    <span class="material-symbols-outlined text-lg">phonelink_setup</span>
                </div>
                <span class="text-base font-black text-[#3525cd] tracking-tight">ContractFlow</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('lines.create') }}" class="p-2 text-[#3525cd] hover:bg-[#eaedff] rounded-xl transition-colors" title="新規登録">
                <span class="material-symbols-outlined text-2xl">add_circle</span>
            </a>
            <div class="w-8 h-8 rounded-full bg-[#dae2fd] text-[#3525cd] font-bold text-xs flex items-center justify-center border border-[#c3c0ff]">
                {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
        </div>
    </header>

    <!-- Mobile Drawer Backdrop Overlay -->
    <div x-show="mobileOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileOpen = false"
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs md:hidden"
         style="display: none;"></div>

    <!-- Mobile Drawer Sidebar (md:hidden) -->
    <aside x-show="mobileOpen" 
           x-transition:enter="transition ease-in-out duration-300 transform"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in-out duration-300 transform"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed top-0 left-0 bottom-0 z-50 w-72 bg-white flex flex-col pt-6 pb-6 shadow-2xl md:hidden"
           style="display: none;">
        
        <div class="px-6 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#3525cd] to-[#4f46e5] flex items-center justify-center text-white shadow-md">
                    <span class="material-symbols-outlined text-2xl">phonelink_setup</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-[#3525cd] tracking-tight">ContractFlow</h2>
                    <p class="text-xs text-[#505f76] font-medium">回線・MNP管理 SaaS</p>
                </div>
            </div>
            <button @click="mobileOpen = false" class="p-1.5 rounded-full text-[#777587] hover:bg-[#f2f3ff] transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <div class="px-6 mb-6">
            <div class="bg-[#f2f3ff] p-3 rounded-2xl border border-[#dae2fd] flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#4f46e5] text-white font-bold text-sm flex items-center justify-center shadow-inner">
                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="overflow-hidden flex-1">
                    <p class="text-sm font-semibold text-[#131b2e] truncate">{{ auth()->user()->name ?? 'ユーザー' }}</p>
                    <p class="text-xs text-[#505f76] truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </div>
        </div>

        <div class="flex-1 px-4 flex flex-col gap-1.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" 
               @click="mobileOpen = false"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#d0e1fb]/70 text-[#0b1c30] font-semibold' : 'text-[#505f76] hover:bg-[#eaedff] hover:text-[#131b2e]' }}">
                <span class="material-symbols-outlined text-xl {{ request()->routeIs('dashboard') ? 'text-[#3525cd]' : 'text-[#505f76]' }}">dashboard</span>
                <span>ダッシュボード</span>
            </a>

            <a href="{{ route('lines.create') }}" 
               @click="mobileOpen = false"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('lines.create') ? 'bg-[#d0e1fb]/70 text-[#0b1c30] font-semibold' : 'text-[#505f76] hover:bg-[#eaedff] hover:text-[#131b2e]' }}">
                <span class="material-symbols-outlined text-xl {{ request()->routeIs('lines.create') ? 'text-[#3525cd]' : 'text-[#505f76]' }}">compare_arrows</span>
                <span>回線追加・乗り換え比較</span>
            </a>

            <a href="{{ route('history') }}" 
               @click="mobileOpen = false"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('history') ? 'bg-[#d0e1fb]/70 text-[#0b1c30] font-semibold' : 'text-[#505f76] hover:bg-[#eaedff] hover:text-[#131b2e]' }}">
                <span class="material-symbols-outlined text-xl {{ request()->routeIs('history') ? 'text-[#3525cd]' : 'text-[#505f76]' }}">history</span>
                <span>乗り換え履歴</span>
            </a>
        </div>

        <div class="px-4 pt-4 border-t border-[#c7c4d8]/40">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-[#ba1a1a] hover:bg-[#ffdad6]/40 rounded-xl transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-xl">logout</span>
                    <span>ログアウト</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Desktop Collapsible SideNavBar (md:flex) -->
    <nav :class="sidebarExpanded ? 'w-64' : 'w-20'" 
         class="fixed left-0 top-0 h-full hidden md:flex flex-col z-40 bg-white border-r border-[#c7c4d8]/50 pt-5 pb-5 shadow-xs transition-all duration-300 ease-in-out">
        
        <!-- Header & Toggle Button -->
        <div class="px-4 mb-6">
            <div class="flex items-center justify-between">
                <!-- Brand (Expanded) -->
                <div x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="flex items-center gap-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#3525cd] to-[#4f46e5] flex items-center justify-center text-white shadow-md shrink-0">
                        <span class="material-symbols-outlined text-2xl">phonelink_setup</span>
                    </div>
                    <div class="overflow-hidden">
                        <h1 class="text-base font-black text-[#3525cd] tracking-tight truncate">ContractFlow</h1>
                        <p class="text-[11px] text-[#505f76] font-medium truncate">回線・MNP管理</p>
                    </div>
                </div>

                <!-- Brand Icon (Collapsed) -->
                <div x-show="!sidebarExpanded" x-transition.opacity.duration.200ms class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#3525cd] to-[#4f46e5] flex items-center justify-center text-white shadow-md mx-auto">
                    <span class="material-symbols-outlined text-2xl">phonelink_setup</span>
                </div>

                <!-- Collapse / Expand Toggle Button -->
                <button @click="sidebarExpanded = !sidebarExpanded" 
                        :title="sidebarExpanded ? 'サイドバーを折りたたむ' : 'サイドバーを展開する'"
                        class="p-2 rounded-xl text-[#505f76] hover:text-[#3525cd] hover:bg-[#eaedff] transition-all cursor-pointer shrink-0"
                        :class="!sidebarExpanded ? 'mt-3 mx-auto flex justify-center' : ''">
                    <span class="material-symbols-outlined text-xl" 
                          x-text="sidebarExpanded ? 'keyboard_double_arrow_left' : 'keyboard_double_arrow_right'">
                    </span>
                </button>
            </div>
            
            <!-- User Profile (Expanded) -->
            <div x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="mt-5 bg-[#f2f3ff] p-3 rounded-2xl border border-[#dae2fd] flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#4f46e5] text-white font-bold text-sm flex items-center justify-center shadow-inner shrink-0">
                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="overflow-hidden flex-1">
                    <p class="text-xs font-bold text-[#131b2e] truncate">{{ auth()->user()->name ?? 'ユーザー' }}</p>
                    <p class="text-[11px] text-[#505f76] truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </div>

            <!-- User Profile Icon (Collapsed) -->
            <div x-show="!sidebarExpanded" x-transition.opacity.duration.200ms class="mt-4 flex justify-center">
                <div class="w-9 h-9 rounded-full bg-[#4f46e5] text-white font-bold text-sm flex items-center justify-center shadow-inner"
                     title="{{ auth()->user()->name ?? 'ユーザー' }} ({{ auth()->user()->email ?? '' }})">
                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 px-3 flex flex-col gap-1.5 overflow-y-auto">
            <!-- Dashboard Link -->
            <a href="{{ route('dashboard') }}" 
               :title="!sidebarExpanded ? 'ダッシュボード (回線一覧)' : ''"
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-medium transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-[#d0e1fb]/70 text-[#0b1c30] font-bold shadow-xs' : 'text-[#505f76] hover:bg-[#eaedff] hover:text-[#131b2e]' }}">
                <span class="material-symbols-outlined text-2xl shrink-0 {{ request()->routeIs('dashboard') ? 'text-[#3525cd]' : 'text-[#505f76] group-hover:text-[#3525cd]' }}">dashboard</span>
                <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="truncate">ダッシュボード</span>
            </a>

            <!-- Line Create / Compare Link -->
            <a href="{{ route('lines.create') }}" 
               :title="!sidebarExpanded ? '回線追加・乗り換え比較' : ''"
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-medium transition-all duration-200 group {{ request()->routeIs('lines.create') ? 'bg-[#d0e1fb]/70 text-[#0b1c30] font-bold shadow-xs' : 'text-[#505f76] hover:bg-[#eaedff] hover:text-[#131b2e]' }}">
                <span class="material-symbols-outlined text-2xl shrink-0 {{ request()->routeIs('lines.create') ? 'text-[#3525cd]' : 'text-[#505f76] group-hover:text-[#3525cd]' }}">compare_arrows</span>
                <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="truncate">回線追加・乗換比較</span>
            </a>

            <!-- History Link -->
            <a href="{{ route('history') }}" 
               :title="!sidebarExpanded ? '乗り換え履歴' : ''"
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-medium transition-all duration-200 group {{ request()->routeIs('history') ? 'bg-[#d0e1fb]/70 text-[#0b1c30] font-bold shadow-xs' : 'text-[#505f76] hover:bg-[#eaedff] hover:text-[#131b2e]' }}">
                <span class="material-symbols-outlined text-2xl shrink-0 {{ request()->routeIs('history') ? 'text-[#3525cd]' : 'text-[#505f76] group-hover:text-[#3525cd]' }}">history</span>
                <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="truncate">乗り換え履歴</span>
            </a>
        </div>

        <!-- Footer Actions -->
        <div class="px-3 pt-3 border-t border-[#c7c4d8]/40">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        :title="!sidebarExpanded ? 'ログアウト' : ''"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold text-[#ba1a1a] hover:bg-[#ffdad6]/40 rounded-2xl transition-colors cursor-pointer group">
                    <span class="material-symbols-outlined text-2xl shrink-0 text-[#ba1a1a]">logout</span>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="truncate">ログアウト</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content Wrapper (dynamically adjusted based on sidebar width) -->
    <div :class="sidebarExpanded ? 'md:pl-64' : 'md:pl-20'" 
         class="flex-1 flex flex-col min-h-screen w-full transition-all duration-300 ease-in-out">
        <!-- Main Content Area -->
        <main class="w-full max-w-[1600px] mx-auto px-4 sm:px-8 md:px-10 py-8 pt-20 md:pt-8 pb-24 md:pb-12 flex-1">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
