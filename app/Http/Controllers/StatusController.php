<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class StatusController extends Controller
{
    public function __invoke(Migrator $migrator): View
    {
        return view('welcome', [
            'application' => $this->applicationInfo(),
            'drivers' => $this->driverInfo(),
            'database' => $this->databaseInfo(),
            'migrations' => $this->migrationInfo($migrator),
            'cron' => $this->cronInfo(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function applicationInfo(): array
    {
        return [
            'Laravel' => app()->version(),
            'PHP' => PHP_VERSION,
            'Environment' => app()->environment(),
            'Debug mode' => config('app.debug') ? 'enabled' : 'disabled',
            'URL' => (string) config('app.url'),
            'Timezone' => (string) config('app.timezone'),
            'Locale' => app()->getLocale(),
            'Server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'Config cached' => app()->configurationIsCached() ? 'yes' : 'no',
            'Routes cached' => app()->routesAreCached() ? 'yes' : 'no',
            'Events cached' => app()->eventsAreCached() ? 'yes' : 'no',
            'Maintenance mode' => app()->isDownForMaintenance() ? 'yes' : 'no',
            'Storage path' => storage_path(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function driverInfo(): array
    {
        return [
            'Database' => (string) config('database.default'),
            'Cache' => (string) config('cache.default'),
            'Queue' => (string) config('queue.default'),
            'Session' => (string) config('session.driver'),
            'Mail' => (string) config('mail.default'),
            'Filesystem' => (string) config('filesystems.default'),
            'Broadcast' => (string) config('broadcasting.default'),
        ];
    }

    /**
     * @return array{connection: string, driver: string, connected: bool, version: ?string, error: ?string}
     */
    private function databaseInfo(): array
    {
        $connection = (string) config('database.default');
        $driver = (string) config("database.connections.{$connection}.driver");

        try {
            $version = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);

            return [
                'connection' => $connection,
                'driver' => $driver,
                'connected' => true,
                'version' => is_string($version) ? $version : null,
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'connection' => $connection,
                'driver' => $driver,
                'connected' => false,
                'version' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{command: string, stores: array<string, array{available: bool, timestamp: ?string, ago: ?string, stale: bool, error: ?string}>}
     */
    private function cronInfo(): array
    {
        $stores = [];

        foreach ((array) config('cron.stores') as $store) {
            $stores[$store] = $this->cronStoreInfo($store);
        }

        return [
            'command' => 'cron:write-timestamp',
            'stores' => $stores,
        ];
    }

    /**
     * @return array{available: bool, timestamp: ?string, ago: ?string, stale: bool, error: ?string}
     */
    private function cronStoreInfo(string $store): array
    {
        try {
            $value = Cache::store($store)->get((string) config('cron.cache_key'));

            if ($value === null) {
                return [
                    'available' => false,
                    'timestamp' => null,
                    'ago' => null,
                    'stale' => true,
                    'error' => 'No timestamp recorded yet.',
                ];
            }

            $ranAt = Carbon::parse($value);

            return [
                'available' => true,
                'timestamp' => $ranAt->toIso8601String(),
                'ago' => $ranAt->diffForHumans(),
                'stale' => $ranAt->lessThan(now()->subMinutes(2)),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'available' => false,
                'timestamp' => null,
                'ago' => null,
                'stale' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{available: bool, ran: int, pending: int, items: list<array{name: string, status: string, batch: ?int}>, error: ?string}
     */
    private function migrationInfo(Migrator $migrator): array
    {
        try {
            if (! $migrator->repositoryExists()) {
                return [
                    'available' => false,
                    'ran' => 0,
                    'pending' => 0,
                    'items' => [],
                    'error' => 'The migrations table does not exist yet.',
                ];
            }

            $ran = $migrator->getRepository()->getRan();
            $batches = $migrator->getRepository()->getMigrationBatches();
            $paths = array_merge($migrator->paths(), [database_path('migrations')]);

            $items = [];

            foreach ($migrator->getMigrationFiles($paths) as $file) {
                $name = $migrator->getMigrationName($file);
                $hasRun = in_array($name, $ran, true);

                $items[] = [
                    'name' => $name,
                    'status' => $hasRun ? 'Ran' : 'Pending',
                    'batch' => $hasRun ? ($batches[$name] ?? null) : null,
                ];
            }

            $ranCount = count(array_filter($items, fn (array $item): bool => $item['status'] === 'Ran'));

            return [
                'available' => true,
                'ran' => $ranCount,
                'pending' => count($items) - $ranCount,
                'items' => $items,
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'available' => false,
                'ran' => 0,
                'pending' => 0,
                'items' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
}
