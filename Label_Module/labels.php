<div class="label-sidebar">
    <h3 style=" padding: 0 14px; margin-bottom: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; 
        letter-spacing: 1.2px; color: #8b99ad; display: flex; align-items: center;">
        Labels
    </h3>

    <div class="labels-control-wrapper">
        <div class="label-add-box">
            <input type="text" id="labelInput" placeholder="New label...">
            <button id="addLabelBtn" title="Add Label">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <i class="bi bi-grid btn-all-icon" onclick="filterByLabel(null)" title="All notes"></i>
    </div>

    <div id="labelList"></div>
</div>