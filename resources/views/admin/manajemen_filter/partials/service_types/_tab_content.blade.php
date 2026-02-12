<!-- Service Types Tab Content -->

<!-- (Info card will be rendered beside the Add button below) -->

<!-- Info + Action Row -->
<div class="flex items-center justify-between gap-4 mb-4">
    <div class="flex-1 bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-[#D1D5DC] mb-1" style="font-family: Arial, sans-serif;"><strong>Tentang Tab Jenis Service</strong></p>
                <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Di sini Anda dapat menambah, mengubah, menonaktifkan, atau menghapus jenis service yang akan muncul sebagai pilihan pada form service. Jika suatu jenis service sedang digunakan oleh data service, penghapusan akan diblokir.</p>
            </div>
        </div>
    </div>

    <div class="flex-shrink-0">
        <button @click="showAddServiceModal = true" class="h-9 px-4 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Jenis Service
        </button>
    </div>
</div>

<!-- Table Container -->
<div class="mt-4 bg-[#111111] border border-[#1E2939] rounded-[14px] p-px overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full table-fixed">
            <colgroup>
                <col style="width:25%">
                <col style="width:35%">
                <col style="width:12%">
                <col style="width:10%">
                <col style="width:13%">
                <col style="width:15%">
            </colgroup>
            <!-- Table Header -->
            <thead class="bg-[#0A0A0A] border-b border-[#1E2939]">
                <tr>
                    <th class="px-4 py-5 text-left">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nama Jenis Service</span>
                        </div>
                    </th>
                    <th class="px-4 py-5 text-left">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Deskripsi</span>
                    </th>
                    <th class="px-4 py-5 text-center">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Digunakan</span>
                    </th>
                    <th class="px-4 py-5 text-left">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Status</span>
                    </th>
                    <th class="px-4 py-5 text-left">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Tanggal Dibuat</span>
                    </th>
                    <th class="px-4 py-5 text-center">
                        <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Aksi</span>
                    </th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
                @forelse($serviceTypes as $type)
                <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A] transition-colors">
                    <!-- Name with Icon -->
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-sm text-white" style="font-family: Arial, sans-serif;">{{ $type->name }}</span>
                        </div>
                    </td>

                    <!-- Description -->
                    <td class="px-4 py-4">
                        <span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $type->description ?? '-' }}</span>
                    </td>

                    <!-- Services Count -->
                    <td class="px-4 py-4">
                        <div class="flex justify-center">
                            <span class="px-2 py-1 bg-[#1A1A1A] text-[#D1D5DC] text-xs rounded-[6px]" style="font-family: Arial, sans-serif;">
                                {{ $type->services_count }} service
                            </span>
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ $type->is_active ? 'bg-[#6B7C4F]' : 'bg-[#4A5565]' }}"></div>
                            <span class="text-sm text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ $type->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    </td>

                    <!-- Created Date -->
                    <td class="px-4 py-4">
                        <span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $type->created_at->format('d M Y') }}</span>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <!-- Edit Button -->
                            <button @click="openEditService({{ json_encode($type) }})" class="p-1.5 bg-[#1A1A1A] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" title="Edit">
                                <svg class="w-3.5 h-3.5 text-[#D1D5DC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>

                            <!-- Toggle Status Button -->
                            <form action="{{ route('admin.filters.service-types.toggle-status', $type->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-1.5 rounded-[10px] hover:opacity-80 transition-all {{ $type->is_active ? 'bg-[#6B7C4F]' : 'bg-[#FB2C36]' }}" title="Toggle Status">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </button>
                            </form>

                            <!-- Delete Button -->
                            <button @click="openDeleteService({{ json_encode($type) }})" class="p-1.5 bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors" title="Delete">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center">
                        <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Belum ada jenis service yang ditambahkan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
