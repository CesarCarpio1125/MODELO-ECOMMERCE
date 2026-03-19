# Modular Commerce

Advanced modular commerce system built with modern technologies.

## 🚀 Tech Stack

Este proyecto está construido sobre un stack moderno y robusto, asegurando escalabilidad y una excelente experiencia de desarrollo.

- **Backend:** Laravel 12
- **Frontend:** Vue.js 3 (Composition API) con Inertia.js v2
- **Base de Datos:** MySQL / PostgreSQL
- **Entorno de Desarrollo:** Docker con Laravel Sail
- **Servidor de Aplicaciones:** Nginx
- **Styling:** Tailwind CSS v3
- **Autenticación:** Laravel Sanctum v4
- **Plataforma Nativa (Opcional):** NativePHP
- **PHP:** Versión 8.4

**Justificación Técnica:** Alinear la documentación con el entorno de desarrollo real es crítico. Especificar Laravel 12 y PHP 8.4 evita discrepancias que pueden conducir a errores de compatibilidad, uso de funciones obsoletas o desconocimiento de nuevas capacidades del framework.

## 📚 Documentation

### **Project Architecture**
- **[CLAUDE.md](./CLAUDE.md)** - Complete architecture guidelines and development rules
- **[Workflows](./.windsurf/workflows/)** - Development workflows and agents

### **Reports & Analysis**
- **[Documentation](./docs/)** - Organized project documentation
  - [Refactor Final Report](./docs/REFACTOR_FINAL_REPORT.md) - Latest optimization results
  - [Code Analysis](./docs/MEJORAS_Y_ELIMINACIONES.md) - Duplication analysis

---

## 🏗️ Architecture

### Backend Principles
- **Thin Controllers**: Controllers only handle HTTP requests/responses
- **Service Layer**: All business logic lives in dedicated service classes
- **Form Requests**: Input validation separated from controllers
- **Repository Pattern**: Data access abstraction when needed
- **SOLID Principles**: Clean, maintainable, and scalable code

### Frontend Architecture
- **Atomic Design**: Organized components (atoms, molecules, organisms, templates)
- **Composition API**: Modern Vue 3 patterns only
- **Global State**: Pinia for centralized state management
- **Type Safety**: JavaScript with JSDoc annotations
- **Reusable Components**: Built for scalability

## 📦 Modules

- **🔐 Auth**: User authentication and authorization
- **👥 Users**: User management and profiles
- **🛍️ Products**: Product catalog and management
- **📦 Inventory**: Stock management and tracking
- **📋 Orders**: Order processing and management
- **🏪 Vendors**: Multi-vendor support
- **📊 Dashboard**: Analytics and reporting (coming soon)

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm/pnpm

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/CesarCarpio1125/MODELO-ECOMMERCE.git
   cd MODELO-ECOMMERCE/modulos/modular-commerce
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup (SQLite)**
   ```bash
   # Create database file
   touch database/database.sqlite
   
   # Update .env file with SQLite configuration
   # DB_CONNECTION=sqlite
   # DB_DATABASE=/path/to/database/database.sqlite
   
   php artisan migrate
   php artisan db:seed
   ```

5. **Storage link**
   ```bash
   php artisan storage:link
   ```

6. **Start development**
   ```bash
   # Start PHP Native Desktop app
   php artisan native:serve
   
   # Or start web server (optional)
   php artisan serve
   ```

### Docker Setup (Alternative)

```bash
# Start all services
sail up -d

# Install dependencies
sail composer install
sail npm install

# Run migrations
sail artisan migrate
sail artisan db:seed

# Start development
sail npm run dev
```

## 🖥️ Desktop App

This application is built as a **PHP Native Desktop** application, providing a native desktop experience:

```bash
# Start desktop app in development
php artisan native:serve

# Build desktop app for production
php artisan native:build

# Build for specific platforms
php artisan native:build --platform=mac
php artisan native:build --platform=windows
php artisan native:build --platform=linux
```

**Features:**
- Native desktop window with menu bar
- File system access
- System notifications
- Auto-updater support
- Cross-platform compatibility (macOS, Windows, Linux)

## 📁 Project Structure

```
modular-commerce/
├── app/
│   ├── Http/Controllers/     # HTTP request handlers
│   ├── Models/              # Eloquent models
│   ├── Services/             # Business logic
│   └── Modules/             # Feature modules
│       ├── Auth/
│       ├── Vendor/
│       └── ...
├── resources/
│   ├── js/
│   │   ├── Components/      # Reusable Vue components
│   │   ├── Layouts/         # Page layouts
│   │   ├── Modules/         # Feature-specific components
│   │   └── Pages/           # Inertia pages
│   └── views/              # Blade templates
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
└── database/
    ├── migrations/          # Database migrations
    └── seeders/            # Database seeders
```

## 🔧 Development

### Code Style
- Follow PSR-12 coding standards
- Use PHPStan for static analysis
- ESLint and Prettier for frontend code

### Testing
```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

### Building for Production
```bash
# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm run build
```

## 🚀 Roadmap de Evolución

El futuro de Modular Commerce se centra en la interactividad, la resiliencia y la expansión de capacidades para los vendedores.

- **Implementación de Interactividad Real-time:** Integración de **Laravel Reverb** para la gestión de WebSockets, permitiendo notificaciones instantáneas de pedidos, actualizaciones de stock en vivo y una experiencia de usuario más dinámica.
- **Sincronización Offline:** Desarrollo de una lógica de cola local (usando SQLite) para la aplicación de escritorio (NativePHP), que registrará las acciones del usuario sin conexión y las sincronizará automáticamente con el servidor central al restablecerse la conectividad.
- **Dashboard Dinámico y Analítico:** Creación de un módulo de analíticas avanzadas con componentes de gráficos reactivos (ej. Chart.js) y una gestión de estado centralizada en **Pinia** para filtros y datos en tiempo real.
- **Nuevos Módulos Estratégicos:**
    - **Sistema de Comisiones:** Para calcular y gestionar las ganancias de los vendedores.
    - **Gestión Avanzada de Variantes de Producto:** Soportar atributos complejos (talla, color, material) con control de stock individual.
    - **Billetera Virtual (Wallet):** Un sistema de saldo interno para vendedores y clientes.
    - **Logs de Auditoría para Vendedores:** Rastrear acciones críticas realizadas por los vendedores en sus productos y pedidos.

**Justificación Técnica:** Este roadmap establece una visión clara y alinea al equipo hacia funcionalidades de alto valor. La adopción de Reverb y una estrategia offline mejora la robustez y la experiencia de usuario, mientras que los nuevos módulos preparan la plataforma para modelos de negocio más complejos y monetizables.

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📞 Support

For support and questions, please open an issue in the repository.
