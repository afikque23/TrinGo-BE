@extends('layouts.sidebar')

@section('title', 'Manajemen Notifikasi - TringGo')
@section('page-title', 'Manajemen Notifikasi')

@section('content')
<div id="notification-root" x-data="notificationManager" data-templates='@json($templates)' data-categories='@json($categories)' class="relative">

<div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-6">
    <p class="text-[#99A1AF] text-sm" style="font-family: Arial, sans-serif;">Konfigurasi jenis dan parameter notifikasi ke pengguna aplikasi</p>
    <button @click="showAddModal = true" class="h-11 px-4 bg-[#6B7C4F] text-white text-base rounded-[10px] hover:bg-[#5A6A40] transition-colors flex items-center gap-2 shrink-0" style="font-family: Arial, sans-serif;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Notifikasi
    </button>
</div>

<div class="space-y-4">
    <template x-for="template in templates" :key="template.id">
        <div class="bg-[#111111] border border-[#1E2939] rounded-[14px] p-[21px]">
            <!-- Card Header: info & actions -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-white mb-1" style="font-family: Arial, sans-serif;" x-text="template.name"></h3>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="font-family: Arial, sans-serif;">
                        <span class="text-[#6A7282]" x-text="'Kategori: ' + (template.category ? template.category.name : '-')"></span>
                        <span class="text-[#6A7282] hidden sm:inline">•</span>
                        <span :style="'color: ' + getPriorityColor(template.priority)" x-text="getPriorityLabel(template.priority)"></span>
                        <span class="text-[#6A7282] hidden sm:inline">•</span>
                        <span class="text-[#6A7282]" x-text="getChannelLabel(template.channel)"></span>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="testPush(template.id)" :disabled="testingNotification === template.id" class="h-[38px] px-3 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors flex items-center gap-2 disabled:opacity-50" style="font-family: Arial, sans-serif;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span x-text="testingNotification === template.id ? 'Mengirim...' : 'Test'"></span>
                    </button>
                    <button @click="toggleActive(template.id)" :class="template.is_active ? 'bg-[#6B7C4F]' : 'bg-[#364153]'" class="w-12 h-6 rounded-full relative flex items-center transition-colors shrink-0">
                        <div :class="template.is_active ? 'right-1' : 'left-1'" class="absolute w-4 h-4 bg-white rounded-full transition-all"></div>
                    </button>
                    <button @click="openEdit(template)" class="h-[38px] px-3 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#2A2A2A] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </button>
                    <button @click="openDelete(template)" class="h-[38px] px-3 bg-[#1A1A1A] border border-[#364153] text-white text-sm rounded-[10px] hover:bg-[#FB2C36] hover:border-[#FB2C36] transition-colors flex items-center gap-2" style="font-family: Arial, sans-serif;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Hapus
                    </button>
                </div>
            </div>

            <div class="bg-[#0A0A0A] border border-[#364153] rounded-[10px] p-[13px] mb-2">
                <p class="text-xs text-[#6A7282] uppercase tracking-wider mb-1" style="font-family: Arial, sans-serif; letter-spacing: 0.3px;">Preview Pesan Template</p>
                <p class="text-sm text-white" style="font-family: Arial, sans-serif;" x-text="template.message_template"></p>
            </div>
        </div>
    </template>
</div>

<!-- Modal Add -->
@include('admin.manajemen_notifikasi.partials._modal_add_new')

<!-- Modal Edit -->
@include('admin.manajemen_notifikasi.partials._modal_edit_new')

<!-- Modal Delete -->
@include('admin.manajemen_notifikasi.partials._modal_delete')

<!-- Modal Variable Info -->
@include('admin.manajemen_notifikasi.partials._modal_variable_info')

<!-- Modal Test Push -->
@include('admin.manajemen_notifikasi.partials._modal_test_push')

<script>
const _notificationRoot = document.getElementById('notification-root');
const _templatesData = _notificationRoot ? JSON.parse(_notificationRoot.getAttribute('data-templates') || '[]') : [];
const _categoriesData = _notificationRoot ? JSON.parse(_notificationRoot.getAttribute('data-categories') || '[]') : [];
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationManager', () => ({
        showDeleteModal: false,
        showAddModal: false,
        showEditModal: false,
        showVariableInfoModal: false,
        showTestPushModal: false,
        selectedNotification: null,
        editingId: null,
        testingNotification: null,
        selectedTemplateId: null,
        testTargetUserId: null,
        users: @json($users),
        templates: _templatesData,
        categories: _categoriesData,
        openEdit(template) {
            this.selectedNotification = template;
            this.showEditModal = true;
        },
        openDelete(template) {
            this.selectedNotification = template;
            this.showDeleteModal = true;
        },
        testPush(templateId) {
            this.selectedTemplateId = templateId;
            this.testTargetUserId = {{ Auth::id() }}; // Default to current admin
            this.showTestPushModal = true;
        },
        getSelectedUserName() {
            if (!this.testTargetUserId) return '';
            
            const user = this.users.find(u => u.id == this.testTargetUserId);
            return user ? user.name : 'Unknown User';
        },
        async sendTestPush() {
            if (!this.testTargetUserId) {
                alert('❌ Pilih user terlebih dahulu!');
                return;
            }

            this.testingNotification = this.selectedTemplateId;
            
            try {
                // Send to selected user (all devices)
                const url = '/admin/notifications/' + this.selectedTemplateId + '/test-push';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: this.testTargetUserId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showTestPushModal = false;
                    
                    let msg = '✅ Notifikasi test berhasil dikirim!\n\n';
                    msg += '� Template: ' + (data.data.template_name || 'N/A') + '\n';
                    msg += '👤 Target: ' + (data.data.target_user || 'Unknown') + '\n';
                    msg += '💬 Preview: ' + data.data.message_preview?.substring(0, 100) + '...\n\n';
                    
                    if (data.data.push_sent && data.data.push_result?.success) {
                        msg += '✅ Push notification berhasil dikirim!\n';
                        msg += '📱 Total device: ' + (data.data.push_result.total_sent || 0) + ' device';
                    } else if (data.data.push_sent && !data.data.push_result?.success) {
                        msg += '⚠️ Push notification gagal dikirim.\n';
                        msg += 'Kemungkinan: User belum login ke mobile app atau FCM token tidak valid.';
                    } else {
                        msg += '📋 Notifikasi tersimpan di database (channel: ' + data.data.channel + ')';
                    }
                    
                    alert(msg);
                } else {
                    alert('❌ Gagal mengirim notifikasi test: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('❌ Error: ' + error.message);
            }
            
            this.testingNotification = null;
        },
        toggleActive(templateId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/notifications/' + templateId + '/toggle';
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        },
        getPriorityColor(priority) {
            const colors = { 'low': '#6A7282', 'normal': '#6A7282', 'high': '#FDC700', 'critical': '#FF6467' };
            return colors[priority] || '#6A7282';
        },
        getPriorityLabel(priority) {
            const labels = { 'low': 'Rendah', 'normal': 'Normal', 'high': 'Tinggi', 'critical': 'Kritikal' };
            return labels[priority] || priority;
        },
        getChannelLabel(channel) {
            const labels = { 'in_app': 'In-App', 'push': 'Push', 'email': 'Email' };
            return labels[channel] || channel;
        }
    }));
});
</script>

</div>
@endsection
