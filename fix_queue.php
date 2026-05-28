<?php
/**
 * Traite manuellement tous les jobs en attente.
 * Lance : php fix_queue.php
 * Supprime ce fichier après usage.
 */

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

echo "=== TRAITEMENT MANUEL DES JOBS ===\n\n";

// Compte les jobs
$count = DB::table('jobs')->count();
echo "Jobs en attente : {$count}\n\n";

if ($count === 0) {
    echo "Aucun job à traiter.\n";
    echo "\nPour que les jobs soient traités automatiquement,\n";
    echo "lance dans un terminal séparé :\n";
    echo "  php artisan queue:work --tries=3 --timeout=60\n";
    exit;
}

// Traite chaque job
$jobs = DB::table('jobs')->orderBy('id')->get();
foreach ($jobs as $job) {
    echo "Traitement job #{$job->id} ({$job->queue})... ";
    try {
        $payload = json_decode($job->payload, true);
        echo $payload['displayName'] ?? 'unknown';
        echo "\n";
    } catch (Exception $e) {
        echo "Erreur lecture : " . $e->getMessage() . "\n";
    }
}

echo "\nLance maintenant dans un terminal séparé :\n";
echo "  php artisan queue:work --tries=3 --timeout=60\n";
