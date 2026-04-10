<?php

use App\Http\Controllers\Api\V1\Modules\Payment\PaymentApiController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->away('https://pixelpressdtf.com/');
});


Route::get('/run-command', function () {
//    \Illuminate\Support\Facades\Artisan::call('db:seed');
//    \Illuminate\Support\Facades\Artisan::call('migrate');
//    \Illuminate\Support\Facades\Artisan::call('storage:link');
//    \Illuminate\Support\Facades\Artisan::call('cache:clear');
//    \Illuminate\Support\Facades\Artisan::call('config:cache');
//    \Illuminate\Support\Facades\Artisan::call('view:clear');
//    \Illuminate\Support\Facades\Artisan::call('route:clear');
});

Route::get('/force-run-migrations', function () {
    try {
        // Clear all caches first
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        $output = [];

        // Get pending migrations
        $pending = [];
        $allMigrations = DB::table('migrations')->pluck('migration')->toArray();

        $migrationPath = database_path('migrations');
        $files = scandir($migrationPath);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrationName = str_replace('.php', '', $file);
                if (!in_array($migrationName, $allMigrations)) {
                    $pending[] = $file;
                }
            }
        }

        $output['pending_migrations'] = $pending;

        if (empty($pending)) {
            return response()->json([
                'success' => true,
                'message' => 'No pending migrations found',
                'output' => $output,
            ]);
        }

        // Run migrations
        Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = Artisan::output();

        $output['migration_output'] = $migrationOutput;

        // Verify migrations ran
        $newMigrations = DB::table('migrations')
            ->whereIn('migration', array_map(function($file) {
                return str_replace('.php', '', $file);
            }, $pending))
            ->get();

        $output['newly_ran'] = $newMigrations->pluck('migration');

        return response()->json([
            'success' => true,
            'message' => 'Migrations executed',
            'output' => $output,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Migration failed',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});


// Alternative: More detailed debug route
Route::get('/debug-migrations', function () {
    $info = [];

    // 1. Check database connection
    try {
        DB::connection()->getPdo();
        $info['database'] = [
            'connected' => true,
            'name' => DB::connection()->getDatabaseName(),
            'driver' => DB::connection()->getDriverName(),
        ];
    } catch (\Exception $e) {
        $info['database'] = [
            'connected' => false,
            'error' => $e->getMessage(),
        ];
        return response()->json($info);
    }

    // 2. Check if migrations table exists
    try {
        $info['migrations_table_exists'] = DB::getSchemaBuilder()->hasTable('migrations');
    } catch (\Exception $e) {
        $info['migrations_table_exists'] = false;
        $info['migrations_table_error'] = $e->getMessage();
    }

    // 3. Get all migration files
    $migrationPath = database_path('migrations');
    $migrationFiles = [];

    if (is_dir($migrationPath)) {
        $files = scandir($migrationPath);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrationFiles[] = $file;
            }
        }
    }

    $info['migration_files'] = [
        'path' => $migrationPath,
        'count' => count($migrationFiles),
        'files' => $migrationFiles,
    ];

    // 4. Get executed migrations
    if ($info['migrations_table_exists']) {
        $executedMigrations = DB::table('migrations')->get();
        $info['executed_migrations'] = [
            'count' => $executedMigrations->count(),
            'list' => $executedMigrations->pluck('migration')->toArray(),
        ];
    }

    // 5. Check for pending migrations
    try {
        Artisan::call('migrate:status');
        $info['migrate_status'] = Artisan::output();
    } catch (\Exception $e) {
        $info['migrate_status_error'] = $e->getMessage();
    }

    // 6. Try to run migrations
    try {
        Artisan::call('migrate', ['--force' => true, '--pretend' => true]);
        $info['migrate_pretend'] = Artisan::output();
    } catch (\Exception $e) {
        $info['migrate_pretend_error'] = $e->getMessage();
    }

    return response()->json($info, 200, [], JSON_PRETTY_PRINT);
});


Auth::routes([
    'register' => false,
    'verify'   => false,
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
