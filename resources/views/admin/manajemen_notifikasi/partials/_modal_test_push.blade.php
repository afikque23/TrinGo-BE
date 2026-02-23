<!-- Modal Test Push to Device -->
<div x-cloak
     x-show="showTestPushModal" 
     @click.away="showTestPushModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
    <div @click.stop class="bg-[#111111] border border-[#1E2939] rounded-[14px] w-full max-w-md">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#1E2939]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#6B7C4F]/20 rounded-[10px] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white" style="font-family: Arial, sans-serif;">Test Push Notification</h3>
                    <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Kirim notifikasi test ke user tertentu</p>
                </div>
            </div>
            <button @click="showTestPushModal = false; testTargetUserId = null" class="text-[#99A1AF] hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-6">
            <!-- Info Card -->
            <div class="bg-[#6B7C4F]/10 border border-[#6B7C4F]/30 rounded-[10px] p-4 mb-5">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[#6B7C4F] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-xs text-white font-semibold mb-1" style="font-family: Arial, sans-serif;">Cross-Device Notification</p>
                        <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                            Notifikasi test akan dikirim ke <strong class="text-white">semua device</strong> yang terdaftar dengan user yang dipilih (smartphone, tablet, dll).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Target User Selection -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-white mb-2" style="font-family: Arial, sans-serif;">
                    Pilih Target User <span class="text-[#FB2C36]">*</span>
                </label>
                <div class="relative">
                    <select 
                        x-model="testTargetUserId"
                        class="w-full h-[46px] px-4 pr-10 bg-[#0A0A0A] border border-[#364153] rounded-[10px] text-white text-sm focus:outline-none focus:border-[#6B7C4F] transition-colors appearance-none"
                        style="font-family: Arial, sans-serif;">
                        <option value="" disabled>-- Pilih User --</option>
                        <option :value="{{ Auth::id() }}">👤 {{ Auth::user()->name }} (Admin - Saya)</option>
                        @foreach($users as $user)
                            @if($user->id !== Auth::id())
                            <option value="{{ $user->id }}">
                                📱 {{ $user->name }} ({{ $user->email }})
                            </option>
                            @endif
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                <p class="text-xs text-[#6A7282] mt-2" style="font-family: Arial, sans-serif;">
                    💡 Pilih user yang ingin menerima test notification. Notifikasi akan dikirim ke semua device yang terdaftar.
                </p>
            </div>

            <!-- Selected User Info (Dynamic) -->
            <div x-show="testTargetUserId" x-transition class="border border-[#364153] bg-[#0A0A0A] rounded-[10px] p-4 mb-5">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-[#6B7C4F]/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-white mb-1" style="font-family: Arial, sans-serif;">
                            Target: <span x-text="getSelectedUserName()"></span>
                        </p>
                        <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;" x-text="'User ID: ' + testTargetUserId"></p>
                        <div class="flex items-center gap-1.5 mt-2">
                            <svg class="w-3.5 h-3.5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-xs text-[#6B7C4F]" style="font-family: Arial, sans-serif;">Kirim ke semua device terdaftar</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center gap-3">
                <button 
                    @click="showTestPushModal = false" 
                    type="button"
                    class="flex-1 h-11 px-4 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors" 
                    style="font-family: Arial, sans-serif;">
                    Batal
                </button>
                <button 
                    @click="sendTestPush()" 
                    :disabled="!testTargetUserId"
                    type="button"
                    class="flex-1 h-11 px-4 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" 
                    style="font-family: Arial, sans-serif;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Kirim Test Push
                </button>
            </div>
        </div>
    </div>
</div>
