document.addEventListener("DOMContentLoaded", function () {
    loadLabels();

    document
        .getElementById("addLabelBtn")
        .addEventListener("click", createLabel);
});

async function loadLabels() {
    const response = await fetch("../API/api_labels.php?action=list");
    const labels = await response.json();

    let html = "";

    labels.forEach(label => {
        html += `
            <div class="label-item">
                <span style="cursor:pointer"
                    onclick="filterByLabel(${label.id})">
                    ${escapeHtml(label.label_name)}
                </span>

                <button onclick="renameLabel(${label.id})">Rename</button>
                <button onclick="deleteLabel(${label.id})">Delete</button>
            </div>
        `;
    });

    document.getElementById("labelList").innerHTML = html;
}

async function createLabel() {
    const input = document.getElementById("labelInput");
    const labelName = input.value.trim();

    if (!labelName) {
        alert("Label name cannot be empty");
        return;
    }

    const formData = new FormData();
    formData.append("action", "create");
    formData.append("label_name", labelName);

    const response = await fetch("../API/api_labels.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        input.value = "";
        loadLabels();
        window.dispatchEvent(new Event("labelsChanged"));
    } else {
        alert(result.message || "Create failed");
    }
}

async function renameLabel(id) {
    const newName = prompt("Enter new label name:");

    if (!newName || !newName.trim()) return;

    const formData = new FormData();
    formData.append("action", "update");
    formData.append("label_id", id);
    formData.append("new_name", newName.trim());

    const response = await fetch("../API/api_labels.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        loadLabels();
        window.dispatchEvent(new Event("labelsChanged"));
    } else {
        alert(result.message || "Update failed");
    }
}

async function deleteLabel(id) {
    if (!confirm("Delete this label?")) return;

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("label_id", id);

    const response = await fetch("../API/api_labels.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        loadLabels();
        window.dispatchEvent(new Event("labelsChanged"));
    } else {
        alert(result.message || "Delete failed");
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

async function filterByLabel(labelId) {
    const url = labelId
        ? `API/api_search.php?label_id=${labelId}`
        : `Note_Module/get_note.php`;

    const res = await fetchJson(url);
    renderNotes(res.data || res);
}

window.filterByLabel = filterByLabel;