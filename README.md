# LangConnect - Language Exchange Platform

A modern language exchange platform built with Laravel 11, featuring real-time messaging, session management, and achievement tracking.

## 🚀 Features

- **User Profiles** - Manage languages, proficiency levels, and bio
- **Learning Requests** - Post and browse language learning help requests
- **Smart Matching** - Intelligent matching system for language partners
- **Real-Time Messaging** - WebSocket-powered instant messaging with Laravel Reverb
- **Voice & Video Calling** - WebRTC-powered peer-to-peer voice and video calls with popup window support
- **Practice Sessions** - Track and manage language practice sessions
- **Collaborative Canvas** - Real-time whiteboard powered by tldraw for note-taking, diagrams, and drawing during sessions
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

See [CLEAN_ARCHITECTURE.md](../docs/CLEAN_ARCHITECTURE.md) for detailed architecture documentation.

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

**Terminal 2 - WebSocket Server (Laravel Reverb):**
```bash
php artisan reverb:start
```

**Terminal 3 - TURN Server (for Voice/Video calls):**
```bash
docker run -d --name coturn --network=host coturn/coturn \
  -n --log-file=stdout \
  --min-port=49160 --max-port=49200 \
  --realm=local --fingerprint \
  --lt-cred-mech --user=user:pass \
  --no-tls --no-dtls \
  --listening-ip=0.0.0.0 \
  --relay-ip=192.168.1.10 \
  --external-ip=192.168.1.10 \
  --verbose
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

See [WEBSOCKET_QUICK_START.md](../docs/WEBSOCKET_QUICK_START.md) for detailed testing instructions.

## 📚 Documentation

All documentation is organized in the [`../docs/`](../docs/) directory:

### Quick Start
- [WebSocket Quick Start](../docs/WEBSOCKET_QUICK_START.md) - Test real-time messaging
- [WebSocket Debugging](../docs/TEST_WEBSOCKET.md) - Step-by-step debugging guide

### Architecture
- [Clean Architecture Guide](../docs/CLEAN_ARCHITECTURE.md) - Complete architecture documentation
- [Architecture Visuals](../docs/ARCHITECTURE_VISUAL.md) - Diagrams and metrics
- [Refactoring Summary](../docs/REFACTORING_SUMMARY.md) - Before/after comparison

### Features
- [Smart Notifications](../docs/SMART_NOTIFICATIONS.md) - Notification system documentation
- [WebSocket Implementation](../docs/WEBSOCKET_UPGRADE_COMPLETE.md) - Technical details
- [Completed Features](../docs/COMPLETED_FEATURES.md) - Full feature list including Voice/Video calling

### Development
- [Factories Guide](../docs/FACTORIES_GUIDE.md) - Laravel factories and seeders guide
- [Project Plan](../docs/LANGUAGE_EXCHANGE_PLAN.md) - Overall project roadmap

## 🛠️ Tech Stack

- **Backend**: Laravel 11, PHP 8.2
- **Frontend**: Blade Templates, Alpine.js, Bootstrap 5, React (for canvas)
- **Collaborative Canvas**: tldraw (React-based whiteboard)
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
    ├── app.js               # Alpine.js setup
    ├── bootstrap.js         # Laravel Echo setup
    └── tldraw-canvas.jsx    # Collaborative canvas (React)

../docs/                  # All project documentation (in parent folder)
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

See [CLEAN_ARCHITECTURE.md](../docs/CLEAN_ARCHITECTURE.md) for details.

## 🚀 WebSocket Features (Laravel Reverb)

LangConnect uses Laravel Reverb for real-time WebSocket communication:

- **Real-time messaging** - Messages appear instantly without refresh
- **Smart notifications** - One notification per sender (no spam)
- **Auto-reconnect** - Handles connection drops gracefully
- **Read receipts** - Auto-mark messages as read
- **Call signaling** - WebRTC offer/answer/ICE candidate exchange

### Reverb Configuration

Ensure your `.env` file has these settings:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Running Reverb

```bash
# Development
php artisan reverb:start

