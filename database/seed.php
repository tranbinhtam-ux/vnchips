<?php
/**
 * Database Migration & Seed Runner
 * Run via CLI: php database/seed.php or via web browser
 */

require_once __DIR__ . '/../config/config.php';

echo "=== VNCHIP DATABASE MIGRATION & SEEDER ===\n";

try {
    // 1. Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Execute schema
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schemaSql);
    echo "[✓] Database tables verified/created successfully.\n";

    // 3. Seed Default Admin
    // Username: admin | Password: admin123
    $defaultUsername = 'admin';
    $defaultPassHash = password_hash('admin123', PASSWORD_DEFAULT);
    $adminStmt = $pdo->prepare("INSERT INTO `admins` (`username`, `password_hash`, `fullname`, `email`, `role`) 
        VALUES (?, ?, 'System Administrator', 'admin@vnchip-inc.com', 'superadmin')
        ON DUPLICATE KEY UPDATE `fullname`=VALUES(`fullname`), `email`=VALUES(`email`)");
    $adminStmt->execute([$defaultUsername, $defaultPassHash]);
    echo "[✓] Admin account seeded: username='admin' | password='admin123'\n";

    // 4. Seed Homepage & System Settings
    $settings = [
        // Hero Stats
        ['setting_key' => 'stat_years_number', 'setting_value' => '6+', 'setting_group' => 'homepage_stats', 'description' => 'Stat 1: Number of years in IC design'],
        ['setting_key' => 'stat_years_label', 'setting_value' => 'years', 'setting_group' => 'homepage_stats', 'description' => 'Stat 1: Unit label'],
        ['setting_key' => 'stat_years_desc', 'setting_value' => 'Deep expertise in physical design and implementation, custom circuit layout, and system design - from 65nm to 3nm nodes', 'setting_group' => 'homepage_stats', 'description' => 'Stat 1: Description text'],
        
        ['setting_key' => 'stat_asic_number', 'setting_value' => '40+', 'setting_group' => 'homepage_stats', 'description' => 'Stat 2: Number of ASICs delivered'],
        ['setting_key' => 'stat_asic_label', 'setting_value' => 'ASIC', 'setting_group' => 'homepage_stats', 'description' => 'Stat 2: Unit label'],
        ['setting_key' => 'stat_asic_desc', 'setting_value' => 'Successful silicon solutions delivered across AI compute, mobile SOC, and 5G signal processing', 'setting_group' => 'homepage_stats', 'description' => 'Stat 2: Description text'],
        
        ['setting_key' => 'stat_chips_number', 'setting_value' => '5M+', 'setting_group' => 'homepage_stats', 'description' => 'Stat 3: Total chips delivered'],
        ['setting_key' => 'stat_chips_label', 'setting_value' => 'chips', 'setting_group' => 'homepage_stats', 'description' => 'Stat 3: Unit label'],
        ['setting_key' => 'stat_chips_desc', 'setting_value' => 'Delivered annually to global partners, testing and post-silicon supports', 'setting_group' => 'homepage_stats', 'description' => 'Stat 3: Description text'],

        // General Info
        ['setting_key' => 'site_name', 'setting_value' => 'VNCHIP Technology JSC', 'setting_group' => 'general', 'description' => 'Website Company Name'],
        ['setting_key' => 'site_email', 'setting_value' => 'institute@vnchip-inc.com', 'setting_group' => 'general', 'description' => 'Primary Contact Email'],
        ['setting_key' => 'site_phone', 'setting_value' => '+84 - 28 268 268 1058', 'setting_group' => 'general', 'description' => 'Primary Contact Hotline'],
        ['setting_key' => 'site_address_vn', 'setting_value' => '190 Doc Lap Street, Phu Tho Hoa Ward, Ho Chi Minh City, Vietnam', 'setting_group' => 'general', 'description' => 'Vietnam Office Address'],
        ['setting_key' => 'site_address_us', 'setting_value' => '6500 River Place Blvd, Building 7, Suites 250 Austin, TX. 78730', 'setting_group' => 'general', 'description' => 'USA Office Address'],
        ['setting_key' => 'homepage_pill_banner', 'setting_value' => 'We drive progress through training, research collaboration, and technology transfer, aligning with international standards and evolving industry trends in the global semiconductor landscape.', 'setting_group' => 'homepage', 'description' => 'Blue Pill highlight banner message']
    ];

    $setStmt = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `description`) 
        VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`), `description`=VALUES(`description`)");
    foreach ($settings as $s) {
        $setStmt->execute([$s['setting_key'], $s['setting_value'], $s['setting_group'], $s['description']]);
    }
    echo "[✓] Settings seeded successfully.\n";

    // 5. Seed Categories
    $categories = [
        ['name' => 'All Categories', 'slug' => 'all-categories', 'description' => 'All news and announcements'],
        ['name' => 'Partners', 'slug' => 'partners', 'description' => 'Joint silicon tapeouts, APR achievements, and foundry collaborations'],
        ['name' => 'Corporate News', 'slug' => 'corporate-news', 'description' => 'Updates, milestones, and corporate announcements from VNCHIP'],
        ['name' => 'Semiconductor R&D', 'slug' => 'semiconductor-rd', 'description' => 'Advanced IC design research, EDA methodologies, and tapeout achievements'],
        ['name' => 'Media & Events', 'slug' => 'media-events', 'description' => 'Television broadcasts, press coverage, exhibitions, and grand opening events'],
        ['name' => 'Talents & Education', 'slug' => 'talents-education', 'description' => 'VNCHIP Talent Program, university partnerships, and engineer training']
    ];

    $catStmt = $pdo->prepare("INSERT INTO `categories` (`name`, `slug`, `description`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`)");
    foreach ($categories as $cat) {
        $catStmt->execute([$cat['name'], $cat['slug'], $cat['description']]);
    }
    echo "[✓] Categories seeded successfully.\n";

    // Retrieve category IDs
    $catMap = [];
    $stmt = $pdo->query("SELECT `id`, `slug` FROM `categories`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $catMap[$row['slug']] = $row['id'];
    }

    // 6. Seed News Articles
    $articles = [
        [
            'category_id' => $catMap['partners'] ?? 1,
            'title' => "Hierarchical APR on TSMC N3E — AXMM top-block",
            'slug' => 'hierarchical-apr-on-tsmc-n3e-axmm-top-block',
            'thumbnail' => 'assets/images/media_card_tsmc_n3e.webp',
            'summary' => "4-partition hierarchical PnR for an edge AI inference processor. IOPin budgeting, cross-partition routing congestion resolution, ELVT...",
            'content' => "<p>Demonstrating world-class physical design and deep sub-micron implementation proficiency, VNCHIP has successfully completed hierarchical Auto Place & Route (APR) and sign-off verification on TSMC's advanced N3E (3nm EUV) process node for an AXMM top-block customer design.</p><p>Key Highlights:</p><ul><li><strong>Block Size:</strong> ~2.5 x 2.5 mm</li><li><strong>Partitions:</strong> 4 hierarchical sub-blocks</li><li><strong>Frequency:</strong> 800 MHz target operating speed</li><li><strong>Instance Count:</strong> 11 Million instances</li><li><strong>Signoff Sign-off:</strong> Timing closure (STA), DRC / LVS / ANT / ERC / PERC, Full EM/IR sign-off, Full IP checklist</li></ul><p>The project encompassed complex multi-corner multi-mode (MCMM) timing closure, dynamic voltage drop IR analysis, signal integrity signoff, and physical verification to ensure first-pass silicon success.</p>",
            'author' => 'Physical Design Team',
            'is_featured' => 1,
            'published_at' => '2026-03-01 10:00:00'
        ],
        [
            'category_id' => $catMap['partners'] ?? 1,
            'title' => "RTL-to-GDSII full-chip — 150M instances",
            'slug' => 'rtl-to-gdsii-full-chip-150m-instances',
            'thumbnail' => 'assets/images/media_card_rtl_150m.webp',
            'summary' => "Turnkey hierarchical PnR for an AI inference SoC across 26 partitions, 8 hardened IPs, 596 clock domains. MCMM/POCV STA sign-off, EM-...",
            'content' => "<p>VNCHIP delivered complete turnkey physical implementation from RTL synthesis to tape-out GDSII for a massive 150M-instance AI inference SoC on TSMC N16 FinFET (16nm) technology.</p><p>Key Technical Metrics:</p><ul><li><strong>Die Size:</strong> ~15 x 18 mm</li><li><strong>Partitions:</strong> 26 hierarchical partitions with 8 hardened IP macros</li><li><strong>Clock Domains:</strong> 596 asynchronous and synchronous clock domains</li><li><strong>Frequencies:</strong> 1.0 GHz core / 1.2 GHz DDR interface</li><li><strong>Signoff Checks:</strong> MCMM / POCV STA timing closure, Full EM/IR sign-off, DRC/LVS clean verification</li></ul><p>Our engineering team addressed severe cross-talk noise and electromigration constraints through customized clock tree synthesis (CTS) topologies and automated power mesh optimization.</p>",
            'author' => 'Physical Design Team',
            'is_featured' => 1,
            'published_at' => '2026-02-20 14:00:00'
        ],
        [
            'category_id' => $catMap['partners'] ?? 1,
            'title' => "Largest-die RTL-to-GDSII — 17.5 x 28 mm",
            'slug' => 'largest-die-rtl-to-gdsii-17-5-x-28-mm',
            'thumbnail' => 'assets/images/media_card_largest_die.webp',
            'summary' => "Turnkey full-chip PnR for an Automotive ADAS vision SoC. 13 partitions, 396 clock domains, 10LM5X2Y2ZUTRDL stack. AOCV MCMM STA...",
            'content' => "<p>VNCHIP completed the largest die area physical design in its portfolio: a massive 17.5 x 28 mm Automotive ADAS vision processor on TSMC N28 HPC+ (28nm) process.</p><p>Key Technical Specs:</p><ul><li><strong>Die Area:</strong> 17.5 x 28 mm reticle-limit challenge</li><li><strong>Partitions:</strong> 13 major subsystem partitions</li><li><strong>Metal Stack:</strong> 10LM 5X 2Y 2Z UTRDL high-density stack</li><li><strong>Frequencies:</strong> 500 MHz core / 800 MHz DDR interfaces</li><li><strong>Complexity:</strong> ~100 Million instances with stringent automotive reliability and thermal grading</li></ul><p>The tape-out demonstrated zero DRC violations, complete antenna rule compliance, and verified ESD protection network robustness.</p>",
            'author' => 'Physical Design Team',
            'is_featured' => 1,
            'published_at' => '2026-02-10 11:30:00'
        ],
        [
            'category_id' => $catMap['media-events'] ?? 1,
            'title' => "VNCHIP Institute’s Grand Opening Featured on HTV9 National Television",
            'slug' => 'vnchip-institutes-grand-opening-featured-on-htv9',
            'thumbnail' => 'assets/images/media_htv9_event.webp',
            'summary' => "HTV9 National Television broadcasted the official inauguration ceremony of the VNCHIP Institute of Semiconductor Research and Development in Ho Chi Minh City.",
            'content' => "<p>On national broadcast television HTV9, the VNCHIP Institute of Semiconductor Research and Development marked its official grand opening in Ho Chi Minh City, solidifying its position as a pioneer in training high-caliber IC design talent and developing cutting-edge semiconductor intellectual properties.</p><p>The event was attended by semiconductor industry leaders, university rectors, international partner delegates, and government representatives. Speaking at the ceremony, Chairman & CEO Luc Duc Tri highlighted VNCHIP's core mission to nurture Vietnam's engineering prowess and bridge the domestic semiconductor workforce directly into the global supply chain.</p><p>The Institute features state-of-the-art EDA clean-room computing facilities, collaborative research labs, and dedicated IC verification suites capable of taping out sub-7nm and 3nm FinFET chips.</p>",
            'author' => 'VNCHIP Media Center',
            'is_featured' => 1,
            'published_at' => '2026-01-28 09:00:00'
        ],
        [
            'category_id' => $catMap['corporate-news'] ?? 1,
            'title' => "LQ INNOVATIONS & VNCHIP Announce Strategic Research Collaboration for New IP Block",
            'slug' => 'lq-innovations-and-vnchip-announce-strategic-research-collaboration',
            'thumbnail' => 'assets/images/business_rd_cube.webp',
            'summary' => "Joint initiative combines senior chip design architects with intensive engineer training to deliver production-ready IP clusters for next-gen SoC and chiplet developments.",
            'content' => "<p>Ho Chi Minh City - LQ INNOVATIONS, led by Mr. Le Trong Quang, and VNCHIP Joint Stock Company proudly announce the launch of the first official research project under the VNCHIP Institute, marking a pivotal milestone in Vietnam’s semiconductor research and development landscape.</p><p>This collaboration marks a significant step toward advancing domestic chip design capabilities through the integration of cutting-edge research, empathic leadership, and top-tier engineering talent from both organizations. Spanning one year, the project involves a dedicated team of 2–3 senior engineers and 6–8 fresh graduates, combining hands-on research with an intensive training program for VNCHIP talents.</p>",
            'author' => 'R&D Department',
            'is_featured' => 1,
            'published_at' => '2026-01-18 14:30:00'
        ],
        [
            'category_id' => $catMap['talents-education'] ?? 1,
            'title' => "Empowering Vietnam IC Design Talent: VNCHIP Talent Program 2026 Cohort",
            'slug' => 'empowering-vietnam-ic-design-talent-program-2026',
            'thumbnail' => 'assets/images/card_talent_clean.png',
            'summary' => "VNCHIP opens admissions for the specialized IC design training program, providing fully funded hands-on semiconductor projects for prospective engineers.",
            'content' => "<p>At VNCHIP, we believe that talent is the core value of innovation and success in the IC design industry. The 2026 VNCHIP Talent Program offers an intensive curriculum covering RTL coding, SystemVerilog UVM verification, physical design implementation, STA timing signoff, and custom analog layout.</p><p>Participants receive direct mentorship from veterans with decades of silicon tapeout experience on top foundry nodes.</p>",
            'author' => 'Talent Lab',
            'is_featured' => 1,
            'published_at' => '2026-01-05 08:00:00'
        ]
    ];

    $artStmt = $pdo->prepare("INSERT INTO `news_articles` (`category_id`, `title`, `slug`, `thumbnail`, `summary`, `content`, `author`, `is_featured`, `published_at`, `status`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'published') 
        ON DUPLICATE KEY UPDATE `category_id`=VALUES(`category_id`), `title`=VALUES(`title`), `summary`=VALUES(`summary`), `content`=VALUES(`content`), `thumbnail`=VALUES(`thumbnail`), `is_featured`=VALUES(`is_featured`)");

    foreach ($articles as $art) {
        $artStmt->execute([
            $art['category_id'],
            $art['title'],
            $art['slug'],
            $art['thumbnail'],
            $art['summary'],
            $art['content'],
            $art['author'],
            $art['is_featured'],
            $art['published_at']
        ]);
    }

    echo "[✓] News articles seeded successfully.\n";

    // 6. Seed Menus Table (Matching vnchips_old structure)
    $menuItems = [
        // Parent: About Us
        ['id' => 3, 'parent_id' => 0, 'title' => 'About Us', 'link' => 'about-us.php', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 32, 'parent_id' => 3, 'title' => 'VNCHIP Journey', 'link' => 'about-us.php#journey', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 34, 'parent_id' => 3, 'title' => 'Leadership', 'link' => 'about-us.php#leadership', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 36, 'parent_id' => 3, 'title' => 'Code of Conduct', 'link' => 'about-us.php#conduct', 'sort_order' => 3, 'is_divider' => 0, 'column_group' => 1],

        // Parent: Business (Mega Menu)
        ['id' => 2, 'parent_id' => 0, 'title' => 'Business', 'link' => 'business.php', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 21, 'parent_id' => 2, 'title' => 'Technology', 'link' => 'business.php#technology', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 22, 'parent_id' => 2, 'title' => 'Design Service', 'link' => 'business.php#design-service', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 23, 'parent_id' => 2, 'title' => 'IP Service', 'link' => 'business.php#ip-service', 'sort_order' => 3, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 24, 'parent_id' => 2, 'title' => 'VNCHIP R&D', 'link' => 'business.php#rd-collaborations', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 2],
        ['id' => 25, 'parent_id' => 2, 'title' => 'Success in Action', 'link' => 'business.php#success-in-action', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 2],
        // Business > Design Service / IP Service sub-dropdowns
        ['id' => 221, 'parent_id' => 22, 'title' => 'Specification to GDSII', 'link' => 'business.php#spec-gdsii', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 222, 'parent_id' => 22, 'title' => 'RTL to GDSII', 'link' => 'business.php#rtl-gdsii', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 223, 'parent_id' => 22, 'title' => 'Netlist to GDSII', 'link' => 'business.php#netlist-gdsii', 'sort_order' => 3, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 231, 'parent_id' => 23, 'title' => 'IP Development', 'link' => 'business.php#ip-development', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 232, 'parent_id' => 23, 'title' => 'Circuit Design', 'link' => 'business.php#circuit-design', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 233, 'parent_id' => 23, 'title' => 'Custom Layout', 'link' => 'business.php#custom-layout', 'sort_order' => 3, 'is_divider' => 0, 'column_group' => 1],

        // Parent: Sustainable
        ['id' => 4, 'parent_id' => 0, 'title' => 'Sustainable', 'link' => 'sustainable.php', 'sort_order' => 3, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 41, 'parent_id' => 4, 'title' => 'Talents Development', 'link' => 'sustainable.php#talents-development', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 44, 'parent_id' => 4, 'title' => 'Social Responsibilities', 'link' => 'sustainable.php#social-responsibilities', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 45, 'parent_id' => 4, 'title' => 'Confidentiality', 'link' => 'sustainable.php#confidentiality', 'sort_order' => 3, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 46, 'parent_id' => 4, 'title' => 'Clean Room', 'link' => 'sustainable.php#clean-room', 'sort_order' => 4, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 47, 'parent_id' => 4, 'title' => 'Partner', 'link' => 'sustainable.php#partner', 'sort_order' => 5, 'is_divider' => 0, 'column_group' => 1],
        // Sustainable > Talents Development sub-dropdown
        ['id' => 411, 'parent_id' => 41, 'title' => 'VNCHIP Talent Program', 'link' => 'program-detail.php?program=talent', 'sort_order' => 1, 'is_divider' => 0, 'column_group' => 1],
        ['id' => 412, 'parent_id' => 41, 'title' => 'VNCHIP Interns Program', 'link' => 'program-detail.php?program=interns', 'sort_order' => 2, 'is_divider' => 0, 'column_group' => 1],

        // Parent: Media Center
        ['id' => 5, 'parent_id' => 0, 'title' => 'Media Center', 'link' => 'media-center.php', 'sort_order' => 4, 'is_divider' => 0, 'column_group' => 1],

        // Parent: TALK TO US (CTA)
        ['id' => 6, 'parent_id' => 0, 'title' => 'TALK TO US', 'link' => 'talk-to-us.php', 'sort_order' => 5, 'is_divider' => 0, 'column_group' => 1],
    ];

    $menuStmt = $pdo->prepare("INSERT INTO `menus` (`id`, `parent_id`, `title`, `link`, `sort_order`, `is_divider`, `column_group`) 
        VALUES (?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`), `title`=VALUES(`title`), `link`=VALUES(`link`), `sort_order`=VALUES(`sort_order`), `is_divider`=VALUES(`is_divider`), `column_group`=VALUES(`column_group`)");

    foreach ($menuItems as $m) {
        $menuStmt->execute([$m['id'], $m['parent_id'], $m['title'], $m['link'], $m['sort_order'], $m['is_divider'], $m['column_group']]);
    }

    echo "[✓] Menus table seeded successfully.\n";
    echo "=== SEEDING COMPLETED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "[!] Error during seeding: " . $e->getMessage() . "\n";
}
