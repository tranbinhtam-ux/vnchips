<?php
/**
 * Talent / Interns program pages (program-detail.php?program=talent|interns)
 *
 * The built-in defaults below are what the page shipped with. An admin edit (Live Edit on
 * the page itself) stores the whole program as JSON in the settings table under
 * `program_<key>_content`; get() lays that over the defaults, so a program that was never
 * edited keeps rendering exactly as before and a reset is one Setting::delete().
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Program {

    const KEYS = ['talent', 'interns'];

    public static function settingKey($key) {
        return 'program_' . $key . '_content';
    }

    public static function isValidKey($key) {
        return in_array($key, self::KEYS, true);
    }

    /** Program as it should render: defaults overridden by whatever the admin saved */
    public static function get($key) {
        if (!self::isValidKey($key)) return null;
        $program = self::sanitize($key, self::defaults($key));
        $saved = Setting::get(self::settingKey($key), '');
        if ($saved !== '') {
            $decoded = json_decode($saved, true);
            if (is_array($decoded)) {
                $program = self::sanitize($key, array_merge($program, $decoded));
            }
        }
        $program['key'] = $key;
        return $program;
    }

    /** True when the admin has saved an override for this program */
    public static function isCustomised($key) {
        return Setting::get(self::settingKey($key), '') !== '';
    }

    /** Validate + store an override. Returns the clean program or false. */
    public static function save($key, array $data) {
        if (!self::isValidKey($key)) return false;
        $clean = self::sanitize($key, array_merge(self::defaults($key), $data));
        $ok = Setting::set(self::settingKey($key), json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'content_sections',
            'Program page content (' . $key . '), edited via Live Edit');
        return $ok ? $clean : false;
    }

    /** Drop the override so the page goes back to the built-in text */
    public static function reset($key) {
        return self::isValidKey($key) ? Setting::delete(self::settingKey($key)) : false;
    }

    /**
     * Image path for a step: legacy entries are bare file names in assets/images/programs/,
     * uploads from Live Edit are full relative paths (uploads/media/...).
     */
    public static function imageSrc($img) {
        $img = trim((string)$img);
        if ($img === '') return asset('images/hero_chip_bg.webp');
        if (preg_match('~^https?://~i', $img)) return $img;
        return strpos($img, '/') === false ? asset('images/programs/' . $img) : asset($img);
    }

    /** Coerce user-supplied structure into the shape the page expects */
    public static function sanitize($key, array $p) {
        // Text is stored as plain characters (en dash, middle dot...), never as HTML entities,
        // so the Live Edit form shows readable text and the page escapes on output.
        $str = static function ($v) { return trim(html_entity_decode((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8')); };
        $strList = static function ($v) use ($str) {
            if (is_string($v)) $v = preg_split('/\r\n|\r|\n/', $v);
            if (!is_array($v)) return [];
            $out = [];
            foreach ($v as $line) {
                $line = $str($line);
                if ($line !== '') $out[] = $line;
            }
            return $out;
        };

        $out = [
            'title'    => $str($p['title'] ?? ''),
            'badge'    => $str($p['badge'] ?? ''),
            'subtitle' => $str($p['subtitle'] ?? ''),
            'desc'     => $strList($p['desc'] ?? []),
            'process'  => $str($p['process'] ?? 'Selection'),
            'steps'    => [],
        ];
        if ($out['title'] === '') $out['title'] = self::defaults($key)['title'];

        foreach ((array)($p['steps'] ?? []) as $s) {
            if (!is_array($s)) continue;
            $title = $str($s['title'] ?? '');
            if ($title === '') continue;
            $out['steps'][] = [
                'img'    => $str($s['img'] ?? ''),
                'title'  => $title,
                'points' => $strList($s['points'] ?? []),
            ];
        }

        if ($key === 'talent') {
            $out['modules'] = [];
            foreach ((array)($p['modules'] ?? []) as $m) {
                if (!is_array($m)) continue;
                $title = $str($m['title'] ?? '');
                if ($title === '') continue;
                $icon = preg_replace('/[^a-z0-9\-]/', '', strtolower($str($m['icon'] ?? 'fa-microchip')));
                $out['modules'][] = [
                    'icon'  => $icon !== '' ? $icon : 'fa-microchip',
                    'title' => $title,
                    'hours' => $str($m['hours'] ?? ''),
                ];
            }
        } else {
            $a = is_array($p['announcement'] ?? null) ? $p['announcement'] : [];
            $out['announcement'] = [
                'title' => $str($a['title'] ?? ''),
                'desc'  => $str($a['desc'] ?? ''),
            ];
        }
        return $out;
    }

    public static function defaults($key) {
        if ($key === 'interns') {
            return [
                'title'    => 'VNCHIP Interns Program',
                'badge'    => '10 interns / year &middot; 3 months',
                'subtitle' => 'Practical experience for students, with support for capstone projects and university internship requirements.',
                'desc'     => [
                    'VNCHIP selects 10 interns each year for a 3-month program with a flexible schedule. Interns combine online training with hands-on practice in semiconductor research and IC design.',
                    'Interns are mentored by VNCHIP experts, work on real projects, and may be considered for the VNCHIP Talent Program.',
                ],
                'process'  => 'Recruitment',
                'announcement' => [
                    'title' => 'Applications open year-round',
                    'desc'  => 'Internship applications are reviewed continuously throughout the year.',
                ],
                'steps' => [
                    ['img' => 'Resume-Screening-1.png', 'title' => '1. Resume Screening', 'points' => [
                        'Review applications and shortlist candidates',
                        'Match candidates to job requirements',
                    ]],
                    ['img' => 'Initial-Screening-Call-2.png', 'title' => '2. Initial Screening Call', 'points' => [
                        'Discuss background and motivation',
                        'Clarify role expectations and align on goals',
                    ]],
                    ['img' => 'Technical-Skills-Assessment-3.png', 'title' => '3. Technical Skills Assessment', 'points' => [
                        'Coding or technical test',
                        'Evaluate problem-solving ability',
                    ]],
                    ['img' => 'First-Interview-4.png', 'title' => '4. First Interview (Technical & Role-Specific)', 'points' => [
                        'Assess job knowledge and past experience',
                        'Technical problem-solving',
                    ]],
                    ['img' => 'Interview-With-HR-5.png', 'title' => '5. Interview With HR', 'points' => [
                        'Cultural fit and values',
                        'Adaptability and interpersonal skills',
                    ]],
                    ['img' => 'Final-Decision-6.png', 'title' => '6. Final Decision & Offer', 'points' => [
                        'Gather interviewer feedback',
                        'Finalize terms and extend the offer',
                    ]],
                    ['img' => 'Onboarding-7.png', 'title' => '7. Onboarding', 'points' => [
                        'Set up the new intern',
                        'Introduce the team, resources and tools',
                    ]],
                ],
            ];
        }

        return [
            'title'    => 'VNCHIP Talent Program',
            'badge'    => '35&ndash;50 participants / year &middot; 3 months',
            'subtitle' => 'Developing young engineers in IC design and core semiconductor technologies to international standards.',
            'desc'     => [
                'The first phase of the program focused on technical training in integrated circuit design, together with foundational skills such as communication and teamwork. From 2026, the program expands to include business-technology thinking, management awareness and professional working skills.',
                'Each year, 35&ndash;50 selected participants join a 3-month program, running from the Orientation Day in late April to the certification ceremony in September.',
            ],
            'process'  => 'Selection',
            'modules'  => [
                ['icon' => 'fa-microchip',    'title' => 'Semiconductor IC Design Practical Competencies', 'hours' => '120 hours'],
                ['icon' => 'fa-user-tie',     'title' => 'Professional Working Competencies (Personal Development Skills)', 'hours' => '60 hours'],
                ['icon' => 'fa-people-group', 'title' => 'Management Competencies for Engineers (Management Module)', 'hours' => '30 hours'],
            ],
            'steps' => [
                ['img' => 'Application-Submission-1.png', 'title' => '1. Application Submission', 'points' => [
                    'Register before the deadline via the QR code',
                    'Submit your CV, academic level, university and major',
                ]],
                ['img' => 'Attend-VNCHIP-Orientation-2.png', 'title' => '2. Attend VNCHIP Orientation', 'points' => [
                    'Register for the orientation event',
                    'Attend the VNCHIP Orientation Day',
                ]],
                ['img' => 'Initial-Screening-3.png', 'title' => '3. Initial Screening', 'points' => [
                    'Review of academic performance',
                    'Relevant coursework and interest in IC design',
                ]],
                ['img' => 'Assessment-Test-4.png', 'title' => '4. Assessment Test', 'points' => [
                    'Foundational IC design knowledge',
                    'Problem-solving and critical thinking',
                ]],
                ['img' => 'Interview-Process-5.png', 'title' => '5. Interview', 'points' => [
                    'Technical skills and communication',
                    'Interpersonal skills and cultural fit',
                ]],
                ['img' => 'VNCHIP-Training-Program-6.png', 'title' => '6. Training Program', 'points' => [
                    'Hands-on ASIC design training from Netlist to GDSII',
                    'Place & Route, STA, physical & power verification, sign-off',
                ]],
                ['img' => 'Receive-Certificates-7.png', 'title' => '7. Receive Certificates', 'points' => [
                    'Successful candidates are awarded VNCHIP certificates',
                ]],
                ['img' => 'Program-Enrollment-Internship-8.png', 'title' => '8. Enrollment & Internship', 'points' => [
                    'Top-performing students are offered internships at VNCHIP',
                ]],
            ],
        ];
    }
}
