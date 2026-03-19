<?php

namespace Database\Seeders;

use App\Modules\Vendor\Product;
use App\Modules\Vendor\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TenisVendorProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the tenis vendor
        $vendor = Vendor::where('store_slug', 'tenis')->first();
        
        if (!$vendor) {
            $this->command->error('Vendor "tenis" not found!');
            return;
        }

        $this->command->info('Found vendor: ' . $vendor->name . ' (ID: ' . $vendor->id . ')');

        // Create 10 products with images
        $products = [
            [
                'name' => 'Tenis Deportivos Pro Max',
                'description' => 'Tenis profesionales de alta rendimiento para atletas',
                'price' => 89.99,
                'stock_quantity' => 50,
                'sku' => 'TENIS-PRO-MAX-011',
                'status' => 'active',
                'image_text' => 'Tenis Pro',
                'bg_color' => [74, 94, 117]
            ],
            [
                'name' => 'Zapatillas Running Elite Ultra',
                'description' => 'Zapatillas ligeras para carreras de larga distancia',
                'price' => 125.50,
                'stock_quantity' => 30,
                'sku' => 'TENIS-ELT-ULT-012',
                'status' => 'active',
                'image_text' => 'Running Elite',
                'bg_color' => [150, 100, 200]
            ],
            [
                'name' => 'Calzado Urbano Style Plus',
                'description' => 'Tenis casuales con diseño moderno y cómodos',
                'price' => 65.00,
                'stock_quantity' => 75,
                'sku' => 'TENIS-URB-PLUS-013',
                'status' => 'active',
                'image_text' => 'Urban Style',
                'bg_color' => [100, 150, 100]
            ],
            [
                'name' => 'Botines Invierno Premium Pro',
                'description' => 'Botines resistentes al agua para climas fríos',
                'price' => 150.00,
                'stock_quantity' => 25,
                'sku' => 'TENIS-WIN-PRO-014',
                'status' => 'draft',
                'image_text' => 'Invierno',
                'bg_color' => [50, 50, 100]
            ],
            [
                'name' => 'Sneakers Clásicos Retro Deluxe',
                'description' => 'Diseño retro con tecnología moderna',
                'price' => 95.75,
                'stock_quantity' => 40,
                'sku' => 'TENIS-RET-DEL-015',
                'status' => 'active',
                'image_text' => 'Retro',
                'bg_color' => [200, 150, 50]
            ],
            [
                'name' => 'Tenis Fitness Plus Advanced',
                'description' => 'Perfectos para gimnasio y entrenamiento',
                'price' => 78.25,
                'stock_quantity' => 60,
                'sku' => 'TENIS-FIT-ADV-016',
                'status' => 'active',
                'image_text' => 'Fitness Plus',
                'bg_color' => [100, 200, 150]
            ],
            [
                'name' => 'Calzado Trail Adventure Pro',
                'description' => 'Tenis para senderismo y terrenos difíciles',
                'price' => 110.00,
                'stock_quantity' => 35,
                'sku' => 'TENIS-TRAIL-ADV-017',
                'status' => 'draft',
                'image_text' => 'Adventure',
                'bg_color' => [150, 100, 50]
            ],
            [
                'name' => 'Zapatillas Skate Street Pro',
                'description' => 'Diseño urbano inspirado en la cultura skate',
                'price' => 85.50,
                'stock_quantity' => 45,
                'sku' => 'TENIS-SKATE-PRO-018',
                'status' => 'active',
                'image_text' => 'Skate Street',
                'bg_color' => [100, 100, 100]
            ],
            [
                'name' => 'Tenis Tennis Professional Elite',
                'description' => 'Calzado específico para jugadores de tenis',
                'price' => 135.00,
                'stock_quantity' => 28,
                'sku' => 'TENIS-TENNIS-ELT-019',
                'status' => 'active',
                'image_text' => 'Tennis Pro',
                'bg_color' => [200, 100, 100]
            ],
            [
                'name' => 'Calzado Casual Comfort Max',
                'description' => 'Máxima comodidad para uso diario',
                'price' => 55.00,
                'stock_quantity' => 80,
                'sku' => 'TENIS-COMF-MAX-020',
                'status' => 'draft',
                'image_text' => 'Comfort',
                'bg_color' => [150, 150, 200]
            ]
        ];

        foreach ($products as $index => $productData) {
            $this->command->info('Creating product: ' . $productData['name']);
            
            // Create the product
            $product = Product::create([
                'vendor_id' => $vendor->id,
                'name' => $productData['name'],
                'slug' => strtolower(str_replace(' ', '-', $productData['name'])) . '-' . uniqid(),
                'description' => $productData['description'],
                'price' => $productData['price'],
                'stock_quantity' => $productData['stock_quantity'],
                'sku' => $productData['sku'],
                'status' => $productData['status'],
                'is_active' => $productData['status'] === 'active',
                'is_featured' => $index < 3, // First 3 products are featured
                'created_by' => 1, // Assuming user ID 1 exists
            ]);

            // Add images to the product
            $this->addProductImages($product, $productData, $index + 1);
            
            $this->command->info('✅ Created: ' . $product->name . ' with images');
        }

        $this->command->info('Successfully created 10 products for vendor "tenis"!');
    }

    /**
     * Add images to a product using MediaLibrary
     */
    private function addProductImages(Product $product, array $productData, int $productNumber): void
    {
        // Create featured image
        $featuredImage = $this->createProductImage(
            $productData['image_text'],
            'Featured',
            $productData['bg_color'],
            600,
            400
        );

        $tempFeaturedPath = sys_get_temp_dir() . "/featured-{$productNumber}.png";
        imagepng($featuredImage, $tempFeaturedPath);
        imagedestroy($featuredImage);

        // Add featured image to MediaLibrary
        $product->addMedia($tempFeaturedPath)
            ->usingFileName("featured-{$productNumber}.png")
            ->withCustomProperties(['type' => 'featured', 'product_number' => $productNumber])
            ->toMediaCollection('featured-image', 'public');

        if (file_exists($tempFeaturedPath)) {
            unlink($tempFeaturedPath);
        }

        // Add gallery images (2-4 per product)
        $galleryCount = rand(2, 4);
        for ($i = 1; $i <= $galleryCount; $i++) {
            $galleryImage = $this->createProductImage(
                $productData['image_text'] . " {$i}",
                "Gallery {$i}",
                [
                    min(255, $productData['bg_color'][0] + ($i * 20)),
                    min(255, $productData['bg_color'][1] + ($i * 15)),
                    min(255, $productData['bg_color'][2] + ($i * 10))
                ],
                400,
                300
            );

            $tempGalleryPath = sys_get_temp_dir() . "/gallery-{$productNumber}-{$i}.png";
            imagepng($galleryImage, $tempGalleryPath);
            imagedestroy($galleryImage);

            $product->addMedia($tempGalleryPath)
                ->usingFileName("gallery-{$productNumber}-{$i}.png")
                ->withCustomProperties([
                    'type' => 'gallery', 
                    'order' => $i,
                    'product_number' => $productNumber
                ])
                ->toMediaCollection('product-gallery', 'public');

            if (file_exists($tempGalleryPath)) {
                unlink($tempGalleryPath);
            }
        }

        $this->command->info("  Added 1 featured + {$galleryCount} gallery images");
    }

    /**
     * Create a product image using GD
     */
    private function createProductImage(string $text, string $subtext, array $bgColor, int $width, int $height)
    {
        $image = imagecreate($width, $height);
        $bg = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $borderColor = imagecolorallocate($image, 255, 255, 255);

        // Fill background
        imagefill($image, 0, 0, $bg);

        // Add main text
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $x = ($width - $textWidth) / 2;
        $y = $height / 2 - 20;
        imagestring($image, $font, $x, $y, $text, $textColor);

        // Add subtext
        $subtextWidth = imagefontwidth($font) * strlen($subtext);
        $subx = ($width - $subtextWidth) / 2;
        $suby = $height / 2 + 20;
        imagestring($image, $font, $subx, $suby, $subtext, $textColor);

        // Add decorative border
        imagerectangle($image, 20, 20, $width - 20, $height - 20, $borderColor);

        return $image;
    }
}
