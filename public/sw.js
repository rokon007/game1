const CACHE_NAME = "app-cache-v2";
const OFFLINE_URL = "/offline.html";

// install event
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                OFFLINE_URL
            ]);
        })
    );
    self.skipWaiting();
});

// fetch event
self.addEventListener("fetch", (event) => {
    const requestUrl = new URL(event.request.url);

    // Laravel auth pages, homepage, or API — always fetch from network
    if (
        requestUrl.pathname === "/" ||
        requestUrl.pathname.startsWith("/login") ||
        requestUrl.pathname.startsWith("/register") ||
        requestUrl.pathname.startsWith("/home") ||
        requestUrl.pathname.startsWith("/dashboard") ||
        requestUrl.pathname.startsWith("/api/")
    ) {
        event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // For images in Owl Carousel — always try network first
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

    // Default: try cache first, then network
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request).then((fetchRes) => {
                return caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, fetchRes.clone());
                    return fetchRes;
                });
            });
        }).catch(() => caches.match(OFFLINE_URL))
    );
});

// activate event (clear old cache)
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((keyList) =>
            Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    return caches.delete(key);
                }
            }))
        )
    );
});
