<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

it('writes the current ISO 8601 timestamp to each configured store', function () {
    config([
        'cron.stores' => ['array'],
        'cron.cache_key' => 'cron:last-run',
    ]);

    $now = Carbon::parse('2026-07-02T10:20:30+00:00');
    Carbon::setTestNow($now);

    $this->artisan('cron:write-timestamp')->assertSuccessful();

    expect(Cache::store('array')->get('cron:last-run'))->toBe($now->toIso8601String());

    Carbon::setTestNow();
});
