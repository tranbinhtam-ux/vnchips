<?php
/**
 * Admin Authentication Guard
 */

if (!defined('VNCHIP_APP')) {
    require_once __DIR__ . '/../../config/config.php';
}

require_once __DIR__ . '/../../models/Admin.php';

// Enforce authentication
Admin::requireAuth();

$currentAdmin = Admin::current();
