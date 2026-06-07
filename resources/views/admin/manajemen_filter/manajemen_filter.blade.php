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
        
        <!-- Tab Navigation (fully responsive, scrollable on mobile) -->
        <div class="filter-tabs-wrapper">
            <!-- Left fade indicator -->
            <div class="filter-tabs-fade filter-tabs-fade--left" id="tabFadeLeft"></div>
            <!-- Right fade indicator -->
            <div class="filter-tabs-fade filter-tabs-fade--right" id="tabFadeRight"></div>

            <div class="filter-tabs-scroll" id="filterTabsScroll">
                <div class="filter-tabs-inner border-b border-[#1E2939]">

                    {{-- Tab: Filter Dinamis --}}
                    <a href="{{ route('admin.filters', ['tab' => 'filters']) }}"
                       :class="activeTab === 'filters'
                           ? 'filter-tab--active'
                           : 'filter-tab--inactive'"
                       class="filter-tab"
                       style="font-family: Arial, sans-serif;"
                       id="tab-filters">
                        <svg class="filter-tab__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33"
                                  d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                        </svg>
                        <span class="filter-tab__text">Filter Dinamis</span>
                    </a>

                    {{-- Tab: Jenis Service --}}
                    <a href="{{ route('admin.filters', ['tab' => 'service-types']) }}"
                       :class="activeTab === 'service-types'
                           ? 'filter-tab--active'
                           : 'filter-tab--inactive'"
                       class="filter-tab"
                       style="font-family: Arial, sans-serif;"
                       id="tab-service-types">
                        <svg class="filter-tab__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="filter-tab__text">Jenis Service</span>
                    </a>

                    {{-- Tab: Opsi Pengingat --}}
                    <a href="{{ route('admin.filters', ['tab' => 'reminder-options']) }}"
                       :class="activeTab === 'reminder-options'
                           ? 'filter-tab--active'
                           : 'filter-tab--inactive'"
                       class="filter-tab"
                       style="font-family: Arial, sans-serif;"
                       id="tab-reminder-options">
                        <svg class="filter-tab__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="filter-tab__text">Opsi Pengingat</span>
                    </a>

                    {{-- Tab: Kategori Notifikasi --}}
                    <a href="{{ route('admin.filters', ['tab' => 'notification-categories']) }}"
                       :class="activeTab === 'notification-categories'
                           ? 'filter-tab--active'
                           : 'filter-tab--inactive'"
                       class="filter-tab"
                       style="font-family: Arial, sans-serif;"
                       id="tab-notification-categories">
                        <svg class="filter-tab__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span class="filter-tab__text">Kategori Notifikasi</span>
                    </a>

                </div>
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
    <div class="mt-4 bg-[#111111] border border-[#1E2939] rounded-[14px] p-px overflow-hidden">
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
                <!-- Table Header -->
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
                        <th class="px-4 py-5 text-left">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Deskripsi</span>
                        </th>
                        <th class="px-4 py-5 text-left">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Jenis Input</span>
                        </th>
                        <th class="px-4 py-5 text-left">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Digunakan Di</span>
                        </th>
                        <th class="px-4 py-5 text-center">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF] whitespace-nowrap" style="font-family: Arial, sans-serif;">Jumlah Opsi</span>
                        </th>
                        <th class="px-4 py-5 text-center">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Urutan</span>
                        </th>
                        <th class="px-4 py-5 text-left">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Status</span>
                        </th>
                        <th class="px-4 py-5 text-center">
                            <span class="text-xs font-normal uppercase tracking-wider text-[#99A1AF]" style="font-family: Arial, sans-serif;">Aksi</span>
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    @foreach($filters as $filter)
                    <tr class="border-b border-[#1E2939] hover:bg-[#0A0A0A] transition-colors">
                        <!-- Name with Icon -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                                </svg>
                                <span class="text-sm text-white" style="font-family: Arial, sans-serif;">{{ $filter['name'] }}</span>
                            </div>
                        </td>

                        <!-- Description -->
                        <td class="px-4 py-4">
                            <span class="text-sm text-[#99A1AF]" style="font-family: Arial, sans-serif;">{{ $filter['description'] }}</span>
                        </td>

                        <!-- Type -->
                        <td class="px-4 py-4">
                            <span class="text-sm text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ $filter['type'] }}</span>
                        </td>

                        <!-- Used In -->
                        <td class="px-4 py-4">
                            <span class="text-sm text-[#D1D5DC]" style="font-family: Arial, sans-serif;">{{ implode(', ', $filter['used_in']) }}</span>
                        </td>

                        <!-- Options Count -->
                        <td class="px-4 py-4">
                            <span class="text-sm text-[#D1D5DC] block text-center" style="font-family: Arial, sans-serif;">{{ $filter['options_count'] ?? '-' }}</span>
                        </td>

                        <!-- Order -->
                        <td class="px-4 py-4">
                            <span class="text-sm text-[#D1D5DC] block text-center" style="font-family: Arial, sans-serif;">{{ $filter['order'] }}</span>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-4" x-data="{ filter: getFilter({{ $filter['id'] }}) }">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" :class="filter.status === 'active' ? 'bg-[#6B7C4F]' : 'bg-[#4A5565]'"></div>
                                <span class="text-sm text-[#D1D5DC] capitalize" style="font-family: Arial, sans-serif;" x-text="filter.status === 'active' ? 'Aktif' : 'Nonaktif'"></span>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Edit Button -->
                                <button @click="openEdit({{ json_encode($filter) }})" class="p-1.5 bg-[#1A1A1A] rounded-[10px] hover:bg-[#2A2A2A] transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5 text-[#D1D5DC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <!-- Toggle Status Button -->
                                <button @click="toggleStatus({{ $filter['id'] }})" x-data="{ filter: getFilter({{ $filter['id'] }}) }" :class="filter.status === 'active' ? 'bg-[#6B7C4F]' : 'bg-[#FB2C36]'" class="p-1.5 rounded-[10px] hover:opacity-80 transition-all" title="Toggle Status">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </button>

                                <!-- Delete Button -->
                                <button @click="openDelete({{ json_encode($filter) }})" class="p-1.5 bg-[#FB2C36] rounded-[10px] hover:bg-[#E01B25] transition-colors" title="Delete">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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

    /* ================================================
       RESPONSIVE TAB NAVIGATION
    ================================================ */

    /* Outer wrapper – creates the stacking context for
       the fade-edge indicators                         */
    .filter-tabs-wrapper {
        position: relative;
        /* bleed past the parent's px-4 / sm:px-8 padding
           so the bottom border runs full-width on mobile */
        margin-left:  -1rem;
        margin-right: -1rem;
    }

    /* On sm+ (≥640 px) the parent padding is 2 rem */
    @media (min-width: 640px) {
        .filter-tabs-wrapper {
            margin-left:  -2rem;
            margin-right: -2rem;
        }
    }

    /* The scrollable rail */
    .filter-tabs-scroll {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;          /* Firefox */
        scroll-behavior: smooth;
    }
    .filter-tabs-scroll::-webkit-scrollbar {
        display: none;                  /* Chrome / Safari */
    }

    /* Inner flex row – add matching horizontal padding so
       first/last tab don't sit flush against the edge    */
    .filter-tabs-inner {
        display: flex;
        flex-direction: row;
        gap: 0;
        min-width: max-content;
        padding-left:  1rem;
        padding-right: 1rem;
    }
    @media (min-width: 640px) {
        .filter-tabs-inner {
            padding-left:  2rem;
            padding-right: 2rem;
        }
    }

    /* Individual tab item */
    .filter-tab {
        display:         inline-flex;
        align-items:     center;
        gap:             0.375rem;   /* 6 px */
        padding:         0.75rem 0.875rem;
        font-size:       0.875rem;   /* 14 px */
        font-weight:     500;
        border-bottom:   2px solid transparent;
        white-space:     nowrap;
        transition:      color 0.2s ease, border-color 0.2s ease,
                         background-color 0.2s ease;
        border-radius:   0;          /* flush underline style */
        text-decoration: none;
        cursor:          pointer;
        /* Scroll-snap so each tab snaps into view on touch */
        scroll-snap-align: start;
    }

    /* On very small phones (< 380 px) tighten padding &
       show only icons; on ≥ 380 px show full text      */
    @media (max-width: 379px) {
        .filter-tab {
            padding: 0.75rem 0.625rem;
        }
        .filter-tab__text {
            display: none;
        }
    }
    @media (min-width: 380px) {
        .filter-tab__text {
            display: inline;
        }
    }

    /* Icon sizing */
    .filter-tab__icon {
        width:  1rem;   /* 16 px */
        height: 1rem;
        flex-shrink: 0;
    }

    /* Active state */
    .filter-tab--active {
        border-bottom-color: #6B7C4F;
        color: #ffffff;
    }

    /* Inactive / hover state */
    .filter-tab--inactive {
        color: #99A1AF;
    }
    .filter-tab--inactive:hover {
        color: #ffffff;
        background-color: rgba(30, 41, 57, 0.35);
    }

    /* ---- Gradient fade edge indicators ---- */
    .filter-tabs-fade {
        position:   absolute;
        top:        0;
        bottom:     2px;            /* sit above the border-b */
        width:      2.5rem;
        pointer-events: none;
        z-index:    2;
        opacity:    0;
        transition: opacity 0.25s ease;
    }
    .filter-tabs-fade--left {
        left: 0;
        background: linear-gradient(to right, #0A0A0A 20%, transparent);
    }
    .filter-tabs-fade--right {
        right: 0;
        background: linear-gradient(to left, #0A0A0A 20%, transparent);
    }
    /* JS will toggle these classes */
    .filter-tabs-fade--visible {
        opacity: 1;
    }

    /* ================================================
       CUSTOM SCROLLBAR – table containers
    ================================================ */
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

    /* Dropdown arrow */
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

<script>
    (function () {
        var rail    = document.getElementById('filterTabsScroll');
        var fadeL   = document.getElementById('tabFadeLeft');
        var fadeR   = document.getElementById('tabFadeRight');

        if (!rail || !fadeL || !fadeR) return;

        function updateFades() {
            var atLeft  = rail.scrollLeft <= 4;
            var atRight = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 4;
            fadeL.classList.toggle('filter-tabs-fade--visible', !atLeft);
            fadeR.classList.toggle('filter-tabs-fade--visible', !atRight);
        }

        /* Scroll active tab into view on page load */
        var activeTab = rail.querySelector('.filter-tab--active');
        if (activeTab) {
            /* Use requestAnimationFrame so the browser has
               laid out before we try to scroll               */
            requestAnimationFrame(function () {
                activeTab.scrollIntoView({ inline: 'nearest', behavior: 'instant' });
                updateFades();
            });
        } else {
            updateFades();
        }

        rail.addEventListener('scroll', updateFades, { passive: true });
        window.addEventListener('resize', updateFades, { passive: true });
    })();
</script>
@endsection
