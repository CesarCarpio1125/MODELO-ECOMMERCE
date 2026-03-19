# Carrusel de Imágenes de Productos

## 🎯 Overview

Sistema de carrusel de imágenes para productos con auto-play, navegación manual y soporte para múltiples imágenes usando Spatie MediaLibrary.

## 📁 Estructura de Archivos

```
resources/js/Components/Product/ImageCarousel.vue    # Componente del carrusel
resources/js/Components/Product/ProductCard.vue       # Componente de tarjeta actualizado
app/Modules/Vendor/Product.php                        # Modelo con MediaLibrary
app/Modules/Vendor/Services/ProductService.php        # Servicio con manejo de imágenes
```

## 🎨 Componente ImageCarousel

### Props disponibles

```vue
<ImageCarousel 
  :images="carouselImages"           # Array de imágenes
  :auto-play="true"                  # Auto-play (default: true)
  :interval="3000"                   # Intervalo en ms (default: 3000)
  :show-thumbnails="true"            # Mostrar thumbnails (default: true)
  height="h-56"                      # Altura del carrusel
  @image-change="handleImageChange"  # Evento cuando cambia imagen
/>
```

### Formato de imágenes

```javascript
const carouselImages = [
  {
    id: 'featured',
    url: 'https://example.com/image-medium.jpg',
    thumb: 'https://example.com/image-thumb.jpg',
    large: 'https://example.com/image-large.jpg',
    webp: 'https://example.com/image.webp',
    name: 'Featured Image',
    order: -1
  },
  // ... más imágenes
]
```

## 🔄 Uso en ProductCard

```vue
<template>
  <ProductCard 
    :product="product"
    :carousel-auto-play="true"
    :carousel-interval="3000"
  />
</template>
```

## 📸 Manejo de Imágenes con MediaLibrary

### Subida de imágenes

```php
// Imagen destacada
$product->addMediaFromRequest('featured_image')
    ->usingFileName($product->slug . '-featured.jpg')
    ->toMediaCollection('featured-image', 'public');

// Galería de imágenes
foreach ($request->images as $index => $image) {
    $product->addMedia($image)
        ->usingFileName($product->slug . '-gallery-' . ($index + 1) . '.jpg')
        ->withCustomProperties(['gallery_order' => $index])
        ->toMediaCollection('product-gallery', 'public');
}
```

### Obtener URLs

```php
// Imagen destacada
$featuredUrl = $product->getFirstMediaUrl('featured-image', 'medium');

// Galería completa
$galleryImages = $product->getMedia('product-gallery')
    ->map(fn ($media) => [
        'url' => $media->getUrl('medium'),
        'thumb' => $media->getUrl('thumb'),
        'large' => $media->getUrl('large'),
        'webp' => $media->getUrl('webp'),
    ])
    ->sortBy('order_column');
```

## 🎛️ Conversiones de Imágenes

El sistema genera automáticamente:

- **thumb**: 150x150px (para thumbnails)
- **medium**: 600x600px (para vista principal)
- **large**: 1200x1200px (para vista ampliada)
- **webp**: Versión WebP optimizada (80% calidad)

## 🚀 Características del Carrusel

### ✅ Funcionalidades implementadas

- **Auto-play**: Cambio automático cada 3 segundos
- **Navegación manual**: Botones anterior/siguiente
- **Indicadores**: Puntos de posición
- **Play/Pause**: Control de auto-play
- **Thumbnails**: Miniaturas navegables
- **Responsive**: Adaptable a diferentes tamaños
- **Touch support**: Soporte para dispositivos móviles
- **Keyboard navigation**: Navegación con teclado
- **Lazy loading**: Carga optimizada de imágenes

### 🎮 Controles del usuario

- **Click en botones**: Navegación anterior/siguiente
- **Click en indicadores**: Saltar a imagen específica
- **Click en thumbnails**: Navegar por miniaturas
- **Hover**: Pausa automática del auto-play
- **Teclado**: Flechas izquierda/derecha para navegar

## 🔧 Configuración

### Personalización de intervalos

```vue
<!-- Carrusel lento (5 segundos) -->
<ImageCarousel :interval="5000" />

<!-- Carrusel rápido (1.5 segundos) -->
<ImageCarousel :interval="1500" />
```

### Deshabilitar auto-play

```vue
<!-- Solo navegación manual -->
<ImageCarousel :auto-play="false" />
```

### Sin thumbnails

```vue
<!-- Carrusel compacto -->
<ImageCarousel :show-thumbnails="false" />
```

## 📱 Responsive Design

El carrusel se adapta automáticamente:

- **Mobile**: Controles simplificados, swipe gestures
- **Tablet**: Controles completos, thumbnails opcionales
- **Desktop**: Todas las funcionalidades disponibles

## 🎨 Estilos Personalizados

El carrusel usa clases Tailwind CSS:

```css
/* Contenedor principal */
.carousel-container {
  @apply relative w-full group;
}

/* Overlay degradado */
.carousel-overlay {
  @apply absolute inset-0 bg-gradient-to-t from-black/20 to-transparent;
}

/* Botones de navegación */
.carousel-nav-button {
  @apply absolute bg-black/50 hover:bg-black/70 text-white p-2 rounded-full;
}
```

## 🔍 Debug y Troubleshooting

### Verificar imágenes cargadas

```javascript
// En ProductCard.vue
console.log('Carousel images:', carouselImages);
console.log('Product media:', product.getMedia());
```

### Validar MediaLibrary

```php
// En ProductService.php
\Log::info('Product media collections:', [
    'featured' => $product->hasMedia('featured-image'),
    'gallery' => $product->hasMedia('product-gallery'),
    'all_media' => $product->getMedia()->toArray()
]);
```

## 🚨 Consideraciones de Performance

- **Lazy loading**: Las imágenes se cargan bajo demanda
- **WebP conversion**: Reducción de tamaño hasta 80%
- **Responsive images**: Diferentes tamaños para diferentes dispositivos
- **Caching**: MediaLibrary cachea las conversiones automáticamente

## 📝 Ejemplo Completo

```vue
<!-- ProductCard.vue -->
<script setup>
import ImageCarousel from '@/Components/Product/ImageCarousel.vue'

const props = defineProps({
  product: Object
})

const carouselImages = computed(() => {
  return props.product.getCarouselImages?.() || []
})
</script>

<template>
  <div class="product-card">
    <ImageCarousel 
      :images="carouselImages"
      :auto-play="true"
      :interval="3000"
      height="h-56"
    />
    
    <div class="product-info">
      <h3>{{ product.name }}</h3>
      <p>{{ product.description }}</p>
    </div>
  </div>
</template>
```

## 🔄 Migración desde Sistema Antiguo

El sistema mantiene compatibilidad con:

- Campo `featured_image` en la tabla products
- Campo `images` (JSON) en la tabla products
- Métodos legacy en el modelo Product

Para migrar completamente:

1. Subir imágenes existentes a MediaLibrary
2. Actualizar referencias en la base de datos
3. Remover campos legacy cuando sea seguro
