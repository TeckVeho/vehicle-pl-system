<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * Không gọi migrate/seed ở đây: trait RefreshDatabase của Laravel
     * đã chạy migrate:fresh + transaction (hoặc restore PDO với SQLite :memory:).
     * Gọi migrate ở đây trùng với RefreshDatabase → SQLite báo "already in active transaction",
     * và với ParaTest (nhiều process) dễ lệch trạng thái connection.
     *
     * Mỗi worker ParaTest là một PHP process riêng → static state và :memory: tách biệt.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $envFile = file_exists(__DIR__.'/../.env.testing') ? '.env.testing' : '.env';
        $app->loadEnvironmentFrom($envFile);

        $app->make(Kernel::class)->bootstrap();

        $conn = Storage::disk('database');
        if (! $conn->exists('database.sqlite')) {
            $conn->put('database.sqlite', '');
        }
        // Chỉ tạo file khi test không dùng SQLite in-memory (phpunit: DB_DATABASE=:memory:).
        $sqliteDb = (string) config('database.connections.sqlite.database', '');
        if ($sqliteDb !== ':memory:' && ! $conn->exists('testing.sqlite')) {
            $conn->put('testing.sqlite', '');
        }

        return $app;
    }
}
