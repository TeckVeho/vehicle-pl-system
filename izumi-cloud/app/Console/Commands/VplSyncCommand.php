<?php

namespace App\Console\Commands;

use App\Services\Vpl\VplClient;
use App\Services\Vpl\UserSyncService;
use App\Services\Vpl\CourseSyncService;
use App\Services\Vpl\VehicleSyncService;
use App\Services\Vpl\DriverSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to sync data from Izumi Cloud → VPL.
 *
 * Usage:
 *   php artisan vpl:sync                    # sync all entities (users → courses → vehicles → drivers)
 *   php artisan vpl:sync --entity=users     # sync users only
 *   php artisan vpl:sync --entity=courses   # sync courses only
 *   php artisan vpl:sync --entity=vehicles  # sync vehicles only
 *   php artisan vpl:sync --entity=drivers   # sync drivers only
 *   php artisan vpl:sync --dry-run          # build payload without sending
 *
 * Reference: Issue #967 (users, courses) / Issue #968 (vehicles, drivers)
 */
class VplSyncCommand extends Command
{
    protected $signature = 'vpl:sync
                            {--entity= : Entity to sync (users, courses, vehicles, drivers). Omit for all.}
                            {--dry-run : Build payload and log it, but do not call VPL API.}';

    protected $description = 'Sync master data from Izumi Cloud to VPL (vehicle-pl-system)';

    protected string $logChannel;

    public function handle(): int
    {
        $this->logChannel = config('vpl.log_channel', 'vpl-sync');

        $entity = $this->option('entity');
        $dryRun = (bool) $this->option('dry-run');

        $entities = $this->resolveEntities($entity);

        if (empty($entities)) {
            $this->error("Unknown entity: {$entity}. Valid: users, courses, vehicles, drivers");
            return self::FAILURE;
        }

        $this->info('=== VPL Sync Start ===');
        $this->log('info', '--- VPL Sync Start ---', ['entities' => $entities, 'dry_run' => $dryRun]);

        if ($dryRun) {
            $this->warn('[DRY-RUN] Payload will be built but NOT sent to VPL.');
        }

        $client = $dryRun ? null : new VplClient();
        $allSuccess = true;

        foreach ($entities as $ent) {
            try {
                $result = $this->syncEntity($ent, $client, $dryRun);
                $this->displayResult($ent, $result, $dryRun);
            } catch (\Throwable $e) {
                $allSuccess = false;
                $this->error("[{$ent}] Failed: {$e->getMessage()}");
                $this->log('error', "[{$ent}] Exception", [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info('=== VPL Sync End ===');
        $this->log('info', '--- VPL Sync End ---');

        return $allSuccess ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Determine which entities to sync.
     */
    protected function resolveEntities(?string $entity): array
    {
        $all = ['users', 'courses', 'vehicles', 'drivers'];

        if (!$entity) {
            return $all;
        }

        $entity = strtolower(trim($entity));

        return in_array($entity, $all, true) ? [$entity] : [];
    }

    /**
     * Dispatch sync to the appropriate service.
     */
    protected function syncEntity(string $entity, ?VplClient $client, bool $dryRun): array
    {
        $this->info("→ Syncing: {$entity}");

        switch ($entity) {
            case 'users':
                $service = new UserSyncService();
                break;
            case 'courses':
                $service = new CourseSyncService();
                break;
            case 'vehicles':
                $service = new VehicleSyncService();
                break;
            case 'drivers':
                $service = new DriverSyncService();
                break;
            default:
                throw new \RuntimeException("No service for entity: {$entity}");
        }

        if ($dryRun) {
            $data = $service->buildPayload();
            $key = $entity; // 'users', 'courses', 'vehicles', or 'drivers'
            $this->log('info', "[{$entity}] Dry-run payload", [
                'count'   => count($data[$key] ?? []),
                'skipped' => count($data['skipped'] ?? []),
                'sample'  => array_slice($data[$key] ?? [], 0, 3), // first 3 for preview
            ]);
            return [
                'total'    => count($data[$key] ?? []) + count($data['skipped'] ?? []),
                'synced'   => count($data[$key] ?? []),
                'skipped'  => $data['skipped'] ?? [],
                'response' => '[DRY-RUN — not sent]',
            ];
        }

        return $service->sync($client);
    }

    /**
     * Print summary to console.
     */
    protected function displayResult(string $entity, array $result, bool $dryRun): void
    {
        $prefix  = $dryRun ? '[DRY-RUN] ' : '';
        $total   = $result['total'] ?? ($result['synced'] + count($result['skipped'] ?? []));
        $synced  = $result['synced'];
        $skipped = count($result['skipped'] ?? []);

        $this->info("{$prefix}  {$entity}: {$total} record(s) total | ✓ {$synced} synced | ⚠ {$skipped} skipped");

        if (!empty($result['skipped']) && $this->getOutput()->isVerbose()) {
            foreach ($result['skipped'] as $skip) {
                $this->line("    - ID {$skip['id']}: {$skip['reason']}");
            }
        }
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->{$level}("[VplSync] {$message}", $context);
    }
}
