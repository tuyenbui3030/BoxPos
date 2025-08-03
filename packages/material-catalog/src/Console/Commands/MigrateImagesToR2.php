<?php

namespace Packages\MaterialCatalog\Console\Commands;

use Illuminate\Console\Command;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialCatalog\Services\CloudflareR2Service;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToR2 extends Command
{
    protected $signature = 'material:migrate-images-to-r2 {--dry-run : Show what would be migrated without actually doing it}';
    protected $description = 'Migrate existing material images from local storage to Cloudflare R2';

    public function handle(CloudflareR2Service $r2Service)
    {
        if (!$r2Service->isConfigured()) {
            $this->error('R2 is not properly configured. Please check your .env file.');
            return 1;
        }

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No actual changes will be made');
        }

        $materials = BuildingMaterial::whereNotNull('images')->get();
        
        if ($materials->isEmpty()) {
            $this->info('No materials with images found.');
            return 0;
        }

        $this->info("Found {$materials->count()} materials with images");
        
        $bar = $this->output->createProgressBar($materials->count());
        $bar->start();

        $migrated = 0;
        $errors = 0;

        foreach ($materials as $material) {
            try {
                $images = $material->images;
                $newImages = [];
                $hasChanges = false;

                foreach ($images as $image) {
                    // Skip if already in new format
                    if (is_array($image) && isset($image['url'])) {
                        $newImages[] = $image;
                        continue;
                    }

                    // Check if it's a local file path
                    if (is_string($image) && Storage::disk('public')->exists($image)) {
                        if (!$dryRun) {
                            // Create a temporary uploaded file from the existing file
                            $filePath = Storage::disk('public')->path($image);
                            $fileName = basename($image);
                            $mimeType = mime_content_type($filePath);
                            
                            // Create a temporary file
                            $tempFile = tmpfile();
                            $tempPath = stream_get_meta_data($tempFile)['uri'];
                            copy($filePath, $tempPath);
                            
                            // Create UploadedFile instance
                            $uploadedFile = new \Illuminate\Http\UploadedFile(
                                $tempPath,
                                $fileName,
                                $mimeType,
                                null,
                                true
                            );

                            // Upload to R2
                            $uploadedImage = $r2Service->uploadSingleImage($uploadedFile, 'materials');
                            
                            if ($uploadedImage) {
                                $newImages[] = $uploadedImage;
                                $hasChanges = true;
                                
                                // Delete old local file
                                Storage::disk('public')->delete($image);
                            } else {
                                $newImages[] = $image; // Keep original if upload failed
                            }
                            
                            fclose($tempFile);
                        } else {
                            $this->line("\nWould migrate: {$image} for material {$material->name}");
                            $hasChanges = true;
                        }
                    } else {
                        // Keep as is if not a local file
                        $newImages[] = $image;
                    }
                }

                if ($hasChanges && !$dryRun) {
                    $material->update(['images' => $newImages]);
                    $migrated++;
                }

            } catch (\Exception $e) {
                $this->error("\nError migrating images for material {$material->name}: " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info('Dry run completed. Use without --dry-run to perform actual migration.');
        } else {
            $this->info("Migration completed!");
            $this->info("Successfully migrated: {$migrated} materials");
            if ($errors > 0) {
                $this->warn("Errors encountered: {$errors} materials");
            }
        }

        return 0;
    }
}