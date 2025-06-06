/**
 * Load axios for HTTP requests
 */
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Initialize Laravel Echo with Pusher
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
Pusher.logToConsole = true; // Enable Pusher debugging

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true,
});

// Subscribe to game.redirect channels for authenticated users
document.addEventListener('DOMContentLoaded', () => {
    const userId = window.userId;

    console.log('Initializing Echo with userId:', userId);

    if (!userId) {
        console.warn('No userId found, skipping channel subscription');
        return;
    }

    // Fetch game IDs for the user
    console.log('Fetching game IDs for user:', userId);
    axios.get('/api/user-games', {
        headers: {
            'Authorization': 'Bearer ' + document.querySelector('meta[name="csrf-token"]')?.content
        }
    })
    .then(response => {
        const gameIds = response.data.gameIds || [];
        console.log('Received game IDs:', gameIds);

        if (gameIds.length === 0) {
            console.log('No games found for user:', userId);
            return;
        }

        gameIds.forEach(gameId => {
            console.log('Subscribing to channel: game.redirect.' + gameId);
            window.Echo.channel(`game.redirect.${gameId}`)
                .listen('.game.redirect', (data) => {
                    console.log('Game redirect event received:', data);
                    const userData = data.redirect_data[userId];
                    if (userData) {
                        console.log('Redirecting user:', userId, 'to', `/game-room/${data.game_id}/${userData.sheet_id}`);
                        window.location.href = `/game-room/${data.game_id}/${userData.sheet_id}`;
                    } else {
                        console.log('No redirect data found for user:', userId, 'Data:', data.redirect_data);
                    }
                })
                .subscribed(() => {
                    console.log('Successfully subscribed to channel: game.redirect.' + gameId);
                })
                .error((error) => {
                    console.error('Failed to subscribe to channel: game.redirect.' + gameId, error);
                });
        });
    })
    .catch(error => {
        console.error('Failed to fetch user games:', error);
    });
});
