<h3>Your Notes List</h3>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Search notes...">
</div>

<div class="note-actions">
    <button onclick="createNoteCard()" title="Add Note">
        <i class="bi bi-plus-lg"></i>
    </button>

    <button onclick="setViewMode('grid')" title="Grid View">
        <i class="bi bi-grid-3x3-gap-fill"></i>
    </button>

    <button onclick="setViewMode('list')" title="List View">
        <i class="bi bi-list-ul"></i>
    </button>
</div>

<div id="notes-list" class="grid"></div>

<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>