<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\Setting;

class MigrateImagesToPublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate-to-public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all images from storage/app/public to public/images folder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting image migration from storage to public...');

        $migratedCount = 0;
        $errorCount = 0;

        // Migrate product images
        $this->info('Migrating product images...');
        $products = Product::whereNotNull('image')->get();

        foreach ($products as $product) {
            $oldPath = $product->image;

            // Check if already migrated (new format)
            if (strpos($oldPath, 'images/products/') === 0) {
                $this->line("Skipping {$product->name} - already migrated");
                continue;
            }

            // Old path in storage
            $sourceFile = storage_path('app/public/' . $oldPath);

            if (!File::exists($sourceFile)) {
                $this->warn("Source file not found: {$sourceFile}");
                $errorCount++;
                continue;
            }

            // New path in public
            $filename = basename($oldPath);
            $newFilename = time() . '_' . uniqid() . '_' . $filename;
            $destinationFolder = public_path('images/products');
            $destinationFile = $destinationFolder . '/' . $newFilename;

            // Ensure destination folder exists
            if (!File::exists($destinationFolder)) {
                File::makeDirectory($destinationFolder, 0755, true);
            }

            // Copy file
            try {
                File::copy($sourceFile, $destinationFile);

                // Update database
                $product->image = 'images/products/' . $newFilename;
                $product->save();

                $this->info("✓ Migrated: {$product->name}");
                $migratedCount++;

            } catch (\Exception $e) {
                $this->error("✗ Failed to migrate {$product->name}: " . $e->getMessage());
                $errorCount++;
            }
        }

        // Migrate logo
        $this->info('Migrating app logo...');
        $logoSetting = Setting::where('key', 'app_logo')->first();

        if ($logoSetting && $logoSetting->value) {
            $oldPath = $logoSetting->value;

            // Check if already migrated
            if (strpos($oldPath, 'images/logos/') === 0) {
                $this->line("Skipping logo - already migrated");
            } else {
                $sourceFile = storage_path('app/public/' . $oldPath);

                if (File::exists($sourceFile)) {
                    $filename = basename($oldPath);
                    $newFilename = time() . '_' . uniqid() . '_' . $filename;
                    $destinationFolder = public_path('images/logos');
                    $destinationFile = $destinationFolder . '/' . $newFilename;

                    if (!File::exists($destinationFolder)) {
                        File::makeDirectory($destinationFolder, 0755, true);
                    }

                    try {
                        File::copy($sourceFile, $destinationFile);

                        Setting::set('app_logo', 'images/logos/' . $newFilename, 'file', 'Logo aplikasi (maksimal 2MB)', true);

                        $this->info("✓ Migrated: App Logo");
                        $migratedCount++;

                    } catch (\Exception $e) {
                        $this->error("✗ Failed to migrate logo: " . $e->getMessage());
                        $errorCount++;
                    }
                } else {
                    $this->warn("Logo source file not found: {$sourceFile}");
                }
            }
        }

        $this->newLine();
        $this->info("Migration completed!");
        $this->info("✓ Successfully migrated: {$migratedCount} files");

        if ($errorCount > 0) {
            $this->warn("✗ Failed: {$errorCount} files");
        }

        $this->newLine();
        $this->comment('Note: Old files in storage/app/public are NOT deleted automatically.');
        $this->comment('Please verify the migration before manually deleting old files.');

        return Command::SUCCESS;
    }
}
