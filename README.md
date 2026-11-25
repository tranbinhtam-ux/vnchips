# vnchips

VNCHIP Technology JSC — Corporate Website, CMS Control Center & Frontend Live Edit System (`https://vnchips.trantam.net`).

## Features
- **Public Frontend:** Homepage, About Us (Journey, Timeline, Vision/Mission/Core Values, Leadership, Key Team, Code of Conduct), Business & Solutions, Technology, Sustainable, Media Center, Partners & Talk To Us.
- **Admin Control Center (`/chip-admin/`):** Dashboard, Traffic Analytics, Articles/News, Team Members, Partners & Case Studies, Customer Inquiries, Hero Banners, Page Sections Builder (Ace Code Editor), Navigation Menus, Media Library, Site Settings & Integrated User Guide.
- **Frontend Live Edit Engine:** Persistent 30-day admin session, Neon hover border highlight (non-intrusive), direct modal editors for all sections, instant image upload & replace, Ace Code Editor HTML syntax highlighting, and Scroll-to-Top widgets on both Admin and Frontend.

## Setup
1. Copy `config/config.local.example.php` to `config/config.local.php` and configure your database credentials.
2. Import `database/schema.sql` into MySQL/MariaDB.
3. Ensure `uploads/` and `storage/sessions/` are writable by the web server.
