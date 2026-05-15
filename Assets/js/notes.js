let currentView = "grid";
let autoSaveTimers = {};
let searchTimer;
const socket = io(window.location.origin);
let isRemoteUpdate = false;

async function fetchJson(url, options = {}) {
    if (!options.headers) {
        options.headers = {};
    }
    options.headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    options.headers['Pragma'] = 'no-cache';

    const res = await fetch(url, options);
    const text = await res.text();

    try {
        return JSON.parse(text);
    } catch (e) {
        console.error("Invalid JSON:", text);
        return {};
    }
}

async function renderNotes(notes) {
    const container = document.getElementById("notes-list");
    if (!container) return;

    container.className = currentView;
    container.innerHTML = "";

    if (!notes || notes.length === 0) {
        container.innerHTML = `
            <div class="notes-empty">
                <i class="bi bi-journal-x"></i>
                <p>No notes yet. Click <strong>+</strong> to create one.</p>
            </div>`;
        return;
    }

    const pinned = notes.filter(n => n.is_pinned == 1);
    const unpinned = notes.filter(n => n.is_pinned != 1);

    if (pinned.length) {
        const sec = document.createElement("div");
        sec.className = "notes-section";
        sec.innerHTML = `<div class="notes-section-label"><i class="bi bi-pin-angle-fill"></i> PINNED</div>`;
        const grid = document.createElement("div");
        grid.className = `notes-group ${currentView}`;
        pinned.forEach(n => grid.innerHTML += renderNoteCard(n));
        sec.appendChild(grid);
        container.appendChild(sec);
    }

    if (unpinned.length) {
        const sec = document.createElement("div");
        sec.className = "notes-section";
        if (pinned.length) {
            sec.innerHTML = `<div class="notes-section-label">RECENT THOUGHTS</div>`;
        }
        const grid = document.createElement("div");
        grid.className = `notes-group ${currentView}`;
        unpinned.forEach(n => grid.innerHTML += renderNoteCard(n));
        sec.appendChild(grid);
        container.appendChild(sec);
    }

    notes.forEach(n => socket.emit("join_note", n.id));

    await hydrateNotes(notes);
    attachAutoSaveEvents();
}

async function hydrateNotes(notes) {
    for (const note of notes) {
        let imgs = null;
        if (navigator.onLine) {
            imgs = await loadNoteImages(note.id);
        } else if (note._images && note._images.length) {
            imgs = note._images.map(img => `
                <div style="display:inline-block;position:relative;margin:5px;">
                    <img src="${img.image_path}"
                        style="width:90px;height:90px;object-fit:cover;border-radius:8px;" />
                </div>
            `).join("");
        }
        const labels = await loadNoteLabels(note.id);
        const imgBox = document.getElementById(`preview-images-${note.id}`);
        if (imgBox && imgs !== null) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(`<div>${imgs}</div>`, "text/html");
            const images = doc.querySelectorAll("img");
            images.forEach(img => {
                const el = document.createElement("img");
                el.src = img.src;
                el.style.cssText = img.style.cssText;
                imgBox.appendChild(el);
            });
        }

        const labelBox = document.getElementById(`preview-labels-${note.id}`);
        if (labelBox) labelBox.innerHTML = labels;
    }
}

window.onload = async function () {
    await loadPreferences();
    await loadNotes();

    const searchInput = document.getElementById("searchInput");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            clearTimeout(searchTimer);

            searchTimer = window.setTimeout(() => {
                const keyword = this.value.trim();

                if (keyword === "") {
                    searchTimer = null;
                    loadNotes();
                    return;
                } else {
                    searchNotes(keyword);
                }
            }, 300);
        });
    }
};

async function loadPreferences() {
    const res = await fetchJson(`${USER_BASE}get_preferences.php`);

    if (!res.data) return;

    if (res.data.view_mode) {
        currentView = res.data.view_mode;
        const container = document.getElementById("notes-list");
        if (container) container.className = currentView;
    }
}

function setViewMode(mode) {
    currentView = mode;

    const container = document.getElementById("notes-list");
    if (container) container.className = mode;

    const groups = document.querySelectorAll('.notes-group');
    groups.forEach(group => {
        group.className = `notes-group ${mode}`;
    });

    savePreference("view_mode", mode);
}

async function savePreference(key, value) {
    await fetchJson(`${USER_BASE}save_preferences.php`, {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: new URLSearchParams({ key, value })
    });
}

async function createNoteCard() {
    const res = await fetchJson(`${NOTE_BASE}create_note.php`, {
        method: "POST"
    });

    if (res.status === "success") {
        loadNotes();
    }
}

async function loadNotes() {
    if (!navigator.onLine) {
        if (typeof getLocalNotes === "function") {
            const localNotes = await getLocalNotes();
            localNotes.sort((a, b) => {
                const timeA = a.updated_at ? new Date(a.updated_at).getTime() : 0;
                const timeB = b.updated_at ? new Date(b.updated_at).getTime() : 0;
                return timeB - timeA;
            });
            await renderNotes(localNotes);
            return;
        }
    }
    const res = await fetchJson(`${NOTE_BASE}get_note.php`);
    const notes = res.data || [];
    if (typeof cacheNotesLocally === "function") {
            for (const note of notes) {
                const imgRes = await fetchJson(`${NOTE_BASE}get_note_images.php?note_id=${note.id}`);
                note._images = imgRes.data || [];
            }
            cacheNotesLocally(notes);}
    await renderNotes(notes);
}

async function loadNoteImages(noteId) {
    const res = await fetchJson(`${NOTE_BASE}get_note_images.php?note_id=${noteId}`);

    if (!res.data) return "";

    return res.data.map(img => `
        <div style="display:inline-block;position:relative;margin:5px;">
            <img src="${img.image_path}"
                style="width:90px;height:90px;object-fit:cover;border-radius:8px;" />
            <button onclick="deleteImage(${img.id}, ${noteId})"
                style="
                    position: absolute;
                    top: 0px;
                    right: 0px;
                    background: none;
                    border: none;
                    color: #e46e70;
                    font-size: 20px;
                    font-weight: bold;
                    line-height: 1;
                    cursor: pointer;
                    padding: 0;
                    transition: all 0.2s;
                    z-index: 10;
                    text-shadow: 0 0 3px rgba(255,255,255,0.8);
                "
                onmouseover="this.style.color='#ff7875'; this.style.transform='scale(1.2)';"
                onmouseout="this.style.color='#e46e70'; this.style.transform='scale(1)';"
            >
                ×
            </button>
        </div>
    `).join("");
}

async function uploadImage(noteId) {
    const input = document.querySelector(`.upload-image[data-id="${noteId}"]`);

    if (!input || !input.files.length) return;

    const form = new FormData();
    form.append("note_id", noteId);
    form.append("image", input.files[0]);

    const res = await fetchJson(`${NOTE_BASE}upload_image.php`, {
        method: "POST",
        body: form
    });

    if (res.status === "success") {
        const box = document.getElementById(`images-${noteId}`);
        if (box) {
            box.innerHTML = await loadNoteImages(noteId);
        }
    }
    await loadModalImages(noteId);
}

