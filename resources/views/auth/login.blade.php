<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin - Tringgo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Small overrides to match provided design */
        .card-shadow { box-shadow: 0 8px 24px rgba(2,6,23,0.6); }
    </style>
</head>
<body class="bg-[#0A0A0A] text-white font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-[1100px] h-auto relative flex items-center justify-center px-4">

            <!-- Left login panel (design width 448) -->
            <div class="w-full max-w-[448px] relative">
                <!-- Header area (flex for responsiveness) -->
                <div class="w-full h-[140px] flex flex-col items-center justify-start pt-4">
                    <div class="w-16 h-16 bg-[#6B7C4F] rounded-[16px] flex items-center justify-center mb-4">
                        <div class="w-8 h-8 border-[2.66667px] border-white rounded"></div>
                    </div>
                    <h1 class="text-[24px] font-bold">Admin Panel</h1>
                    <p class="text-[14px] text-[#99A1AF]">Tringgo Management System</p>
                </div>

                <!-- Card -->
                <div class="mt-6 w-full bg-[#111111] border border-[#1E2939] rounded-[14px] p-[33px] card-shadow">
                    <div class="w-full max-w-[382px] mx-auto">
                        <div class="mb-4">
                            <h2 class="text-[20px] font-bold">Login Admin</h2>
                            <p class="text-[14px] text-[#99A1AF]">Masuk ke dashboard administratif</p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-4 bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('login.submit') }}" method="POST" class="space-y-4 mt-6">
                            @csrf

                            <div class="space-y-2">
                                <label class="block mb-2 text-[14px] text-[#99A1AF]">Email Admin</label>
                                <div class="relative">
                                    <div class="absolute left-3 top-3 w-5 h-5 flex items-center justify-center">
                                        <div class="w-4 h-4 border-[1.66667px] border-[#6A7282] rounded"></div>
                                    </div>
                                    <input name="email" id="email" type="email" required placeholder="masukkan email admin" value="{{ old('email') }}"
                                        class="w-full h-[46px] pl-[44px] pr-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white placeholder:opacity-50" />
                                </div>
                            </div>

                            <div class="space-y-2 mt-4">
                                <label class="block mb-2 text-[14px] text-[#99A1AF]">Password</label>
                                <div class="relative">
                                    <div class="absolute left-3 top-3 w-5 h-5 flex items-center justify-center">
                                        <div class="w-4 h-4 border-[1.66667px] border-[#6A7282] rounded"></div>
                                    </div>
                                    <input name="password" id="password" type="password" required placeholder="masukkan password"
                                        class="w-full h-[46px] pl-[44px] pr-4 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white placeholder:opacity-50" />
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-2">
                                <label class="flex items-center gap-2 text-[#99A1AF]"><input type="checkbox" name="remember" class="w-4 h-4 bg-[#0A0A0A]"/> Ingat saya</label>
                            </div>

                            <button type="submit" class="w-full h-[48px] bg-[#6B7C4F] rounded-[10px] flex items-center justify-center gap-2 text-white font-medium mt-2">
                                <div class="w-5 h-5 border-[1.66667px] border-white rounded"></div>
                                Login ke Admin Panel
                            </button>
                        </form>

                        <div class="mt-6 text-[12px] text-[#4A5565] text-center">© 2026 Tringgo. All rights reserved.</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
