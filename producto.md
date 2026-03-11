# 🏗️ Arquitectura del Sistema de Productos

## 📋 Overview

El sistema de productos sigue una arquitectura MVC tradicional con servicios para la lógica de negocio. Está diseñado para manejar productos con ULIDs como identificadores únicos.

## 🗂️ Estructura de Archivos

```
app/
├── Http/Controllers/
│   └── StoreController.php              # Controlador principal de productos
├── Modules/Vendor/
│   ├── Models/
│   │   ├── Product.php                   # Modelo Product con ULIDs
│   │   └── Vendor.php                    # Modelo Vendor con ULIDs
│   ├── Services/
│   │   └── ProductService.php             # Lógica de negocio de productos
│   ├── Requests/
│   │   ├── CreateProductRequest.php       # Validación para creación
│   │   ├── QuickCreateProductRequest.php # Validación para quick add
│   │   └── UpdateProductRequest.php       # Validación para actualización
│   └── Policies/
│       └── ProductPolicy.php              # Políticas de autorización

resources/js/
├── Components/ProductForm/
│   └── QuickAddProductModal.vue          # Modal para creación rápida
├── Pages/Store/
│   ├── Manage.vue                        # Página principal de gestión
│   └── Product/
│       ├── Create.vue                     # Formulario de creación
│       └── Edit.vue                       # Formulario de edición
└── Composables/
    └── useProductManagement.js           # Lógica reutilizable (propuesto)
```

## 🔄 Flujo Completo de Productos

### 1️⃣ Creación de Productos

#### **A) Quick Add Product (Modal)**
```
Frontend: QuickAddProductModal.vue
↓
Backend: StoreController::quickStoreProduct()
↓
Service: ProductService::createProduct()
↓
Database: Product (con ULID)
```

**Frontend (QuickAddProductModal.vue):**
```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  description: '',
  price: '',
  stock_quantity: 0,
  featured_image: null
})

const submit = () => {
  form.post(route('store.products.quick-store', props.vendor.id), {
    onSuccess: () => {
      form.reset()
      emit('close')  // Cierra el modal
    }
  })
}
</script>
```

**Backend (StoreController::quickStoreProduct):**
```php
public function quickStoreProduct(QuickCreateProductRequest $request, Vendor $vendor)
{
    try {
        $user = auth()->user();
        
        // Validación de ownership
        if ($vendor->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Datos para creación rápida
        $quickData = [
            'name' => $request->validated()['name'],
            'description' => $request->validated()['description'] ?? null,
            'price' => $request->validated()['price'],
            'stock_quantity' => $request->validated()['stock_quantity'],
            'status' => 'draft',  // Default a draft
            'featured_image' => $request->file('featured_image'),
        ];

        $product = $this->productService->createProduct($vendor, $quickData);

        return redirect()
            ->route('store.manage')
            ->with('success', 'Product created successfully!');
            
    } catch (\Exception $e) {
        return back()
            ->withErrors(['quick_store_product' => $e->getMessage()]);
    }
}
```

#### **B) Creación Completa (Formulario)**
```
Frontend: Store/Product/Create.vue
↓
Backend: StoreController::storeProduct()
↓
Service: ProductService::createProduct()
↓
Database: Product
```

### 2️⃣ Edición de Productos

#### **Flujo de Edición:**
```
Frontend: Store/Manage.vue (Click Edit)
↓
Backend: StoreController::editProduct()
↓
Frontend: Store/Product/Edit.vue
↓
Backend: StoreController::updateProduct()
↓
Service: ProductService::updateProduct()
↓
Database: Product actualizado
```

**Backend (StoreController::editProduct):**
```php
public function editProduct(Product $product): Response
{
    // Obtener vendor del usuario
    $user = auth()->user();
    $userVendor = $user->vendors()->first();

    // Validación de ownership
    if ($userVendor->id !== $product->vendor_id) {
        abort(403, 'You can only edit your own products.');
    }

    // Autorización vía políticas
    $this->authorize('update', $product);

    // Cargar relaciones
    $product->load('variants');

    return Inertia::render('Store/Product/Edit', [
        'product' => $product,
    ]);
}
```