window.uploadImage = uploadImage;

async function deleteImage(imageId, noteId) {
    if (!confirm("Delete image?")) return;

    const form = new FormData();
    form.append("image_id", imageId);

    const res = await fetchJson(`${NOTE_BASE}delete_image.php`, {
        method: "POST",
        body: form
    });

    if (res.status === "success") {
        const box = document.getElementById(`images-${noteId}`);
        if (box) {
            box.innerHTML = await loadNoteImages(noteId);
        }
    }
}

window.deleteImage = deleteImage;

function getTextColorForBg(hex) {
    if (!hex || hex === 'transparent') return '#1e293b';
    hex = hex.trim();
    if (hex.startsWith('rgb')) {
        const parts = hex.match(/\d+/g);
        if (!parts || parts.length < 3) return '#1e293b';
        const r = parseInt(parts[0]);
        const g = parseInt(parts[1]);
        const b = parseInt(parts[2]);
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.5 ? '#1e293b' : '#f1f5f9';
    }
    const h = hex.replace('#', '');
    if (h.length !== 6) return '#1e293b';
    const r = parseInt(h.substring(0, 2), 16);
    const g = parseInt(h.substring(2, 4), 16);
    const b = parseInt(h.substring(4, 6), 16);
    if (isNaN(r) || isNaN(g) || isNaN(b)) return '#1e293b';
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? '#1e293b' : '#f1f5f9';
}

function renderNoteCard(note) {
    const color = note.note_color || "#ffffff";
    const textColor = getTextColorForBg(color);
    const isPinned = note.is_pinned == 1;
    const isLocked = note.is_locked == 1;
    const isShared = note.is_shared == 1;
    const timeAgo = formatTimeAgo(note.updated_at);
    
    const displayTitle = isLocked ? "Locked Note" : (escHtmlCard(note.title) || "<span class='note-empty-title'>Untitled</span>");
    const displayContent = isLocked ? "<i>This content is password protected</i>" : escHtmlCard(note.content);

    const statusIcons = `
        ${isLocked ? `<i class="bi bi-lock-fill status-icon" title="Locked"></i>` : ""}
        ${isShared ? `<i class="bi bi-share-fill status-icon" title="Shared"></i>` : ""}
    `;

    return `
     <div class="note-card" data-id="${note.id}"
        style="background:${color}; --note-text-color:${textColor};"
        onclick="openNoteModal(${note.id})">

       <div class="note-preview-images" id="preview-images-${note.id}" style="${isLocked ? 'display:none' : ''}"></div>

        <div class="note-card-body">
            <div class="note-preview-title">${displayTitle}</div>
            <div class="note-preview-content">${displayContent}</div>
            <div class="note-preview-labels" id="preview-labels-${note.id}"></div>
        </div>

        <div class="note-preview-footer">
            <span class="note-meta-time">${timeAgo}</span>
            <div class="note-preview-right">
                <div class="note-status-icons">${statusIcons}</div>
                <i class="bi bi-pin${isPinned ? "-fill pinned" : ""} pin-quick-btn"
                    title="${isPinned ? "Unpin" : "Pin"}"
                    onclick="event.stopPropagation(); togglePin(${note.id})"></i>
            </div>
        </div>
    </div>`;
}

function triggerFile(noteId) {
    const input = document.querySelector(`.upload-image[data-id="${noteId}"]`);
    if (input) input.click();
}

document.addEventListener("change", function (e) {
    if (e.target.classList.contains("upload-image")) {
        const noteId = e.target.dataset.id;
        uploadImage(noteId);
        e.target.value = "";
    }
});

function attachAutoSaveEvents() {
    document.querySelectorAll(".note-title, .note-content, .font-size, .font-style, .note-color")
        .forEach(el => {

            const type = (el.tagName === "SELECT" || el.type === "color") ? "change" : "input";

            el.addEventListener(type, function () {
                const id = this.dataset.id;

                syncNoteStyle(id);
                if (!isRemoteUpdate) {
                    socket.emit("edit_note", {
                        noteId: id,
                        title: document.querySelector(`.note-title[data-id="${id}"]`)?.value || "",
                        content: document.querySelector(`.note-content[data-id="${id}"]`)?.value || "",
                        font_size: document.querySelector(`.font-size[data-id="${id}"]`)?.value,
                        font_style: document.querySelector(`.font-style[data-id="${id}"]`)?.value,
                        note_color: document.querySelector(`.note-color[data-id="${id}"]`)?.value
                    });
                }
                clearTimeout(autoSaveTimers[id]);

                autoSaveTimers[id] = setTimeout(() => {
                    saveNote(id);
                }, 300);

                if (this.classList.contains("font-size")) {
                    savePreference("font_size", this.value);
                }

                if (this.classList.contains("font-style")) {
                    savePreference("font_style", this.value);
                }

                if (this.classList.contains("note-color")) {
                    savePreference("note_color", this.value);
                }
            });
        });
}

function syncNoteStyle(noteId) {
    const card = document.querySelector(`.note-card[data-id="${noteId}"]`);
    const title = document.querySelector(`.note-title[data-id="${noteId}"]`);
    const content = document.querySelector(`.note-content[data-id="${noteId}"]`);

    const size = document.querySelector(`.font-size[data-id="${noteId}"]`)?.value;
    const style = document.querySelector(`.font-style[data-id="${noteId}"]`)?.value;
    const color = document.querySelector(`.note-color[data-id="${noteId}"]`)?.value;

    if (!card) return;

    if (size) {
        const px = size + "px";
        card.style.fontSize = px;
        if (title) title.style.fontSize = px;
        if (content) content.style.fontSize = px;
    }

    if (style) {
        card.style.fontFamily = style;
        if (title) title.style.fontFamily = style;
        if (content) content.style.fontFamily = style;
    }

    if (color) {
        card.style.background = color;
        const textColor = getTextColorForBg(color);
        card.style.setProperty('--note-text-color', textColor);
    }
}

async function searchNotes(keyword) {
    const res = await fetchJson(
        `${API_BASE}api_search.php?keyword=${encodeURIComponent(keyword)}`
    );

    const notes =
        Array.isArray(res) ? res :
        Array.isArray(res?.data) ? res.data :
        [];

    await renderNotes(notes);
}

async function loadNoteLabels(noteId) {
    const res = await fetchJson(`${NOTE_BASE}get_note_labels.php?note_id=${noteId}`);

    if (!res.data) return "";

    return res.data.map(l => `
        <span class="label-badge">${l.label_name}</span>
    `).join("");
}

async function getAllLabels() {
    const res = await fetch(`${API_BASE}api_labels.php?action=list`);
    return await res.json();
}

async function getNoteLabelIds(noteId) {
    const res = await fetchJson(`${NOTE_BASE}get_note_labels.php?note_id=${noteId}`);

    if (!res.data) return [];

    return res.data.map(l => parseInt(l.id));
}

