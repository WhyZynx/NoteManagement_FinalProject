const CACHE_NAME = "mindflow-v1";

self.addEventListener("install", function(event) {
    self.skipWaiting();
});

self.addEventListener("activate", function(event) {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", function(event) {
    if (event.request.method !== "GET") return;
    if (event.request.url.includes("socket.io")) return;
    if (event.request.url.includes("/uploads/")) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                return cached || fetch(event.request).then(res => {
                    if (res.ok) {
                        const clone = res.clone();
                        caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    }
                    return res;
                }).catch(() => new Response("", { status: 503 }));
            })
        );
        return;
    }
    event.respondWith(
        fetch(event.request)
            .then(function(response) {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(function() {
                return caches.match(event.request).then(function(cached) {
                    return cached || new Response("Offline - no cache available", {
                        status: 503,
                        statusText: "Service Unavailable"
                    });
                });
            })
    );
});