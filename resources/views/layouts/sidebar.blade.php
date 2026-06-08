<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MotoTracker Admin')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* ========================
           SIDEBAR
        ======================== */
        #sidebar {
            position: fixed;
            left: 0;
            top: 0;
            z-index: 50;
            width: 16rem;
            height: 100vh;
            background-color: #111111;
            border-right: 1px solid #1E2939;
            display: flex;
            flex-direction: column;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        /* Backdrop */
        #sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 40;
            background-color: rgba(0, 0, 0, 0.6);
        }

        /* Mobile: open state */
        body.sidebar-open #sidebar {
            transform: translateX(0);
        }
        body.sidebar-open #sidebar-backdrop {
            display: block;
        }

        /* ========================
           TOPBAR
        ======================== */
        #topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 40;
            background-color: #111111;
            border-bottom: 1px solid #1E2939;
            padding: 1rem;
        }

        /* ========================
           MAIN CONTENT
        ======================== */
        #page-content {
            margin-left: 0;
            padding-top: 73px; /* height of topbar */
            min-height: 100vh;
            background-color: #0a0a0a;
        }

        /* Mobile-only elements: hidden on desktop */
        .mobile-only {
            display: flex;
        }

        /* ========================
           DESKTOP (>= 1024px)
        ======================== */
        @media (min-width: 1024px) {
            /* Sidebar always visible on desktop */
            #sidebar {
                transform: translateX(0) !important;
            }
            #sidebar-backdrop {
                display: none !important;
            }
            /* Push topbar & content to the right */
            #topbar {
                left: 16rem;
            }
            #page-content {
                margin-left: 16rem;
            }
            /* Hide mobile-only elements on desktop */
            .mobile-only {
                display: none !important;
            }
        }

        /* Responsive padding adjustment */
        @media (min-width: 640px) {
            #topbar {
                padding: 1rem 2rem;
            }
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased">

    <!-- Sidebar Backdrop -->
    <div id="sidebar-backdrop" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-[#1E2939] flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">MotoTracker</h1>
                <p class="text-xs text-[#6A7282] mt-0.5 uppercase tracking-wider" style="font-family: Arial, sans-serif;">ADMIN PANEL</p>
            </div>
            <!-- Close button (mobile only) -->
            <button onclick="closeSidebar()" class="mobile-only text-gray-400 hover:text-white focus:outline-none" title="Close Sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 pt-4">
            <div class="flex flex-col gap-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 pl-3 h-10 rounded-[10px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#6B7C4F] text-white' : 'text-[#99A1AF] hover:bg-[#1E2939]' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span class="text-sm" style="font-family: Arial, sans-serif;">Dashboard Admin</span>
                </a>

                <a href="{{ route('admin.templates') }}" class="flex items-center gap-3 pl-3 h-10 rounded-[10px] transition-colors {{ request()->routeIs('admin.templates*') ? 'bg-[#6B7C4F] text-white' : 'text-[#99A1AF] hover:bg-[#1E2939]' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm" style="font-family: Arial, sans-serif;">Monitoring Tips Perawatan</span>
                </a>

                <a href="{{ route('admin.fuzzy.index') }}" class="flex items-center gap-3 pl-3 h-10 rounded-[10px] transition-colors {{ request()->routeIs('admin.fuzzy*') ? 'bg-[#6B7C4F] text-white' : 'text-[#99A1AF] hover:bg-[#1E2939]' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span class="text-sm" style="font-family: Arial, sans-serif;">Konfigurasi Fuzzy</span>
                </a>

                <a href="{{ route('admin.notifications') }}" class="flex items-center gap-3 pl-3 h-10 rounded-[10px] transition-colors {{ request()->routeIs('admin.notifications*') ? 'bg-[#6B7C4F] text-white' : 'text-[#99A1AF] hover:bg-[#1E2939]' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="text-sm" style="font-family: Arial, sans-serif;">Manajemen Notifikasi</span>
                </a>

                <a href="{{ route('admin.filters') }}" class="flex items-center gap-3 pl-3 h-10 rounded-[10px] transition-colors {{ request()->routeIs('admin.filters*') ? 'bg-[#6B7C4F] text-white' : 'text-[#99A1AF] hover:bg-[#1E2939]' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span class="text-sm" style="font-family: Arial, sans-serif;">Manajemen Filter</span>
                </a>

                <a href="{{ route('admin.content') }}" class="flex items-center gap-3 pl-3 h-10 rounded-[10px] transition-colors {{ request()->routeIs('admin.content*') ? 'bg-[#6B7C4F] text-white' : 'text-[#99A1AF] hover:bg-[#1E2939]' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span class="text-sm" style="font-family: Arial, sans-serif;">Manajemen Konten</span>
                </a>
            </div>
        </nav>

        <!-- System Status -->
        <div class="px-4 pt-4 border-t border-[#1E2939]">
            <div class="bg-[#0A0A0A] rounded-[10px] px-3 py-3 border border-[#1E2939]">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-3 h-3" fill="none" stroke="#6B7C4F" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="text-xs font-normal uppercase tracking-wider text-[#6B7C4F]" style="font-family: Arial, sans-serif;">SYSTEM STATUS</span>
                </div>
                <p class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Operational</p>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-[#6B7C4F] to-blue-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="font-family: Arial, sans-serif;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate" style="font-family: Arial, sans-serif;">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Administrator</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[#99A1AF] hover:text-red-400 transition-colors" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Top Bar -->
    <header id="topbar">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Hamburger (mobile only) -->
                <button onclick="toggleSidebar()" class="mobile-only text-gray-400 hover:text-white focus:outline-none" title="Toggle Sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-xl font-semibold text-white" style="font-family: Arial, sans-serif;">@yield('page-title', 'Dashboard')</h2>
            </div>
            <div>
                <span class="text-sm text-gray-400 hidden sm:inline">{{ now()->format('l, d F Y') }}</span>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <div id="page-content">
        <div class="p-4 sm:p-8">
            @if (session('success'))
                <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
        }
        function closeSidebar() {
            document.body.classList.remove('sidebar-open');
        }
    </script>
</body>
</html>