async function renderLabelSelector(noteId) {
    const labels = await getAllLabels();
    const selectedIds = await getNoteLabelIds(noteId);

    const box = document.querySelector(`.label-selector[data-id="${noteId}"] .label-box`);
    if (!box) return;

    box.innerHTML = labels.map(l => `
        <label>
            <input type="checkbox"
                   value="${l.id}"
                   data-note="${noteId}"
                   ${selectedIds.includes(parseInt(l.id)) ? "checked" : ""}>
            ${l.label_name}
        </label>
    `).join("");

    box.querySelectorAll("input").forEach(cb => {
        cb.addEventListener("change", function () {
            saveNote(noteId);
        });
    });
}

window.addEventListener("labelsUpdated", async function () {
    const notes = document.querySelectorAll(".note-card");

    for (const note of notes) {
        const noteId = note.dataset.id;
        await renderLabelSelector(noteId);
    }
});

window.addEventListener("labelsChanged", async function () {
    const notes = document.querySelectorAll(".note-card");

    for (const note of notes) {
        const noteId = note.dataset.id;

        const boxLabel = document.getElementById(`labels-${noteId}`);
        if (boxLabel) {
            boxLabel.innerHTML = await loadNoteLabels(noteId);
        }

        await renderLabelSelector(noteId);
    }
});

let currentShareNoteId = null;

window.openShareModal = function (noteId) {
    currentShareNoteId = noteId;

    const modal = document.getElementById("shareModal");
    const emailInput = document.getElementById("shareEmail");

    if (emailInput) emailInput.value = "";

    if (modal) modal.style.display = "flex"; 

    updateSharedUsersList(noteId);
};
window.updateSharedUsersList = async function(noteId) {
    const listContainer = document.getElementById("sharedUsersList");
    if (!listContainer) return;
    
    listContainer.innerHTML = '<div style="padding: 8px 0;"><span style="font-size: 13px; color: #555;">Loading...</span></div>';

    try {
        const res = await fetch(`${API_BASE}api_sharing.php?action=get_shared_users&note_id=${noteId}`);
        const data = await res.json();

        if (data.status === "success" && data.data && data.data.length > 0) {
            listContainer.innerHTML = data.data.map(user => `
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f9f9f9;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <span style="font-size: 13px; font-weight: 600; color: #333;">${escHtmlCard(user.email)}</span>
                        <span style="font-size: 11px; color: #777; text-transform: uppercase;">${user.permission}</span>
                    </div>
                    <button onclick="revokeShare(${noteId}, '${user.email}')" 
                            style="background: none; border: none; color: #e46e70; cursor: pointer; padding: 5px; border-radius: 5px;"
                            title="Remove access">
                        <i class="bi bi-x-circle-fill" style="font-size: 16px;"></i>
                    </button>
                </div>
            `).join("");
        } else {
            listContainer.innerHTML = '<div style="padding: 8px 0;"><span style="font-size: 13px; color: #999; font-style: italic;">Not shared with anyone yet.</span></div>';
        }
    } catch (err) {
        console.error("Error fetching shared users:", err);
        listContainer.innerHTML = '<div style="padding: 8px 0;"><span style="font-size: 13px; color: #e46e70;">Failed to load list.</span></div>';
    }
};

window.revokeShare = async function(noteId, email) {
    if (!confirm(`Revoke access for ${email}?`)) return;

    try {
        const res = await fetch(`${API_BASE}api_sharing.php`, {
            method: "POST",
            body: new URLSearchParams({
                action: "revoke_share",
                note_id: noteId,
                email: email
            })
        });

        const data = await res.json();
        if (data.status === "success") {
            updateSharedUsersList(noteId);
            if (typeof socket !== 'undefined') {
                socket.emit("note_revoked", { note_id: noteId, email: email });
            }
        } else {
            alert(data.message || "Failed to revoke share");
        }
    } catch (err) {
        console.error("Error revoking share:", err);
        alert("Network error while revoking share");
    }
};


window.closeShareModal = function () {
    const modal = document.getElementById("shareModal");
    if (modal) modal.style.display = "none";
};

document.addEventListener("click", async function (e) {

    if (e.target.id === "confirmShare") {

        if (!currentShareNoteId) {
            alert("No note selected");
            return;
        }

        const email = document.getElementById("shareEmail").value.trim();
        const permission = document.getElementById("sharePermission").value;

        if (!email) {
            alert("Please enter email");
            return;
        }

        try {
            const res = await fetch(`${API_BASE}api_sharing.php`, {
                method: "POST",
                body: new URLSearchParams({
                    action: "share_note",
                    note_id: currentShareNoteId,
                    email: email,
                    permission: permission
                })
            });

            const text = await res.text();
            console.log("API RESPONSE:", text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                alert("API is not returning JSON");
                return;
            }

            if (data.status === "success") {
                updateSharedUsersList(currentShareNoteId);
                document.getElementById("shareEmail").value = "";
                showToast("Shared successfully", "success");
                if (typeof socket !== 'undefined') {
                    socket.emit("note_shared", { to_email: email });
                }
            } else {
                showToast(data.message || "Share failed", "error");
            } 
        }
        catch (err) {
            console.error(err);
            alert("Network error");
        }
    }

    if (e.target.id === "shareModal") {
        closeShareModal();
    }
});

async function loadSharedNotes() {
    try {
        const res = await fetch(`${NOTE_BASE}shared_notes.php`);
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Invalid JSON:", text);
            return;
        }

        const container = document.getElementById("shared-notes");
        if (!container) return;

        if (data.status !== "success" || !data.data || data.data.length === 0) {
            container.innerHTML = `
                <p style="color: #9aa4b2; font-size: 13px; text-align: center; margin: 20px 0; font-style: italic;">
                    No shared notes
                </p>`;
            return;
        }
        if (!window.sharedNotesData) window.sharedNotesData = {};

        container.className = currentView; 
        container.innerHTML = `<div class="notes-group ${currentView}">` + data.data.map(n => {
            const color = n.note_color || "#ffffff";
            const textColor = getTextColorForBg(color);
            const timeAgo = formatTimeAgo(n.shared_at);
            
            window.sharedNotesData[n.id] = n;

            return `
                <div class="note-card" data-id="${n.id}"
                    style="background:${color}; --note-text-color:${textColor}; cursor: pointer;"
                    onclick="openSharedNoteModal(${n.id})">
                    <div class="note-card-body">
                        <div class="note-preview-title">${escHtmlCard(n.title) || "<span class='note-empty-title'>Untitled</span>"}</div>
                        <div class="note-preview-content">${escHtmlCard(n.content)}</div>
                    </div>
                    <div class="note-preview-footer">
                        <span class="note-meta-time">${timeAgo}</span>
                        <div class="note-preview-right" style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 12px; background: rgba(0,0,0,0.08); text-transform: uppercase;">${n.permission}</span>
                            <i class="bi bi-people-fill" title="From: ${n.owner_email}"></i>
                        </div>
                    </div>
                </div>`;
        }).join("") + `</div>`;

        data.data.forEach(n => socket.emit("join_note", n.id));
    } 
    catch (err) {
        console.error("Load shared error:", err);
    }
}

window.addEventListener("load", function () {
    loadSharedNotes();
});

