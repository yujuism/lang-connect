# LangConnect - Language Exchange Platform

A modern language exchange platform built with Laravel 11, featuring real-time messaging, session management, and achievement tracking.

## 🚀 Features

- **User Profiles** - Manage languages, proficiency levels, and bio
- **Learning Requests** - Post and browse language learning help requests
- **Smart Matching** - Intelligent matching system for language partners
- **Real-Time Messaging** - WebSocket-powered instant messaging with Laravel Reverb
- **Practice Sessions** - Track and manage language practice sessions
- **Review System** - Rate and review language partners
- **Achievements** - Unlock achievements based on activity
- **Smart Notifications** - One notification per conversation (no spam!)

## 🏗️ Architecture

LangConnect follows **Clean Architecture** principles with a service layer pattern:

```
HTTP Request → Controller → FormRequest → Service → Model → Database
```

- **Controllers**: Thin, handle HTTP routing only
- **FormRequests**: Validation layer with custom error messages
- **Services**: All business logic (reusable and testable)
- **Models**: Data access via Eloquent ORM

See [docs/CLEAN_ARCHITECTURE.md](docs/CLEAN_ARCHITECTURE.md) for detailed architecture documentation.

## 📋 Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ & NPM
- Laravel 11

## 🔧 Installation

### 1. Clone & Install Dependencies

```bash
git clone <repository-url>
cd product-management
composer install
npm install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=langconnect
DB_USERNAME=root
DB_PASSWORD=yourpassword

# WebSocket Configuration (Laravel Reverb)
BROADCAST_CONNECTION=reverb
VITE_REVERB_APP_KEY=s7itfxjhpvsazfryujyb
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### 3. Database Setup

```bash
php artisan migrate:fresh --seed
```

This will create:
- Database schema
- 30+ languages
- 20+ achievements
- Demo users (Alice, Marie, etc.)

### 4. Build Assets

```bash
npm run build
# Or for development with hot reload:
npm run dev
```

### 5. Start Servers

**Terminal 1 - Laravel Server:**
```bash
./start-server.sh
# Or: php artisan serve
```

**Terminal 2 - WebSocket Server:**
```bash
php artisan reverb:start
```

Visit: `http://localhost:8000`

## 🧪 Testing

### Test Users

After seeding, you can login with:

- **Alice** - alice@example.com / password
- **Marie** - marie@example.com / password
- **Carlos** - carlos@example.com / password

### Test Real-Time Messaging

1. Open two browsers (Chrome + Firefox or Incognito)
2. Login as Alice in Browser 1
3. Login as Marie in Browser 2
4. Start a conversation
5. Messages appear **instantly** via WebSocket! ⚡

See [docs/WEBSOCKET_QUICK_START.md](docs/WEBSOCKET_QUICK_START.md) for detailed testing instructions.

## 📚 Documentation

All documentation is organized in the [`docs/`](docs/) directory:

### Quick Start
- [WebSocket Quick Start](docs/WEBSOCKET_QUICK_START.md) - Test real-time messaging
- [WebSocket Debugging](docs/TEST_WEBSOCKET.md) - Step-by-step debugging guide

### Architecture
- [Clean Architecture Guide](docs/CLEAN_ARCHITECTURE.md) - Complete architecture documentation
- [Architecture Visuals](docs/ARCHITECTURE_VISUAL.md) - Diagrams and metrics
- [Refactoring Summary](docs/REFACTORING_SUMMARY.md) - Before/after comparison

### Features
- [Smart Notifications](docs/SMART_NOTIFICATIONS.md) - Notification system documentation
- [WebSocket Implementation](docs/WEBSOCKET_UPGRADE_COMPLETE.md) - Technical details

### Development
- [Factories Guide](docs/FACTORIES_GUIDE.md) - Laravel factories and seeders guide
- [Project Plan](docs/LANGUAGE_EXCHANGE_PLAN.md) - Overall project roadmap

## 🛠️ Tech Stack

- **Backend**: Laravel 11, PHP 8.2
- **Frontend**: Blade Templates, Alpine.js, Bootstrap 5
- **Database**: MySQL 8.0
- **Real-Time**: Laravel Reverb (WebSocket server)
- **Build Tool**: Vite
- **Broadcasting**: Laravel Echo, Pusher JS

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/      # Thin controllers (HTTP routing only)
│   └── Requests/         # Form validation classes
├── Services/             # Business logic layer
│   ├── ProfileService.php
│   ├── MessageService.php
│   ├── AchievementService.php
│   └── MatchingService.php
├── Models/               # Eloquent models (data access)
├── Events/               # Broadcast events
└── Listeners/            # Event listeners

resources/
├── views/                # Blade templates
└── js/
    ├── app.js           # Alpine.js setup
    └── bootstrap.js     # Laravel Echo setup

docs/                     # All project documentation
```

## 🔑 Key Commands

```bash
# Development
php artisan serve                    # Start Laravel server
php artisan reverb:start             # Start WebSocket server
npm run dev                          # Build assets with hot reload

# Database
php artisan migrate:fresh --seed     # Reset database with test data
php artisan tinker                   # Laravel REPL

# Testing
php artisan test                     # Run tests
php artisan route:list               # View all routes

# Production
npm run build                        # Build assets for production
php artisan config:cache             # Cache config
php artisan route:cache              # Cache routes
```

## 🎯 Clean Code Standards

This project follows professional Laravel best practices:

✅ **Service Layer** - Business logic separated from controllers
✅ **Form Requests** - Validation in dedicated classes
✅ **Dependency Injection** - Services injected via constructors
✅ **Type Hints** - PHPDoc comments for IDE support
✅ **Transactions** - Database operations wrapped in transactions
✅ **Events** - Decoupled side effects via Laravel events

See [docs/CLEAN_ARCHITECTURE.md](docs/CLEAN_ARCHITECTURE.md) for details.

## 🚀 WebSocket Features

- **Real-time messaging** - Messages appear instantly without refresh
- **Smart notifications** - One notification per sender (no spam)
- **Auto-reconnect** - Handles connection drops gracefully
- **Read receipts** - Auto-mark messages as read
- **Broadcasting** - Laravel Reverb for production-ready WebSockets

## 📝 License

This project is open-sourced software licensed under the MIT license.

## 🙏 Credits

Built with Laravel 11 and modern web technologies.
