<div class="label-sidebar">
    <h3>Labels</h3>

    <div class="labels-control-wrapper">
        <div class="label-add-box">
            <input type="text" id="labelInput" placeholder="New label...">
            <button id="addLabelBtn" title="Add Label">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
        <button class="btn-all" onclick="filterByLabel(null)">All</button>
    </div>

    <div id="labelList"></div>
</div>