window.createNoteCard = createNoteCard;
window.setViewMode = setViewMode;
window.deleteNote = deleteNote;
window.saveNote = saveNote;
window.loadNotes = loadNotes;

socket.on("note_updated", (data) => {
    isRemoteUpdate = true;

    const id = data.noteId;

    const title = document.querySelector(`.note-title[data-id="${id}"]`);
    const content = document.querySelector(`.note-content[data-id="${id}"]`);
    const size = document.querySelector(`.font-size[data-id="${id}"]`);
    const style = document.querySelector(`.font-style[data-id="${id}"]`);
    const color = document.querySelector(`.note-color[data-id="${id}"]`);

    if (title) title.value = data.title;
    if (content) content.value = data.content;
    if (size && data.font_size) size.value = data.font_size;
    if (style && data.font_style) style.value = data.font_style;
    if (color && data.note_color) color.value = data.note_color;

    syncNoteStyle(id);

    if (window.sharedNotesData && window.sharedNotesData[id]) {
        window.sharedNotesData[id].title = data.title;
        window.sharedNotesData[id].content = data.content;
        if (data.font_size) window.sharedNotesData[id].font_size = data.font_size;
        if (data.font_style) window.sharedNotesData[id].font_style = data.font_style;
        if (data.note_color) window.sharedNotesData[id].note_color = data.note_color;

        const card = document.querySelector(`#shared-notes .note-card[data-id="${id}"]`);
        if (card) {
            const previewTitle = card.querySelector(".note-preview-title");
            const previewContent = card.querySelector(".note-preview-content");
            if (previewTitle) previewTitle.innerHTML = escHtmlCard(data.title) || "<span class='note-empty-title'>Untitled</span>";
            if (previewContent) previewContent.innerHTML = escHtmlCard(data.content);
            if (data.note_color) {
                card.style.background = data.note_color;
                card.style.setProperty('--note-text-color', getTextColorForBg(data.note_color));
            }
        }
    }

    setTimeout(() => {
        isRemoteUpdate = false;
    }, 50);
});

socket.on("note_revoked", function(data) {
    const currentUser = window.currentUserEmail;
    if (data.email === currentUser) {
        const modal = document.getElementById("shared-note-edit-modal");
        if (modal) modal.remove();
        loadSharedNotes();
        showToast("Your access to a note has been revoked", "info");
    }
});

function formatTimeAgo(dateStr) {
    if (!dateStr) return "";
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60) return "Just now";
    if (diff < 3600) return Math.floor(diff / 60) + "m ago";
    if (diff < 86400) return Math.floor(diff / 3600) + "h ago";
    if (diff < 604800) return Math.floor(diff / 86400) + "d ago";
    return new Date(dateStr).toLocaleDateString();
}

