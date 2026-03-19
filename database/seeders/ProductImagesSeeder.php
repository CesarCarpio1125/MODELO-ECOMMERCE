<?php

namespace Database\Seeders;

use App\Modules\Vendor\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the tienda 2 product
        $product = Product::where('name', 'tienda 2')
            ->where('price', 2.00)
            ->first();

        if (!$product) {
            $this->command->error('Product "tienda 2" not found!');
            return;
        }

        $this->command->info('Found product: ' . $product->name . ' (ID: ' . $product->id . ')');

        // Create a sample image file for testing
        $this->createSampleImage($product);

        // Add image using MediaLibrary
        $this->addMediaLibraryImages($product);

        // Also add legacy images for testing
        $this->addLegacyImages($product);

        $this->command->info('Images added successfully to product!');
    }

    /**
     * Create a sample image file in storage
     */
    private function createSampleImage(Product $product): void
    {
        // Create a simple PNG image using GD
        $image = imagecreate(400, 300);
        $bgColor = imagecolorallocate($image, 38, 51, 71); // #263347
        $textColor = imagecolorallocate($image, 255, 255, 255);
        
        // Fill background
        imagefill($image, 0, 0, $bgColor);
        
        // Add text
        $text = "Tienda 2 - $2.00";
        $font = 5; // Built-in font
        $textWidth = imagefontwidth($font) * strlen($text);
        $x = (400 - $textWidth) / 2;
        $y = 150;
        
        imagestring($image, $font, $x, $y, $text, $textColor);
        
        // Draw border
        imagerectangle($image, 50, 50, 350, 250, $textColor);
        
        // Save to storage
        $path = "products/{$product->id}/featured-image.png";
        $tempPath = sys_get_temp_dir() . '/featured-image.png';
        imagepng($image, $tempPath);
        
        Storage::disk('public')->put($path, file_get_contents($tempPath));
        
        // Clean up
        imagedestroy($image);
        unlink($tempPath);
        
        $this->command->info("Created sample image: {$path}");
    }

    /**
     * Add images using MediaLibrary
     */
    private function addMediaLibraryImages(Product $product): void
    {
        // Create featured image
        $image = imagecreate(600, 400);
        $bgColor = imagecolorallocate($image, 74, 94, 117); // #4a5e75
        $textColor = imagecolorallocate($image, 255, 255, 255);
        
        imagefill($image, 0, 0, $bgColor);
        
        $text = "Featured Image";
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $x = (600 - $textWidth) / 2;
        $y = 200;
        
        imagestring($image, $font, $x, $y, $text, $textColor);
        
        // Draw circle
        imageellipse($image, 300, 200, 160, 160, $textColor);
        
        $tempPath = sys_get_temp_dir() . '/featured-image.png';
        imagepng($image, $tempPath);
        imagedestroy($image);

        // Add to MediaLibrary
        $product->addMedia($tempPath)
            ->usingFileName('featured-image.png')
            ->withCustomProperties(['type' => 'featured', 'uploaded_via' => 'seeder'])
            ->toMediaCollection('featured-image', 'public');

        // Clean up temp file if it still exists
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        
        $this->command->info('Added featured image to MediaLibrary');

        // Add gallery images
        for ($i = 1; $i <= 3; $i++) {
            $image = imagecreate(400, 300);
            $bgColor = imagecolorallocate($image, 100 + $i * 50, 150, 200);
            $textColor = imagecolorallocate($image, 255, 255, 255);
            
            imagefill($image, 0, 0, $bgColor);
            
            $text = "Gallery Image {$i}";
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($text);
            $x = (400 - $textWidth) / 2;
            $y = 150;
            
            imagestring($image, $font, $x, $y, $text, $textColor);
            
            // Draw border
            imagerectangle($image, 20, 20, 380, 280, $textColor);
            
            $tempPath = sys_get_temp_dir() . "/gallery-image-{$i}.png";
            imagepng($image, $tempPath);
            imagedestroy($image);

            $product->addMedia($tempPath)
                ->usingFileName("gallery-image-{$i}.png")
                ->withCustomProperties([
                    'type' => 'gallery', 
                    'order' => $i,
                    'uploaded_via' => 'seeder'
                ])
                ->toMediaCollection('product-gallery', 'public');

            // Clean up temp file if it still exists
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        $this->command->info('Added 3 gallery images to MediaLibrary');
    }

    /**
     * Add legacy images for testing
     */
    private function addLegacyImages(Product $product): void
    {
        // Update legacy fields
        $product->featured_image = "products/{$product->id}/featured-image.png";
        $product->images = [
            "products/{$product->id}/gallery-1.png",
            "products/{$product->id}/gallery-2.png"
        ];
        $product->save();

        $this->command->info('Updated legacy image fields');
    }
}
