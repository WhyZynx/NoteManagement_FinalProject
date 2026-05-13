<div class="label-sidebar">
    <div class="label-header" onclick="toggleLabelPanel()">
        <span class="label-header-title">
            Labels
        </span>
        <div class="label-header-actions">
            <i class="bi bi-grid-3x3-gap label-add-icon" onclick="event.stopPropagation(); filterByLabel(null)" title="All notes"></i>
            <i class="bi bi-plus label-add-icon" onclick="event.stopPropagation(); openAddLabelPopup()" title="Add label"></i>
            <i class="bi bi-chevron-down label-chevron" id="labelChevron"></i>
        </div>
    </div>

    <div class="label-add-popup" id="labelAddPopup">
        <input type="text" id="labelInput" placeholder="Label name..." maxlength="50">
        <div class="label-popup-actions">
            <button onclick="cancelAddLabel()">Cancel</button>
            <button class="confirm" onclick="createLabel()">Add</button>
        </div>
    </div>

    <div class="label-panel" id="labelPanel">
        <div id="labelList"></div>
    </div>
</div>