**Backend (StoreController::updateProduct):**
```php
public function updateProduct(UpdateProductRequest $request, Product $product)
{
    // Validación de ownership
    $user = auth()->user();
    $userVendor = $user->vendors()->first();

    if ($userVendor->id !== $product->vendor_id) {
        abort(403, 'You can only edit your own products.');
    }

    $this->authorize('update', $product);

    try {
        $this->productService->updateProduct($product, $request->validated());

        return redirect()
            ->route('store.manage')
            ->with('success', 'Product updated successfully!');
            
    } catch (\Exception $e) {
        return back()
            ->withErrors(['update_product' => $e->getMessage()]);
    }
}
```

### 3️⃣ Eliminación de Productos

#### **Flujo de Eliminación:**
```
Frontend: Store/Manage.vue (Click Delete)
↓
Modal de confirmación
↓
Backend: StoreController::destroyProduct()
↓
Service: ProductService::deleteProduct()
↓
Database: Product eliminado
```

**Backend (StoreController::destroyProduct):**
```php
public function destroyProduct(Product $product)
{
    // Validación de ownership
    $user = auth()->user();
    $userVendor = $user->vendors()->first();

    if ($userVendor->id !== $product->vendor_id) {
        abort(403, 'You can only delete your own products.');
    }

    $this->authorize('delete', $product);

    try {
        $this->productService->deleteProduct($product);

        return redirect()
            ->route('store.manage')
            ->with('success', 'Product deleted successfully!');
            
    } catch (\Exception $e) {
        return back()
            ->withErrors(['delete_product' => $e->getMessage()]);
    }
}
```

### 4️⃣ Toggle Status de Productos

#### **Flujo de Toggle:**
```
Frontend: Store/Manage.vue (Click Toggle)
↓
Backend: StoreController::toggleProductStatus()
↓
Database: Product status actualizado
```

**Backend (StoreController::toggleProductStatus):**
```php
public function toggleProductStatus(Product $product)
{
    // Solo validación vía políticas
    $this->authorize('update', $product);

    $newStatus = $product->isActive() ? 'draft' : 'active';
    $product->update(['status' => $newStatus]);

    return back()->with('success', "Product status updated to {$newStatus}!");
}
```

## 🔧 Componentes Clave

### **ProductService.php**
```php
class ProductService
{
    public function createProduct(Vendor $vendor, array $data): Product
    {
        // ⚠️ PROBLEMA: Convierte ULID a integer
        $product = new Product([
            'vendor_id' => (int) $vendor->id,  // ¡ERROR!
            'name' => $data['name'],
            'slug' => Product::generateSlug($data['name']),
            // ...
        ]);
        
        $product->save();
        return $product->fresh();
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function deleteProduct(Product $product): bool
    {
        // Eliminar imágenes, variantes, y producto
        $this->deleteProductImages($product);
        $product->variants()->delete();
        return $product->delete();
    }
}
```

### **Product Model (con ULIDs)**
```php
class Product extends Model
{
    use HasUlids;  // 🎯 ULID como primary key

    protected $fillable = [
        'id',           // ULID (26-char string)
        'vendor_id',    // ULID (26-char string)
        'name',
        'slug',
        'description',
        'price',
        'stock_quantity',
        'status',       // 'active', 'draft', 'archived'
        'featured_image',
        'created_by',   // User ID (integer)
        // ...
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
```

### **ProductPolicy.php**
```php
class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        // Obtiene vendor para comparar user_id
        $vendor = \App\Modules\Vendor\Vendor::find($product->vendor_id);

        return $vendor && (string) $user->id === (string) $vendor->user_id 
            || $user->role === 'admin';
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);  // Misma lógica
    }
}
```

## 🚨 Problemas Críticos Actuales

### **1. ULID Type Casting Error**
```php
// ProductService.php - LÍNEA 25
'vendor_id' => (int) $vendor->id,  // ❌ Convierte ULID a integer
```
**Impacto:** Ownership validation falla, inconsistencia en DB

### **2. Ownership Validation Inconsistente**
```php
// StoreController - Comparación directa
if ($userVendor->id !== $product->vendor_id) {
    // ❌ Puede fallar con ULIDs
}

// ProductPolicy - Con casting
return (string) $user->id === (string) $vendor->user_id;  // ✅ Correcto
```

### **3. Quick Add Response Handling**
```php
// Backend devuelve redirect (normal HTTP)
return redirect()->route('store.manage');

// Frontend espera AJAX response
form.post()  // ❌ No maneja redirect correctamente
```

## 🛠️ Rutas Definidas

