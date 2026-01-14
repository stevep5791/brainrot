# Project: Brain Rot ML Training Data Collection

## Overview
A web application designed to collect examples of "brain rot" content from social media for machine learning training. The platform allows users to submit problematic content (text, images, videos) with categorization, and provides an administrative interface for reviewing and managing submissions. This is part of the "Presidential AI Challenge" initiative.

## Goals
- **Primary:** Create a functional demo-ready platform for collecting and categorizing brain rot content
- **Secondary:** Build user authentication system and admin panel for content management
- **Future:** Develop "Better World Browser" - an Electron app that wraps social media with injected "Report Brain Rot" buttons for seamless data collection
- **Success criteria:** Demo-ready system with auth, submissions, and admin panel working end-to-end

## Tech Stack
- **Language/Framework:** PHP 8.2, vanilla JavaScript
- **Database:** MariaDB (brainrot_submissions database on Beauty server)
- **Web Server:** Apache on Beauty (10.0.100.111)
- **Frontend:** HTML5, CSS3 (Classic Federal patriotic theme)
- **Infrastructure:** Apache virtual host, systemd, direct filesystem-based deployment

## Architecture
- **Front-controller pattern:** All requests through `public_htdocs/index.php`
- **Authentication:** Session-based with `classes/Auth.php` class
- **Database Access:** Direct MySQLi queries (development approach for debuggability)
- **File Uploads:** Stored in `public_htdocs/uploads/` with filesystem references
- **Admin Panel:** Paginated list view + detail view for submissions

### Key Files and Purposes
- `public_htdocs/index.php` - Main submission page with user bar
- `public_htdocs/submit.php` - Form submission handler
- `public_htdocs/auth/*.php` - Login, register, logout handlers
- `public_htdocs/admin/*.php` - Admin panel (index, view)
- `classes/Auth.php` - Authentication class with session management
- `public_htdocs/css/style.css` - Classic Federal theme (current)
- `public_htdocs/css/themes/` - Alternative theme options

### Data Flow
1. User visits site → registers or logs in
2. User submits content (text/image/video) + selects categories
3. `submit.php` saves to database with userid linkage
4. Admin logs in → views paginated submissions → can search/filter
5. Admin clicks submission → views full detail with all metadata

## Key Decisions & Rationale

| Decision | Why | Date |
|----------|-----|------|
| Direct MySQLi queries vs prepared statements | Debuggability during development - can see actual SQL with values in debugger | 2026-01-13 |
| Session-based auth vs JWT | Simpler for demo, no client-side token management needed | 2026-01-13 |
| Classic Federal patriotic theme | Aligns with "Presidential AI Challenge" branding, professional government aesthetic | 2026-01-13 |
| Beauty server for production | Separate from main dev server (Beast), dedicated domain | 2026-01-13 |
| GitHub repo (stevep5791/brainrot) | Version control and remote backup | 2026-01-13 |

## Known Gotchas & Lessons Learned
- **Browser caching:** Theme changes may not appear without hard refresh (Ctrl+Shift+R)
- **File upload permissions:** uploads/ directory must be writable by www-data user
- **Database credentials:** User `brainrot_user` with password `BrainRot2024!` for application access
- **Admin credentials:** Username `admin`, password `BrainRotAdmin2024!` for admin panel
- **MySQL reverse tunnel:** When accessing Beauty's DB from Beast, use explicit host binding with `--protocol=TCP --host=10.0.100.110`

## External Dependencies
- **GitHub:** Repository at https://github.com/stevep5791/brainrot
- **Domain:** https://brainrot.smartechconstruction.com/ (production URL)
- **No external APIs:** Currently self-contained, future vision includes social media integration via Electron wrapper

## Current Status (2026-01-13)

### What's Working
- User registration and login system
- Session-based authentication
- Content submission form (text + files + categories)
- Admin panel with paginated submission list
- Individual submission detail view
- Search functionality in admin panel
- Classic Federal patriotic theme applied
- Git repository initialized and connected to GitHub

### What's In Progress
- End-to-end testing of full submission flow
- Testing file upload functionality
- Creating test data for demo

### What's Planned
- **Immediate:** Complete demo testing and create sample submissions
- **Short-term:** Begin "Better World Browser" Electron app development
- **Long-term:**
  - Injected "Report Brain Rot" buttons in social media feeds
  - Real-time ML inference to warn users about brain rot content
  - Cross-platform browser app (Mac/Windows/Linux)
  - Mobile app integration

## Future Vision: Better World Browser

An Electron-based browser application that:
1. Students login to brainrot credentials
2. Browse social media normally (Facebook, TikTok, etc.)
3. See injected "Report Brain Rot" buttons on posts (like Waze hazard reports)
4. Click to report → data flows to brainrot server
5. ML model trains on reported content
6. Eventually: AI warns users in real-time about brain rot content

This transforms data collection from manual submission to seamless in-context reporting.
