# Note Management Application
**Final Project - Web Programming & Applications | Semester II/2024-2025**

## Overview
A web-based note management application that enables users to create, organize, and manage notes with support for text, images, and advanced features like sharing, password protection, and real-time collaboration.

---

## Technology Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 7.4+ |
| Database | MySQL 5.7+ |
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript |
| Authentication | bcrypt Password Hashing |
| Real-time Features | WebSocket (for collaboration) |
| Progressive Features | Service Workers (offline support) |

---

## System Architecture

```
┌─────────────────┐
│   Frontend      │
│ (HTML/CSS/JS)   │
└────────┬────────┘
         │
┌────────▼────────┐
│  PHP Backend    │
│ (MVC Pattern)   │
└────────┬────────┘
         │
┌────────▼────────┐
│  MySQL Database │
└─────────────────┘
```

---

## Database Schema

### Core Tables

#### `users`
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255),
    is_verified INT DEFAULT 0,
    theme_mode VARCHAR(50) DEFAULT 'light',
    font_size INT DEFAULT 16,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `notes`
```sql
CREATE TABLE notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_password_protected BOOLEAN DEFAULT FALSE,
    password_hash VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `labels`
```sql
CREATE TABLE labels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, name)
);
```

#### `note_labels`
```sql
CREATE TABLE note_labels (
    note_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (note_id, label_id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
);
```

#### `note_attachments`
```sql
CREATE TABLE note_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    note_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
);
```

#### `note_sharing`
```sql
CREATE TABLE note_sharing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    note_id INT NOT NULL,
    owner_id INT NOT NULL,
    recipient_id INT NOT NULL,
    permission_level ENUM('read-only', 'edit') DEFAULT 'read-only',
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (note_id, recipient_id)
);
```

#### `notifications`
```sql
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50),
    message TEXT,
    related_note_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `password_reset_tokens`
```sql
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## File Structure

```
NoteManagement_FinalProject/
├── index.php                 # Home page (requires login)
├── db.php                    # Database connection & session init
│
├── Auth Module/
│   ├── login.php            # Login page & form handler
│   ├── register.php         # Registration page & form handler
│   ├── logout.php           # Session destruction
│   ├── forgot_password.php  # Password reset request
│   ├── reset_password.php   # Password reset form
│   └── verify_email.php     # Email verification link handler
│
├── User Module/
│   ├── profile.php          # View user profile
│   ├── update_profile.php   # Update user info & avatar
│   ├── setting.php          # User preferences (theme, font size)
│   ├── save_preferences.php # Save user settings
│   └── change_password.php  # Change account password
│
├── Note Module/
│   ├── notes.php            # Display notes (grid/list view)
│   ├── create_note.php      # Create/edit note form
│   ├── save_note.php        # Auto-save note content
│   ├── delete_note.php      # Delete note with confirmation
│   ├── upload_attachment.php# Upload images to notes
│   ├── pin_note.php         # Pin/unpin notes
│   └── search_notes.php     # Live search endpoint
│
├── Label Module/
│   ├── labels.php           # Label management UI
│   ├── create_label.php     # Create label
│   ├── update_label.php     # Edit label
│   ├── delete_label.php     # Delete label
│   └── filter_notes.php     # Filter by labels
│
├── Sharing Module/
│   ├── share_note.php       # Share note form
│   ├── send_share.php       # Process share request
│   ├── shared_notes.php     # View received notes
│   ├── manage_sharing.php   # View/revoke sharing
│   └── collaboration.php    # WebSocket endpoint (realtime edit)
│
├── Note Security/
│   ├── lock_note.php        # Add password to note
│   ├── unlock_note.php      # View password-protected note
│   ├── change_note_password.php # Update note password
│   └── disable_note_lock.php    # Remove password
│
├── Assets/
│   ├── css/
│   │   ├── style.css        # Main stylesheet
│   │   ├── responsive.css   # Media queries for devices
│   │   └── theme.css        # Light/Dark theme styles
│   ├── js/
│   │   ├── app.js           # Main application logic
│   │   ├── notes.js         # Note CRUD operations
│   │   ├── search.js        # Live search functionality
│   │   ├── offline.js       # Service worker & offline cache
│   │   ├── collaboration.js # WebSocket collaboration
│   │   └── ui.js            # UI interactions & effects
│   └── images/
│       └── web_img/         # Images (logo, icons)
│
├── API/ (optional - for AJAX)
│   ├── api_notes.php        # Note endpoints
│   ├── api_labels.php       # Label endpoints
│   ├── api_search.php       # Search endpoint
│   ├── api_sharing.php      # Sharing endpoints
│   └── api_auth.php         # Authentication endpoints
│
├── Utils/
│   ├── email.php            # Email sending utilities
│   ├── validation.php       # Input validation functions
│   ├── security.php         # Security helpers (CSRF, auth)
│   └── helpers.php          # General helper functions
│
├── config.php               # Configuration (if needed)
├── .env.example             # Environment variables template
├── docker-compose.yml       # Docker configuration (optional)
├── README.md                # This file
└── readme.txt               # Setup instructions for grader
```

---

## 28 Required Features

### Account Management (2.0 pts)

| # | Feature | Status | Notes |
|---|---------|--------|-------|
| 1 | User Registration | Required | Email, display name, password (hashed with bcrypt) |
| 2 | Account Activation | Required | Email verification link, notification on login |
| 3 | User Login/Logout | Required | Session-based authentication |
| 4 | Password Reset | Required | Email verification or OTP token |
| 5 | View Profile | Required | Display user info |
| 6 | Edit Profile & Avatar | Required | Update name and profile picture |
| 7 | Change Password | Required | Verify old password first |
| 8 | User Preferences | Required | Theme (light/dark), font size |

### Simple Note Management (4.0 pts)

| # | Feature | Status | Notes |
|---|---------|--------|-------|
| 9 | List View | Required | Alternative display mode |
| 10 | Grid View | Required | Default display layout |
| 11 | Create Notes | Required | Title + content only |
| 12 | Update Notes | Required | Same interface as create |
| 13 | Delete Notes | Required | Confirmation dialog required |
| 14 | Auto-Save | Required | Save without button click |
| 15 | Image Attachments | Required | Single or multiple images |
| 16 | Pin Notes | Required | Pinned notes stay at top |
| 17 | Search Notes | Required | Live search, no button needed |
| 18 | Label Management | Required | List, add, edit, delete labels |
| 19 | Attach Labels | Required | One or multiple labels per note |
| 20 | Filter by Labels | Required | Show only labeled notes |

### Advanced Note Management (2.0 pts)

| # | Feature | Status | Notes |
|---|---------|--------|-------|
| 21 | Lock/Unlock Notes | Required | Password protection per note |
| 22 | Change Note Password | Required | Requires old password verification |
| 23 | Share Notes | Required | Email recipients, permission levels |
| 24 | Real-time Collaboration | Required | WebSocket for simultaneous editing |

### Other Requirements (2.0 pts)

| # | Feature | Points | Requirements |
|---|---------|--------|--------------|
| 25 | UI/UX Design | 0.5 | Above-average design |
| 26 | Responsive Design | 0.5 | Mobile, tablet, desktop support |
| 27 | Offline Capabilities | 0.5 | PWA with service workers & offline sync |
| 28 | Online Deployment | 0.5 | Public hosting OR Docker Compose setup |

---

## Key Implementation Requirements

### Security
- Use prepared statements to prevent SQL injection
- Hash all passwords with bcrypt (PASSWORD_BCRYPT)
- Implement CSRF tokens for form submissions
- Validate and sanitize all inputs
- Use HTTPS for online deployment

### User Experience
- Auto-save without user action (AJAX)
- Live search with 300ms debounce
- Responsive design supporting mobile/tablet/desktop
- Loading indicators for async operations
- Error handling with user-friendly messages

### Data Integrity
- Confirmation dialogs for destructive actions
- Transaction support for multi-table operations
- Proper cascade deletion for related records
- Timestamps for all records (created_at, updated_at)

### Performance
- Index frequently queried columns
- Cache user preferences in session
- Optimize image uploads (compression, resizing)
- Implement pagination for large note lists

---

## Setup Instructions for Development

### 1. Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx with mod_rewrite
- Composer (optional, for dependencies)

### 2. Database Setup
```sql
-- Create database
CREATE DATABASE note_management;
USE note_management;

