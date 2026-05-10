@extends('layouts.sidebar')

@section('title', 'Riwayat Perubahan Fuzzy - MotoTracker Admin')
@section('page-title', 'Riwayat Perubahan Fuzzy')

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
  [class] { font-family: 'Inter', Arial, sans-serif; }
  .audit-table th { padding: 9px 16px 9px 0; font-size:11px; color:#4a5565; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid #1e2939; font-weight:600; }
  .audit-table td { padding: 11px 16px 11px 0; font-size:12px; border-bottom:1px solid #1e293918; color:#99a1af; }
  .audit-table tbody tr:hover td { background:#ffffff03; }
</style>
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-white text-2xl font-bold mb-1">Riwayat Perubahan</h2>
            <p class="text-[#99a1af] text-sm">Log perubahan konfigurasi fuzzy terbaru</p>
        </div>
        <a href="{{ route('admin.fuzzy.index') }}" class="inline-flex items-center gap-2 text-xs text-[#6a7282] hover:text-white border border-[#1e2939] hover:border-[#364153] px-4 py-2 rounded-[10px] transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <div class="bg-[#111111] border border-[#1e2939] rounded-[14px] p-6">
        <div class="overflow-x-auto">
            <table class="audit-table w-full">
                <thead>
                    <tr>
                        @foreach(['Waktu','Admin','Komponen','Yang Diubah','Perubahan'] as $h)
                            <th class="text-left">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="text-[#4a5565] whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-[#6a7282]">{{ $log->admin->email ?? '-' }}</td>
                            <td><span class="text-xs text-[#99a1af] bg-[#1e2939] px-2 py-0.5 rounded">{{ $log->component_name }} ({{ $log->motor_type }})</span></td>
                            <td>{{ $log->field_changed }}</td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[#4a5565]">{{ $log->old_value ?? '-' }}</span>
                                    <span class="text-[#364153]">&rarr;</span>
                                    <span class="text-[#d1d5dc]">{{ $log->new_value ?? '-' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-[#4a5565] text-sm">Belum ada riwayat perubahan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>
@endsection
