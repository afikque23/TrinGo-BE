@extends('layouts.sidebar')

@section('title', 'Manajemen Konten - TringGo')
@section('page-title', 'Manajemen Konten')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="{
    selectedContent: null,
    selectedContentData: null,
    editMode: false,
    showAddSectionModal: false,
    newSectionTitle: '',
    newSectionItems: [''],
    loading: false,
    saving: false,
    contents: [],
    contentSections: {},
    csrfToken: '{{ csrf_token() }}',
    
    async init() {
        await this.fetchContents();
    },
    
    async fetchContents() {
        this.loading = true;
        console.log('🔍 Fetching contents from API...');
        try {
            const response = await fetch('/api/v1/motorcycle/admin/contents', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                credentials: 'same-origin'
            });
            
            console.log('📡 Response status:', response.status);
            console.log('📡 Response ok:', response.ok);
            
            if (response.ok) {
                const data = await response.json();
                console.log('📦 Response data:', data);
                console.log('📦 Data success:', data.success);
                console.log('📦 Data array length:', data.data ? data.data.length : 0);
                
                if (data.success) {
                    this.contents = data.data.map(content => ({
                        id: content.id,
                        emoji: this.getEmojiByType(content.type),
                        title: content.title,
                        description: this.getDescriptionByType(content.type),
                        type: this.getTypeLabel(content.type),
                        sections: this.parseSections(content.body).length,
                        words: this.countWords(content.body),
                        updated: this.formatDate(content.updated_at),
                        active: content.status === 'published',
                        rawData: content
                    }));
                    
                    console.log('✅ Contents loaded:', this.contents.length);
                    
                    // Parse sections untuk masing-masing content
                    data.data.forEach(content => {
                        this.contentSections[content.id] = this.parseSections(content.body);
                    });
                } else {
                    console.error('❌ API returned success=false:', data.message);
                    this.showNotification(data.message || 'Gagal memuat konten', 'error');
                }
            } else {
                const errorData = await response.text();
                console.error('❌ Response not OK:', response.status, errorData);
                this.showNotification('Gagal memuat konten. Pastikan Anda sudah login sebagai admin.', 'error');
            }
        } catch (error) {
            console.error('💥 Error fetching contents:', error);
            this.showNotification('Terjadi kesalahan saat memuat konten', 'error');
        } finally {
            this.loading = false;
        }
    },
    
    async saveContent() {
        if (!this.selectedContent) return;
        
        this.saving = true;
        const content = this.contents.find(c => c.id === this.selectedContent);
        if (!content) return;
        
        try {
            const sections = this.contentSections[this.selectedContent] || [];
            const body = JSON.stringify(sections.map(s => ({
                section: s.title,
                content: s.items[0] || '',
                items: s.items.slice(1).filter(item => item && item.trim() !== '')
            })));
            
            const response = await fetch(`/api/v1/motorcycle/admin/contents/${this.selectedContent}`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    title: content.rawData.title,
                    body: body,
                    status: content.active ? 'published' : 'draft'
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                this.showNotification('✅ Konten berhasil disimpan!', 'success');
                this.editMode = false;
                await this.fetchContents();
            } else {
                this.showNotification(data.message || 'Gagal meny impan konten', 'error');
            }
        } catch (error) {
            console.error('Error saving content:', error);
            this.showNotification('Terjadi kesalahan saat menyimpan', 'error');
        } finally {
            this.saving = false;
        }
    },
    
    parseSections(bodyJson) {
        try {
            const parsed = JSON.parse(bodyJson);
            return parsed.map((section, index) => ({
                id: index + 1,
                title: section.section || section.title || section.question || '',
                items: [
                    section.content || section.answer || '',
                    ...(section.items || [])
                ].filter(item => item !== undefined && item !== null)
            }));
        } catch (e) {
            return [];
        }
    },
    
    getEmojiByType(type) {
        const emojis = {
            'terms': '📜',
            'privacy': '🔒',
            'guide': '📖',
            'about': 'ℹ️',
            'faq': '❓',
            'system_info': '⚙️',
            'support': '🆘'
        };
        return emojis[type] || '📄';
    },
    
    getDescriptionByType(type) {
        const descriptions = {
            'terms': 'Aturan penggunaan aplikasi TringGo',
            'privacy': 'Cara kami mengumpulkan dan melindungi data Anda',
            'guide': 'Tutorial lengkap penggunaan fitur aplikasi',
            'about': 'Informasi aplikasi TringGo',
            'faq': 'Pertanyaan yang sering diajukan (FAQ)',
            'system_info': 'Transparansi algoritma dan logika sistem',
            'support': 'Pusat bantuan, kontak, dan dukungan pengguna'
        };
        return descriptions[type] || 'Konten aplikasi mobile';
    },
    
    getTypeLabel(type) {
        const labels = {
            'terms': 'Berbasis Point',
            'privacy': 'Berbasis Section',
            'guide': 'Berbasis Section',
            'about': 'Halaman Info',
            'faq': 'FAQ (Tanya Jawab)',
            'system_info': 'Berbasis Section',
            'support': 'Bantuan & Dukungan'
        };
        return labels[type] || 'Dokumen';
    },
    
    countWords(bodyJson) {
        try {
            const parsed = JSON.parse(bodyJson);
            let text = JSON.stringify(parsed);
            return text.split(/\s+/).length;
        } catch (e) {
            return 0;
        }
    },
    
    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) return 'Hari ini';
        if (days === 1) return 'Kemarin';
        if (days === 2) return '2 hari lalu';
        if (days < 7) return `${days} hari lalu`;
        if (days < 14) return '1 minggu lalu';
        if (days < 21) return '2 minggu lalu';
        if (days < 28) return '3 minggu lalu';
        if (days < 60) return '1 bulan lalu';
        return `${Math.floor(days / 30)} bulan lalu`;
    },
    
    showNotification(message, type = 'success') {
        // Simple notification (you can integrate with your existing notification system)
        alert(message);
    },
    
    tempContentSections: {
        1: [
            {
                id: 1,
                title: 'Selamat Datang di TringGo',
                items: ['Dengan menggunakan aplikasi TringGo, Anda menyatakan bahwa Anda telah membaca, memahami, dan menyetujui untuk terikat oleh Syarat dan Ketentuan ini.']
            },
            {
                id: 2,
                title: '1. Penerimaan Ketentuan',
                items: [
                    'Dengan membuat akun atau menggunakan TringGo, Anda menyatakan bahwa:',
                    'Anda memiliki hak penuh untuk memiliki dan menggunakan kendaraan yang terdaftar',
                    'Anda memiliki kapasitas hukum untuk menyetujui perjanjian yang mengikat',
                    'Informasi yang Anda berikan adalah akurat dan lengkap',
                    'Anda akan mematuhi semua hukum dan peraturan yang berlaku'
                ]
            },
            {
                id: 3,
                title: '2. Akun Pengguna',
                items: ['', '', '', '']
            },
            {
                id: 4,
                title: '3. Penggunaan Layanan',
                items: ['', '', '', '']
            },
            {
                id: 5,
                title: '4. Aktivitas yang Dilarang',
                items: ['', '', '', '']
            }
        ],
        6: [
            {
                id: 1,
                title: 'Sistem Adaptif Berbasis Data',
                items: ['Aplikasi ini menggunakan pendekatan analisis berbasis data untuk memberikan rekomendasi perawatan yang disesuaikan dengan pola penggunaan kendaraan Anda.']
            },
            {
                id: 2,
                title: 'Data yang Dianalisis Sistem',
                items: [
                    'Jarak Tempuh - Total kilometer yang telah ditempuh dan pola perjalanan',
                    'Waktu Servis Terakhir - Tanggal dan odometer saat perawatan terakhir',
                    'Frekuensi Penggunaan - Seberapa sering kendaraan digunakan',
                    'Riwayat Perawatan - Data historis servis dan komponen'
                ]
            },
            {
                id: 3,
                title: '1. Klasifikasi Pola Penggunaan',
                items: ['Sistem menghitung rata-rata jarak tempuh harian dan mengklasifikasikan: Ringan (<15km/hari), Normal (15-50km/hari), atau Berat (>50km/hari).']
            },
            {
                id: 4,
                title: '2. Penyesuaian Interval Perawatan',
                items: ['Interval standar disesuaikan dengan kategori penggunaan—penggunaan berat mendapat interval lebih pendek.']
            },
            {
                id: 5,
                title: '3. Deteksi Tren dan Anomali',
                items: ['Sistem memantau perubahan pola mingguan untuk mendeteksi anomali.']
            },
            {
                id: 6,
                title: '4. Rekomendasi Kontekstual',
                items: ['Insight dipilih berdasarkan relevansi dengan kondisi kendaraan saat ini.']
            },
            {
                id: 7,
                title: 'Catatan Teknis',
                items: ['Sistem ini menggunakan pendekatan deterministik dengan aturan yang telah ditetapkan, bukan prediksi probabilistik.']
            }
        ]
    },
    
    get editingSections() {
        return this.contentSections[this.selectedContent] || [];
    },
    set editingSections(value) {
        if (this.selectedContent) {
            this.contentSections[this.selectedContent] = value;
        }
    },
    
    selectContent(contentId) {
        this.selectedContent = contentId;
        this.editMode = false;
        const content = this.contents.find(c => c.id === contentId);
        if (content) {
            this.selectedContentData = content.rawData;
        }
    },
    
    addSection() {
        const sections = this.contentSections[this.selectedContent] || [];
        const newId = sections.length > 0 ? Math.max(...sections.map(s => s.id)) + 1 : 1;
        if (!this.contentSections[this.selectedContent]) {
            this.contentSections[this.selectedContent] = [];
        }
        this.contentSections[this.selectedContent].push({
            id: newId,
            title: this.newSectionTitle,
            items: this.newSectionItems.filter(item => item.trim() !== '')
        });
        this.newSectionTitle = '';
        this.newSectionItems = [''];
        this.showAddSectionModal = false;
    },
    addItemToSection(sectionId) {
        const sections = this.contentSections[this.selectedContent] || [];
        const section = sections.find(s => s.id === sectionId);
        if (section) {
            section.items.push('');
        }
    },
    removeSection(sectionId) {
        if (this.contentSections[this.selectedContent]) {
            this.contentSections[this.selectedContent] = this.contentSections[this.selectedContent].filter(s => s.id !== sectionId);
        }
    },
    removeItem(sectionId, itemIndex) {
        const sections = this.contentSections[this.selectedContent] || [];
        const section = sections.find(s => s.id === sectionId);
        if (section) {
            section.items.splice(itemIndex, 1);
        }
    },
    addNewItemToModal() {
        this.newSectionItems.push('');
    },
    removeModalItem(index) {
        this.newSectionItems.splice(index, 1);
    }
}">
    <!-- Description -->
    <p class="text-[#99A1AF] text-sm mb-6" style="font-family: Arial, sans-serif;">Kelola konten informasi statis aplikasi mobile</p>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-3">
        <!-- Left Column - Content List -->
        <div class="flex flex-col gap-3">
            <!-- Loading State -->
            <template x-if="loading && contents.length === 0">
                <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-8 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#6B7C4F]"></div>
                    <p class="text-[#99A1AF] text-sm mt-3">Memuat konten...</p>
                </div>
            </template>
            
            <template x-for="content in contents" :key="content.id">
                <div 
                    @click="selectContent(content.id)"
                    :class="selectedContent === content.id ? 'ring-2 ring-[#6B7C4F] shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)]' : ''"
                    class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-4 cursor-pointer hover:border-[#6B7C4F] transition-all">
                    <!-- Content Header -->
                    <div class="flex items-start gap-3 mb-2">
                        <span class="text-2xl" x-text="content.emoji"></span>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-white text-sm mb-1" style="font-family: Arial, sans-serif;" x-text="content.title"></h3>
                            <p class="text-[#99A1AF] text-xs leading-4" style="font-family: Arial, sans-serif;" x-text="content.description"></p>
                            
                            <!-- Type & Status -->
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-[#4A5565]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                    <span class="text-[#4A5565] text-xs" style="font-family: Arial, sans-serif;" x-text="content.type"></span>
                                </div>
                                <div class="w-2 h-2 rounded-full bg-[#6B7C4F]"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Footer -->
                    <div class="flex items-center gap-4 pt-3 border-t border-[#1E2939] text-xs text-[#4A5565]" style="font-family: Arial, sans-serif;">
                        <span x-text="content.sections + ' bagian'"></span>
                        <span>•</span>
                        <span x-text="content.words + ' kata'"></span>
                        <span>•</span>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span x-text="content.updated"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Right Column - Content Editor -->
        <div class="relative bg-[#111111] border border-[#1E2939] rounded-[14px] flex flex-col min-h-[848px]">
            <!-- Empty State -->
            <div x-show="!selectedContent" class="flex flex-col items-center justify-center h-full p-12">
                <svg class="w-16 h-16 text-[#364153] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-[#6A7282] text-sm text-center" style="font-family: Arial, sans-serif;">Pilih konten untuk mulai mengedit</p>
            </div>

            <!-- Content Editor View -->
            <template x-if="selectedContent">
                <div class="flex flex-col h-full">
                    <template x-for="content in contents.filter(c => c.id === selectedContent)" :key="content.id">
                        <div class="flex flex-col h-full">
                            <!-- Header - Read Only View -->
                            <div x-show="!editMode" class="bg-[#0A0A0A] border-b border-[#1E2939] px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-3xl" x-text="content.emoji"></span>
                                        <div>
                                            <h3 class="text-white text-lg font-bold mb-1" style="font-family: Arial, sans-serif;" x-text="content.title"></h3>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-[#6A7282]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M4 6h16M4 12h16M4 18h16"></path>
                                                </svg>
                                                <span class="text-[#6A7282] text-xs" style="font-family: Arial, sans-serif;" x-text="content.type"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button @click="editMode = true" class="bg-[#6B7C4F] hover:bg-[#5a6a42] text-white px-4 py-2 rounded-[10px] flex items-center gap-2 text-sm transition-colors" style="font-family: Arial, sans-serif;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                </div>
                            </div>

                            <!-- Scrollable Content Area - Read Only View -->
                            <div x-show="!editMode" class="flex-1 overflow-y-auto px-6 py-6 space-y-4 bg-[#0A0A0A]">
                                <!-- Dynamic Content Sections -->
                                <div class="space-y-3">
                                    <template x-for="section in editingSections" :key="section.id">
                                        <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4">
                                            <div class="flex items-start gap-3 mb-3">
                                                <svg class="w-4 h-4 text-[#6B7C4F] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <h4 class="text-white text-sm font-bold" style="font-family: Arial, sans-serif;" x-text="section.title"></h4>
                                            </div>
                                            <div class="space-y-2 text-[#D1D5DC] text-sm" style="font-family: Arial, sans-serif;">
                                                <template x-for="(item, idx) in section.items" :key="idx">
                                                    <p x-show="item && item.trim() !== ''" x-html="idx === 0 ? item : '• ' + item"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Footer - Read Only View -->
                            <div x-show="!editMode" class="border-t border-[#1E2939] bg-[#0A0A0A] px-6 py-3">
                                <div class="flex items-center justify-between text-xs text-[#6A7282]" style="font-family: Arial, sans-serif;">
                                    <div class="flex items-center gap-4">
                                        <span x-text="content.sections + ' bagian'"></span>
                                        <span>•</span>
                                        <span x-text="content.words + ' kata'"></span>
                                    </div>
                                    <span x-text="'Terakhir diubah: ' + content.updated"></span>
                                </div>
                            </div>

                            <!-- EDIT MODE VIEW -->
                            <template x-if="editMode">
                                <div class="absolute inset-0 bg-[#111111] border border-[#1E2939] rounded-[14px] overflow-hidden flex flex-col" style="font-family: Arial, sans-serif;">
                                    <!-- Header -->
                                    <div class="bg-[#0A0A0A] border-b border-[#1E2939] px-6 py-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="text-[30px] leading-[36px]" x-text="content.emoji"></span>
                                                <div>
                                                    <h3 class="text-white text-lg font-bold mb-1" x-text="'Edit Konten'"></h3>
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-[#6A7282]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M4 6h16M4 12h16M4 18h16"></path>
                                                        </svg>
                                                        <span class="text-[#6A7282] text-xs" x-text="content.type"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button @click="saveContent()" :disabled="saving" class="bg-[#6B7C4F] hover:bg-[#5a6a42] disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-[10px] flex items-center gap-2 text-sm transition-colors">
                                                    <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <div x-show="saving" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                                    <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
                                                </button>
                                                <button @click="editMode = false" :disabled="saving" class="bg-[#1A1A1A] hover:bg-[#252525] disabled:opacity-50 disabled:cursor-not-allowed border border-[#364153] text-white px-4 py-2 rounded-[10px] text-sm transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Scrollable Content Area -->
                                    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-4">
                                        <!-- Dynamic Sections Loop -->
                                        <template x-for="section in editingSections" :key="section.id">
                                            <div class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4">
                                                <!-- Section Header -->
                                                <div class="flex items-center justify-between gap-2 mb-3">
                                                    <input type="text" x-model="section.title" class="flex-1 bg-[#111111] border border-[#364153] rounded-[10px] px-3 py-2 text-white text-sm font-bold placeholder:text-[rgba(255,255,255,0.5)]" placeholder="Judul bagian...">
                                                    <button @click="removeSection(section.id)" class="bg-[rgba(251,44,54,0.1)] hover:bg-[rgba(251,44,54,0.15)] border border-[rgba(251,44,54,0.3)] text-[#FF6467] px-3 py-1.5 rounded-[10px] text-xs flex items-center gap-1.5 transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Hapus Bagian
                                                    </button>
                                                </div>
                                                <!-- Section Items -->
                                                <template x-for="(item, index) in section.items" :key="index">
                                                    <div class="flex items-start gap-2 mb-2">
                                                        <textarea x-model="section.items[index]" class="flex-1 bg-[#111111] border border-[#364153] rounded-[10px] px-3 py-2 text-[#D1D5DC] text-sm placeholder:text-[rgba(255,255,255,0.5)] resize-none h-[38px]" placeholder="Isi item..."></textarea>
                                                        <button @click="removeItem(section.id, index)" class="bg-[rgba(251,44,54,0.1)] hover:bg-[rgba(251,44,54,0.15)] border border-[rgba(251,44,54,0.3)] text-[#FF6467] p-2 rounded-[10px] transition-colors flex-shrink-0">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                                <!-- Add Item Button -->
                                                <button @click="addItemToSection(section.id)" class="bg-[#1A1A1A] hover:bg-[#252525] border border-[#364153] text-[#99A1AF] px-3 py-1.5 rounded-[10px] text-xs flex items-center gap-1.5 transition-colors mt-2">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Tambah Item
                                                </button>
                                            </div>
                                        </template>

                                        <!-- Inline Add New Section Form -->
                                        <div x-show="showAddSectionModal" x-cloak class="bg-[#0A0A0A] border border-[#1E2939] rounded-[10px] p-4">
                                            <!-- Header with Close Button -->
                                            <div class="flex items-center justify-between mb-3">
                                                <input type="text" x-model="newSectionTitle" class="flex-1 bg-[#111111] border border-[#364153] rounded-[10px] px-3 py-2 text-white text-sm font-bold placeholder:text-[rgba(255,255,255,0.5)]" placeholder="Judul bagian baru...">
                                                <button @click="showAddSectionModal = false; newSectionTitle = ''; newSectionItems = ['']" class="ml-2 text-[#99A1AF] hover:text-white transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Section Items -->
                                            <template x-for="(item, index) in newSectionItems" :key="index">
                                                <div class="flex items-start gap-2 mb-2">
                                                    <textarea x-model="newSectionItems[index]" class="flex-1 bg-[#111111] border border-[#364153] rounded-[10px] px-3 py-2 text-[#D1D5DC] text-sm placeholder:text-[rgba(255,255,255,0.5)] resize-none h-[38px]" placeholder="Isi item..."></textarea>
                                                    <button @click="removeModalItem(index)" x-show="newSectionItems.length > 1" class="bg-[rgba(251,44,54,0.1)] hover:bg-[rgba(251,44,54,0.15)] border border-[rgba(251,44,54,0.3)] text-[#FF6467] p-2 rounded-[10px] transition-colors flex-shrink-0">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>

                                            <!-- Add Item Button -->
                                            <button @click="addNewItemToModal()" class="bg-[#1A1A1A] hover:bg-[#252525] border border-[#364153] text-[#99A1AF] px-3 py-1.5 rounded-[10px] text-xs flex items-center gap-1.5 transition-colors mt-2">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.17" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Tambah Item
                                            </button>

                                            <!-- Save Button -->
                                            <button @click="addSection()" class="w-full bg-[#6B7C4F] hover:bg-[#5a6a42] text-white py-2.5 rounded-[10px] flex items-center justify-center gap-2 text-sm transition-colors mt-3">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Simpan Bagian Baru
                                            </button>
                                        </div>

                                        <!-- Add New Section Button -->
                                        <button x-show="!showAddSectionModal" @click="showAddSectionModal = true" class="w-full bg-[#6B7C4F] hover:bg-[#5a6a42] text-white py-2.5 rounded-[10px] flex items-center justify-center gap-2 text-sm transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Tambah Bagian Baru
                                        </button>
                                    </div>

                                    <!-- Footer -->
                                    <div class="border-t border-[#1E2939] bg-[#0A0A0A] px-6 py-4">
                                        <div class="flex items-center justify-between text-xs text-[#6A7282]">
                                            <div class="flex items-center gap-4">
                                                <span x-text="content.sections + ' bagian'"></span>
                                                <span>•</span>
                                                <span x-text="content.words + ' kata'"></span>
                                            </div>
                                            <span x-text="'Terakhir diubah: ' + content.updated"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Warning Info Box -->
    <div class="mt-6 bg-[rgba(240,177,0,0.1)] border border-[rgba(240,177,0,0.3)] rounded-[14px] p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-[#F0B100] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.67" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="text-[#F0B100] text-sm font-bold mb-1" style="font-family: Arial, sans-serif;">Informasi Penting</h4>
                <p class="text-[rgba(255,240,133,0.8)] text-sm leading-relaxed" style="font-family: Arial, sans-serif;">
                    Perubahan konten akan langsung ditampilkan di aplikasi mobile setelah disimpan. Pastikan semua informasi sudah benar sebelum menyimpan perubahan.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
