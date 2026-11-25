<?php
/**
 * Talk To Us / Contact Page
 * Matching Page 6 of VNCHIP Design Specification
 */

require_once __DIR__ . '/config/config.php';

$successMessage = '';
$errorMessage = '';

// Handle Form Submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $fullname = trim($firstName . ' ' . $lastName);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($firstName) || empty($email) || empty($message)) {
        $errorMessage = 'Please fill in all required fields (First Name, Email, and Message).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please provide a valid email address.';
    } else {
        $db = Database::getInstance()->getConnection();
        if ($db) {
            try {
                $serviceInterest = !empty($country) ? "Country: $country" : "General Inquiry";
                $stmt = $db->prepare("INSERT INTO `inquiries` (`fullname`, `email`, `phone`, `company`, `service_interest`, `message`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullname, $email, $phone, $company, $serviceInterest, $message]);
                $successMessage = 'Thank you for reaching out! Our semiconductor engineering team has received your inquiry and will respond promptly.';
            } catch (PDOException $e) {
                $errorMessage = 'Failed to submit your inquiry. Please try again later or email us directly.';
            }
        } else {
            $successMessage = 'Thank you! Your message has been received.';
        }
    }
}

$pageTitle = 'Talk To Us | Connect with IC Design Experts | VNCHIP Technology JSC';
$pageDescription = 'Connect with VNCHIP semiconductor experts. Global offices in Ho Chi Minh City, Vietnam and Austin, TX, USA for turnkey IC design and research.';
$activePage = 'contact';

$contactTitle = Setting::getOr('contact_page_title', 'CONNECT WITH EXPERTS');
$contactOfficesTitle = Setting::getOr('contact_offices_title', 'VNCHIP OFFICES');
$contactOfficesDesc = Setting::getOr('contact_offices_desc', 'Our semiconductor experts are ready to assist you with technical inquiries and project collaborations globally.');
$addressVn = Setting::getOr('site_address_vn', SITE_ADDRESS_VN);
$addressUs = Setting::getOr('site_address_us', SITE_ADDRESS_US);
$phone = Setting::getOr('site_phone', SITE_PHONE);
$email = Setting::getOr('site_email', SITE_EMAIL);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Full Immersion Contact Section matching Page 6 of PDF -->
<section class="contact-page-section" id="live-contact-section">
  <div class="contact-page-bg"></div>
  <div class="container contact-page-content">
    
    <!-- Title -->
    <h1 class="contact-page-title" id="live-contact-title"><?= e($contactTitle) ?></h1>

    <div class="contact-main-grid" id="live-contact-grid">
      
      <!-- Left Column: VNCHIP Offices & Contact Details -->
      <div class="contact-left-col">
        <h2 class="contact-offices-title" id="live-contact-offices-title"><?= e($contactOfficesTitle) ?></h2>
        <p class="contact-offices-desc" id="live-contact-offices-desc">
          <?= e($contactOfficesDesc) ?>
        </p>

        <div class="contact-items-list">
          
          <!-- Vietnam Office -->
          <div class="contact-item-row">
            <div class="contact-icon-box">
              <i class="fa-solid fa-location-dot"></i>
            </div>
            <div>
              <div class="contact-item-label">VIETNAM OFFICE</div>
              <div class="contact-item-val" id="live-contact-address-vn"><?= nl2br(e($addressVn)) ?></div>
            </div>
          </div>

          <!-- USA Office -->
          <div class="contact-item-row">
            <div class="contact-icon-box">
              <i class="fa-solid fa-location-dot"></i>
            </div>
            <div>
              <div class="contact-item-label">USA OFFICE</div>
              <div class="contact-item-val" id="live-contact-address-us"><?= nl2br(e($addressUs)) ?></div>
            </div>
          </div>

          <!-- Phone -->
          <div class="contact-item-row">
            <div class="contact-icon-box">
              <i class="fa-solid fa-phone"></i>
            </div>
            <div>
              <div class="contact-item-label">PHONE</div>
              <div class="contact-item-val" id="live-contact-phone">
                <a href="tel:<?= str_replace([' ', '-', '.'], '', e($phone)) ?>"><?= e($phone) ?></a>
              </div>
            </div>
          </div>

          <!-- Email -->
          <div class="contact-item-row">
            <div class="contact-icon-box">
              <i class="fa-solid fa-envelope"></i>
            </div>
            <div>
              <div class="contact-item-label">EMAIL</div>
              <div class="contact-item-val" id="live-contact-email">
                <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Right Column: Interactive Floating Inquiry Form -->
      <div class="contact-right-col">
        <div class="contact-card-box" id="contact-form">
          
          <?php if ($successMessage): ?>
            <div style="background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; font-size: 0.92rem;">
              <i class="fa-solid fa-circle-check" style="color: #10B981; margin-right: 6px;"></i> <?= e($successMessage) ?>
            </div>
          <?php endif; ?>

          <?php if ($errorMessage): ?>
            <div style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; font-size: 0.92rem;">
              <i class="fa-solid fa-circle-exclamation" style="color: #EF4444; margin-right: 6px;"></i> <?= e($errorMessage) ?>
            </div>
          <?php endif; ?>

          <form action="<?= url('talk-to-us.php') ?>" method="POST" id="contactInquiryForm">
            
            <div class="contact-form-grid">
              
              <!-- First Name -->
              <div class="contact-input-wrap">
                <label class="contact-input-label" for="first_name">First Name*</label>
                <input type="text" id="first_name" name="first_name" required class="contact-input" placeholder="">
              </div>

              <!-- Last Name -->
              <div class="contact-input-wrap">
                <label class="contact-input-label" for="last_name">Last Name*</label>
                <input type="text" id="last_name" name="last_name" required class="contact-input" placeholder="">
              </div>

              <!-- Contact Number -->
              <div class="contact-input-wrap">
                <label class="contact-input-label" for="phone">Contact Number</label>
                <input type="tel" id="phone" name="phone" class="contact-input" placeholder="">
              </div>

              <!-- Email -->
              <div class="contact-input-wrap">
                <label class="contact-input-label" for="email">Email*</label>
                <input type="email" id="email" name="email" required class="contact-input" placeholder="">
              </div>

              <!-- Company Name -->
              <div class="contact-input-wrap">
                <label class="contact-input-label" for="company">Company Name*</label>
                <input type="text" id="company" name="company" required class="contact-input" placeholder="">
              </div>

              <!-- Country -->
              <div class="contact-input-wrap">
                <label class="contact-input-label" for="country">Country*</label>
                <input type="text" id="country" name="country" required class="contact-input" placeholder="Select your country">
              </div>

              <!-- Message -->
              <div class="contact-input-wrap contact-form-full">
                <label class="contact-input-label" for="message">Message*</label>
                <textarea id="message" name="message" rows="3" required class="contact-textarea" placeholder=""></textarea>
              </div>

            </div>

            <button type="submit" class="contact-submit-btn">
              Submit Inquiry
            </button>

          </form>

        </div>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
