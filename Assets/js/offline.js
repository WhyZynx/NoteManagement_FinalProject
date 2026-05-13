
if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
        navigator.serviceWorker
            .register("/NoteManagement_FinalProject/service-worker.js", {
                scope: "/NoteManagement_FinalProject/"
            })
            .then(function (reg) {
                console.log("[SW] Registered:", reg.scope);
            })
            .catch(function (err) {
                console.warn("[SW] Registration failed:", err);
            });
    });
}

const DB_NAME = "mindflow_offline";
const DB_VERSION = 1;
let db = null;

function openDB() {
    return new Promise(function (resolve, reject) {
        const req = indexedDB.open(DB_NAME, DB_VERSION);

        req.onupgradeneeded = function (e) {
            const db = e.target.result;
            if (!db.objectStoreNames.contains("notes")) {
                db.createObjectStore("notes", { keyPath: "id" });
            }
            if (!db.objectStoreNames.contains("sync_queue")) {
                db.createObjectStore("sync_queue", {
                    keyPath: "qid",
                    autoIncrement: true
                });
            }
        };

        req.onsuccess = function (e) {
            db = e.target.result;
            resolve(db);
        };

        req.onerror = function () {
            reject(req.error);
        };
    });
}

function cacheNotesLocally(notes) {
    if (!db || !Array.isArray(notes)) return;
    const tx = db.transaction("notes", "readwrite");
    const store = tx.objectStore("notes");
    store.clear();
    notes.forEach(n => store.put(n));
}

function getLocalNotes() {
    return new Promise(function (resolve) {
        if (!db) return resolve([]);
        const tx = db.transaction("notes", "readonly");
        const store = tx.objectStore("notes");
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => resolve([]);
    });
}

function addToSyncQueue(action, data) {
    if (!db) return;
    const tx = db.transaction("sync_queue", "readwrite");
    tx.objectStore("sync_queue").add({
        action: action,   
        data: data,
        timestamp: Date.now()
    });
}
function getSyncQueue() {
    return new Promise(function (resolve) {
        if (!db) return resolve([]);
        const tx = db.transaction("sync_queue", "readonly");
        const req = tx.objectStore("sync_queue").getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => resolve([]);
    });
}

function removeSyncItem(qid) {
    if (!db) return;
    const tx = db.transaction("sync_queue", "readwrite");
    tx.objectStore("sync_queue").delete(qid);
}

async function syncPendingChanges() {
    const queue = await getSyncQueue();
    if (queue.length === 0) return;

    console.log(`[Offline] Syncing ${queue.length} pending change(s)...`);
    const base = window.location.origin + "/NoteManagement_FinalProject/Note_Module/";
    const uniqueQueue = [];
    const seen = new Set();

    for (let i = queue.length - 1; i >= 0; i--) {
        const item = queue[i];
        const key = item.action + "_" + (item.data.note_id || "");
        
        if (!seen.has(key)) {
            seen.add(key);
            uniqueQueue.unshift(item);
        } else {
            removeSyncItem(item.qid); 
        }
    }

    for (const item of uniqueQueue) {
        try {
            let url = "";
            let body = new FormData();

            if (item.action === "save_note") {
                url = base + "save_note.php";
                Object.entries(item.data).forEach(([k, v]) => body.append(k, v));
            } else if (item.action === "delete_note") {
                url = base + "delete_note.php";
                body.append("note_id", item.data.note_id);
            } else if (item.action === "pin_note") {
                url = base + "pin_note.php";
                body.append("note_id", item.data.note_id);
                body.append("force_pin", item.data.pin);
            }

            if (url) {
                const res = await fetch(url, { method: "POST", body });
                if (res.ok) {
                    removeSyncItem(item.qid);
                    console.log(`[Offline] Synced: ${item.action} (qid=${item.qid})`);
                    await new Promise(r => setTimeout(r, 1000));
                }
            }
        } catch (err) {
            console.warn("[Offline] Sync failed for item:", item, err);
        }
    }
    if (typeof loadNotes === "function") loadNotes();
}
window.addEventListener("load", async function () {
    await openDB();
    if (!navigator.onLine) showOfflineBanner();
});

window.addToSyncQueue = addToSyncQueue;
let offlineBanner = null;

function showOfflineBanner() {
    if (offlineBanner) return;

    offlineBanner = document.createElement("div");
    offlineBanner.id = "offline-banner";
    offlineBanner.className = "offline-banner";
    offlineBanner.innerHTML = `
        <i class="bi bi-wifi-off"></i>
        <span>You're offline — showing cached notes. Changes will sync when reconnected.</span>
    `;
    document.body.prepend(offlineBanner);

    const main = document.querySelector(".main-content");
    if (main) main.style.paddingTop = (parseInt(getComputedStyle(main).paddingTop) + 44) + "px";
}

function showOnlineBanner() {
    if (!offlineBanner) return;

    offlineBanner.classList.add("online");
    offlineBanner.innerHTML = `
        <i class="bi bi-wifi"></i>
        <span>Back online — syncing your changes...</span>
    `;

    const main = document.querySelector(".main-content");
    if (main) main.style.paddingTop = "";

    syncPendingChanges().then(function () {
        setTimeout(function () {
            if (offlineBanner) {
                offlineBanner.remove();
                offlineBanner = null;
            }
        }, 2500);
    });
}
window.addEventListener("offline", showOfflineBanner);
window.addEventListener("online", showOnlineBanner);
