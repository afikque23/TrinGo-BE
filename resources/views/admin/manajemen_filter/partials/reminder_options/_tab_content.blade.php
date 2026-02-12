{{-- Tab Content: Reminder Options --}}

<!-- Info Card + Action Button Row -->
<div class="flex items-center justify-between gap-4 mb-4">
    <!-- Info Card -->
    <div class="flex-1 bg-[#111111] border border-[#1E2939] rounded-[14px] p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-[#1A2330] rounded-[10px] flex items-center justify-center">
                <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-white mb-1" style="font-family: Arial, sans-serif;">Tentang Tab Opsi Pengingat</h3>
                <p class="text-xs text-[#99A1AF] leading-relaxed" style="font-family: Arial, sans-serif;">
                    Kelola opsi pengingat yang tersedia untuk reminder servis. Ada 2 jenis: <strong class="text-white">Jarak (km)</strong> untuk pengingat berdasarkan odometer, dan <strong class="text-white">Waktu  (hari/minggu/bulan/tahun)</strong> untuk pengingat berdasarkan tanggal.
                </p>
            </div>
        </div>
    </div>

    <!-- Add Button -->
    <div class="flex-shrink-0">
        <button @click="showAddReminderModal = true" class="h-full px-4 py-3 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Opsi Pengingat
        </button>
    </div>
</div>

<!-- Table Container -->
<div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-px overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full table-fixed">
            <colgroup>
                <col style="width:20%">
                <col style="width:12%">
                <col style="width:15%">
                <col style="width:10%">
                <col style="width:15%">
                <col style="width:15%">
                <col style="width:13%">
            </colgroup>
            <!-- Table Header -->
            <thead class="bg-[#0A0A0A] border-b border-[#1E2939]">
                <tr>
                    <th class="px-4 py-5 text-left">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Label</span>
                        </div>
                    </th>
                    <th class="px-4 py-5 text-center">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nilai</span>
                    </th>
                    <th class="px-4 py-5 text-left">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Unit</span>
                    </th>
                    <th class="px-4 py-5 text-left">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Jenis</span>
                    </th>
                    <th class="px-4 py-5 text-left">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Status</span>
                    </th>
                    <th class="px-4 py-5 text-left">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Dibuat</span>
                    </th>
                    <th class="px-4 py-5 text-center">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Aksi</span>
                    </th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
                @forelse($reminderOptions as $option)
                <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A] transition-colors">
                    <!-- Label with Icon -->
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="text-sm text-white" style="font-family: Arial, sans-serif;">{{ $option['label'] }}</span>
                        </div>
                    </td>

                    <!-- Value -->
                    <td class="px-4 py-4 text-center">
                        <span class="text-sm text-[#D1D5DC] font-medium" style="font-family: Arial, sans-serif;">{{ $option['value'] }}</span>
                    </td>

                    <!-- Unit -->
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-[6px] text-xs font-medium" style="font-family: Arial, sans-serif;"
                              :class="{
                                  'bg-[#6B7C4F]/20 text-[#6B7C4F]': '{{ $option['unit'] }}' === 'km',
                                  'bg-[#3B82F6]/20 text-[#3B82F6]': ['days', 'weeks', 'months', 'years'].includes('{{ $option['unit'] }}')
                              }">
                            @if($option['unit'] === 'km')
                                Kilometer
                            @elseif($option['unit'] === 'days')
                                Hari
                            @elseif($option['unit'] === 'weeks')
                                Minggu
                            @elseif($option['unit'] === 'months')
                                Bulan
                            @elseif($option['unit'] === 'years')
                                Tahun
                            @endif
                        </span>
                    </td>

                    <!-- Type -->
                    <td class="px-4 py-4">
                        <span class="text-sm text-[#D1D5DC]" style="font-family: Arial, sans-serif;">
                            @if($option['unit'] === 'km')
                                Jarak
                            @else
                                Waktu
                            @endif
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ $option['is_active'] ? 'bg-[#6B7C4F]' : 'bg-[#4A5565]' }}"></div>
                            <span class="text-sm text-[#D1D5DC] capitalize" style="font-family: Arial, sans-serif;">
                                {{ $option['is_active'] ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </td>

                    <!-- Created Date -->
                    <td class="px-4 py-4">
                        <span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                            {{ \Carbon\Carbon::parse($option['created_at'])->format('d M Y') }}
                        </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <!-- Edit Button -->
                            <button @click="openEditReminder({{ json_encode($option) }})" class="p-1.5 bg-[#1A1A1A] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" title="Edit">
                                <svg class="w-3.5 h-3.5 text-[#D1D5DC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>

                            <!-- Toggle Status Button -->
                            <form method="POST" action="{{ route('admin.filters.reminder-options.toggle-status', $option['id']) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-1.5 rounded-[10px] hover:opacity-80 transition-all {{ $option['is_active'] ? 'bg-[#6B7C4F]' : 'bg-[#FB2C36]' }}" title="Toggle Status">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </button>
                            </form>

                            <!-- Delete Button -->
                            <button @click="openDeleteReminder({{ json_encode($option) }})" class="p-1.5 bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors" title="Delete">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 bg-[#1A1A1A] rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">
                                Belum ada opsi pengingat. Klik tombol "Tambah Opsi Pengingat" untuk menambahkan.
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