-- Import schema from provided SQL files
-- Or run migrations if available
```

### 3. Configuration
- Edit `db.php` with your database credentials
- Create `.env` file (if using environment variables)
- Set proper permissions on upload directories

### 4. Service Workers & Offline Support
- Place service worker files in root directory
- Implement IndexedDB for offline note storage
- Sync data when connection is restored

### 5. Email Configuration
- Configure mail server credentials in `email.php`
- Set up email templates for notifications
- Test email sending locally (use MailHog or similar)

### 6. Testing Before Deployment
- Test all 28 features
- Verify responsive design on multiple devices
- Test offline functionality with browser dev tools
- Check database performance with large datasets
- Validate security (no SQL injection, XSS, CSRF)

---

## Deployment Options

### Option A: Online Hosting
- Use cloud services (Heroku, AWS, DigitalOcean, etc.)
- Configure database on host platform
- Enable HTTPS certificates
- Document public URL and credentials

### Option B: Docker Compose
- Use provided `docker-compose.yml`
- Include PHP, MySQL, Nginx services
- Add `.dockerignore` to exclude unnecessary files
- Provide clear instructions in `readme.txt`

---

## Notes for Code Review

1. **Base Code Status**: Current implementation includes authentication basics (login, register), profile management, and user preferences. Advanced features (sharing, real-time collaboration, offline support) still need implementation.

2. **SQL Injection Risks**: Some files use string interpolation in queries. Migrate all queries to prepared statements before submission.

3. **Email Verification**: Placeholder system in place. Implement actual email sending with verification tokens.

4. **Code Organization**: Consider refactoring to MVC pattern with controllers, models, and views for better maintainability.

5. **Testing**: Ensure all features work without errors and handle edge cases (empty notes list, invalid file uploads, etc.).

---

## Grading Checklist

- [ ] All 28 features implemented and tested
- [ ] Code compiles and runs without errors
- [ ] Database properly structured with all required tables
- [ ] Responsive design verified on 3+ devices
- [ ] Offline functionality working
- [ ] Security vulnerabilities addressed
- [ ] Demo video recorded (1080p, all members, all 28 features)
- [ ] Rubric.docx completed with self-assessment
- [ ] README.txt with setup instructions provided
- [ ] Source code cleaned (no node_modules, .git history compressed, etc.)
- [ ] Project submitted in ZIP format with correct naming

---

**Last Updated**: April 8, 2026  
**Version**: 1.0.0
