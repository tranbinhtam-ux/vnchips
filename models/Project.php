<?php
/**
 * Partners Projects (business.php#partners-projects cards -> project-detail.php?slug=...)
 *
 * data/projects.php stays the built-in list. An admin edit on business.php (Live Edit) stores
 * the whole list as JSON in the settings table under `business_projects`; when that exists it
 * replaces the built-in list entirely, so projects can be added, removed and reordered.
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Project {

    const SETTING_KEY = 'business_projects';

    /** Every project in display order */
    public static function all() {
        $saved = Setting::get(self::SETTING_KEY, '');
        if ($saved !== '') {
            $decoded = json_decode($saved, true);
            if (is_array($decoded)) {
                return self::sanitize($decoded);
            }
        }
        return self::defaults();
    }

    public static function bySlug($slug) {
        foreach (self::all() as $p) {
            if ($p['slug'] === $slug) return $p;
        }
        return null;
    }

    public static function defaults() {
        return self::sanitize(require __DIR__ . '/../data/projects.php');
    }

    public static function isCustomised() {
        return Setting::get(self::SETTING_KEY, '') !== '';
    }

    /** Validate + store the list. Returns the clean list or false. */
    public static function save(array $list) {
        $clean = self::sanitize($list);
        $ok = Setting::set(self::SETTING_KEY, json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'content_sections',
            'Partners Projects on business.php, edited via Live Edit');
        return $ok ? $clean : false;
    }

    public static function reset() {
        return Setting::delete(self::SETTING_KEY);
    }

    /** Coerce a list into [{slug,title,subtitle,image,summary,content}], dropping empty rows */
    public static function sanitize(array $list) {
        $out = [];
        $seen = [];
        foreach ($list as $p) {
            if (!is_array($p)) continue;
            $title = trim((string)($p['title'] ?? ''));
            if ($title === '') continue;
            $slug = normalizeSlug((string)($p['slug'] ?? ''));
            if ($slug === '') $slug = slugify($title);
            $base = $slug; $n = 2;
            while (isset($seen[$slug])) { $slug = $base . '-' . $n++; }
            $seen[$slug] = true;
            $out[] = [
                'slug'     => $slug,
                'title'    => $title,
                'subtitle' => trim((string)($p['subtitle'] ?? '')),
                'image'    => trim((string)($p['image'] ?? '')),
                'summary'  => trim((string)($p['summary'] ?? '')),
                'content'  => trim((string)($p['content'] ?? '')),
            ];
        }
        return $out;
    }
}