```php
// web.php
Route::middleware(['auth', 'verified', 'vendor.required'])->group(function () {
    // Gestión de productos
    Route::get('/store/manage', [StoreController::class, 'manage'])->name('store.manage');
    Route::get('/store/products/create', [StoreController::class, 'createProduct'])->name('store.products.create');
    Route::post('/store/products', [StoreController::class, 'storeProduct'])->name('store.products.store');
    
    // Quick Add (AJAX)
    Route::post('/store/products/quick-store/{vendor}', [StoreController::class, 'quickStoreProduct'])->name('store.products.quick-store');
    
    // CRUD individual
    Route::get('/store/products/{product}/edit', [StoreController::class, 'editProduct'])->name('store.products.edit');
    Route::put('/store/products/{product}', [StoreController::class, 'updateProduct'])->name('store.products.update');
    Route::delete('/store/products/{product}', [StoreController::class, 'destroyProduct'])->name('store.products.destroy');
    Route::patch('/store/products/{product}/toggle-status', [StoreController::class, 'toggleProductStatus'])->name('store.products.toggle-status');
});
```

## 📱 Frontend Integration

### **Store/Manage.vue**
```vue
<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import QuickAddProductModal from '@/Components/ProductForm/QuickAddProductModal.vue'

const showQuickAddModal = ref(false)

// Eliminar producto
const deleteProduct = () => {
  deleteProductForm.delete(route('store.products.destroy', productToDelete.value.id))
}

// Toggle status
const toggleProductStatus = (product) => {
  router.patch(route('store.products.toggle-status', product.id))
}
</script>

<template>
  <!-- Quick Add Modal -->
  <QuickAddProductModal
    :show="showQuickAddModal"
    :vendor="vendor"
    @close="showQuickAddModal = false"
  />

  <!-- Product List -->
  <div v-for="product in products" :key="product.id">
    <Link :href="route('store.products.edit', product.id)">
      Edit
    </Link>
    
    <button @click="toggleProductStatus(product)">
      {{ product.isActive() ? 'Deactivate' : 'Activate' }}
    </button>
    
    <button @click="confirmDelete(product)">
      Delete
    </button>
  </div>
</template>
```

## 🔍 Flujo de Datos (Ejemplo: Editar Producto)

1. **Usuario hace click en "Edit"**
   ```
   Store/Manage.vue → route('store.products.edit', product.id)
   ```

2. **Route Model Binding**
   ```
   Laravel encuentra Product por ULID: "01kjxxfm80wdt49taebry16vb3"
   ```

3. **Validación de Ownership**
   ```
   StoreController::editProduct()
   ├─ $user->vendors()->first() → Vendor del usuario
   ├─ $userVendor->id vs $product->vendor_id → Comparación ULID
   └─ $this->authorize('update', $product) → ProductPolicy
   ```

4. **Renderizado del Form**
   ```
   Inertia::render('Store/Product/Edit', ['product' => $product])
   ```

5. **Envío del Formulario**
   ```
   Store/Product/Edit.vue → PUT /store/products/{product}
   ```

6. **Actualización**
   ```
   StoreController::updateProduct()
   ├─ Validación ownership (otra vez)
   ├─ $this->authorize('update', $product)
   ├─ ProductService::updateProduct()
   └─ redirect()->route('store.manage')
   ```

## 📋 Checklist de Funcionalidad

### **✅ Funciona:**
- [x] Creación Quick Add (modal)
- [x] Listado de productos
- [x] Navegación a edición
- [x] Toggle status
- [x] Route Model Binding con ULIDs

### **❌ Problemas:**
- [ ] Ownership validation inconsistente
- [ ] ULID casting en ProductService
- [ ] Quick Add response handling
- [ ] Manejo centralizado de errores

### **🔧 Mejoras Necesarias:**
- [ ] Corregir `(int) $vendor->id` en ProductService
- [ ] Estandarizar ownership validation con string casting
- [ ] Implementar respuesta JSON para Quick Add
- [ ] Agregar manejo centralizado de errores
- [ ] Crear composable para lógica reutilizable

## 🚀 Próximos Pasos

1. **Corregir ProductService** - Remover casting a integer
2. **Estandarizar Validación** - Usar string casting en todos lados
3. **Mejorar Quick Add** - Respuesta JSON + manejo frontend
4. **Crear Composable** - Lógica reutilizable para productos
5. **Testing** - Validar todos los flujos con ULIDs

---

*Esta documentación refleja el estado actual del sistema y los problemas identificados. Las mejoras propuestas pueden implementarse gradualmente para lograr una arquitectura más robusta.*
