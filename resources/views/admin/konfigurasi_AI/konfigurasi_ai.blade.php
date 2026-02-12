@extends('layouts.sidebar')

@section('title', 'Konfigurasi AI - MotoTracker')
@section('page-title', 'Konfigurasi AI')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-white mb-2">Parameter Sistem AI</h1>
    <p class="text-gray-400">Konfigurasi aturan insight dan template pesan AI berbasis data pengguna</p>
</div>

<!-- Tabbed Container -->
<div class="bg-[#111111] border border-gray-800 rounded-xl overflow-hidden">
    <!-- Tabs Header -->
    <div class="flex border-b border-gray-800">
        <button id="tabAturanInsight" onclick="switchTab('aturan')" class="flex-1 flex items-center justify-center gap-2 px-6 py-3.5 bg-[#6B7C4F] text-white font-bold text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Aturan Insight
        </button>
        <button id="tabTemplatePesan" onclick="switchTab('template')" class="flex-1 flex items-center justify-center gap-2 px-6 py-3.5 bg-black text-gray-400 font-bold text-sm hover:bg-gray-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Template Pesan
        </button>
    </div>

    <!-- Tab Content Aturan Insight -->
    <div id="contentAturanInsight" class="p-6">
        @include('admin.konfigurasi_AI.partials.tab_aturan_insight')
    </div>

    <!-- Tab Content Template Pesan -->
    <div id="contentTemplatePesan" class="p-6 hidden">
        @include('admin.konfigurasi_AI.partials.tab_template_pesan')
    </div>
</div>

<!-- Include Modals -->
@include('admin.konfigurasi_AI.partials.modal_tambah_aturan')
@include('admin.konfigurasi_AI.partials.modal_edit_aturan')
@include('admin.konfigurasi_AI.partials.modal_hapus_aturan')
@include('admin.konfigurasi_AI.partials.modal_tambah_template')
@include('admin.konfigurasi_AI.partials.modal_edit_template')
@include('admin.konfigurasi_AI.partials.modal_hapus_template')

<script>
    // Tab switching
    function switchTab(tab) {
        if (tab === 'aturan') {
            document.getElementById('tabAturanInsight').classList.add('bg-[#6B7C4F]', 'text-white');
            document.getElementById('tabAturanInsight').classList.remove('bg-black', 'text-gray-400');
            document.getElementById('tabTemplatePesan').classList.remove('bg-[#6B7C4F]', 'text-white');
            document.getElementById('tabTemplatePesan').classList.add('bg-black', 'text-gray-400');
            
            document.getElementById('contentAturanInsight').classList.remove('hidden');
            document.getElementById('contentTemplatePesan').classList.add('hidden');
        } else if (tab === 'template') {
            document.getElementById('tabTemplatePesan').classList.add('bg-[#6B7C4F]', 'text-white');
            document.getElementById('tabTemplatePesan').classList.remove('bg-black', 'text-gray-400');
            document.getElementById('tabAturanInsight').classList.remove('bg-[#6B7C4F]', 'text-white');
            document.getElementById('tabAturanInsight').classList.add('bg-black', 'text-gray-400');
            
            document.getElementById('contentTemplatePesan').classList.remove('hidden');
            document.getElementById('contentAturanInsight').classList.add('hidden');
        }
    }

    // Open modal
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close modal
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Open delete modal with rule name
    function openModalHapus(namaAturan) {
        document.getElementById('namaAturanHapus').textContent = namaAturan;
        openModal('modalHapusAturan');
    }

    // Confirm delete action
    function confirmHapus() {
        // Add your delete logic here
        console.log('Deleting rule...');
        closeModal('modalHapusAturan');
        // Show success notification or reload page
    }

    // Template modal functions  
    function openModalEditTemplate(templateName) {
        document.getElementById('editTemplateTitle').textContent = 'Edit: ' + templateName;
        // You can also update the subtitle if needed
        // document.getElementById('editTemplateSubtitle').textContent = 'Category • Rule Name';
        openModal('modalEditTemplate');
    }

    function openModalHapusTemplate(templateName) {
        document.getElementById('namaTemplateHapus').textContent = '"' + templateName + '"';
        openModal('modalHapusTemplate');
    }

    function confirmHapusTemplate() {
        console.log('Deleting template...');
        closeModal('modalHapusTemplate');
        // Show success notification or reload page
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modals = ['modalTambahAturan', 'modalEditAturan', 'modalHapusAturan', 'modalTambahTemplate', 'modalEditTemplate', 'modalHapusTemplate'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                closeModal(modalId);
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = ['modalTambahAturan', 'modalEditAturan', 'modalHapusAturan', 'modalTambahTemplate', 'modalEditTemplate', 'modalHapusTemplate'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (!modal.classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });
</script>
@endsection
