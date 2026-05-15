const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

const app = express();
const server = http.createServer(app);

const io = new Server(server, {
    cors: {
        origin: "https://mindflow-note.onrender.com", 
        methods: ["GET", "POST"]
    }
});

const PORT = 3001;
const noteRooms = {};

io.on("connection", function(socket) {
    console.log("[Socket] Connected:", socket.id);
    socket.on("join_note", function(noteId) {
        const room = `note_${noteId}`;
        socket.join(room);

        if (!noteRooms[noteId]) noteRooms[noteId] = new Set();
        noteRooms[noteId].add(socket.id);

        console.log(`[Socket] ${socket.id} joined note ${noteId}`);
    });
    socket.on("leave_note", function(noteId) {
        const room = `note_${noteId}`;
        socket.leave(room);

        if (noteRooms[noteId]) {
            noteRooms[noteId].delete(socket.id);
        }
    });
    socket.on("edit_note", function(data) {
        const room = `note_${data.noteId}`;
        socket.to(room).emit("note_updated", data);

        console.log(`[Socket] Note ${data.noteId} updated by ${socket.id}`);
    });
    socket.on("disconnect", function() {
        console.log("[Socket] Disconnected:", socket.id);
        for (const noteId in noteRooms) {
            noteRooms[noteId].delete(socket.id);
        }
    });
});
app.get("/health", function(req, res) {
    res.json({ status: "ok", connections: io.engine.clientsCount });
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`[MindFlow] Realtime server running on port ${PORT}`);
});
