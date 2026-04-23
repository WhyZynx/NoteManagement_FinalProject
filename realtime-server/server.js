const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

const app = express();
const server = http.createServer(app);

const io = new Server(server, {
    cors: {
        origin: "*"
    }
});

io.on("connection", (socket) => {

    socket.on("join_note", (noteId) => {
        socket.join("note_" + noteId);
    });

    socket.on("edit_note", (data) => {

        socket.to("note_" + data.noteId)
              .emit("note_updated", data);
    });

});

server.listen(3001, () => {
    console.log("WebSocket running on http://localhost:3001");
});