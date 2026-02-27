# Modular Commerce

Advanced modular commerce system built with modern technologies.

## 🚀 Tech Stack

**Backend:**
- Laravel 11
- PHP 8.2+
- MySQL/PostgreSQL
- Redis (optional)

**Frontend:**
- Vue 3 (Composition API)
- Inertia.js
- Pinia (State Management)
- Tailwind CSS
- Vite

**Desktop App:**
- NativePHP/Electron
- Cross-platform support

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

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Storage link**
   ```bash
   php artisan storage:link
   ```

6. **Start development server**
   ```bash
   # Web server
   php artisan serve
   
   # Frontend build (in another terminal)
   npm run dev
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

This application includes NativePHP support for desktop deployment:

```bash
# Install NativePHP dependencies
composer require nativephp/electron

# Build desktop app
php artisan native:serve

# Build for production
php artisan native:build
```

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

## 🎯 Features

### Multi-Vendor Support
- Vendor registration and management
- Individual vendor stores
- Product management per vendor
- Order management per vendor

### Modern Frontend
- Reactive UI with Vue 3
- Smooth page transitions with Inertia
- Responsive design with Tailwind
- Real-time updates (coming soon)

### Image Management
- GD library integration
- Multiple format support (JPEG, PNG, GIF, WebP)
- Base64 encoding for NativePHP
- Automatic image optimization

### Security
- Laravel's built-in security features
- CSRF protection
- Input validation
- File upload security

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
