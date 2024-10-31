<?php

namespace App\Observers;

use App\Models\Installation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class InstallationObserver
{
    public function creating(Installation $installation)
    {
        if ($installation->license_id) {
            // Get the license
            $license = DB::table('licenses')->where('id', $installation->license_id)->first();

            Log::info('Creating Installation - License Check', [
                'license_id' => $installation->license_id,
                'current_seats_used' => $license->seats_used ?? 0,
                'seats_available' => $license->seats_available ?? 'unlimited'
            ]);

            // Check seat availability
            if ($license && $license->seats_available !== null && $license->seats_used >= $license->seats_available) {
                throw ValidationException::withMessages([
                    'license_id' => "No available seats for this license. Maximum seats ({$license->seats_available}) already used.",
                ]);
            }
        }
    }

    public function created(Installation $installation)
    {
        if ($installation->license_id) {
            // Use a cache lock to prevent duplicate processing
            $lockKey = "installation_created_{$installation->id}";

            if (!Cache::has($lockKey)) {
                try {
                    Cache::put($lockKey, true, now()->addMinutes(1));

                    DB::beginTransaction();

                    $affected = DB::table('licenses')
                        ->where('id', $installation->license_id)
                        ->increment('seats_used');

                    Log::info('Installation Created - Seats Updated', [
                        'license_id' => $installation->license_id,
                        'rows_affected' => $affected,
                        'installation_id' => $installation->id
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to update seats_used', [
                        'error' => $e->getMessage(),
                        'license_id' => $installation->license_id
                    ]);
                }
            }
        }
    }

    public function deleted(Installation $installation)
    {
        if ($installation->license_id) {
            // Use a cache lock to prevent duplicate processing
            $lockKey = "installation_deleted_{$installation->id}";

            if (!Cache::has($lockKey)) {
                try {
                    Cache::put($lockKey, true, now()->addMinutes(1));

                    DB::beginTransaction();

                    $affected = DB::table('licenses')
                        ->where('id', $installation->license_id)
                        ->where('seats_used', '>', 0)  // Prevent negative values
                        ->decrement('seats_used');

                    Log::info('Installation Deleted - Seats Updated', [
                        'license_id' => $installation->license_id,
                        'rows_affected' => $affected,
                        'installation_id' => $installation->id
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to update seats_used', [
                        'error' => $e->getMessage(),
                        'license_id' => $installation->license_id
                    ]);
                }
            }
        }
    }
}
