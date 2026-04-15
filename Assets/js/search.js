let searchTimer;

const searchInput = document.getElementById("searchInput");

searchInput.addEventListener("input", function () {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        searchNotes(this.value);
    }, 300);
});

async function searchNotes(keyword) {
    const response = await fetch(
        `API/api_search.php?keyword=${encodeURIComponent(keyword)}`
    );

    const notes = await response.json();

    renderNotes(notes);
}