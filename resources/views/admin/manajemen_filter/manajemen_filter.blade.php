@extends('layouts.sidebar')

@section('title', 'Manajemen Filter - MotoTracker Admin')

@section('page-title', 'Manajemen Filter')

@section('content')
<div class="flex flex-col gap-6" x-data="{ 
    activeTab: '{{ $activeTab }}',
    showAddModal: false, 
    showEditModal: false, 
    showDeleteModal: false,
    selectedFilter: null,
    filters: {{ json_encode($filters) }},
    serviceTypes: {{ json_encode($serviceTypes) }},
    selectedType: null,
    showAddServiceModal: false,
    showEditServiceModal: false,
    showDeleteServiceModal: false,
    reminderOptions: {{ json_encode($reminderOptions) }},
    selectedOption: null,
    showAddReminderModal: false,
    showEditReminderModal: false,
    showDeleteReminderModal: false,
    notificationCategories: {{ json_encode($notificationCategories) }},
    selectedCategory: null,
    showAddCategoryModal: false,
    showEditCategoryModal: false,
    showDeleteCategoryModal: false,
    openEdit(filter) {
        this.selectedFilter = filter;
        this.showEditModal = true;
    },
    openDelete(filter) {
        this.selectedFilter = filter;
        this.showDeleteModal = true;
    },
    toggleStatus(filterId) {
        const filterIndex = this.filters.findIndex(f => f.id === filterId);
        if (filterIndex !== -1) {
            this.filters[filterIndex].status = this.filters[filterIndex].status === 'active' ? 'inactive' : 'active';
        }
    },
    getFilter(filterId) {
        return this.filters.find(f => f.id === filterId);
    },
    openEditService(type) {
        this.selectedType = type;
        this.showEditServiceModal = true;
    },
    openDeleteService(type) {
        this.selectedType = type;
        this.showDeleteServiceModal = true;
    },
    openEditReminder(option) {
        this.selectedOption = option;
        this.showEditReminderModal = true;
    },
    openDeleteReminder(option) {
        this.selectedOption = option;
        this.showDeleteReminderModal = true;
    },
    openEditCategory(category) {
        this.selectedCategory = category;
        this.showEditCategoryModal = true;
    },
    openDeleteCategory(category) {
        this.selectedCategory = category;
        this.showDeleteCategoryModal = true;
    }
}">
    <!-- Page Header with Tabs -->
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-xl sm:text-2xl font-bold text-white" style="font-family: Arial, sans-serif;">Manajemen Filter &amp; Master Data</h1>
            <p class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">Pusat konfigurasi filter dinamis dan master data untuk aplikasi mototracker</p>
        </div>
        
        <!-- Tab Navigation (scrollable on mobile) -->
        <div class="overflow-x-auto -mx-1">
            <div class="flex gap-0 border-b border-[#1E2939] min-w-max px-1">
            <a href="{{ route('admin.filters', ['tab' => 'filters']) }}" 
               :class="activeTab === 'filters' ? 'border-[#6B7C4F] text-white' : 'border-transparent text-[#99A1AF] hover:text-white'"
               class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap" 
               style="font-family: Arial, sans-serif;">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                    </svg>
                    Filter Dinamis
                </div>
            </a>
            <a href="{{ route('admin.filters', ['tab' => 'service-types']) }}" 
               :class="activeTab === 'service-types' ? 'border-[#6B7C4F] text-white' : 'border-transparent text-[#99A1AF] hover:text-white'"
               class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap" 
               style="font-family: Arial, sans-serif;">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Jenis Service
                </div>
            </a>
            <a href="{{ route('admin.filters', ['tab' => 'reminder-options']) }}" 
               :class="activeTab === 'reminder-options' ? 'border-[#6B7C4F] text-white' : 'border-transparent text-[#99A1AF] hover:text-white'"
               class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap" 
               style="font-family: Arial, sans-serif;">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Opsi Pengingat
                </div>
            </a>
            <a href="{{ route('admin.filters', ['tab' => 'notification-categories']) }}" 
               :class="activeTab === 'notification-categories' ? 'border-[#6B7C4F] text-white' : 'border-transparent text-[#99A1AF] hover:text-white'"
               class="px-3 sm:px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap" 
               style="font-family: Arial, sans-serif;">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Kategori Notifikasi
                </div>
            </a>
            </div>
        </div>
    </div>

    <!-- Tab Content: Filter Dinamis -->
    <div x-show="activeTab === 'filters'" x-cloak>
    <!-- Filter Bar -->
    <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Modul Filter -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Modul</label>
                <select class="w-full h-[38.5px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                    <option value="">Semua</option>
                    <option value="template">Template</option>
                    <option value="komunitas">Tips Perawatan</option>
                    <option value="ai">AI</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Status</label>
                <select class="w-full h-[38.5px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                    <option value="">Semua</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

            <!-- Jenis Input Filter -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs text-[#99A1AF]" style="font-family: Arial, sans-serif;">Jenis Input</label>
                <select class="w-full h-[38.5px] bg-[#0A0A0A] border border-[#364153] rounded-[10px] px-3 text-sm text-white focus:outline-none focus:border-[#6B7C4F]" style="font-family: Arial, sans-serif;">
                    <option value="">Semua</option>
                    <option value="dropdown">Dropdown</option>
                    <option value="multi-select">Multi-select</option>
                    <option value="range">Range</option>
                    <option value="toggle">Toggle</option>
                </select>
            </div>

            <!-- Add Button -->
            <div class="flex items-end">
                <button @click="showAddModal = true" class="w-full h-[38.5px] bg-[#6B7C4F] text-white text-sm rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center justify-center gap-2" style="font-family: Arial, sans-serif;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="mt-4">
        <!-- Mobile Card View -->
        <div class="block md:hidden space-y-3">
            @foreach($filters as $filter)
            <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-4" x-data="{ filter: getFilter({{ $filter['id'] }}) }">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-3.5 h-4 text-[#4A5565] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                        </svg>
                        <span class="text-sm font-medium text-white truncate" style="font-family: Arial, sans-serif;">{{ $filter['name'] }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <div class="w-2 h-2 rounded-full" :class="filter.status === 'active' ? 'bg-[#6B7C4F]' : 'bg-[#4A5565]'"></div>
                        <span class="text-xs text-[#D1D5DC]" style="font-family: Arial, sans-serif;" x-text="filter.status === 'active' ? 'Aktif' : 'Nonaktif'"></span>
                    </div>
                </div>
                <p class="text-xs text-[#99A1AF] mb-3" style="font-family: Arial, sans-serif;">{{ $filter['description'] }}</p>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="bg-[#0A0A0A] rounded-[8px] px-3 py-2">
                        <p class="text-[10px] text-[#4A5565] uppercase tracking-wider mb-0.5" style="font-family: Arial, sans-serif;">Jenis Input</p>
                        <p class="text-xs text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ $filter['type'] }}</p>
                    </div>
                    <div class="bg-[#0A0A0A] rounded-[8px] px-3 py-2">
                        <p class="text-[10px] text-[#4A5565] uppercase tracking-wider mb-0.5" style="font-family: Arial, sans-serif;">Digunakan Di</p>
                        <p class="text-xs text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ implode(', ', $filter['used_in']) }}</p>
                    </div>
                    <div class="bg-[#0A0A0A] rounded-[8px] px-3 py-2">
                        <p class="text-[10px] text-[#4A5565] uppercase tracking-wider mb-0.5" style="font-family: Arial, sans-serif;">Jumlah Opsi</p>
                        <p class="text-xs text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ $filter['options_count'] ?? '-' }}</p>
                    </div>
                    <div class="bg-[#0A0A0A] rounded-[8px] px-3 py-2">
                        <p class="text-[10px] text-[#4A5565] uppercase tracking-wider mb-0.5" style="font-family: Arial, sans-serif;">Urutan</p>
                        <p class="text-xs text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ $filter['order'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="openEdit({{ json_encode($filter) }})" class="flex-1 h-9 bg-[#1A1A1A] rounded-[8px] hover:bg-[#2A2A2A] transition-colors flex items-center justify-center gap-1.5" title="Edit">
                        <svg class="w-3.5 h-3.5 text-[#D1D5DC]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span class="text-xs text-[#D1D5DC]" style="font-family: Arial, sans-serif;">Edit</span>
                    </button>
                    <button @click="toggleStatus({{ $filter['id'] }})" :class="filter.status === 'active' ? 'bg-[#6B7C4F]' : 'bg-[#FB2C36]'" class="flex-1 h-9 rounded-[8px] hover:opacity-80 transition-all flex items-center justify-center gap-1.5" title="Toggle Status">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span class="text-xs text-white" style="font-family: Arial, sans-serif;" x-text="filter.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'"></span>
                    </button>
                    <button @click="openDelete({{ json_encode($filter) }})" class="h-9 px-3 bg-[#FB2C36] rounded-[8px] hover:bg-[#E01B25] transition-colors flex items-center justify-center" title="Delete">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block bg-[#111111] border border-[#1E2939] rounded-[14px] p-px overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-fixed" style="min-width: 650px;">
                    <colgroup>
                        <col style="width:17%">
                        <col style="width:23%">
                        <col style="width:10%">
                        <col style="width:13%">
                        <col style="width:9%">
                        <col style="width:7%">
                        <col style="width:9%">
                        <col style="width:12%">
                    </colgroup>
                    <thead class="bg-[#0A0A0A] border-b border-[#1E2939]">
                        <tr>
                            <th class="px-4 py-5 text-left">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                                    </svg>
                                    <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Nama Filter</span>
                                </div>
                            </th>
                            <th class="px-4 py-5 text-left"><span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Deskripsi</span></th>
                            <th class="px-4 py-5 text-left"><span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Jenis Input</span></th>
                            <th class="px-4 py-5 text-left"><span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Digunakan Di</span></th>
                            <th class="px-4 py-5 text-center"><span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF] whitespace-nowrap" style="font-family: Arial, sans-serif;">Jumlah Opsi</span></th>
                            <th class="px-4 py-5 text-center"><span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Urutan</span></th>
                            <th class="px-4 py-5 text-left"><span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Status</span></th>
                            <th class="px-4 py-5 text-center"><span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filters as $filter)
                        <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A] transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                                    <span class="text-sm text-white" style="font-family: Arial, sans-serif;">{{ $filter['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4"><span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $filter['description'] }}</span></td>
                            <td class="px-4 py-4"><span class="text-sm text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ $filter['type'] }}</span></td>
                            <td class="px-4 py-4"><span class="text-sm text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ implode(', ', $filter['used_in']) }}</span></td>
                            <td class="px-4 py-4"><span class="text-sm text-[#D1D5DC] block text-center" style="font-family: Arial, sans-serif;">{{ $filter['options_count'] ?? '-' }}</span></td>
                            <td class="px-4 py-4"><span class="text-sm text-[#D1D5DC] block text-center" style="font-family: Arial, sans-serif;">{{ $filter['order'] }}</span></td>
                            <td class="px-4 py-4" x-data="{ filter: getFilter({{ $filter['id'] }}) }">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" :class="filter.status === 'active' ? 'bg-[#6B7C4F]' : 'bg-[#4A5565]'"></div>
                                    <span class="text-sm text-[#D1D5DC] capitalize" style="font-family: Arial, sans-serif;" x-text="filter.status === 'active' ? 'Aktif' : 'Nonaktif'"></span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEdit({{ json_encode($filter) }})" class="p-1.5 bg-[#1A1A1A] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" title="Edit">
                                        <svg class="w-3.5 h-3.5 text-[#D1D5DC]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="toggleStatus({{ $filter['id'] }})" x-data="{ filter: getFilter({{ $filter['id'] }}) }" :class="filter.status === 'active' ? 'bg-[#6B7C4F]' : 'bg-[#FB2C36]'" class="p-1.5 rounded-[10px] hover:opacity-80 transition-all" title="Toggle Status">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </button>
                                    <button @click="openDelete({{ json_encode($filter) }})" class="p-1.5 bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors" title="Delete">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.manajemen_filter.partials.filters._modal_tambah')

    @include('admin.manajemen_filter.partials.filters._modal_edit')

    @include('admin.manajemen_filter.partials.filters._modal_hapus')
    </div>
    <!-- End Tab Content: Filter Dinamis -->

    <!-- Tab Content: Jenis Service -->
    <div x-show="activeTab === 'service-types'" x-cloak>
        @include('admin.manajemen_filter.partials.service_types._tab_content')
    </div>
    @include('admin.manajemen_filter.partials.service_types._modal_tambah')
    @include('admin.manajemen_filter.partials.service_types._modal_edit')
    @include('admin.manajemen_filter.partials.service_types._modal_hapus')
    <!-- End Tab Content: Jenis Service -->

    <!-- Tab Content: Opsi Pengingat -->
    <div x-show="activeTab === 'reminder-options'" x-cloak>
        @include('admin.manajemen_filter.partials.reminder_options._tab_content')
    </div>
    @include('admin.manajemen_filter.partials.reminder_options._modal_tambah')
    @include('admin.manajemen_filter.partials.reminder_options._modal_edit')
    @include('admin.manajemen_filter.partials.reminder_options._modal_hapus')
    <!-- End Tab Content: Opsi Pengingat -->

    <!-- Tab Content: Kategori Notifikasi -->
    <div x-show="activeTab === 'notification-categories'" x-cloak>
        @include('admin.manajemen_filter.partials.notification_categories._tab_content')
    </div>
    @include('admin.manajemen_filter.partials.notification_categories._modal_tambah')
    @include('admin.manajemen_filter.partials.notification_categories._modal_edit')
    @include('admin.manajemen_filter.partials.notification_categories._modal_hapus')
    <!-- End Tab Content: Kategori Notifikasi -->
</div>

<style>
    /* Alpine.js cloak */
    [x-cloak] { 
        display: none !important; 
    }

    /* Custom scrollbar for table */
    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #0A0A0A;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #1E2939;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #364153;
    }

    /* Dropdown styling */
    select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2399A1AF'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.25rem;
        padding-right: 2.5rem;
        appearance: none;
    }

    /* Modal scrollbar */
    .max-h-\[90vh\]::-webkit-scrollbar {
        width: 8px;
    }

    .max-h-\[90vh\]::-webkit-scrollbar-track {
        background: #0A0A0A;
        border-radius: 4px;
    }

    .max-h-\[90vh\]::-webkit-scrollbar-thumb {
        background: #1E2939;
        border-radius: 4px;
    }

    .max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
        background: #364153;
    }
</style>
@endsection
