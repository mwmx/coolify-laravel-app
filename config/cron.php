<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cron Timestamp Storage
    |--------------------------------------------------------------------------
    |
    | The scheduled cron:write-timestamp command writes the current ISO 8601
    | timestamp to each of these cache stores every minute, and the status
    | dashboard reads it back to show when the scheduler last ran. Using
    | shared cache backends (rather than a local file) means the scheduler
    | and web containers observe the same value.
    |
    */

    'cache_key' => env('CRON_CACHE_KEY', 'cron:last-run'),

    'stores' => ['redis', 'memcached'],

];
