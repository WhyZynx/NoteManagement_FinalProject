# MindFlow - Smart Note Management System

MindFlow is a professional, PHP-based note management application featuring real-time collaboration, advanced security locking, custom labeling, and robust offline support.

Project Compliance Note: This repository contains the complete source code, required functional modules, configuration assets, and the orchestration setup required to successfully execute the application on any grading environment.

---

## Team Members

| No. | Student ID | Full Name |
|:---:|:-----------|:-----------|
| 1 | 524H0019 | Liêu Thảo Nghi |
| 2 | 524H0027 | Nguyễn Thị Như Quỳnh |
| 3 | 524H0130 | Nguyễn Lý Bảo Trân |
| 4 | 524H0040 | Võ Ngọc Thanh Vy |

---

## Live Access and Repository

* GitHub Repository: https://github.com/WhyZynx/NoteManagement_FinalProject.git
* Production Deployment: https://mindflow-note.onrender.com/

---

## Core Features

* User Management: Registration, login, secure logout, profile updating, and email reset flow simulation.
* Rich Notes CRUD: Create, modify, delete, search, and pin important notes seamlessly.
* Classification: Dynamic label generation and multi-tag filtering.
* Security Mechanics: Password-protected encryption handles sensitive individual notes.
* Real-time Sync: Multi-user collaborative workspace powered exclusively via WebSockets (Socket.IO).
* PWA Capabilities: Built-in Service Workers provide secure offline read capabilities.
* Dynamic Themes: Toggle fluidly between Light, Dark, Hologram, Custom, and Gradient view aesthetics.

---

## Technology Stack

| Layer | Technology | Version / Specification |
| :--- | :--- | :--- |
| Backend Core | PHP | v8.2 (with Apache Bundle) |
| Realtime Engine | Node.js / Socket.IO | v18.x Runtime Environment |
| Database Storage | MySQL | v8.0 Community Server |
| Frontend Foundation | HTML5 / CSS3 / Vanilla JS | Responsive Grid and Flexbox |
| Offline Cache | Service Worker API | Progressive Web App Standard |
| Orchestration | Docker / Docker Compose | Containerized Cross-Platform Engine |

---

## Project Architecture

The workspace directory is entirely self-contained within the root htdocs layout without nested project subfolders:

```text
C:\xampp\htdocs\
├── Auth_Module\          # Authentication pages and handlers (login, registration)
├── User_Module\          # Profile, user preferences, and settings
├── Note_Module\          # Notes CRUD, locking mechanics, and file uploads
├── Label_Module\         # Label creation and note filtering logic
├── API\                  # AJAX and backend API endpoints
├── Utils\                # Connection helpers, validations, and email utilities
├── Assets\               # Main CSS stylesheets, JavaScript files, and core UI graphics
├── realtime-server\      # Node.js Socket.IO server for live multi-user collaboration
├── sql_init\             # Database schema initialization and data seed SQL scripts
├── uploads\              # Storage directory for user-uploaded images and avatars
├── docker-compose.yml    # Docker Compose orchestration configurations
├── Dockerfile            # Apache and PHP container build specifications
├── db.php                # Dynamic database connection handler
└── README.md             # Project documentation

```

---

## Seeded Test Accounts

The operational database initialization automates the insertion of two complete profiles. Use these to comprehensively evaluate permissions and real-time synchronization hooks:

| Target Profile | Test Username / Email | Standard Access Password |
| --- | --- | --- |
| Primary Account | testuser1@gmail.com | 12345678 |
| Secondary Account | testuser2@gmail.com | 12345678 |

---

## Connection Port Matrix

When spun up inside Docker Compose, services communicate internally and bind safely using these mapped network pathways:

* Web Front-facing Client: http://localhost:8080 (Proxied container port 80)
* Realtime Synchronization: http://localhost:3001 (Socket.IO operational engine)
* Isolated Database Engine: localhost:3307 (Forwards out from core internal 3306)

---

## Step-by-Step Deployment Guide

### Option A: Standard Containerized Run via Docker Compose (Recommended)

Docker Compose abstracts dependencies, compiles Node requirements, mounts initial structural definitions, and builds the runtime automatically.

1. Launch your host terminal or PowerShell as an Administrator.
2. Point your command focus into the installation destination folder:
```bash
cd C:\xampp\htdocs

```


3. Execute the standard composition flush and build suite:
```bash
docker compose down -v && docker compose up -d --build --force-recreate

```


4. Note: Allow roughly 15 seconds for the structural migrations inside the isolated DB engine to cleanly settle.
5. Launch your browser of choice and interact directly via: http://localhost:8080

Maintenance Operations:

* To cleanly park the virtual systems: docker compose down
* To wipe the database data and re-trigger fresh seeded accounts: docker compose down -v

---

### Option B: Local Stack Run via Native XAMPP Engine

The dynamic adapter file db.php is engineered with fallback logic. If it notices missing container definitions, it bridges access back to structural default values on your host.

1. Free up ports by running `docker compose down` in your project terminal.
2. Fire up the XAMPP Control Panel on your desktop.
3. Turn on the Apache and MySQL modules.
4. Navigate to your local server admin workspace (http://localhost/phpmyadmin), define a new relational layer catalog titled `mindflow_db`, and import the initialization asset file located at `./sql_init/mindflow_db.sql`.
5. Point your production browser path cleanly to: http://localhost/

---

## Grading Evaluation Checklist

Instructors are invited to use this structured sequence to verify complete operational alignment:

* [ ] Infrastructure Check: Execute docker compose up -d --build and check that ports 8080 and 3001 are serving safely.
* [ ] Authentication Flow: Log into http://localhost:8080 using testuser1@gmail.com / 12345678.
* [ ] Core Utility Run: Create, edit, label, pin, and remove a dynamic note instance.
* [ ] Data Attachment: Attach an image asset to an active note; ensure upload renders properly.
* [ ] Locking Integrity: Apply an isolated passcode to a note element; verify content obscures instantly.
* [ ] Collaborative Echo: Open an Incognito browser pane. Log into testuser2@gmail.com. Share a live permission note from Account 1 and test that edits sync instantly across both screens without manual page reloads.
* [ ] PWA Offline Integrity: Toggle your developer inspector panel network emulation mode to Offline; ensure cached assets remain reachable.

```

```
