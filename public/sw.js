const CACHE_NAME = "app-cache-v3";
const OFFLINE_URL = "/offline.html";

// Install event
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([OFFLINE_URL]);
        })
    );
    self.skipWaiting();
});

// Fetch event
self.addEventListener("fetch", (event) => {
    const requestUrl = new URL(event.request.url);

    // Dynamic pages (Laravel routes, homepage, etc.) - Network First
    if (
        requestUrl.pathname === "/" ||
        requestUrl.pathname.startsWith("/login") ||
        requestUrl.pathname.startsWith("/register") ||
        requestUrl.pathname.startsWith("/home") ||
        requestUrl.pathname.startsWith("/dashboard") ||
        requestUrl.pathname.startsWith("/profile") ||
        requestUrl.pathname.startsWith("/api/")
    ) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request).then((cached) => cached || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // Images (Owl Carousel, etc.) - Network First
    if (requestUrl.pathname.match(/\.(?:png|jpg|jpeg|gif|webp)$/)) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Static assets (CSS, JS, icons) - Cache First
    event.respondWith(
        caches.match(event.request).then((response) => {
            return (
                response ||
                fetch(event.request).then((fetchRes) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, fetchRes.clone());
                        return fetchRes;
                    });
                })
            );
        }).catch(() => caches.match(OFFLINE_URL))
    );
});

// Activate event - clear old cache
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((keyList) =>
            Promise.all(
                keyList.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            )
        )
    );
});
