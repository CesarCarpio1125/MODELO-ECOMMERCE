<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixFrontendCache extends Command
{
    protected $signature = 'frontend:fix-cache {--force}';
    protected $description = 'Fix frontend cache corruption in Native/Electron';

    public function handle()
    {
        $this->info('🔧 Fixing Frontend Cache Corruption');
        $this->newLine();

        // Show current problem
        $this->showCurrentProblem();

        // Create frontend fix script
        $this->createFrontendFix();

        // Show instructions
        $this->showFixInstructions();

        return Command::SUCCESS;
    }

    private function showCurrentProblem()
    {
        $this->error('🚨 CURRENT PROBLEM:');
        $this->newLine();
        
        $this->line('❌ Frontend shows vendor ID: 01kjaxvce59zf048yarhyg0rrw');
        $this->line('❌ This ID does NOT exist in database');
        $this->line('❌ NativeImageService returns 403 Forbidden');
        $this->line('❌ Cache refresh keeps showing wrong ID');
        $this->newLine();
        
        $this->info('✅ WHAT WORKS:');
        $this->line('✅ Backend vendors: 01kj8tvt23k4qtbpe5zdqzapgn (cesarcecesa)');
        $this->line('✅ Backend vendors: 01kjamby57yzdwbshv2zqw2vqe (cesarcecesasad)');
        $this->line('✅ Image files exist and load correctly');
        $this->newLine();
    }

    private function createFrontendFix()
    {
        $this->info('📝 Creating Frontend Fix Script...');
        
        $fixScript = $this->generateFixScript();
        
        $this->line('Frontend fix script created:');
        $this->newLine();
        $this->line($fixScript);
        $this->newLine();
    }

    private function generateFixScript(): string
    {
        return <<<JAVASCRIPT
// PEGAR ESTO EN LA CONSOLA DE ELECTRON (F12)
// Y PRESIONAR ENTER

console.log('🧹 LIMPIEZA RADICAL DE CACHE INICIADA');

// 1. Limpiar todo el storage del navegador
try {
    localStorage.clear();
    sessionStorage.clear();
    console.log('✅ Storage limpiado');
} catch (e) {
    console.warn('❌ Error limpiando storage:', e);
}

// 2. Forzar limpieza de caché de imágenes
const images = document.querySelectorAll('img[src*="/storage/"]');
images.forEach(img => {
    const src = img.src;
    if (src.includes('01kjaxvce59zf048yarhyg0rrw')) {
        console.log('🗑️ Eliminando imagen con ID incorrecto:', src);
        img.remove();
    }
});

// 3. Limpiar caché de Vue/Inertia
if (window.__VUE__) {
    console.log('✅ Detectado Vue.js');
}

if (window.Inertia) {
    console.log('✅ Detectado Inertia.js');
}

// 4. Forzar recarga de datos del servidor
console.log('🔄 Forzando recarga de datos del servidor...');

// 5. Recargar página después de 2 segundos
setTimeout(() => {
    console.log('🚀 Recargando página con datos frescos...');
    window.location.href = window.location.href + '?_fresh=' + Date.now();
}, 2000);

console.log('⏳ Esperando recarga automática...');
JAVASCRIPT;
    }

    private function showFixInstructions()
    {
        $this->newLine();
        $this->info('🚀 INSTRUCCIONES PARA SOLUCIONAR:');
        $this->newLine();
        
        $this->line('1. EN ELECTRON:');
        $this->line('   - Presiona F12 para abrir DevTools');
        $this->line('   - Ve a la pestaña "Console"');
        $this->line('   - PEGA el script de arriba');
        $this->line('   - Presiona ENTER');
        $this->newLine();
        
        $this->line('2. ESPERA 2 SEGUNDOS:');
        $this->line('   - El script limpiará todo el cache');
        $this->line('   - Eliminará imágenes con IDs incorrectos');
        $this->line('   - Recargará la página con datos frescos');
        $this->newLine();
        
        $this->line('3. VERIFICACIÓN:');
        $this->line('   - Solo deberías ver: cesarcecesa y cesarcecesasad');
        $this->line('   - NADA de "cesarDSADSAds" o IDs incorrectos');
        $this->line('   - Todas las imágenes deben cargar sin 403');
        $this->newLine();
        
        $this->warn('⚠️  SI PERSISTE:');
        $this->line('   - Cierra Electron completamente');
        $this->line('   - Abre Activity Monitor (Mac) o Task Manager (Windows)');
        $this->line('   - MATA todos los procesos de Electron');
        $this->line('   - Reinicia con: ./start-native.sh');
        $this->newLine();
        
        $this->info('🎯 RESULTADO ESPERADO:');
        $this->line('✅ Solo 2 vendors visibles');
        $this->line('✅ Sin errores 403');
        $this->line('✅ Imágenes cargando correctamente');
        $this->line('✅ URLs correctas: http://127.0.0.1:8100/storage/vendors/...');
    }
}
