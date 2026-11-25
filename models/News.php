<?php
/**
 * News / Media Center Model with Full CRUD Operations
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class News {
    /**
     * Get featured news articles for homepage / index preview
     */
    public static function getFeatured($limit = 4) {
        $db = Database::getInstance()->getConnection();
        if (!$db) {
            return self::getFallbackArticles();
        }

        try {
            $sql = "SELECT n.*, c.name AS category_name, c.slug AS category_slug 
                    FROM `news_articles` n 
                    LEFT JOIN `categories` c ON n.category_id = c.id 
                    WHERE n.status = 'published' 
                    ORDER BY n.is_featured DESC, n.published_at DESC 
                    LIMIT :limit";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
            return !empty($results) ? $results : self::getFallbackArticles();
        } catch (PDOException $e) {
            error_log("News::getFeatured error: " . $e->getMessage());
            return self::getFallbackArticles();
        }
    }

    /**
     * Get paginated articles with optional category filter & status (for admin/frontend)
     * $excludeCategorySlugs hides whole categories (e.g. Partners on the public Media Center)
     */
    public static function getAll($limit = 6, $offset = 0, $categorySlug = null, $keyword = null, $status = 'published', array $excludeCategorySlugs = [], $moduleType = null, $month = null, $year = null) {
        $db = Database::getInstance()->getConnection();
        if (!$db) {
            return ['items' => self::getFallbackArticles(), 'total' => count(self::getFallbackArticles())];
        }

        try {
            $where = [];
            $params = [];

            if ($status !== 'all') {
                $where[] = "n.status = :status";
                $params[':status'] = $status;
            }

            if (!empty($categorySlug)) {
                $where[] = "c.slug = :category_slug";
                $params[':category_slug'] = $categorySlug;
            }

            if (!empty($moduleType) && $moduleType !== 'all') {
                $where[] = "c.module_type = :module_type";
                $params[':module_type'] = $moduleType;
            }

            if (!empty($month)) {
                $where[] = "MONTH(n.published_at) = :pub_month";
                $params[':pub_month'] = (int)$month;
            }

            if (!empty($year)) {
                $where[] = "YEAR(n.published_at) = :pub_year";
                $params[':pub_year'] = (int)$year;
            }

            if (!empty($excludeCategorySlugs)) {
                $placeholders = [];
                foreach (array_values($excludeCategorySlugs) as $i => $slug) {
                    $placeholders[] = ":ex_cat_$i";
                    $params[":ex_cat_$i"] = $slug;
                }
                $where[] = "(c.slug IS NULL OR c.slug NOT IN (" . implode(', ', $placeholders) . "))";
            }

            if (!empty($keyword)) {
                $where[] = "(n.title LIKE :kw OR n.summary LIKE :kw OR n.content LIKE :kw)";
                $params[':kw'] = '%' . $keyword . '%';
            }

            $whereSql = !empty($where) ? implode(' AND ', $where) : '1=1';

            // Count total
            $countSql = "SELECT COUNT(*) FROM `news_articles` n LEFT JOIN `categories` c ON n.category_id = c.id WHERE $whereSql";
            $countStmt = $db->prepare($countSql);
            foreach ($params as $k => $v) {
                $countStmt->bindValue($k, $v);
            }
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();

            // Fetch items
            $sql = "SELECT n.*, c.name AS category_name, c.slug AS category_slug, c.module_type 
                    FROM `news_articles` n 
                    LEFT JOIN `categories` c ON n.category_id = c.id 
                    WHERE $whereSql 
                    ORDER BY n.is_pinned DESC, n.sort_order ASC, n.published_at DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            return ['items' => $items, 'total' => $total];
        } catch (PDOException $e) {
            error_log("News::getAll error: " . $e->getMessage());
            return ['items' => self::getFallbackArticles(), 'total' => count(self::getFallbackArticles())];
        }
    }

    /**
     * Get single article by slug
     */
    public static function getBySlug($slug) {
        $db = Database::getInstance()->getConnection();
        $article = null;

        if ($db) {
            try {
                $sql = "SELECT n.*, c.name AS category_name, c.slug AS category_slug 
                        FROM `news_articles` n 
                        LEFT JOIN `categories` c ON n.category_id = c.id 
                        WHERE n.slug = :slug AND n.status = 'published' 
                        LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([':slug' => $slug]);
                $article = $stmt->fetch();

                if ($article) {
                    $db->prepare("UPDATE `news_articles` SET `views` = `views` + 1 WHERE `id` = :id")->execute([':id' => $article['id']]);
                }
            } catch (PDOException $e) {
                error_log("News::getBySlug error: " . $e->getMessage());
                $article = null;
            }
        }

        // Fallback to static/scraped articles if not found in database or database is offline
        if (!$article) {
            $fallbacks = self::getFallbackArticles();
            foreach ($fallbacks as $item) {
                if (($item['slug'] ?? '') === $slug) {
                    return $item;
                }
            }
        }

        return $article;
    }

    /**
     * Get related articles from the same category or latest
     */
    public static function getRelated($categoryId, $excludeId, $limit = 3) {
        $db = Database::getInstance()->getConnection();
        $items = [];

        if ($db) {
            try {
                $sql = "SELECT n.*, c.name AS category_name, c.slug AS category_slug 
                        FROM `news_articles` n 
                        LEFT JOIN `categories` c ON n.category_id = c.id 
                        WHERE n.status = 'published' AND n.id != :exclude_id 
                        ORDER BY (n.category_id = :cat_id) DESC, n.published_at DESC 
                        LIMIT :limit";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':exclude_id', (int)$excludeId, PDO::PARAM_INT);
                $stmt->bindValue(':cat_id', (int)$categoryId, PDO::PARAM_INT);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                $items = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("News::getRelated error: " . $e->getMessage());
                $items = [];
            }
        }

        // Fallback if empty
        if (empty($items)) {
            $fallbacks = self::getFallbackArticles();
            $filtered = [];
            foreach ($fallbacks as $art) {
                if (($art['id'] ?? 0) != $excludeId && ($art['slug'] ?? '') !== $excludeId) {
                    $filtered[] = $art;
                }
                if (count($filtered) >= $limit) break;
            }
            return $filtered;
        }

        return $items;
    }

    /**
     * Get single article by ID (Admin)
     */
    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM `news_articles` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("News::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create article (Admin)
     */
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['title']);
            
            // Ensure unique slug
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM `news_articles` WHERE `slug` = :slug");
            $checkStmt->execute([':slug' => $slug]);
            if ($checkStmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }

            $sql = "INSERT INTO `news_articles` (`category_id`, `title`, `slug`, `custom_url`, `thumbnail`, `summary`, `content`, `author`, `is_featured`, `is_pinned`, `sort_order`, `status`, `published_at`) 
                    VALUES (:cat, :title, :slug, :curl, :thumb, :summary, :content, :author, :featured, :pinned, :sort, :status, :pub_at)";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':cat' => (int)$data['category_id'],
                ':title' => $data['title'],
                ':slug' => $slug,
                ':curl' => !empty($data['custom_url']) ? $data['custom_url'] : null,
                ':thumb' => $data['thumbnail'] ?? 'assets/images/media_htv9_event.webp',
                ':summary' => $data['summary'],
                ':content' => $data['content'],
                ':author' => $data['author'] ?? 'VNCHIP Editorial Team',
                ':featured' => !empty($data['is_featured']) ? 1 : 0,
                ':pinned' => !empty($data['is_pinned']) ? 1 : 0,
                ':sort' => (int)($data['sort_order'] ?? 0),
                ':status' => $data['status'] ?? 'published',
                ':pub_at' => !empty($data['published_at']) ? $data['published_at'] : date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            error_log("News::create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update article (Admin)
     */
    public static function update($id, $data) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['title']);
            
            // Ensure unique slug excluding current
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM `news_articles` WHERE `slug` = :slug AND `id` != :id");
            $checkStmt->execute([':slug' => $slug, ':id' => (int)$id]);
            if ($checkStmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }

            $sql = "UPDATE `news_articles` SET 
                    `category_id` = :cat,
                    `title` = :title,
                    `slug` = :slug,
                    `custom_url` = :curl,
                    `thumbnail` = :thumb,
                    `summary` = :summary,
                    `content` = :content,
                    `author` = :author,
                    `is_featured` = :featured,
                    `is_pinned` = :pinned,
                    `sort_order` = :sort,
                    `status` = :status,
                    `published_at` = :pub_at
                    WHERE `id` = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':id' => (int)$id,
                ':cat' => (int)$data['category_id'],
                ':title' => $data['title'],
                ':slug' => $slug,
                ':curl' => !empty($data['custom_url']) ? $data['custom_url'] : null,
                ':thumb' => $data['thumbnail'],
                ':summary' => $data['summary'],
                ':content' => $data['content'],
                ':author' => $data['author'] ?? 'VNCHIP Editorial Team',
                ':featured' => !empty($data['is_featured']) ? 1 : 0,
                ':pinned' => !empty($data['is_pinned']) ? 1 : 0,
                ':sort' => (int)($data['sort_order'] ?? 0),
                ':status' => $data['status'] ?? 'published',
                ':pub_at' => !empty($data['published_at']) ? $data['published_at'] : date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            error_log("News::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete article (Admin)
     */
    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("DELETE FROM `news_articles` WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("News::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle featured status (Admin)
     */
    public static function toggleFeatured($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE `news_articles` SET `is_featured` = 1 - `is_featured` WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("News::toggleFeatured error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle pinned status (Admin - pin to top)
     */
    public static function togglePinned($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE `news_articles` SET `is_pinned` = 1 - `is_pinned` WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("News::togglePinned error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle status (published <-> draft)
     */
    public static function toggleStatus($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE `news_articles` SET `status` = IF(`status`='published', 'draft', 'published') WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("News::toggleStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Duplicate article (Admin)
     */
    public static function duplicate($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $src = self::getById($id);
            if (!$src) return false;

            $newTitle = 'Copy of ' . $src['title'];
            $newSlug = slugify($newTitle) . '-' . time();

            $sql = "INSERT INTO `news_articles` (`category_id`, `title`, `slug`, `custom_url`, `thumbnail`, `summary`, `content`, `author`, `is_featured`, `is_pinned`, `sort_order`, `status`, `published_at`) 
                    VALUES (:cat, :title, :slug, :curl, :thumb, :summary, :content, :author, 0, 0, 0, 'draft', NOW())";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':cat' => (int)$src['category_id'],
                ':title' => $newTitle,
                ':slug' => $newSlug,
                ':curl' => $src['custom_url'] ?? null,
                ':thumb' => $src['thumbnail'],
                ':summary' => $src['summary'],
                ':content' => $src['content'],
                ':author' => $src['author']
            ]);
        } catch (PDOException $e) {
            error_log("News::duplicate error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update sort order for reordered articles
     */
    public static function updateOrder(array $orderedIds) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("UPDATE `news_articles` SET `sort_order` = :sort WHERE `id` = :id");
            foreach ($orderedIds as $index => $id) {
                $stmt->execute([':sort' => (int)$index, ':id' => (int)$id]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("News::updateOrder error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all categories with article count
     */
    public static function getCategories() {
        $db = Database::getInstance()->getConnection();
        if (!$db) return self::getDefaultCategories();

        try {
            $sql = "SELECT c.*, COUNT(n.id) as article_count 
                    FROM `categories` c 
                    LEFT JOIN `news_articles` n ON c.id = n.category_id 
                    GROUP BY c.id 
                    ORDER BY c.name ASC";
            $results = $db->query($sql)->fetchAll();
            return !empty($results) ? $results : self::getDefaultCategories();
        } catch (PDOException $e) {
            error_log("News::getCategories error: " . $e->getMessage());
            return self::getDefaultCategories();
        }
    }

    /**
     * Default category list for fallback
     */
    public static function getDefaultCategories() {
        return [
            ['id' => 1, 'name' => 'All Categories', 'slug' => 'all-categories', 'article_count' => 6],
            ['id' => 2, 'name' => 'Partners', 'slug' => 'partners', 'article_count' => 3],
            ['id' => 3, 'name' => 'Corporate News', 'slug' => 'corporate-news', 'article_count' => 2],
            ['id' => 4, 'name' => 'Semiconductor R&D', 'slug' => 'semiconductor-rd', 'article_count' => 2],
            ['id' => 5, 'name' => 'Media & Events', 'slug' => 'media-events', 'article_count' => 2],
            ['id' => 6, 'name' => 'Talents & Education', 'slug' => 'talents-education', 'article_count' => 3]
        ];
    }

    /**
     * Get total counts for dashboard stats
     */
    public static function getDashboardStats() {
        $db = Database::getInstance()->getConnection();
        if (!$db) return ['articles' => 4, 'categories' => 4, 'inquiries' => 0, 'views' => 120];

        try {
            $articles = (int)$db->query("SELECT COUNT(*) FROM `news_articles`")->fetchColumn();
            $categories = (int)$db->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
            $inquiries = (int)$db->query("SELECT COUNT(*) FROM `inquiries`")->fetchColumn();
            $newInquiries = (int)$db->query("SELECT COUNT(*) FROM `inquiries` WHERE `status` = 'new'")->fetchColumn();
            $views = (int)$db->query("SELECT SUM(`views`) FROM `news_articles`")->fetchColumn();

            return [
                'articles' => $articles,
                'categories' => $categories,
                'inquiries' => $inquiries,
                'new_inquiries' => $newInquiries,
                'views' => $views
            ];
        } catch (PDOException $e) {
            error_log("News::getDashboardStats error: " . $e->getMessage());
            return ['articles' => 0, 'categories' => 0, 'inquiries' => 0, 'new_inquiries' => 0, 'views' => 0];
        }
    }

    /**
     * Fallback mock data - Synced with https://vnchip-inc.com/news or scraped_articles.json
     */
    private static function getFallbackArticles() {
        $scrapedFile = __DIR__ . '/../scraped_articles.json';
        if (file_exists($scrapedFile)) {
            $json = file_get_contents($scrapedFile);
            $data = json_decode($json, true);
            if (!empty($data) && is_array($data)) {
                return $data;
            }
        }

        return [
            [
                'id' => 1,
                'title' => "VNCHIP Orientation 2026: Policy-Driven Development of Vietnam’s Semiconductor Industry – Insights from Dr.Huynh Phuoc Nghia",
                'slug' => 'vnchip-orientation-2026-policy-driven-development-of-vietnam-s-semiconductor-industry-insights-from-dr-huynh-phuoc-nghia',
                'thumbnail' => 'uploads/posts/34.png',
                'summary' => "Vietnam's semiconductor industry is at a pivotal crossroads. Transitioning from an emerging player to a critical global hub requires more than just ambition—it demands a robust, strategically integrated national policy framework.",
                'category_name' => 'Talents & Education',
                'category_slug' => 'talents-education',
                'published_at' => '2026-04-15 02:56:00',
                'is_featured' => 1,
                'views' => 142
            ],
            [
                'id' => 2,
                'title' => "VNCHIP Orientation 2026: How Cadence is Applying Generative AI in Chip Design",
                'slug' => 'vnchip-orientation-2026-how-cadence-is-applying-generative-ai-in-chip-design',
                'thumbnail' => 'uploads/posts/27_1.png',
                'summary' => "Artificial intelligence is no longer a distant concept, it is actively reshaping how semiconductor engineering is done today. As the industry faces increasing pressure to meet stringent PPA targets and accelerate system verification at advanced nodes, traditional design methodologies are rapidly evolving.",
                'category_name' => 'Semiconductor R&D',
                'category_slug' => 'semiconductor-rd',
                'published_at' => '2026-04-15 02:14:00',
                'is_featured' => 1,
                'views' => 128
            ],
            [
                'id' => 3,
                'title' => "VNCHIP Launches Talent Program 2025 with a Power-Packed Orientation Day",
                'slug' => 'vnchip-launches-talent-program-2025-with-a-power-packed-orientation-day',
                'thumbnail' => 'uploads/posts/Messenger_creation_C7568729-33A9-41D4-A711-4C2E64F98DC7.jpeg',
                'summary' => "The energy was electric at VNCHIP’s Talent Program 2025 Orientation Day, as nearly 100 of Vietnam’s brightest 4th-year university students gathered to kickstart their journey in Integrated Circuit (IC) Design.",
                'category_name' => 'Talents & Education',
                'category_slug' => 'talents-education',
                'published_at' => '2025-07-27 03:27:00',
                'is_featured' => 1,
                'views' => 110
            ],
            [
                'id' => 4,
                'title' => "VNCHIP Institute’s Grand Opening Featured on HTV9 National Television",
                'slug' => 'vnchip-institute-s-grand-opening-featured-on-htv-9-national-television',
                'thumbnail' => 'uploads/posts/HTV9.jpg',
                'summary' => "Vietnam’s First Cadence Certified Lab Marks a New Milestone in IC Design Innovation. HTV9 – National Television of Vietnam broadcasted the official inauguration ceremony of the VNCHIP Institute of Semiconductor Research and Development.",
                'category_name' => 'Media & Events',
                'category_slug' => 'media-events',
                'published_at' => '2025-07-25 23:09:00',
                'is_featured' => 1,
                'views' => 95
            ],
            [
                'id' => 5,
                'title' => "VNCHIP Talent Program 2024",
                'slug' => 'vnchip-talent-program-2024',
                'thumbnail' => 'uploads/posts/certificates.jpg',
                'summary' => "On October 5th, 2024, we were thrilled to present certificates to all the amazing university students who successfully completed our VNCHIP Talent Program after 4 months of intensive training.",
                'category_name' => 'Talents & Education',
                'category_slug' => 'talents-education',
                'published_at' => '2024-12-15 12:09:00',
                'is_featured' => 0,
                'views' => 88
            ],
            [
                'id' => 6,
                'title' => "VNCHIP Cultural Development Program (VCDP)",
                'slug' => 'vnchip-cultural-development-program-vcdp',
                'thumbnail' => 'uploads/posts/VCDP2.jpg',
                'summary' => "At VNCHIP, our Cultural Development Program is centered on humanity and high performance. By prioritizing teamwork, personal growth, and a positive work environment, we cultivate a culture where individuals and the team thrive together.",
                'category_name' => 'Corporate News',
                'category_slug' => 'corporate-news',
                'published_at' => '2024-12-14 15:06:00',
                'is_featured' => 0,
                'views' => 79
            ]
        ];
    }
}
