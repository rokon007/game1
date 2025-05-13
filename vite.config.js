import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default ({ mode }) => {
    // Load env file based on `mode` in the current working directory
    const env = loadEnv(mode, process.cwd(), '');

    return defineConfig({
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
        ],
        define: {
            'process.env': {
                VITE_PUSHER_APP_KEY: JSON.stringify(env.VITE_PUSHER_APP_KEY),
                VITE_PUSHER_APP_CLUSTER: JSON.stringify(env.VITE_PUSHER_APP_CLUSTER),
            }
        }
    });
};









// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: [
//                 'resources/css/app.css',
//                 'resources/js/app.js',
//             ],
//             refresh: true,
//         }),
//     ],
// });
