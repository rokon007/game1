const CACHE_NAME = "app-cache-v5";
const OFFLINE_URL = "/offline.html";

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([OFFLINE_URL]);
        })
    );
    self.skipWaiting();
});

self.addEventListener("fetch", (event) => {
    const requestUrl = new URL(event.request.url);

    // 🚫 Admin routes should never be cached
    if (requestUrl.pathname.startsWith("/admin")) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // 🚫 Real-time routes should never be cached
    if (
        requestUrl.pathname.startsWith("/notifications") ||
        requestUrl.pathname.startsWith("/chat") ||
        requestUrl.pathname.startsWith("/messages") ||
        requestUrl.pathname.startsWith("/ws") ||
        requestUrl.pathname.startsWith("/api/notifications") ||
        requestUrl.pathname.startsWith("/api/chat")
    ) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Dynamic Laravel pages - Network First
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

    // Images - Network First
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

    // Static assets - Cache First
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
