@extends('layouts.sidebar')

@section('title', 'Jenis Service - MotoTracker Admin')

@section('page-title', 'Manajemen Jenis Service')

@section('content')
<div class="flex flex-col gap-6" x-data="{ 
    showAddModal: false, 
    showEditModal: false, 
    showDeleteModal: false,
    selectedType: null,
    serviceTypes: {{ json_encode($serviceTypes) }},
    openEdit(type) {
        this.selectedType = type;
        this.showEditModal = true;
    },
    openDelete(type) {
        this.selectedType = type;
        this.showDeleteModal = true;
    }
}">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">Manajemen Jenis Service</h1>
            <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Kelola master data jenis service untuk aplikasi mobile</p>
        </div>
        <button @click="showAddModal = true" class="h-9 px-4 bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Jenis Service
        </button>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-[rgba(107,124,79,0.1)] border border-[rgba(107,124,79,0.3)] rounded-[14px] p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-[#6B7C4F] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M5 13l4 4L19 7"></path>
        </svg>
        <p class="text-sm text-[#6B7C4F]" style="font-family: Arial, sans-serif;">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-[rgba(251,44,54,0.1)] border border-[rgba(251,44,54,0.3)] rounded-[14px] p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-[#FB2C36] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-sm text-[#FB2C36]" style="font-family: Arial, sans-serif;">{{ session('error') }}</p>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#6B7C4F] bg-opacity-20 rounded-[10px] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Total Jenis Service</p>
                    <p class="text-xl font-bold text-white" style="font-family: Arial, sans-serif;">{{ count($serviceTypes) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#6B7C4F] bg-opacity-20 rounded-[10px] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Aktif</p>
                    <p class="text-xl font-bold text-white" style="font-family: Arial, sans-serif;">{{ collect($serviceTypes)->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#99A1AF] bg-opacity-20 rounded-[10px] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#99A1AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nonaktif</p>
                    <p class="text-xl font-bold text-white" style="font-family: Arial, sans-serif;">{{ collect($serviceTypes)->where('is_active', false)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <!-- Table Header -->
                <thead class="bg-[#0A0A0A] border-b border-[#1E2939]">
                    <tr>
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nama Jenis Service</span>
                        </th>
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Deskripsi</span>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Digunakan</span>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Status</span>
                        </th>
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Dibuat</span>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Aksi</span>
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    @forelse($serviceTypes as $type)
                    <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A] transition-colors">
                        <!-- Name -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#6B7C4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-white" style="font-family: Arial, sans-serif;">{{ $type['name'] }}</span>
                            </div>
                        </td>

                        <!-- Description -->
                        <td class="px-6 py-4">
                            <span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $type['description'] ?? '-' }}</span>
                        </td>

                        <!-- Services Count -->
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-[#6B7C4F] bg-[#6B7C4F] bg-opacity-10 rounded-[6px]" style="font-family: Arial, sans-serif;">
                                {{ $type['services_count'] }} service
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-2 h-2 rounded-full {{ $type['is_active'] ? 'bg-[#6B7C4F]' : 'bg-[#4A5565]' }}"></div>
                                <span class="text-sm text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ $type['is_active'] ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                        </td>

                        <!-- Created At -->
                        <td class="px-6 py-4">
                            <span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $type['created_at'] }}</span>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Edit Button -->
                                <button @click="openEdit({{ json_encode($type) }})" class="p-1.5 bg-[#1A1A1A] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5 text-[#D1D5DC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <!-- Toggle Status Button -->
                                <form action="{{ route('admin.service-types.toggle-status', $type['id']) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 {{ $type['is_active'] ? 'bg-[#6B7C4F]' : 'bg-[#FB2C36]' }} rounded-[10px] hover:opacity-80 transition-all" title="Toggle Status">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </button>
                                </form>

                                <!-- Delete Button -->
                                <button @click="openDelete({{ json_encode($type) }})" class="p-1.5 bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors" title="Delete">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Belum ada jenis service</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.service_types.partials._modal_tambah')
    @include('admin.service_types.partials._modal_edit')
    @include('admin.service_types.partials._modal_hapus')
</div>

<style>
    [x-cloak] { 
        display: none !important; 
    }
</style>
@endsection