# Production (with debugging)
php artisan reverb:start --debug
```

## 📞 Voice & Video Calling

LangConnect includes WebRTC-powered voice and video calling with the following features:

- **Peer-to-peer calls** - Direct connection between users for low latency
- **Voice calls** - Audio-only calls for language practice
- **Video calls** - Face-to-face video calls
- **Mid-call video toggle** - Start with voice and add video later
- **Call signaling** - Uses Laravel Reverb WebSocket for call setup

### TURN Server Setup (Required for LAN/Network calls)

For calls to work across different networks, you need a TURN server. Run with Docker:

```bash
docker run -d --name coturn --network=host coturn/coturn \
  -n --log-file=stdout \
  --min-port=49160 --max-port=49200 \
  --realm=local --fingerprint \
  --lt-cred-mech --user=user:pass \
  --no-tls --no-dtls \
  --listening-ip=0.0.0.0 \
  --relay-ip=YOUR_SERVER_IP \
  --external-ip=YOUR_SERVER_IP \
  --verbose
```

Replace `YOUR_SERVER_IP` with your server's IP address (e.g., `192.168.1.10`).

Then update the TURN server configuration in `resources/views/messages/show.blade.php`:

```javascript
const rtcConfig = {
    iceServers: [
        { urls: 'stun:YOUR_SERVER_IP:3478' },
        {
            urls: 'turn:YOUR_SERVER_IP:3478',
            username: 'user',
            credential: 'pass'
        }
    ],
    sdpSemantics: 'unified-plan'
};
```

### Testing Voice/Video Calls

1. Open two browsers (Chrome + Firefox or Incognito)
2. Login as different users
3. Go to Messages and open a conversation
4. Click the phone or video icon to start a call
5. Accept the call on the other browser

## 🎨 Collaborative Canvas

Practice sessions include a real-time collaborative canvas powered by tldraw:

- **Real-time collaboration** - Both participants see changes instantly via WebSocket
- **Rich drawing tools** - Shapes, arrows, text, sticky notes, freehand drawing
- **Cursor tracking** - See your partner's cursor position with their name
- **Follow mode** - Follow your partner's viewport to see what they're working on
- **Auto-save** - Canvas state saves to database automatically (2 second debounce)
- **Persistent history** - Canvas is preserved after session ends (read-only)
- **Participant-only access** - Only session participants can view and edit

### Technical Implementation

- **Frontend**: React component with tldraw (`resources/js/tldraw-canvas.jsx`)
- **Controller**: `CanvasController` - Handles save/load/broadcast
- **Event**: `CanvasChanged` - Broadcasts canvas updates to session channel
- **Channel**: `private-session.{sessionId}` - Private channel for participants
- **Storage**: `canvas_data` JSON column on `practice_sessions` table
- **Routes**:
  - `POST /sessions/{session}/canvas` - Save canvas state
  - `GET /sessions/{session}/canvas` - Load canvas state
  - `POST /sessions/{session}/canvas/broadcast` - Broadcast changes to partner

### Reverb Configuration for Canvas

The canvas sends frequent updates. Ensure Reverb is configured with adequate limits:

```php
// config/reverb.php
'max_request_size' => 1_000_000,  // 1MB for canvas snapshots
'max_message_size' => 1_000_000,
'ping_interval' => 25,             // Keep connections alive
'activity_timeout' => 120,         // 2 minute timeout
```

### Testing Collaborative Canvas

1. Start a practice session between two users
2. Open the session in two browsers
3. Draw or add shapes - they appear on both screens instantly
4. Click "Follow [partner]" to sync your view with theirs
5. After session completion, canvas is preserved but read-only

## 📝 License

This project is open-sourced software licensed under the MIT license.

## 🙏 Credits

Built with Laravel 11 and modern web technologies.
