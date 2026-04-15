let currentView = "grid";
let autoSaveTimers = {};
let searchTimer;

async function fetchJson(url, options = {}) {
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
        container.innerHTML = "<p>No notes</p>";
        return;
    }

    container.innerHTML = notes.map(renderNoteCard).join("");

    await hydrateNotes(notes);

    attachAutoSaveEvents();
}

async function hydrateNotes(notes) {
    for (const note of notes) {
        const [imgs, labels] = await Promise.all([
            loadNoteImages(note.id),
            loadNoteLabels(note.id)
        ]);

        const imgBox = document.getElementById(`images-${note.id}`);
        if (imgBox) imgBox.innerHTML = imgs;

        const labelBox = document.getElementById(`labels-${note.id}`);
        if (labelBox) labelBox.innerHTML = labels;

        await renderLabelSelector(note.id);
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
    const res = await fetchJson("User_Module/get_preferences.php");

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

    savePreference("view_mode", mode);
}

async function savePreference(key, value) {
    await fetchJson("User_Module/save_preferences.php", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: new URLSearchParams({ key, value })
    });
}

async function createNoteCard() {
    const res = await fetchJson("Note_Module/create_note.php", {
        method: "POST"
    });

    if (res.status === "success") {
        loadNotes();
    }
}

async function loadNotes() {
    const res = await fetchJson("Note_Module/get_note.php");
    await renderNotes(res.data || []);
}

async function loadNoteImages(noteId) {
    const res = await fetchJson(`Note_Module/get_note_images.php?note_id=${noteId}`);

    if (!res.data) return "";

    return res.data.map(img => `
        <div style="display:inline-block;position:relative;margin:5px;">
            <img src="${img.image_path}"
                style="width:90px;height:90px;object-fit:cover;border-radius:8px;" />
            <button onclick="deleteImage(${img.id}, ${noteId})"
                style="position:absolute;top:2px;right:2px;width:20px;height:20px;border-radius:50%;border:none;background:red;color:white;cursor:pointer;">×</button>
        </div>
    `).join("");
}

async function uploadImage(noteId) {
    const input = document.querySelector(`.upload-image[data-id="${noteId}"]`);

    if (!input || !input.files.length) return;

    const form = new FormData();
    form.append("note_id", noteId);
    form.append("image", input.files[0]);

    const res = await fetchJson("Note_Module/upload_image.php", {
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

window.uploadImage = uploadImage;

async function deleteImage(imageId, noteId) {
    if (!confirm("Delete image?")) return;

    const form = new FormData();
    form.append("image_id", imageId);

    const res = await fetchJson("Note_Module/delete_image.php", {
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

function renderNoteCard(note) {
    const size = note.font_size || 16;
    const style = note.font_style || "sans-serif";
    const color = note.note_color || "#ffffff";

    return `
    <div class="note-card" data-id="${note.id}"
        style="font-size:${size}px;font-family:${style};background:${color};padding:16px;border-radius:12px;border:1px solid #ddd;">

        <div class="note-images" id="images-${note.id}"></div>

        <input class="note-title" data-id="${note.id}"
            value="${note.title || ""}"
            style="font-size:${size}px;font-family:${style};" />

        <div class="note-labels" id="labels-${note.id}"></div>

<div class="label-selector" data-id="${note.id}">
    <div class="label-box"></div>
</div>

        <textarea class="note-content" data-id="${note.id}"
            style="font-size:${size}px;font-family:${style};">${note.content || ""}</textarea>

        <select class="font-size" data-id="${note.id}">
            <option value="14" ${size == 14 ? "selected" : ""}>14</option>
            <option value="16" ${size == 16 ? "selected" : ""}>16</option>
            <option value="18" ${size == 18 ? "selected" : ""}>18</option>
            <option value="20" ${size == 20 ? "selected" : ""}>20</option>
        </select>

        <select class="font-style" data-id="${note.id}">
            <option value="Arial" ${style === "Arial" ? "selected" : ""}>Arial</option>
            <option value="serif" ${style === "serif" ? "selected" : ""}>Serif</option>
            <option value="sans-serif" ${style === "sans-serif" ? "selected" : ""}>Sans-serif</option>
            <option value="Montserrat" ${style === "Montserrat" ? "selected" : ""}>Montserrat</option>
        </select>

        <input type="color" class="note-color" data-id="${note.id}" value="${color}">

        <input type="file" class="upload-image" data-id="${note.id}">
        <button onclick="uploadImage(${note.id})">Upload</button>
        <button onclick="deleteNote(${note.id})">Delete</button>
    </div>`;
}

function attachAutoSaveEvents() {
    document.querySelectorAll(".note-title, .note-content, .font-size, .font-style, .note-color")
        .forEach(el => {

            const type = (el.tagName === "SELECT" || el.type === "color") ? "change" : "input";

            el.addEventListener(type, function () {
                const id = this.dataset.id;

                syncNoteStyle(id);

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
        card.style.backgroundColor = color;
    }
}

async function saveNote(noteId) {
    const form = new FormData();
    const labelIds = Array.from(
    document.querySelectorAll(`.label-selector[data-id="${noteId}"] input:checked`)
).map(cb => cb.value);

form.append("labels", JSON.stringify(labelIds));

    form.append("note_id", noteId);
    form.append("title", document.querySelector(`.note-title[data-id="${noteId}"]`)?.value || "");
    form.append("content", document.querySelector(`.note-content[data-id="${noteId}"]`)?.value || "");
    form.append("font_size", document.querySelector(`.font-size[data-id="${noteId}"]`)?.value || 16);
    form.append("font_style", document.querySelector(`.font-style[data-id="${noteId}"]`)?.value || "Arial");
    form.append("note_color", document.querySelector(`.note-color[data-id="${noteId}"]`)?.value || "#ffffff");

    await fetchJson("Note_Module/save_note.php", {
        method: "POST",
        body: form
    });
}

async function deleteNote(id) {
    if (!confirm("Delete this note?")) return;

    const form = new FormData();
    form.append("note_id", id);

    await fetchJson("Note_Module/delete_note.php", {
        method: "POST",
        body: form
    });

    loadNotes();
}

async function searchNotes(keyword) {
    const res = await fetchJson(
        `API/api_search.php?keyword=${encodeURIComponent(keyword)}`
    );

    const notes =
        Array.isArray(res) ? res :
        Array.isArray(res?.data) ? res.data :
        [];

    await renderNotes(notes);
}

async function loadNoteLabels(noteId) {
    const res = await fetchJson(`Note_Module/get_note_labels.php?note_id=${noteId}`);

    if (!res.data) return "";

    return res.data.map(l => `
        <span class="label-badge">${l.label_name}</span>
    `).join("");
}
async function getAllLabels() {
    const res = await fetch("../API/api_labels.php?action=list");
    return await res.json();
}

async function getNoteLabelIds(noteId) {
    const res = await fetchJson(`Note_Module/get_note_labels.php?note_id=${noteId}`);

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

window.createNoteCard = createNoteCard;
window.setViewMode = setViewMode;
window.deleteNote = deleteNote;
window.saveNote = saveNote;
window.loadNotes = loadNotes;

