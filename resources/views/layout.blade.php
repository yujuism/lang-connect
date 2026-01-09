@php
    use Illuminate\Support\Facades\Auth;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LangConnect - Language Exchange Platform')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary-color: #10b981;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --bg-tertiary: #f3f4f6;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            border: none;
            position: relative;
            z-index: 1030;
        }

        .dropdown-menu {
            z-index: 1040;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        .btn {
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        footer {
            background-color: var(--bg-primary);
            border-top: 1px solid var(--border-color);
        }

        .badge {
            font-weight: 500;
            padding: 0.35rem 0.75rem;
            border-radius: 0.5rem;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
        }
    </style>

    @yield('extra-css')

    <!-- Vite Assets (includes WebSocket / Laravel Echo) -->
    @vite(['resources/js/app.js'])
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-translate"></i> LangConnect
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('learning-requests.browse') ? 'active' : '' }}" href="{{ route('learning-requests.browse') }}">
                                <i class="bi bi-search"></i> Browse Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('learning-requests.*') ? 'active' : '' }}" href="{{ route('learning-requests.index') }}">
                                <i class="bi bi-chat-dots"></i> My Requests
                            </a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('members') ? 'active' : '' }}" href="{{ route('members') }}">
                            <i class="bi bi-people"></i> Community
                        </a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leaderboard.*') ? 'active' : '' }}" href="{{ route('leaderboard.index') }}">
                                <i class="bi bi-trophy-fill"></i> Leaderboard
                            </a>
                        </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link position-relative {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                                <i class="bi bi-chat-dots"></i> Messages
                                @if(Auth::user()->getUnreadMessageCount() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        {{ Auth::user()->getUnreadMessageCount() }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                                <i class="bi bi-bell"></i>
                                @if(Auth::user()->getUnreadNotificationCount() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        {{ Auth::user()->getUnreadNotificationCount() }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.show', Auth::user()) }}"><i class="bi bi-person"></i> My Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-pencil"></i> Edit Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('sessions.index') }}"><i class="bi bi-chat-dots"></i> My Sessions</a></li>
                                <li><a class="dropdown-item" href="{{ route('achievements.index') }}"><i class="bi bi-trophy"></i> Achievements</a></li>
                                <li><a class="dropdown-item" href="{{ route('levels.index') }}"><i class="bi bi-bar-chart-fill"></i> Level System</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm ms-2" href="{{ route('learning-requests.create') }}">
                                <i class="bi bi-plus-circle"></i> Request Help
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm ms-2" href="{{ route('register') }}">
                                <i class="bi bi-person-plus"></i> Sign Up
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Achievement Notifications -->
    @include('components.achievement-notification')

    <!-- Main Content -->
    <main class="@if(!request()->routeIs('home')) container my-4 @endif">
        @if(session('success'))
            <div class="@if(request()->routeIs('home')) container @endif">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="@if(request()->routeIs('home')) container @endif">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="@if(request()->routeIs('home')) container @endif">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3">LangConnect</h5>
                    <p class="text-secondary mb-0" style="max-width: 350px;">
                        A community-driven language exchange platform. Connect, practice, grow together.
                    </p>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h6 class="fw-semibold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('home') }}" class="text-secondary text-decoration-none hover-link">Home</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-link">Find Partner</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-link">Community</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-semibold mb-3">Platform Stats</h6>
                    <ul class="list-unstyled">
                        <li class="text-secondary mb-2"><span class="fw-semibold text-primary">{{ App\Models\Language::count() }}</span> Languages</li>
                        <li class="text-secondary mb-2"><span class="fw-semibold text-primary">{{ App\Models\PracticeSession::count() }}</span> Sessions</li>
                        <li class="text-secondary mb-2"><span class="fw-semibold text-primary">{{ App\Models\Achievement::count() }}</span> Achievements</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="opacity: 0.1;">
            <div class="text-center">
                <p class="text-secondary mb-0 small">&copy; 2025 LangConnect. Built with Laravel & Bootstrap.</p>
            </div>
        </div>
    </footer>

    <style>
        .hover-link {
            transition: all 0.3s ease;
        }
        .hover-link:hover {
            color: var(--primary-color) !important;
            padding-left: 4px;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- WebRTC Adapter - handles browser compatibility for WebRTC -->
    <script src="https://webrtc.github.io/adapter/adapter-latest.js"></script>

    @auth
    <!-- Global Incoming Call Handler -->
    <div id="global-incoming-call" class="d-none position-fixed" style="top: 20px; right: 20px; z-index: 10000; width: 320px; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.3); background: linear-gradient(135deg, #1a1a2e, #16213e);">
        <div class="p-4 text-white text-center">
            <div class="mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto"
                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); font-size: 2rem; font-weight: bold; animation: pulse 1.5s ease-in-out infinite;">
                    <span id="incoming-caller-initial">?</span>
                </div>
            </div>
            <h5 class="fw-semibold mb-1" id="incoming-caller-name">Incoming Call</h5>
            <p class="small opacity-75 mb-3" id="incoming-call-label">Voice call...</p>
            <div class="d-flex justify-content-center gap-3">
                <button class="btn rounded-circle d-flex align-items-center justify-content-center" onclick="rejectGlobalCall()" style="width: 56px; height: 56px; background: #dc2626; border: none; color: white;">
                    <i class="bi bi-telephone-x-fill fs-5"></i>
                </button>
                <button class="btn rounded-circle d-flex align-items-center justify-content-center" onclick="acceptGlobalCall()" style="width: 56px; height: 56px; background: #22c55e; border: none; color: white;">
                    <i class="bi bi-telephone-fill fs-5"></i>
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>

    <script>
        // Global call state
        let globalIncomingCall = null;
        let globalCallWindow = null;

        // Open call window (used by any page)
        window.openCallWindow = function(partnerId, callType) {
            const width = 400;
            const height = 550;
            const left = window.screen.width - width - 20;
            const top = 100;

            globalCallWindow = window.open(
                `/call/${partnerId}/window?type=${callType}`,
                'LangConnectCall',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=no`
            );

            if (globalCallWindow) {
                globalCallWindow.focus();
            }
            return globalCallWindow;
        };

        // Accept incoming call
        function acceptGlobalCall() {
            if (!globalIncomingCall) return;

            document.getElementById('global-incoming-call').classList.add('d-none');

            const width = 400;
            const height = 550;
            const left = window.screen.width - width - 20;
            const top = 100;

            globalCallWindow = window.open(
                `/call/${globalIncomingCall.caller_id}/window?call_id=${globalIncomingCall.call_id}&call_type=${globalIncomingCall.type}&auto_accept=1`,
                'LangConnectCall',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=no`
            );

            if (globalCallWindow) {
                globalCallWindow.focus();
            }

            globalIncomingCall = null;
        }

        // Reject incoming call
        async function rejectGlobalCall() {
            if (!globalIncomingCall) return;

            document.getElementById('global-incoming-call').classList.add('d-none');

            await fetch(`/call/${globalIncomingCall.call_id}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            globalIncomingCall = null;
        }

        // Setup global WebSocket listener for incoming calls
        function setupGlobalCallListener() {
            if (typeof window.Echo === 'undefined') {
                setTimeout(setupGlobalCallListener, 200);
                return;
            }

            const currentUserId = {{ auth()->id() }};

            // Listen on the user's private channel for incoming calls from anyone
            window.Echo.private(`user.${currentUserId}`)
                .listen('.call.initiated', (event) => {
                    // Don't show if already in a call window or on a page handling calls
                    if (globalCallWindow && !globalCallWindow.closed) return;
                    if (window.currentCall) return;

                    globalIncomingCall = event;

                    document.getElementById('incoming-caller-initial').textContent = event.caller_name ? event.caller_name.charAt(0).toUpperCase() : '?';
                    document.getElementById('incoming-caller-name').textContent = event.caller_name || 'Incoming Call';
                    document.getElementById('incoming-call-label').textContent = event.type === 'video' ? 'Video call...' : 'Voice call...';
                    document.getElementById('global-incoming-call').classList.remove('d-none');

                    // Auto-hide after 30 seconds if not answered
                    setTimeout(() => {
                        if (globalIncomingCall && globalIncomingCall.call_id === event.call_id) {
                            document.getElementById('global-incoming-call').classList.add('d-none');
                            globalIncomingCall = null;
                        }
                    }, 30000);
                });
        }

        // Start listening when page loads
        document.addEventListener('DOMContentLoaded', setupGlobalCallListener);
    </script>
    @endauth

    @yield('extra-js')
    @stack('scripts')
</body>
</html>
