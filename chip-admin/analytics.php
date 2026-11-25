<?php
/**
 * Admin Traffic Analytics
 * High-Tech Interactive Analytics with Chart.js
 * Modeled after VNCHIP Labs Control Center
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

$db = Database::getInstance()->getConnection();

// Timeframe filter: day, month, quarter, year
$filter = $_GET['filter'] ?? 'month';
$filterDays = 30;
if ($filter === 'day') $filterDays = 1;
elseif ($filter === 'quarter') $filterDays = 90;
elseif ($filter === 'year') $filterDays = 365;
else $filter = 'month';

$error = '';
$summary = ['today' => 0, 'week' => 0, 'month' => 0, 'total' => 0, 'unique' => 0];
$daily = [];
$devices = $browsers = $topPages = $topReferrers = $topIps = [];

if ($db) {
    try {
        $summary = $db->query("SELECT
                SUM(`created_at` >= CURDATE()) AS today,
                SUM(`created_at` >= CURDATE() - INTERVAL 6 DAY) AS week,
                SUM(`created_at` >= CURDATE() - INTERVAL 29 DAY) AS month,
                COUNT(*) AS total,
                COUNT(DISTINCT `ip_address`) AS `unique`
            FROM `visitor_logs`")->fetch();

        $since = "`created_at` >= CURDATE() - INTERVAL " . ($filterDays - 1) . " DAY";

        $rows = $db->query("SELECT DATE(`created_at`) AS d, COUNT(*) AS c FROM `visitor_logs` WHERE $since GROUP BY d")->fetchAll(PDO::FETCH_KEY_PAIR);
        for ($i = $filterDays - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $daily[$d] = (int)($rows[$d] ?? 0);
        }

        $group = function ($column, $limit) use ($db, $since) {
            return $db->query("SELECT COALESCE(NULLIF(`$column`, ''), '(none)') AS label, COUNT(*) AS c FROM `visitor_logs` WHERE $since GROUP BY label ORDER BY c DESC LIMIT $limit")->fetchAll();
        };
        $devices = $group('device', 5);
        $browsers = $group('browser', 6);
        $topPages = $group('page_url', 8);
        $topIps = $group('ip_address', 8);
        $topReferrers = $db->query("SELECT `referrer` AS label, COUNT(*) AS c FROM `visitor_logs` WHERE $since AND `referrer` <> '' AND `referrer` NOT LIKE " . $db->quote('%' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '%') . " GROUP BY label ORDER BY c DESC LIMIT 8")->fetchAll();
    } catch (PDOException $e) {
        $error = 'Visitor logs table query error: ' . $e->getMessage();
    }
}

$periodTotal = array_sum($daily);
$chartLabels = array_map(function($d) { return date('M d', strtotime($d)); }, array_keys($daily));
$chartValues = array_values($daily);

$deviceLabels = [];
$deviceValues = [];
foreach ($devices as $dev) {
    $deviceLabels[] = ucfirst($dev['label']);
    $deviceValues[] = (int)$dev['c'];
}
if (empty($deviceLabels)) {
    $deviceLabels = ['Desktop', 'Mobile', 'Tablet'];
    $deviceValues = [10, 5, 1];
}

$adminTitle = 'Traffic Analytics';
$adminBreadcrumb = 'Marketing / <span>Global Insights</span>';
$adminActivePage = 'analytics';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Timeframe Filter Buttons -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
  <div style="font-size: 0.95rem; font-weight: 700; color: #0F172A;">
    Live Web Analytics &middot; <span style="color: var(--admin-primary); font-weight: 800;"><?= number_format($periodTotal) ?> Views</span> in <?= ucfirst($filter) ?> filter
  </div>
  
  <div class="btn-group shadow-sm" role="group" style="background: #FFFFFF; border-radius: 9999px; padding: 4px; border: 1px solid var(--admin-border);">
    <a href="<?= admin_url('analytics.php?filter=day') ?>" class="btn btn-sm btn-pill <?= ($filter === 'day') ? 'btn-dark' : 'btn-secondary' ?>" style="border: none;">Day</a>
    <a href="<?= admin_url('analytics.php?filter=month') ?>" class="btn btn-sm btn-pill <?= ($filter === 'month') ? 'btn-dark' : 'btn-secondary' ?>" style="border: none;">Month</a>
    <a href="<?= admin_url('analytics.php?filter=quarter') ?>" class="btn btn-sm btn-pill <?= ($filter === 'quarter') ? 'btn-dark' : 'btn-secondary' ?>" style="border: none;">Quarter</a>
    <a href="<?= admin_url('analytics.php?filter=year') ?>" class="btn btn-sm btn-pill <?= ($filter === 'year') ? 'btn-dark' : 'btn-secondary' ?>" style="border: none;">Year</a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
<?php endif; ?>

<!-- 4 KPI Cards -->
<div class="stats-grid">
  <div class="card-stat">
    <div>
      <div class="stat-label">TODAY</div>
      <div class="stat-value"><?= number_format((int)$summary['today']) ?></div>
      <div class="stat-subtext">Page views since midnight</div>
    </div>
    <div class="icon-shape icon-orange"><i class="fa-solid fa-calendar-day"></i></div>
  </div>

  <div class="card-stat">
    <div>
      <div class="stat-label">LAST 7 DAYS</div>
      <div class="stat-value"><?= number_format((int)$summary['week']) ?></div>
      <div class="stat-subtext">Rolling 7-day volume</div>
    </div>
    <div class="icon-shape icon-blue"><i class="fa-solid fa-calendar-week"></i></div>
  </div>

  <div class="card-stat">
    <div>
      <div class="stat-label">LAST 30 DAYS</div>
      <div class="stat-value"><?= number_format((int)$summary['month']) ?></div>
      <div class="stat-subtext">Monthly total traffic</div>
    </div>
    <div class="icon-shape icon-green"><i class="fa-solid fa-calendar-days"></i></div>
  </div>

  <div class="card-stat">
    <div>
      <div class="stat-label">TOTAL / UNIQUE IPS</div>
      <div class="stat-value"><?= number_format((int)$summary['total']) ?> <span style="font-size: 1rem; color: #94A3B8;">/ <?= number_format((int)$summary['unique']) ?></span></div>
      <div class="stat-subtext">All-time tracked visitors</div>
    </div>
    <div class="icon-shape icon-purple"><i class="fa-solid fa-users"></i></div>
  </div>
</div>

<!-- Charts Grid: Visitors Overview + Device Breakdown -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px;">
  
  <!-- Line Chart: Visitors Overview -->
  <div class="admin-panel" style="margin-bottom: 0;">
    <div class="panel-header">
      <div>
        <h3 class="panel-title"><i class="fa-solid fa-chart-area" style="color: var(--admin-primary);"></i> Visitors Overview (<?= ucfirst($filter) ?>)</h3>
        <div class="panel-subtitle">Daily traffic timeline &middot; Excludes internal admin sessions</div>
      </div>
      <span class="badge badge-success"><i class="fa-solid fa-circle-dot"></i> Live Data</span>
    </div>
    <div style="height: 320px; position: relative;">
      <canvas id="trafficChart"></canvas>
    </div>
  </div>

  <!-- Doughnut Chart: Device Distribution -->
  <div class="admin-panel" style="margin-bottom: 0;">
    <div class="panel-header">
      <div>
        <h3 class="panel-title"><i class="fa-solid fa-mobile-screen" style="color: var(--admin-info);"></i> Device Distribution</h3>
        <div class="panel-subtitle">Traffic by client device category</div>
      </div>
    </div>
    <div style="height: 250px; position: relative; display: flex; align-items: center; justify-content: center;">
      <canvas id="deviceChart"></canvas>
    </div>
    <div style="display: flex; justify-content: space-around; margin-top: 15px; font-size: 0.8rem; text-align: center;">
      <?php foreach ($devices as $dev): ?>
        <div>
          <div style="font-weight: 700; color: #0F172A;"><?= number_format($dev['c']) ?></div>
          <div style="color: var(--admin-text-muted); font-size: 0.75rem;"><?= ucfirst($dev['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- Breakdown Tables Grid: Top Pages & Frequent IPs -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
  
  <!-- Most Visited Pages -->
  <div class="admin-panel">
    <div class="panel-header">
      <h3 class="panel-title"><i class="fa-solid fa-file-lines" style="color: var(--admin-primary);"></i> Most Visited Pages</h3>
    </div>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Page URL</th>
          <th style="text-align: right;">Hits</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($topPages)): ?>
          <tr><td colspan="2" style="text-align: center; color: #94A3B8; padding: 20px;">No visit data yet.</td></tr>
        <?php else: ?>
          <?php foreach ($topPages as $p): ?>
            <tr>
              <td>
                <a href="<?= url(ltrim($p['label'], '/')) ?>" target="_blank" style="color: #0F172A; font-weight: 600; text-decoration: none;">
                  <?= e($p['label']) ?>
                </a>
              </td>
              <td style="text-align: right;">
                <span class="badge badge-primary"><?= number_format($p['c']) ?> hits</span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Frequent Visitors (IP) -->
  <div class="admin-panel">
    <div class="panel-header">
      <h3 class="panel-title"><i class="fa-solid fa-shield-halved" style="color: var(--admin-danger);"></i> Frequent Visitors (IP)</h3>
    </div>
    <table class="admin-table">
      <thead>
        <tr>
          <th>IP Address</th>
          <th style="text-align: right;">Requests</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($topIps)): ?>
          <tr><td colspan="2" style="text-align: center; color: #94A3B8; padding: 20px;">No logs recorded yet.</td></tr>
        <?php else: ?>
          <?php foreach ($topIps as $ip): ?>
            <tr>
              <td style="font-family: monospace; font-size: 0.85rem; color: #334155;">
                <i class="fa-solid fa-network-wired text-muted me-2"></i> <?= e($ip['label']) ?>
              </td>
              <td style="text-align: right;">
                <span class="badge badge-dark"><?= number_format($ip['c']) ?> requests</span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Visitors Line Chart
  const ctxTraffic = document.getElementById('trafficChart');
  if (ctxTraffic) {
    const gradient = ctxTraffic.getContext('2d').createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(242, 91, 34, 0.25)');
    gradient.addColorStop(1, 'rgba(242, 91, 34, 0.0)');

    new Chart(ctxTraffic, {
      type: 'line',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
          label: 'Views',
          data: <?= json_encode($chartValues) ?>,
          borderColor: '#F25B22',
          borderWidth: 3,
          backgroundColor: gradient,
          fill: true,
          tension: 0.35,
          pointBackgroundColor: '#F25B22',
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#050C17',
            titleFont: { family: 'Inter', weight: 'bold' },
            bodyFont: { family: 'Inter' },
            padding: 12,
            cornerRadius: 10
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { family: 'Inter', size: 11 }, color: '#94A3B8' }
          },
          y: {
            beginAtZero: true,
            grid: { color: '#F1F5F9' },
            ticks: { font: { family: 'Inter', size: 11 }, color: '#94A3B8', precision: 0 }
          }
        }
      }
    });
  }

  // 2. Device Doughnut Chart
  const ctxDevice = document.getElementById('deviceChart');
  if (ctxDevice) {
    new Chart(ctxDevice, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($deviceLabels) ?>,
        datasets: [{
          data: <?= json_encode($deviceValues) ?>,
          backgroundColor: ['#F25B22', '#0284C7', '#10B981', '#8B5CF6', '#F59E0B'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { display: false }
        }
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
