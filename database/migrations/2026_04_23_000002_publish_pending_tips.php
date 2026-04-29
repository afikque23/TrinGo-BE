<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing data created before auto-publish: make it visible in public listings.
        DB::table('tips')
            ->where('status', 'pending_review')
            ->update([
                'status' => 'published',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // No-op: we can't safely determine which rows were previously pending.
    }
};
