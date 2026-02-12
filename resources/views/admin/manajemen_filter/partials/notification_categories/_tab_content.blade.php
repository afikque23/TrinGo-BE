<!-- Notification Categories Table -->
<div class="flex flex-col gap-4">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-white mb-1" style="font-family: Arial, sans-serif;">Kategori Notifikasi</h3>
            <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Kelola kategori untuk sistem notifikasi aplikasi (Servis, Perjalanan, Peringatan, Rekomendasi)</p>
        </div>
        <button @click="showAddCategoryModal = true" class="h-11 px-4 bg-[#6B7C4F] text-white text-base rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Kategori
        </button>
    </div>

    <!-- Table -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-[#0A0A0A]">
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif;">Nama Kategori</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif;">
                            <div class="flex items-center gap-1.5">
                                <span>Key</span>
                                <div class="relative group">
                                    <svg class="w-3.5 h-3.5 text-[#6B7C4F] cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="absolute left-0 top-full mt-2 w-64 bg-[#0A0A0A] border border-[#364153] rounded-lg p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                        <p class="text-xs text-white font-medium mb-1" style="font-family: Arial, sans-serif;">Key (Unique Identifier)</p>
                                        <p class="text-xs text-[#99A1AF] leading-relaxed" style="font-family: Arial, sans-serif;">Identifier unik untuk kategori ini. Digunakan dalam sistem untuk mengidentifikasi kategori secara programatik. Contoh: <code class="text-[#6B7C4F]">service</code>, <code class="text-[#6B7C4F]">trip</code>, <code class="text-[#6B7C4F]">reminder</code></p>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif;">
                            <div class="flex items-center gap-1.5">
                                <span>Icon</span>
                                <div class="relative group">
                                    <svg class="w-3.5 h-3.5 text-[#6B7C4F] cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="absolute left-0 top-full mt-2 w-72 bg-[#0A0A0A] border border-[#364153] rounded-lg p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                        <p class="text-xs text-white font-medium mb-2" style="font-family: Arial, sans-serif;">Icon Yang Tersedia</p>
                                        <div class="space-y-1.5 text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[#6B7C4F]">🔧</span>
                                                <span>Servis/Maintenance</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[#6B7C4F]">🛣️</span>
                                                <span>Trip/Perjalanan</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[#6B7C4F]">⚠️</span>
                                                <span>Peringatan/Warning</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[#6B7C4F]">💡</span>
                                                <span>Rekomendasi/Saran</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[#6B7C4F]">📢</span>
                                                <span>Pengumuman/Announcement</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[#6B7C4F]">✅</span>
                                                <span>Selesai/Completed</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-[#6A7282] mt-2 pt-2 border-t border-[#1E2939]" style="font-family: Arial, sans-serif;">Anda bisa menggunakan emoji Unicode atau nama icon</p>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif;">Color</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif;">Urutan</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif;">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-[#99A1AF] uppercase tracking-wider" style="font-family: Arial, sans-serif;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1E2939]">
                    @forelse($notificationCategories as $category)
                    <tr class="hover:bg-[#0A0A0A] transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm text-white" style="font-family: Arial, sans-serif;">{{ $category->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-xs text-[#6A7282] bg-[#0A0A0A] px-2 py-1 rounded" style="font-family: Consolas, monospace;">{{ $category->key }}</code>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $category->icon ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($category->color)
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded border border-[#364153]" x-bind:style="'background-color: {{ $category->color }}'"></div>
                                <span class="text-xs text-[#99A1AF]" style="font-family: Consolas, monospace;">{{ $category->color }}</span>
                            </div>
                            @else
                            <span class="text-sm text-[#99A1AF]">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm text-white" style="font-family: Arial, sans-serif;">{{ $category->sort_order }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('admin.filters.notification-categories.toggle-status', $category) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors {{ $category->is_active ? 'bg-[#6B7C4F]/20 text-[#6B7C4F]' : 'bg-[#364153]/20 text-[#6A7282]' }}" style="font-family: Arial, sans-serif;">
                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Edit Button -->
                                <button @click="openEditCategory({{ json_encode($category) }})" class="p-1.5 bg-[#1A1A1A] border border-[#364153] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <!-- Delete Button -->
                                <button @click="openDeleteCategory({{ json_encode($category) }})" class="p-1.5 bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors" title="Delete">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-[#364153]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-[#99A1AF] mb-1" style="font-family: Arial, sans-serif;">Belum ada kategori notifikasi</p>
                                    <button @click="showAddCategoryModal = true" class="text-sm text-[#6B7C4F] hover:text-[#5A6A40] transition-colors" style="font-family: Arial, sans-serif;">
                                        Tambah kategori pertama
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
