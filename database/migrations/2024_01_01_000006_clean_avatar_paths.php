<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Strip any path prefix from avatar column — store only the bare filename.
     * Fixes records that stored "storage/avatars/1_xxx.webp" or full URLs.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('avatar')
            ->where('avatar', '!=', '')
            ->get(['id', 'avatar'])
            ->each(function ($user) {
                $filename = basename($user->avatar);

                // Only update if the stored value contains a slash (i.e. it's a path not a bare filename)
                if (str_contains($user->avatar, '/') || str_contains($user->avatar, '\\')) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['avatar' => $filename]);
                }
            });
    }

    public function down(): void
    {
        // Not reversible — original paths are gone
    }
};