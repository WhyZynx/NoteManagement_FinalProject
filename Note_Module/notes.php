<h3>Your Notes List</h3>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Search notes...">
</div>

<hr>

<button onclick="createNoteCard()">Add Note</button>
<button onclick="setViewMode('grid')">Grid View</button>
<button onclick="setViewMode('list')">List View</button>

<hr>

<div id="notes-list" class="grid"></div>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>