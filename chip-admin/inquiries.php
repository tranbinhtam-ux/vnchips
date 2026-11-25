<?php
/**
 * Admin Inquiries / Contact Requests Management
 * Enhanced with Status Filter Tabs, 1-Click CSV Export (Excel UTF-8), and Quick-View Modal.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

$db = Database::getInstance()->getConnection();
$message = '';

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $db) {
    $rows = $db->query("SELECT * FROM `inquiries` ORDER BY `created_at` DESC")->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=vnchips_inquiries_' . date('Ymd_His') . '.csv');
    
    $out = fopen('php://output', 'w');
    // Output UTF-8 BOM so Excel opens Vietnamese characters correctly
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($out, ['ID', 'Date Created', 'Full Name', 'Email', 'Phone', 'Company', 'Service Interest', 'Status', 'Message Content']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'],
            $r['created_at'],
            $r['fullname'],
            $r['email'],
            $r['phone'],
            $r['company'],
            $r['service_interest'],
            $r['status'],
            $r['message']
        ]);
    }
    fclose($out);
    exit;
}

// Handle Status Change via GET fallback
if (isset($_GET['action']) && $_GET['action'] === 'status' && !empty($_GET['id']) && !empty($_GET['status']) && $db) {
    $id = (int)$_GET['id'];
    $st = $_GET['status'];
    $stmt = $db->prepare("UPDATE `inquiries` SET `status` = :st WHERE `id` = :id");
    $stmt->execute([':st' => $st, ':id' => $id]);
    $message = 'Inquiry status updated to ' . e($st) . '.';
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id']) && $db) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("DELETE FROM `inquiries` WHERE `id` = :id");
    $stmt->execute([':id' => $id]);
    $message = 'Inquiry deleted successfully.';
}

$statusFilter = $_GET['status'] ?? 'all';

$allInquiries = [];
if ($db) {
    try {
        $allInquiries = $db->query("SELECT * FROM `inquiries` ORDER BY `created_at` DESC")->fetchAll();
    } catch (PDOException $e) {}
}

$inquiries = array_filter($allInquiries, function($inq) use ($statusFilter) {
    if ($statusFilter === 'all') return true;
    return $inq['status'] === $statusFilter;
});

// Stats
$totalCount = count($allInquiries);
$newCount = count(array_filter($allInquiries, fn($i) => $i['status'] === 'new'));
$inReviewCount = count(array_filter($allInquiries, fn($i) => $i['status'] === 'in_review'));
$respondedCount = count(array_filter($allInquiries, fn($i) => $i['status'] === 'responded'));

$adminTitle = 'Contact Inquiries & Customer Leads';
$adminActivePage = 'inquiries';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-inbox text-primary me-2"></i>Contact Inquiries & Customer Leads
      </h3>
      <p class="text-muted small mb-0">Manage incoming quotation requests, partnership inquiries, and consulting proposals</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('inquiries.php?export=csv') ?>" class="btn btn-outline-success">
        <i class="fa-solid fa-file-excel me-1"></i> Export to CSV / Excel
      </a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i><?= e($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Stats Bar -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <a href="<?= admin_url('inquiries.php') ?>" class="text-decoration-none">
        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Total Inquiries</div>
            <div class="fs-4 fw-bold text-dark"><?= $totalCount ?></div>
          </div>
          <div class="text-primary fs-3"><i class="fa-solid fa-envelopes-bulk"></i></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="<?= admin_url('inquiries.php?status=new') ?>" class="text-decoration-none">
        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">New Messages</div>
            <div class="fs-4 fw-bold text-danger"><?= $newCount ?></div>
          </div>
          <div class="text-danger fs-3"><i class="fa-solid fa-bell"></i></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="<?= admin_url('inquiries.php?status=in_review') ?>" class="text-decoration-none">
        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">In Review</div>
            <div class="fs-4 fw-bold text-warning"><?= $inReviewCount ?></div>
          </div>
          <div class="text-warning fs-3"><i class="fa-solid fa-clock-rotate-left"></i></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="<?= admin_url('inquiries.php?status=responded') ?>" class="text-decoration-none">
        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Responded</div>
            <div class="fs-4 fw-bold text-success"><?= $respondedCount ?></div>
          </div>
          <div class="text-success fs-3"><i class="fa-solid fa-check-double"></i></div>
        </div>
      </a>
    </div>
  </div>

  <!-- Filter Pills -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="btn-group btn-group-sm">
      <a href="<?= admin_url('inquiries.php') ?>" class="btn <?= ($statusFilter === 'all') ? 'btn-primary' : 'btn-outline-secondary' ?>">
        All (<?= $totalCount ?>)
      </a>
      <a href="<?= admin_url('inquiries.php?status=new') ?>" class="btn <?= ($statusFilter === 'new') ? 'btn-danger' : 'btn-outline-secondary' ?>">
        New (<?= $newCount ?>)
      </a>
      <a href="<?= admin_url('inquiries.php?status=in_review') ?>" class="btn <?= ($statusFilter === 'in_review') ? 'btn-warning' : 'btn-outline-secondary' ?>">
        In Review (<?= $inReviewCount ?>)
      </a>
      <a href="<?= admin_url('inquiries.php?status=responded') ?>" class="btn <?= ($statusFilter === 'responded') ? 'btn-success' : 'btn-outline-secondary' ?>">
        Responded (<?= $respondedCount ?>)
      </a>
    </div>

    <div class="small text-muted">
      Click on any row or the <i class="fa-solid fa-eye text-primary"></i> icon to view full message & reply.
    </div>
  </div>

  <!-- Inquiries Table -->
  <div class="table-responsive">
    <table class="admin-table align-middle">
      <thead>
        <tr>
          <th style="width: 110px;">Date</th>
          <th>Client & Company</th>
          <th>Contact Info</th>
          <th>Service Interest</th>
          <th>Message Snippet</th>
          <th style="width: 100px; text-align: center;">Status</th>
          <th style="text-align: right; width: 130px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($inquiries)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <i class="fa-regular fa-envelope-open fs-2 d-block mb-2"></i>
              No inquiries found in this status.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($inquiries as $inq): ?>
            <tr style="cursor: pointer;" class="inquiry-row" data-id="<?= $inq['id'] ?>"
                data-fullname="<?= e($inq['fullname']) ?>"
                data-email="<?= e($inq['email']) ?>"
                data-phone="<?= e($inq['phone'] ?: '') ?>"
                data-company="<?= e($inq['company'] ?: '') ?>"
                data-interest="<?= e($inq['service_interest'] ?? 'General') ?>"
                data-date="<?= formatDate($inq['created_at'], 'M d, Y H:i') ?>"
                data-status="<?= e($inq['status']) ?>"
                data-message="<?= e($inq['message']) ?>">
              <!-- Date -->
              <td style="font-size: 0.8rem; color: #64748B; white-space: nowrap;">
                <?= formatDate($inq['created_at'], 'M d, Y') ?>
                <div style="font-size: 0.72rem; color: #94A3B8;"><?= date('H:i', strtotime($inq['created_at'])) ?></div>
              </td>

              <!-- Name & Company -->
              <td>
                <div class="fw-bold text-dark"><?= e($inq['fullname']) ?></div>
                <?php if (!empty($inq['company'])): ?>
                  <div class="small text-muted"><i class="fa-solid fa-building me-1"></i><?= e($inq['company']) ?></div>
                <?php endif; ?>
              </td>

              <!-- Contact Info -->
              <td style="font-size: 0.85rem;">
                <div><a href="mailto:<?= e($inq['email']) ?>" class="text-decoration-none text-primary" onclick="event.stopPropagation();"><i class="fa-solid fa-envelope me-1"></i><?= e($inq['email']) ?></a></div>
                <?php if (!empty($inq['phone'])): ?>
                  <div class="small text-muted"><a href="tel:<?= e($inq['phone']) ?>" class="text-decoration-none text-muted" onclick="event.stopPropagation();"><i class="fa-solid fa-phone me-1"></i><?= e($inq['phone']) ?></a></div>
                <?php endif; ?>
              </td>

              <!-- Service Interest -->
              <td>
                <span class="badge badge-info" style="font-size: 0.78rem;">
                  <?= e($inq['service_interest'] ?? 'General') ?>
                </span>
              </td>

              <!-- Message -->
              <td>
                <div class="text-truncate" style="max-width: 280px; font-size: 0.85rem; color: #475569;">
                  <?= e($inq['message']) ?>
                </div>
              </td>

              <!-- Status -->
              <td style="text-align: center;">
                <?php if ($inq['status'] === 'new'): ?>
                  <span class="badge badge-danger">New</span>
                <?php elseif ($inq['status'] === 'in_review'): ?>
                  <span class="badge badge-warning">In Review</span>
                <?php else: ?>
                  <span class="badge badge-success">Responded</span>
                <?php endif; ?>
              </td>

              <!-- Actions -->
              <td style="text-align: right;" onclick="event.stopPropagation();">
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-outline-secondary btn-view-inquiry" title="Quick View">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                  <a href="<?= admin_url('inquiries.php?action=delete&id=' . $inq['id']) ?>" 
                     onclick="return confirm('Are you sure you want to delete inquiry from <?= addslashes($inq['fullname']) ?>?');" 
                     class="btn btn-outline-danger" title="Delete Inquiry">
                    <i class="fa-solid fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header border-0 pb-0 pt-4 px-4">
        <h5 class="modal-title fw-bold text-dark">
          <i class="fa-solid fa-envelope-open-text text-primary me-2"></i>Inquiry Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="p-3 rounded-3 bg-light">
              <div class="text-muted small">Sender Name</div>
              <div class="fw-bold fs-5 text-dark" id="modal-name">-</div>
              <div class="text-muted small mt-1" id="modal-company">-</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-3 bg-light">
              <div class="text-muted small">Service Interest</div>
              <div class="fw-bold fs-5 text-primary" id="modal-interest">-</div>
              <div class="text-muted small mt-1" id="modal-date">-</div>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="small fw-semibold text-muted mb-1">Email Address</div>
            <div>
              <a id="modal-email-link" href="#" class="btn btn-outline-primary btn-sm w-100 text-start">
                <i class="fa-solid fa-envelope me-2"></i><span id="modal-email">-</span>
              </a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="small fw-semibold text-muted mb-1">Phone Number</div>
            <div>
              <a id="modal-phone-link" href="#" class="btn btn-outline-secondary btn-sm w-100 text-start">
                <i class="fa-solid fa-phone me-2"></i><span id="modal-phone">-</span>
              </a>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <div class="small fw-semibold text-muted mb-2">Customer Message</div>
          <div class="p-3 rounded-3 border bg-white" id="modal-message" style="white-space: pre-wrap; font-size: 0.92rem; line-height: 1.6; max-height: 250px; overflow-y: auto;">
            -
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
          <div class="d-flex align-items-center gap-2">
            <label class="small fw-bold text-dark mb-0">Change Status:</label>
            <select id="modal-status-select" class="form-select form-select-sm" style="width: 150px;">
              <option value="new">New</option>
              <option value="in_review">In Review</option>
              <option value="responded">Responded</option>
            </select>
            <button type="button" id="btn-save-status" class="btn btn-sm btn-primary">Update</button>
          </div>

          <div class="d-flex gap-2">
            <a id="btn-modal-reply" href="#" class="btn btn-primary btn-sm">
              <i class="fa-solid fa-paper-plane me-1"></i> Send Reply Email
            </a>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('inquiryModal');
  const modal = new bootstrap.Modal(modalEl);
  let activeInquiryId = 0;

  function openInquiryModal(row) {
    activeInquiryId = row.getAttribute('data-id');
    const name = row.getAttribute('data-fullname');
    const email = row.getAttribute('data-email');
    const phone = row.getAttribute('data-phone') || 'N/A';
    const company = row.getAttribute('data-company') || 'N/A';
    const interest = row.getAttribute('data-interest');
    const date = row.getAttribute('data-date');
    const status = row.getAttribute('data-status');
    const message = row.getAttribute('data-message');

    document.getElementById('modal-name').textContent = name;
    document.getElementById('modal-company').textContent = 'Company: ' + company;
    document.getElementById('modal-interest').textContent = interest;
    document.getElementById('modal-date').textContent = 'Submitted: ' + date;
    document.getElementById('modal-email').textContent = email;
    document.getElementById('modal-email-link').href = 'mailto:' + encodeURIComponent(email);
    document.getElementById('modal-phone').textContent = phone;
    document.getElementById('modal-phone-link').href = phone !== 'N/A' ? 'tel:' + encodeURIComponent(phone) : '#';
    document.getElementById('modal-message').textContent = message;
    document.getElementById('modal-status-select').value = status;
    document.getElementById('btn-modal-reply').href = 'mailto:' + encodeURIComponent(email) + '?subject=' + encodeURIComponent('VNCHIP Response: ' + interest);

    modal.show();
  }

  // Row click opens modal
  document.querySelectorAll('.inquiry-row').forEach(row => {
    row.addEventListener('click', function() {
      openInquiryModal(this);
    });
  });

  // Action button click opens modal
  document.querySelectorAll('.btn-view-inquiry').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      openInquiryModal(this.closest('.inquiry-row'));
    });
  });

  // Save Status in Modal
  document.getElementById('btn-save-status').addEventListener('click', function() {
    if (!activeInquiryId) return;
    const newStatus = document.getElementById('modal-status-select').value;
    const btn = this;
    btn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'update_inquiry_status');
    formData.append('id', activeInquiryId);
    formData.append('status', newStatus);

    fetch('<?= admin_url("ajax/entity-actions.php") ?>', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      if (data.success) {
        showToast('Inquiry status updated to ' + newStatus, 'success');
        modal.hide();
        setTimeout(() => location.reload(), 400);
      } else {
        showToast('Failed to update status', 'error');
      }
    })
    .catch(err => {
      btn.disabled = false;
      showToast('Network error', 'error');
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