function escHtmlCard(str) {
    if (!str) return "";
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function showDeleteModal(noteId) {
    const old = document.getElementById("delete-modal");
    if (old) old.remove();

    const modal = document.createElement("div");
    modal.id = "delete-modal";
    modal.className = "modal-overlay";
    modal.innerHTML = `
        <div class="modal-box">
            <div class="modal-icon"><i class="bi bi-trash3-fill"></i></div>
            <h3 class="modal-title">Delete note?</h3>
            <p class="modal-desc">This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel"
                    onclick="document.getElementById('delete-modal').remove()">Cancel</button>
                <button class="modal-btn modal-btn-danger"
                    onclick="confirmDeleteNote(${noteId})">Delete</button>
            </div>
        </div>`;
    document.body.appendChild(modal);
}

async function openNoteModal(noteId, isVerified = false) {
    let notesCheck = [];
    if (!navigator.onLine && typeof getLocalNotes === "function") {
        notesCheck = await getLocalNotes();
    } else {
        const resCheck = await fetchJson(`${NOTE_BASE}get_note.php`);
        notesCheck = resCheck.data || [];
    }
    const noteCheck = notesCheck.find(n => n.id == noteId);

    if (!isVerified && noteCheck && noteCheck.is_locked == 1) {
        loadNotes(); 
        showPasswordPrompt(noteId, function () {
            openNoteModal(noteId, true);
        });
        return;
    }

    if (autoSaveTimers[noteId]) {
        clearTimeout(autoSaveTimers[noteId]);
        await saveNote(noteId);
    }

    const existing = document.getElementById("note-edit-modal");
    if (existing) existing.remove();

    let notes = [];
    if (!navigator.onLine && typeof getLocalNotes === "function") {
        notes = await getLocalNotes();
    } else {
        const res = await fetchJson(`${NOTE_BASE}get_note.php`);
        notes = res.data || [];
    }
    const note = notes.find(n => n.id == noteId);
    if (!note) return;

    const size = note.font_size || 16;
    const style = note.font_style || "sans-serif";
    const color = note.note_color || "#ffffff";
    const isPinned = note.is_pinned == 1;

    const labelRes = await fetchJson(`${NOTE_BASE}get_note_labels.php?note_id=${noteId}`);
    const allLabels = await fetchJson(`${API_BASE}api_labels.php?action=list`);
    const assignedIds = (labelRes.data || []).map(l => parseInt(l.id));

    const labelCheckboxes = (Array.isArray(allLabels) ? allLabels : []).map(l => `
    <label class="modal-label-item">
        <input type="checkbox" value="${l.id}" data-note="${noteId}"
            ${assignedIds.includes(parseInt(l.id)) ? "checked" : ""}>
        <span>${escHtmlCard(l.label_name)}</span>
    </label>
    `).join("");

    const modal = document.createElement("div");
    modal.id = "note-edit-modal";
    modal.className = "note-modal-overlay";
    const modalTextColor = getTextColorForBg(color);
    modal.innerHTML = `
        <div class="note-modal-box" style="background:${color}; --note-text-color:${modalTextColor};" onclick="event.stopPropagation()">
            <div class="note-modal-header">
                <input class="note-modal-title note-title" data-id="${noteId}"
                    placeholder="Title"
                    value="${(note.title || '').replace(/"/g, '&quot;')}">
                <button class="note-modal-close" onclick="closeNoteModal(${noteId})">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="note-modal-images" id="modal-images-${noteId}"></div>

            <textarea class="note-modal-content note-content" data-id="${noteId}"
                placeholder="Take a note...">${note.content || ''}</textarea>

            <div class="note-modal-labels">
                <div class="note-modal-labels-list" id="modal-labels-${noteId}"></div>
                ${labelCheckboxes ? `
                <div class="note-modal-label-picker">
                    <span class="label-picker-title"><i class="bi bi-tag"></i> Labels</span>
                    <div class="label-picker-options">${labelCheckboxes}</div>
                </div>` : ""}
            </div>

            <div class="note-modal-toolbar">
                <div class="note-controls">
                    <select class="font-size" data-id="${noteId}">
                        <option value="14" ${size == 14 ? "selected" : ""}>14</option>
                        <option value="16" ${size == 16 ? "selected" : ""}>16</option>
                        <option value="18" ${size == 18 ? "selected" : ""}>18</option>
                        <option value="20" ${size == 20 ? "selected" : ""}>20</option>
                    </select>
                    <select class="font-style" data-id="${noteId}">
                        <option value="Arial" ${style === "Arial" ? "selected" : ""}>Arial</option>
                        <option value="serif" ${style === "serif" ? "selected" : ""}>Serif</option>
                        <option value="sans-serif" ${style === "sans-serif" ? "selected" : ""}>Sans-serif</option>
                        <option value="Montserrat" ${style === "Montserrat" ? "selected" : ""}>Montserrat</option>
                    </select>
                    <input type="color" class="note-color" data-id="${noteId}" value="${color}">
                </div>

                <div class="note-modal-actions">
                    <label class="action-icon" title="Upload image">
                        <i class="bi bi-image"></i>
                        <input type="file" class="upload-image" data-id="${noteId}"
                            accept="image/jpeg,image/png,image/webp" style="display:none;">
                    </label>
                    <i class="bi bi-pin${isPinned ? "-fill pinned" : ""} action-icon"
                        title="${isPinned ? "Unpin" : "Pin"}"
                        onclick="togglePin(${noteId})"></i>
                    <i class="bi bi-${note.is_locked == 1 ? 'lock-fill locked' : 'lock'} action-icon"
                        title="${note.is_locked == 1 ? 'Unlock note' : 'Lock note'}"
                        onclick="openLockModal(${noteId})"></i>
                    <i class="bi bi-send action-icon" title="Share"
                        onclick="openShareModal(${noteId})"></i>
                    <i class="bi bi-trash3 action-icon delete" title="Delete"
                        onclick="closeNoteModal(${noteId}); deleteNote(${noteId})"></i>
                </div>
            </div>

        </div>`;

    document.body.appendChild(modal);

    let mouseDownOnOverlay = false;

    modal.addEventListener("mousedown", function (e) {
        mouseDownOnOverlay = (e.target === modal);
    });

    modal.addEventListener("mouseup", function (e) {
        if (mouseDownOnOverlay && e.target === modal) {
            closeNoteModal(noteId);
        }
        mouseDownOnOverlay = false;
    });

    const box = modal.querySelector(".note-modal-box");

    const textarea = box.querySelector("textarea");
    autoResizeModalTextarea(textarea);

    attachAutoSaveEvents();

    const colorPicker = box.querySelector(".note-color");
    colorPicker.addEventListener("input", function () {
        box.style.background = this.value;
        const textColor = getTextColorForBg(this.value);
        box.style.setProperty('--note-text-color', textColor);
    });

    await loadModalImages(noteId);
    await loadModalLabels(noteId);

    box.querySelectorAll(".label-picker-options input[type=checkbox]").forEach(cb => {
        cb.addEventListener("change", () => saveNote(noteId));
    });
}

async function loadModalImages(noteId) {
    const container = document.getElementById(`modal-images-${noteId}`);
    if (!container) return;
    if (!navigator.onLine) {
        if (typeof getLocalNotes === "function") {
            const localNotes = await getLocalNotes();
            const note = localNotes.find(n => n.id == noteId);
            if (note && note._images && note._images.length) {
                container.innerHTML = note._images.map(img => `
                    <div class="note-img-wrap">
                        <img src="${img.image_path}" alt="note image" loading="lazy">
                    </div>
                `).join("");
            }
        }
        return;
    }
    const res = await fetchJson(`${NOTE_BASE}get_note_images.php?note_id=${noteId}`);
    if (!res.data || !res.data.length) return;

    container.innerHTML = res.data.map(img => `
        <div class="note-img-wrap">
            <img src="${img.image_path}" alt="note image" loading="lazy">
            <button class="note-img-delete" onclick="deleteImage(${img.id}, ${noteId}); loadModalImages(${noteId})">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join("");
}

async function loadModalLabels(noteId) {
    const container = document.getElementById(`modal-labels-${noteId}`);
    if (!container) return;

    const res = await fetchJson(`${NOTE_BASE}get_note_labels.php?note_id=${noteId}`);
    if (!res.data) return;

    container.innerHTML = res.data.map(l => `
        <span class="label-badge">${escHtmlCard(l.label_name)}</span>
    `).join("");
}

async function closeNoteModal(noteId) {
    if (autoSaveTimers[noteId]) {
        clearTimeout(autoSaveTimers[noteId]);
        delete autoSaveTimers[noteId];
    }
    await saveNote(noteId);

    const modal = document.getElementById("note-edit-modal");
    if (modal) {
        modal.style.opacity = "0";
        setTimeout(() => {
            modal.remove();
            loadNotes();
        }, 200);
    }
}

function autoResizeModalTextarea(el) {
    if (!el) return;
    const resize = () => {
        el.style.height = "auto";
        el.style.height = Math.min(el.scrollHeight, 400) + "px";
    };
    el.addEventListener("input", resize);
    resize();
}

window.openNoteModal = openNoteModal;
window.closeNoteModal = closeNoteModal;
window.loadModalImages = loadModalImages;

async function openLockModal(noteId) {
    const res = await fetchJson(`${NOTE_BASE}get_note.php`);
    const notes = res.data || [];
    const note = notes.find(n => n.id == noteId);
    if (!note) return;

    const isLocked = note.is_locked == 1;

    const old = document.getElementById("lock-modal");
    if (old) old.remove();

    const modal = document.createElement("div");
    modal.id = "lock-modal";
    modal.className = "modal-overlay";

    if (!isLocked) {
        modal.innerHTML = `
            <div class="modal-box lock-modal-box" onclick="event.stopPropagation()">
                <div class="modal-icon lock-icon"><i class="bi bi-lock-fill"></i></div>
                <h3 class="modal-title">Lock this note</h3>
                <p class="modal-desc">Set a password to protect this note.</p>
                <div class="lock-input-group">
                    <div class="lock-field">
                        <input type="text" name="fakeusernameremembered" style="display:none;" autocomplete="username">
                        <input type="password" id="lockPassword" class="lock-input" placeholder="Password" autocomplete="off" data-form-type="other">
                        <i class="bi bi-eye-slash lock-eye" onclick="toggleLockEye('lockPassword', this)"></i>
                    </div>
                    <div class="lock-field">
                        <input type="password" id="lockPasswordConfirm" class="lock-input" placeholder="Confirm password" autocomplete="off" data-form-type="other">
                        <i class="bi bi-eye-slash lock-eye" onclick="toggleLockEye('lockPasswordConfirm', this)"></i>
                    </div>
                    <p class="lock-error" id="lockError"></p>
                </div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" onclick="closeLockModal()">Cancel</button>
                    <button class="modal-btn modal-btn-primary" onclick="confirmLockNote(${noteId})">
                        <i class="bi bi-lock"></i> Lock
                    </button>
                </div>
            </div>`;
    } else {
        modal.innerHTML = `
            <div class="modal-box lock-modal-box" onclick="event.stopPropagation()">
                <div class="modal-icon" style="color:#22c55e"><i class="bi bi-unlock-fill"></i></div>
                <h3 class="modal-title">Unlock this note</h3>
                <p class="modal-desc">Enter the current password to remove protection.</p>
                <div class="lock-input-group">
                    <div class="lock-field">
                        <input type="password" id="unlockPassword" class="lock-input" placeholder="Current password" 
                            autocomplete="off" data-form-type="other">
                        <i class="bi bi-eye-slash lock-eye" onclick="toggleLockEye('unlockPassword', this)"></i>
                    </div>
                    <p class="lock-error" id="lockError"></p>
                </div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-cancel" onclick="closeLockModal()">Cancel</button>
                    <button class="modal-btn modal-btn-primary" style="background:#22c55e" onclick="confirmUnlockNote(${noteId})">
                        <i class="bi bi-unlock"></i> Unlock
                    </button>
                </div>
                <div style="margin-top:16px;text-align:center">
                    <button class="lock-change-btn" onclick="closeLockModal(); openChangePwModal(${noteId})">
                        <i class="bi bi-key"></i> Change password instead
                    </button>
                </div>
            </div>`;
    }

    document.body.appendChild(modal);
    modal.addEventListener("mousedown", function (e) {
        if (e.target === modal) closeLockModal();
    });

    setTimeout(() => {
        modal.querySelectorAll(".lock-input").forEach(el => {
            el.value = "";
        });
        const first = modal.querySelector(".lock-input");
        if (first) first.focus();
    }, 200);
}

function closeLockModal() {
    const m = document.getElementById("lock-modal");
    if (m) m.remove();
}

function toggleLockEye(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    } else {
        input.type = "password";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    }
}

async function confirmLockNote(noteId) {
    const pw = document.getElementById("lockPassword")?.value;
    const pw2 = document.getElementById("lockPasswordConfirm")?.value;
    const errEl = document.getElementById("lockError");

    if (!pw || pw.length < 4) {
        errEl.textContent = "Password must be at least 4 characters.";
        return;
    }
    if (pw !== pw2) {
        errEl.textContent = "Passwords do not match.";
        return;
    }

    errEl.textContent = "";

    const form = new FormData();
    form.append("note_id", noteId);
    form.append("password", pw);

    const res = await fetchJson(`${NOTE_BASE}lock_note.php`, { method: "POST", body: form });

    if (res.status === "success" || res === "Locked successfully") {
        closeLockModal();
        showToast("Note locked successfully", "success");
        loadNotes();
    } else {
        errEl.textContent = res.message || "Failed to lock note.";
    }
}

async function confirmUnlockNote(noteId) {
    const pw = document.getElementById("unlockPassword")?.value;
    const errEl = document.getElementById("lockError");

    if (!pw) {
        errEl.textContent = "Please enter the password.";
        return;
    }

    const form = new FormData();
    form.append("note_id", noteId);
    form.append("password", pw);
    form.append("action", "unlock");

    const res = await fetchJson(`${NOTE_BASE}lock_note.php`, { method: "POST", body: form });

    if (res.status === "success") {
        closeLockModal();
        showToast("Note unlocked", "success");
        loadNotes();
    } else {
        errEl.textContent = res.message || "Wrong password.";
    }
}

async function openChangePwModal(noteId) {
    const old = document.getElementById("change-pw-modal");
    if (old) old.remove();

    const modal = document.createElement("div");
    modal.id = "change-pw-modal";
    modal.className = "modal-overlay";
    modal.innerHTML = `
        <div class="modal-box lock-modal-box" onclick="event.stopPropagation()">
            <div class="modal-icon" style="color:#f59e0b"><i class="bi bi-key-fill"></i></div>
            <h3 class="modal-title">Change note password</h3>
            <p class="modal-desc">Enter current password then set a new one.</p>
            <div class="lock-input-group">
                <div class="lock-field">
                    <input type="password" id="oldPassword" class="lock-input" placeholder="Current password" autocomplete="off" data-form-type="other">
                    <i class="bi bi-eye-slash lock-eye" onclick="toggleLockEye('oldPassword', this)"></i>
                </div>
                <div class="lock-field">
                    <input type="password" id="newPassword" class="lock-input" placeholder="New password" autocomplete="off" data-form-type="other">
                    <i class="bi bi-eye-slash lock-eye" onclick="toggleLockEye('newPassword', this)"></i>
                </div>
                <div class="lock-field">
                    <input type="password" id="newPasswordConfirm" class="lock-input" placeholder="Confirm new password" autocomplete="off" data-form-type="other">
                    <i class="bi bi-eye-slash lock-eye" onclick="toggleLockEye('newPasswordConfirm', this)"></i>
                </div>
                <p class="lock-error" id="changePwError"></p>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="closeChangePwModal()">Cancel</button>
                <button class="modal-btn modal-btn-primary" style="background:#f59e0b" onclick="confirmChangePw(${noteId})">
                    <i class="bi bi-check2"></i> Save
                </button>
            </div>
        </div>`;

    document.body.appendChild(modal);
    modal.addEventListener("mousedown", function (e) {
        if (e.target === modal) closeChangePwModal();
    });
    setTimeout(() => {
        modal.querySelectorAll(".lock-input").forEach(el => { el.value = ""; });
        modal.querySelector(".lock-input")?.focus();
    }, 200);
}

function closeChangePwModal() {
    document.getElementById("change-pw-modal")?.remove();
}

async function confirmChangePw(noteId) {
    const oldPw = document.getElementById("oldPassword")?.value;
    const newPw = document.getElementById("newPassword")?.value;
    const newPw2 = document.getElementById("newPasswordConfirm")?.value;
    const errEl = document.getElementById("changePwError");

    if (!oldPw) { errEl.textContent = "Enter current password."; return; }
    if (!newPw || newPw.length < 4) { errEl.textContent = "New password must be at least 4 characters."; return; }
    if (newPw !== newPw2) { errEl.textContent = "New passwords do not match."; return; }

    errEl.textContent = "";

    const form = new FormData();
    form.append("note_id", noteId);
    form.append("old_password", oldPw);
    form.append("new_password", newPw);

    const res = await fetchJson(`${NOTE_BASE}change_note_password.php`, { method: "POST", body: form });

    if (res.status === "success" || res === "Password changed successfully") {
        closeChangePwModal();
        showToast("Password changed successfully", "success");
    } else {
        errEl.textContent = res.message || res || "Failed. Check current password.";
    }
}

function showToast(message, type = "success") {
    const existing = document.getElementById("app-toast");
    if (existing) existing.remove();

    const toast = document.createElement("div");
    toast.id = "app-toast";
    toast.className = `app-toast app-toast-${type}`;
    toast.innerHTML = `
        <i class="bi bi-${type === "success" ? "check-circle-fill" : type === "error" ? "x-circle-fill" : "info-circle-fill"}"></i>
        <span>${message}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showPasswordPrompt(noteId, onSuccess) {
    const old = document.getElementById("pw-prompt-modal");
    if (old) old.remove();

    const modal = document.createElement("div");
    modal.id = "pw-prompt-modal";
    modal.className = "modal-overlay";
    modal.innerHTML = `
        <div class="modal-box lock-modal-box" onclick="event.stopPropagation()">
            <div class="modal-icon" style="color:#6366f1"><i class="bi bi-shield-lock-fill"></i></div>
            <h3 class="modal-title">Note is locked</h3>
            <p class="modal-desc">Enter password to view or edit this note.</p>
            <div class="lock-input-group">
                <div class="lock-field">
                    <input type="password" id="promptPassword" class="lock-input" placeholder="Password" autocomplete="off" data-form-type="other">
                    <i class="bi bi-eye-slash lock-eye" onclick="toggleLockEye('promptPassword', this)"></i>
                </div>
                <p class="lock-error" id="promptError"></p>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" onclick="document.getElementById('pw-prompt-modal').remove()">Cancel</button>
                <button class="modal-btn modal-btn-primary" id="promptConfirmBtn">
                    <i class="bi bi-unlock"></i> Unlock
                </button>
            </div>
        </div>`;

    document.body.appendChild(modal);
    setTimeout(() => {
        modal.querySelectorAll(".lock-input").forEach(el => { el.value = ""; });
        modal.querySelector(".lock-input")?.focus();
    }, 200);

    modal.querySelector("#promptPassword").addEventListener("keydown", function (e) {
        if (e.key === "Enter") document.getElementById("promptConfirmBtn").click();
    });

    document.getElementById("promptConfirmBtn").addEventListener("click", async function () {
        const pw = document.getElementById("promptPassword")?.value;
        const errEl = document.getElementById("promptError");
        if (!pw) { errEl.textContent = "Enter password."; return; }

        const form = new FormData();
        form.append("note_id", noteId);
        form.append("password", pw);
        form.append("action", "verify");

        const res = await fetchJson(`${NOTE_BASE}lock_note.php`, { method: "POST", body: form });

        if (res.status === "success") {
            modal.remove();
            onSuccess();
        } else {
            errEl.textContent = "Wrong password. Try again.";
            document.getElementById("promptPassword").value = "";
            document.getElementById("promptPassword").focus();
        }
    });

    modal.addEventListener("mousedown", function (e) {
        if (e.target === modal) modal.remove();
    });
}

async function deleteNote(id) {
    showDeleteModal(id);
}

async function saveNote(noteId) {
    const titleEl = document.querySelector(`.note-title[data-id="${noteId}"]`);
    const contentEl = document.querySelector(`.note-content[data-id="${noteId}"]`);
    if (!titleEl || !contentEl) return;

    const sizeEl = document.querySelector(`.font-size[data-id="${noteId}"]`);
    const styleEl = document.querySelector(`.font-style[data-id="${noteId}"]`);
    const colorEl = document.querySelector(`.note-color[data-id="${noteId}"]`);

    const labelIds = Array.from(
        document.querySelectorAll(
            `.label-picker-options input[type=checkbox][data-note="${noteId}"]:checked,
             #note-edit-modal .label-picker-options input[type=checkbox]:checked`
        )
    ).map(cb => cb.value);

    const data = {
        note_id: noteId,
        title: titleEl.value,
        content: contentEl.value,
        font_size: sizeEl ? sizeEl.value : 16,
        font_style: styleEl ? styleEl.value : "Arial",
        note_color: colorEl ? colorEl.value : "#ffffff",
        labels: JSON.stringify(labelIds)
    };

    if (!navigator.onLine) {
        if (typeof addToSyncQueue === "function") {
            addToSyncQueue("save_note", data);
            showSaveIndicator(noteId, "queued");
        }
        if (typeof cacheNotesLocally === "function" && typeof getLocalNotes === "function") {
        const localNotes = await getLocalNotes();
        const idx = localNotes.findIndex(n => n.id == noteId);
            if (idx !== -1) {
                localNotes[idx].title = data.title;
                localNotes[idx].content = data.content;
                localNotes[idx].note_color = data.note_color;
                localNotes[idx].font_size = data.font_size;
                localNotes[idx].font_style = data.font_style;
                localNotes[idx].updated_at = new Date().toISOString();
                localNotes.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                cacheNotesLocally(localNotes);
                renderNotes(localNotes);
            }
        }
        return;
    }

    const form = new FormData();
    Object.entries(data).forEach(([k, v]) => form.append(k, v));
    await fetchJson(`${NOTE_BASE}save_note.php`, { method: "POST", body: form });
    showSaveIndicator(noteId, "saved");
}

async function confirmDeleteNote(noteId) {
    if (!navigator.onLine) {
        if (typeof addToSyncQueue === "function") {
            addToSyncQueue("delete_note", { note_id: noteId });
        }
        const card = document.querySelector(`.note-card[data-id="${noteId}"]`);
        if (card) card.style.display = "none";
        document.getElementById("delete-modal")?.remove();
        if (typeof showToast === "function") showToast("Deleted offline — will sync when back online", "info");
        return;
    }

    const form = new FormData();
    form.append("note_id", noteId);
    await fetchJson(`${NOTE_BASE}delete_note.php`, { method: "POST", body: form });
    document.getElementById("delete-modal")?.remove();
    loadNotes();
}

async function togglePin(noteId) {
    if (!navigator.onLine) {
        if (typeof addToSyncQueue === "function") {
            if (typeof getLocalNotes === "function" && typeof cacheNotesLocally === "function") {
                const localNotes = await getLocalNotes();
                const note = localNotes.find(n => n.id == noteId);
                if (note) {
                    const newPinState = note.is_pinned == 1 ? 0 : 1;
                    addToSyncQueue("pin_note", { 
                        note_id: noteId, 
                        pin: newPinState
                    });
                     note.is_pinned = newPinState;
                    cacheNotesLocally(localNotes);
                }
             }
        }

        const pinBtn = document.querySelector(`.note-card[data-id="${noteId}"] .pin-quick-btn`);
        if (pinBtn) {
            pinBtn.classList.toggle("pinned");
            pinBtn.classList.toggle("bi-pin-fill");
            pinBtn.classList.toggle("bi-pin");
        }
        if (typeof showToast === "function") showToast("Pinned offline — will sync when reconnected", "info");
        const modal = document.getElementById("note-edit-modal");
        if (!modal) {
            if (typeof getLocalNotes === "function") {
                getLocalNotes().then(localNotes => renderNotes(localNotes));
            }
        }
        return;
    }

    const form = new FormData();
    form.append("note_id", noteId);
    await fetchJson(`${NOTE_BASE}pin_note.php`, { method: "POST", body: form });

    const pinIcon = document.querySelector(`#note-edit-modal .action-icon[onclick*="togglePin"]`);
    if (pinIcon) {
        const isPinned = pinIcon.classList.contains("pinned");
        if (isPinned) {
            pinIcon.classList.remove("pinned", "bi-pin-fill");
            pinIcon.classList.add("bi-pin");
            pinIcon.title = "Pin";
        } else {
            pinIcon.classList.add("pinned", "bi-pin-fill");
            pinIcon.classList.remove("bi-pin");
            pinIcon.title = "Unpin";
        }
    }

    const pinQuickBtn = document.querySelector(`.note-card[data-id="${noteId}"] .pin-quick-btn`);
    if (pinQuickBtn) {
        const isPinned = pinQuickBtn.classList.contains("pinned");
        if (isPinned) {
            pinQuickBtn.classList.remove("pinned", "bi-pin-fill");
            pinQuickBtn.classList.add("bi-pin");
        } else {
            pinQuickBtn.classList.add("pinned", "bi-pin-fill");
            pinQuickBtn.classList.remove("bi-pin");
        }
    }

    loadNotes();
}

function showSaveIndicator(noteId, status) {
    const modal = document.getElementById("note-edit-modal");
    if (!modal) return;

    let el = modal.querySelector(".save-indicator");
    if (!el) {
        el = document.createElement("span");
        el.className = "save-indicator";
        modal.querySelector(".note-modal-toolbar")?.prepend(el);
    }

    if (status === "saved") {
        el.textContent = "✓ Saved";
        el.style.color = "#22c55e";
    } else if (status === "queued") {
        el.textContent = "⏳ Queued (offline)";
        el.style.color = "#f59e0b";
    }

    setTimeout(() => { el.textContent = ""; }, 3000);
}

window.openSharedNoteModal = async function(noteId) {
    const note = window.sharedNotesData ? window.sharedNotesData[noteId] : null;
    if (!note) return;

    const size = note.font_size || 16;
    const style = note.font_style || "sans-serif";
    const color = note.note_color || "#ffffff";
    const canEdit = (note.permission === "edit");

    const existing = document.getElementById("shared-note-edit-modal");
    if (existing) existing.remove();

    const modal = document.createElement("div");
    modal.id = "shared-note-edit-modal";
    modal.className = "note-modal-overlay";
    const modalTextColor = getTextColorForBg(color);

    modal.innerHTML = `
        <div class="note-modal-box" style="background:${color}; --note-text-color:${modalTextColor};" onclick="event.stopPropagation()">
            <div class="note-modal-header">
                <input class="note-modal-title note-title" data-id="${noteId}"
                    placeholder="Title"
                    value="${(note.title || '').replace(/"/g, '&quot;')}"
                    ${canEdit ? '' : 'readonly'}>
                <button class="note-modal-close" onclick="closeSharedNoteModal(${noteId})">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div style="padding: 0 20px 10px 20px; font-size: 12px; display: flex; justify-content: space-between; align-items: center;">
                <span style="opacity: 0.8;"><i class="bi bi-person-circle"></i> From: <strong>${note.owner_email}</strong></span>
                <span style="background: ${canEdit ? 'rgba(34, 197, 94, 0.1)' : 'rgba(245, 158, 11, 0.1)'}; color: ${canEdit ? '#22c55e' : '#f59e0b'}; padding: 4px 10px; border-radius: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    ${canEdit ? '<i class="bi bi-pencil-fill"></i> Edit' : '<i class="bi bi-eye-fill"></i> Read-only'}
                </span>
            </div>

            <textarea class="note-modal-content note-content" data-id="${noteId}"
                placeholder="Note content..." ${canEdit ? '' : 'readonly'}>${note.content || ''}</textarea>

            <div class="note-modal-toolbar">
                <div class="note-controls">
                    <select class="font-size" data-id="${noteId}" ${canEdit ? '' : 'disabled'}>
                        <option value="14" ${size == 14 ? "selected" : ""}>14</option>
                        <option value="16" ${size == 16 ? "selected" : ""}>16</option>
                        <option value="18" ${size == 18 ? "selected" : ""}>18</option>
                        <option value="20" ${size == 20 ? "selected" : ""}>20</option>
                    </select>
                    <select class="font-style" data-id="${noteId}" ${canEdit ? '' : 'disabled'}>
                        <option value="Arial" ${style === "Arial" ? "selected" : ""}>Arial</option>
                        <option value="serif" ${style === "serif" ? "selected" : ""}>Serif</option>
                        <option value="sans-serif" ${style === "sans-serif" ? "selected" : ""}>Sans-serif</option>
                        <option value="Montserrat" ${style === "Montserrat" ? "selected" : ""}>Montserrat</option>
                    </select>
                    <input type="color" class="note-color" data-id="${noteId}" value="${color}" ${canEdit ? '' : 'disabled'}>
                </div>
                
                <div class="note-modal-actions"></div>
            </div>
        </div>`;

    document.body.appendChild(modal);

    let mouseDownOnOverlay = false;
    modal.addEventListener("mousedown", function (e) { mouseDownOnOverlay = (e.target === modal); });
    modal.addEventListener("mouseup", function (e) {
        if (mouseDownOnOverlay && e.target === modal) closeSharedNoteModal(noteId);
        mouseDownOnOverlay = false;
    });

    const box = modal.querySelector(".note-modal-box");
    const textarea = box.querySelector("textarea");
    if (typeof autoResizeModalTextarea === "function") autoResizeModalTextarea(textarea);

    if (canEdit) {
        const els = box.querySelectorAll(".note-title, .note-content, .font-size, .font-style, .note-color");
        els.forEach(el => {
            const type = (el.tagName === "SELECT" || el.type === "color") ? "change" : "input";
            el.addEventListener(type, function () {
                if (typeof syncNoteStyle === "function") syncNoteStyle(noteId);
                
                if (typeof isRemoteUpdate !== 'undefined' && !isRemoteUpdate && typeof socket !== 'undefined') {
                    socket.emit("edit_note", {
                        noteId: noteId,
                        title: box.querySelector('.note-title').value || "",
                        content: box.querySelector('.note-content').value || "",
                        font_size: box.querySelector('.font-size').value,
                        font_style: box.querySelector('.font-style').value,
                        note_color: box.querySelector('.note-color').value
                    });
                }
                
                clearTimeout(autoSaveTimers[noteId]);
                autoSaveTimers[noteId] = setTimeout(() => {
                    if (typeof saveNote === "function") saveNote(noteId);
                }, 300);
            });
        });

        const colorPicker = box.querySelector(".note-color");
        colorPicker.addEventListener("input", function () {
            box.style.background = this.value;
            const textColor = getTextColorForBg(this.value);
            box.style.setProperty('--note-text-color', textColor);
        });
    }
};

window.closeSharedNoteModal = async function(noteId) {
    if (autoSaveTimers[noteId]) {
        clearTimeout(autoSaveTimers[noteId]);
        delete autoSaveTimers[noteId];
        if (typeof saveNote === "function") await saveNote(noteId);
    }
    const modal = document.getElementById("shared-note-edit-modal");
    if (modal) {
        modal.style.opacity = "0";
        setTimeout(() => {
            modal.remove();
            loadSharedNotes(); 
        }, 200);
    }
}


window.openLockModal = openLockModal;
window.closeLockModal = closeLockModal;
window.confirmLockNote = confirmLockNote;
window.confirmUnlockNote = confirmUnlockNote;
window.openChangePwModal = openChangePwModal;
window.closeChangePwModal = closeChangePwModal;
window.confirmChangePw = confirmChangePw;
window.showToast = showToast;
window.toggleLockEye = toggleLockEye;
window.saveNote = saveNote;
window.confirmDeleteNote = confirmDeleteNote;
window.togglePin = togglePin;
