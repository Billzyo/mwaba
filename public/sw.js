/**
 * Service Worker for Farm Monitoring System
 * Provides offline functionality and caching
 */

const CACHE_NAME = 'farm-monitor-v1.0.0';
const STATIC_CACHE = 'farm-monitor-static-v1.0.0';
const DYNAMIC_CACHE = 'farm-monitor-dynamic-v1.0.0';

// Files to cache immediately
const STATIC_ASSETS = [
    '/mwaba/',
    '/mwaba/dashboard',
    '/mwaba/login',
    'public/assets/css/styles.css',
    'public/assets/js/script.js',
    'public/assets/js/realtime.js',
    'public/assets/images/poslogo.png',
    'https://cdn.jsdelivr.net/npm/chart.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// API endpoints to cache with network-first strategy
const API_ENDPOINTS = [
    '/mwaba/api/realtime/data',
    '/mwaba/api/realtime/charts',
    '/mwaba/api/analytics/data'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Service Worker: Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('Service Worker: Static assets cached successfully');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Service Worker: Failed to cache static assets', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Service Worker: Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activated successfully');
                return self.clients.claim();
            })
    );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Handle different types of requests
    if (isAPIRequest(url.pathname)) {
        // API requests: Network first, then cache
        event.respondWith(networkFirstStrategy(request));
    } else if (isStaticAsset(url.pathname)) {
        // Static assets: Cache first, then network
        event.respondWith(cacheFirstStrategy(request));
    } else if (isPageRequest(url.pathname)) {
        // Page requests: Network first, then cache
        event.respondWith(networkFirstStrategy(request));
    } else {
        // Other requests: Network only
        event.respondWith(fetch(request));
    }
});

// Cache first strategy (for static assets)
async function cacheFirstStrategy(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        
        // Cache the response for future use
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.error('Cache first strategy failed:', error);
        
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/mwaba/offline.html');
        }
        
        throw error;
    }
}

// Network first strategy (for API and page requests)
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);
        
        // Cache successful responses
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('Network failed, trying cache:', error);
        
        // Try cache as fallback
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/mwaba/offline.html');
        }
        
        // Return offline data for API requests
        if (isAPIRequest(request.url)) {
            return getOfflineData(request.url);
        }
        
        throw error;
    }
}

// Check if request is for API endpoint
function isAPIRequest(pathname) {
    return API_ENDPOINTS.some(endpoint => pathname.includes(endpoint));
}

// Check if request is for static asset
function isStaticAsset(pathname) {
    return pathname.includes('/public/assets/') || 
           pathname.includes('.css') || 
           pathname.includes('.js') || 
           pathname.includes('.png') || 
           pathname.includes('.jpg') || 
           pathname.includes('.ico');
}

// Check if request is for page
function isPageRequest(pathname) {
    return pathname.startsWith('/mwaba/') && 
           !pathname.includes('/api/') && 
           !pathname.includes('/public/');
}

// Get offline data for API requests
function getOfflineData(url) {
    const offlineData = {
        '/mwaba/api/realtime/data': {
            status: 'success',
            data: {
                temperature: { value: 25, status: 'normal' },
                humidity: { value: 60, status: 'normal' },
                soil_moisture: { value: 45, status: 'normal' }
            },
            offline: true,
            timestamp: Date.now()
        },
        '/mwaba/api/realtime/charts': {
            status: 'success',
            data: {
                temperature: { labels: [], datasets: [{ data: [] }] },
                humidity: { labels: [], datasets: [{ data: [] }] },
                soil_moisture: { labels: [], datasets: [{ data: [] }] }
            },
            offline: true,
            timestamp: Date.now()
        },
        '/mwaba/api/analytics/data': {
            status: 'success',
            data: {
                sensorStats: [],
                insights: {
                    weather: { forecast: 'Unknown', recommendation: 'Check connection' },
                    crops: { status: 'Unknown', recommendation: 'Check connection' }
                }
            },
            offline: true,
            timestamp: Date.now()
        }
    };
    
    const data = offlineData[url] || {
        status: 'error',
        message: 'Offline - No cached data available',
        offline: true,
        timestamp: Date.now()
    };
    
    return new Response(JSON.stringify(data), {
        headers: {
            'Content-Type': 'application/json',
            'Cache-Control': 'no-cache'
        }
    });
}

// Background sync for offline actions
self.addEventListener('sync', event => {
    console.log('Service Worker: Background sync triggered');
    
    if (event.tag === 'sensor-data-sync') {
        event.waitUntil(syncSensorData());
    }
});

// Sync sensor data when back online
async function syncSensorData() {
    try {
        // Get pending sensor data from IndexedDB
        const pendingData = await getPendingSensorData();
        
        for (const data of pendingData) {
            try {
                const response = await fetch('/mwaba/api/realtime/broadcast', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    // Remove from pending queue
                    await removePendingSensorData(data.id);
                    console.log('Service Worker: Synced sensor data:', data.id);
                }
            } catch (error) {
                console.error('Service Worker: Failed to sync sensor data:', error);
            }
        }
    } catch (error) {
        console.error('Service Worker: Background sync failed:', error);
    }
}

// Push notification handling
self.addEventListener('push', event => {
    console.log('Service Worker: Push notification received');
    
    const options = {
        body: 'New farm alert detected',
        icon: 'public/assets/images/icon-192x192.png',
        badge: 'public/assets/images/badge-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            url: '/mwaba/dashboard'
        },
        actions: [
            {
                action: 'view',
                title: 'View Dashboard',
                icon: 'public/assets/images/icon-96x96.png'
            },
            {
                action: 'dismiss',
                title: 'Dismiss'
            }
        ]
    };
    
    if (event.data) {
        const data = event.data.json();
        options.body = data.message || options.body;
        options.data.url = data.url || options.data.url;
    }
    
    event.waitUntil(
        self.registration.showNotification('Farm Monitor Alert', options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    console.log('Service Worker: Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/mwaba/dashboard')
        );
    }
});

// Message handling from main thread
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CACHE_SENSOR_DATA') {
        cacheSensorDataOffline(event.data.payload);
    }
});

// Cache sensor data for offline sync
async function cacheSensorDataOffline(data) {
    try {
        // Store in IndexedDB for later sync
        await storePendingSensorData(data);
        console.log('Service Worker: Cached sensor data for offline sync');
    } catch (error) {
        console.error('Service Worker: Failed to cache sensor data:', error);
    }
}

// IndexedDB operations (simplified)
async function getPendingSensorData() {
    // In a real implementation, you'd use IndexedDB
    return [];
}

async function storePendingSensorData(data) {
    // In a real implementation, you'd use IndexedDB
    console.log('Storing pending sensor data:', data);
}

async function removePendingSensorData(id) {
    // In a real implementation, you'd use IndexedDB
    console.log('Removing pending sensor data:', id);
}

// Periodic background sync (if supported)
self.addEventListener('periodicsync', event => {
    if (event.tag === 'sensor-data-sync') {
        event.waitUntil(syncSensorData());
    }
});

console.log('Service Worker: Loaded successfully');
