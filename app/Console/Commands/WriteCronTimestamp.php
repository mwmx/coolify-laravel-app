<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class WriteCronTimestamp extends Command
{
    protected $signature = 'cron:write-timestamp';

    protected $description = 'Write the current ISO 8601 timestamp to the cron cache stores.';

    public function handle(): int
    {
        $timestamp = now()->toIso8601String();
        $key = (string) config('cron.cache_key');

        foreach ((array) config('cron.stores') as $store) {
            try {
                Cache::store($store)->forever($key, $timestamp);
            } catch (Throwable $e) {
                $this->warn("Failed to write timestamp to [{$store}]: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
