import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Track online users globally
window.onlineUsers = new Set();

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    // Join presence channel to track online users
    window.Echo.join('online')
        .here((users) => {
            window.onlineUsers = new Set(users.map(u => u.id));
            window.dispatchEvent(new CustomEvent('online-users-updated'));
        })
        .joining((user) => {
            window.onlineUsers.add(user.id);
            window.dispatchEvent(new CustomEvent('online-users-updated'));
        })
        .leaving((user) => {
            window.onlineUsers.delete(user.id);
            window.dispatchEvent(new CustomEvent('online-users-updated'));
        });
} catch (error) {
    window.Echo = null;
}

// Helper function to check if a user is online
window.isUserOnline = function(userId) {
    return window.onlineUsers.has(userId);
};
