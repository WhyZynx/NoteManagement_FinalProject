let labelPanelOpen = false;

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("labelList")) {
        loadLabels();
    }
    document.addEventListener("click", function(e) {
        const popup = document.getElementById("labelAddPopup");
        if (!popup) return;
        if (popup && popup.classList.contains("show") &&
            !popup.contains(e.target) &&
            !e.target.classList.contains("label-add-icon")) {
            cancelAddLabel();
        }
    });
});

function toggleLabelPanel() {
    labelPanelOpen = !labelPanelOpen;
    const panel = document.getElementById("labelPanel");
    const chevron = document.getElementById("labelChevron");
    panel.classList.toggle("open", labelPanelOpen);
    chevron.classList.toggle("rotated", labelPanelOpen);
}

function openAddLabelPopup() {
    const popup = document.getElementById("labelAddPopup");
    popup.classList.add("show");
    document.getElementById("labelInput").focus();
}

function cancelAddLabel() {
    const popup = document.getElementById("labelAddPopup");
    popup.classList.remove("show");
    document.getElementById("labelInput").value = "";
}

async function loadLabels() {
    const response = await fetch(`${API_BASE}api_labels.php?action=list`);
    const labels = await response.json();

    let html = "";
    labels.forEach(label => {
        html += `
            <div class="label-item" data-id="${label.id}">
                <span class="label-name" onclick="filterByLabel(${label.id})">
                    <i class="bi bi-circle"></i>
                    ${escapeHtml(label.label_name)}
                </span>
                <div class="label-actions">
                    <button onclick="renameLabel(${label.id})" title="Rename">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="delete-btn" onclick="deleteLabel(${label.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });

    document.getElementById("labelList").innerHTML = html || `<div class="label-empty">No labels yet</div>`;
}

async function createLabel() {
    const input = document.getElementById("labelInput");
    const labelName = input.value.trim();
    if (!labelName) return;

    const formData = new FormData();
    formData.append("action", "create");
    formData.append("label_name", labelName);

    const response = await fetch(`${API_BASE}api_labels.php`, {
        method: "POST",
        body: formData
    });

    const result = await response.json();
    if (result.success) {
        cancelAddLabel();
        loadLabels();
        if (!labelPanelOpen) toggleLabelPanel();
        window.dispatchEvent(new Event("labelsChanged"));
    }
}

async function renameLabel(id) {
    const item = document.querySelector(`.label-item[data-id="${id}"] .label-name`);
    const currentName = item ? item.textContent.trim() : "";

    const input = document.createElement("input");
    input.className = "label-rename-input";
    input.value = currentName;
    item.replaceWith(input);
    input.focus();

    async function saveRename() {
        const newName = input.value.trim();
        if (!newName || newName === currentName) {
            loadLabels();
            return;
        }
        const formData = new FormData();
        formData.append("action", "update");
        formData.append("label_id", id);
        formData.append("new_name", newName);
        const res = await fetch(`${API_BASE}api_labels.php`, { method: "POST", body: formData });
        const result = await res.json();
        if (result.success) {
            loadLabels();
            window.dispatchEvent(new Event("labelsChanged"));
        }
    }

    input.addEventListener("blur", saveRename);
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") input.blur();
        if (e.key === "Escape") { loadLabels(); }
    });
}

async function deleteLabel(id) {
    if (!confirm("Delete this label?")) return;

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("label_id", id);

    const response = await fetch(`${API_BASE}api_labels.php`, { method: "POST", body: formData });
    const result = await response.json();

    if (result.success) {
        loadLabels();
        window.dispatchEvent(new Event("labelsChanged"));
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

async function filterByLabel(labelId) {
    document.querySelectorAll(".label-item").forEach(el => el.classList.remove("active"));
    if (labelId) {
        document.querySelector(`.label-item[data-id="${labelId}"]`)?.classList.add("active");
    }
    const url = labelId
        ? `${API_BASE}api_search.php?label_id=${labelId}`
        : `${NOTE_BASE}get_note.php`;
    const res = await fetchJson(url);
    renderNotes(res.data || res);
}

window.filterByLabel = filterByLabel;
window.toggleLabelPanel = toggleLabelPanel;
window.openAddLabelPopup = openAddLabelPopup;
window.cancelAddLabel = cancelAddLabel;
window.createLabel = createLabel;
window.renameLabel = renameLabel;
window.deleteLabel = deleteLabel;