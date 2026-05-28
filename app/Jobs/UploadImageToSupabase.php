<?php

namespace App\Jobs;

use App\Models\PostImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadImageToSupabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre de tentatives max en cas d'échec.
     */
    public int $tries = 3;

    /**
     * Timeout du job en secondes.
     */
    public int $timeout = 120;

    public function __construct(
        private readonly int    $postImageId,
        private readonly string $tempPath,
    ) {}

    public function handle(): void
    {
        $postImage = PostImage::find($this->postImageId);

        if (! $postImage) {
            Log::warning("UploadImageToSupabase: PostImage #{$this->postImageId} introuvable.");
            $this->cleanup();
            return;
        }

        // Récupère le contenu du fichier temporaire local
        $fileContents = Storage::disk('local')->get($this->tempPath);

        if (! $fileContents) {
            Log::error("UploadImageToSupabase: fichier temporaire introuvable : {$this->tempPath}");
            $this->cleanup();
            return;
        }

        // Détermine l'extension depuis le chemin temporaire
        $extension = pathinfo($this->tempPath, PATHINFO_EXTENSION);
        $supabasePath = 'posts/' . basename($this->tempPath, '.' . $extension) . '.' . $extension;

        // Upload vers Supabase
        Storage::disk('supabase')->put($supabasePath, $fileContents);

        // Met à jour le chemin en DB avec le chemin Supabase final
        $postImage->update(['image_path' => $supabasePath]);

        // Supprime le fichier temporaire local
        $this->cleanup();
    }

    /**
     * En cas d'échec définitif, on garde l'entrée en DB mais on log l'erreur.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UploadImageToSupabase failed pour PostImage #{$this->postImageId}: " . $exception->getMessage());
        $this->cleanup();
    }

    private function cleanup(): void
    {
        if (Storage::disk('local')->exists($this->tempPath)) {
            Storage::disk('local')->delete($this->tempPath);
        }
    }
}
