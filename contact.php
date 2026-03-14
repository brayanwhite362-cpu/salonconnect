<?php
require_once "config/init.php";
require_once "config/db.php";

$showPopup = false;
$error = '';

// Your email where messages will be sent
$your_email = "brayan@celeste.lk";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = $_POST['subject'] ?? 'General Inquiry';
    $message_content = trim($_POST['message'] ?? '');
    
    // Simple validation
    if (empty($name) || empty($email) || empty($message_content)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        
        // ===== Save to database =====
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message_content);
        $stmt->execute();
        $stmt->close();
        
        // ===== Send email =====
        $to = $your_email;
        $email_subject = "Contact Form: " . $subject;
        
        $email_message = "
        <html>
        <head>
            <style>
                body { font-family: 'Inter', Arial, sans-serif; background: #0b0b12; }
                .email-container { max-width: 600px; margin: 0 auto; background: #1a1a2a; border: 1px solid rgba(200,161,74,0.3); border-radius: 20px; padding: 30px; }
                .header { text-align: center; margin-bottom: 30px; }
                .logo { font-size: 32px; font-weight: 700; color: white; }
                .logo span { color: #c8a14a; }
                .content { color: #f5f4ff; }
                .details { background: rgba(255,255,255,.05); padding: 25px; border-radius: 15px; margin: 20px 0; }
                .detail-row { padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.1); }
                .detail-row:last-child { border-bottom: none; }
                .label { color: #b8b6c8; font-weight: 600; }
                .value { color: #c8a14a; margin-left: 10px; }
                .message-box { background: rgba(255,255,255,.03); padding: 20px; border-radius: 12px; margin-top: 20px; border-left: 3px solid #c8a14a; }
                .footer { text-align: center; color: #b8b6c8; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.1); }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='logo'>Salon<span>Connect</span></div>
                    <h2 style='color: #c8a14a; margin-top: 10px;'>New Contact Form Submission</h2>
                </div>
                <div class='content'>
                    <div class='details'>
                        <div class='detail-row'><span class='label'>Name:</span> <span class='value'>" . htmlspecialchars($name) . "</span></div>
                        <div class='detail-row'><span class='label'>Email:</span> <span class='value'>" . htmlspecialchars($email) . "</span></div>
                        " . ($phone ? "<div class='detail-row'><span class='label'>Phone:</span> <span class='value'>" . htmlspecialchars($phone) . "</span></div>" : "") . "
                        <div class='detail-row'><span class='label'>Subject:</span> <span class='value'>" . htmlspecialchars($subject) . "</span></div>
                    </div>
                    <div class='message-box'>
                        <p style='margin:0 0 10px 0; color:#b8b6c8;'><strong>Message:</strong></p>
                        <p style='margin:0; color:white; line-height:1.6;'>" . nl2br(htmlspecialchars($message_content)) . "</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>This message was sent from your SalonConnect contact form.</p>
                    <p>&copy; " . date('Y') . " SalonConnect</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: " . $name . " <" . $email . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        
        mail($to, $email_subject, $email_message, $headers);
        
        // Show success popup
        $showPopup = true;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
  <title>Contact Us | SalonConnect</title>
  
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <!-- Navbar CSS -->
  <link rel="stylesheet" href="assets/css/navbar.css">
  
  <style>
    :root {
      --bg: #0b0b12;
      --text: #ffffff;
      --muted: #b8b6c8;
      --accent: #7b2cbf;
      --accent-light: #9d4edd;
      --gold: #c8a14a;
      --border: rgba(255,255,255,0.08);
      --card-bg: rgba(255,255,255,0.02);
    }
    
    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
    }
    
    /* ===== PREMIUM TYPOGRAPHY ===== */
    .display-heading {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      letter-spacing: -0.02em;
    }
    
    .gradient-text {
      background: linear-gradient(135deg, var(--gold), var(--accent-light), var(--gold));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-size: 200% 200%;
      animation: gradientShift 8s ease infinite;
    }
    
    /* ===== RICH ANIMATIONS ===== */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes gentlePulse {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.1); opacity: 0.5; }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* ===== WEDDING PAGE STYLE HERO (with slowZoom animation) ===== */
    .contact-hero {
      position: relative;
      min-height: 500px;
      border-radius: 40px;
      overflow: hidden;
      margin: 30px 0 50px;
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
      animation: fadeInUp 1s ease;
    }
    
    .contact-hero-image {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.25;
      mix-blend-mode: overlay;
      animation: slowZoom 20s ease-in-out infinite;
    }
    
    @keyframes slowZoom {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    .contact-hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, rgba(11,11,18,0.8) 0%, rgba(11,11,18,0.4) 100%);
    }
    
    .contact-hero-content {
      position: relative;
      z-index: 2;
      padding: 80px 40px;
      text-align: center;
    }
    
    .hero-pill {
      display: inline-block;
      padding: 8px 20px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 40px;
      color: white;
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 1px;
      margin-bottom: 25px;
      backdrop-filter: blur(5px);
      animation: fadeInUp 0.8s ease;
    }
    
    .contact-hero-title {
      font-size: 64px;
      font-weight: 800;
      margin-bottom: 20px;
      text-shadow: 0 10px 30px rgba(0,0,0,0.5);
      background: linear-gradient(135deg, #ffffff, #c8a14a);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: fadeInUp 0.8s ease 0.2s both;
    }
    
    .contact-hero-subtitle {
      font-size: 20px;
      color: rgba(255,255,255,0.9);
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
      animation: fadeInUp 0.8s ease 0.4s both;
    }
    
    /* ===== SUCCESS POPUP ===== */
    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      backdrop-filter: blur(5px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    
    .popup-overlay.show {
      opacity: 1;
      visibility: visible;
    }
    
    .popup-card {
      background: #1a1a2a;
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 30px;
      padding: 40px;
      max-width: 400px;
      text-align: center;
      transform: scale(0.9);
      transition: all 0.3s ease;
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
      animation: fadeInUp 0.5s ease;
    }
    
    .popup-overlay.show .popup-card {
      transform: scale(1);
    }
    
    .popup-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      animation: gentlePulse 2s infinite;
    }
    
    .popup-icon .material-symbols-rounded {
      font-size: 40px;
      color: white;
    }
    
    .popup-title {
      font-size: 24px;
      font-weight: 700;
      color: #c8a14a;
      margin-bottom: 10px;
    }
    
    .popup-message {
      color: #b8b6c8;
      margin-bottom: 25px;
      line-height: 1.6;
    }
    
    .popup-button {
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 40px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
    }
    
    .popup-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(123,44,191,.3);
    }
    
    /* ===== ERROR MESSAGE ===== */
    .error-message {
      background: rgba(220,53,69,.1);
      border: 1px solid rgba(220,53,69,.3);
      color: #dc3545;
      padding: 15px 20px;
      border-radius: 16px;
      margin-bottom: 20px;
      animation: fadeInLeft 0.5s ease;
    }
    
    /* ===== CONTACT GRID ===== */
    .contact-wrapper {
      margin: 60px 0;
    }
    
    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1.5fr;
      gap: 40px;
    }
    
    /* ===== INFO CARDS ===== */
    .info-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 30px;
      padding: 35px;
      margin-bottom: 25px;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      animation: fadeInLeft 0.6s ease backwards;
      position: relative;
      overflow: hidden;
    }
    
    .info-card:nth-child(1) { animation-delay: 0.1s; }
    .info-card:nth-child(2) { animation-delay: 0.2s; }
    .info-card:nth-child(3) { animation-delay: 0.3s; }
    .info-card:nth-child(4) { animation-delay: 0.4s; }
    
    .info-card:hover {
      transform: translateY(-10px);
      border-color: rgba(200,161,74,.5);
      box-shadow: 0 30px 40px -20px rgba(0,0,0,0.5);
    }
    
    .info-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #7b2cbf, #c8a14a);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }
    
    .info-card:hover::before {
      transform: translateX(100%);
    }
    
    .info-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.2));
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    
    .info-card:hover .info-icon {
      transform: scale(1.1) rotate(5deg);
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
    }
    
    .info-card:hover .info-icon .material-symbols-rounded {
      color: white;
    }
    
    .info-icon .material-symbols-rounded {
      color: var(--gold);
      font-size: 28px;
      transition: all 0.3s ease;
    }
    
    .info-title {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 15px;
      transition: color 0.3s ease;
    }
    
    .info-card:hover .info-title {
      color: var(--gold);
    }
    
    .info-detail {
      color: var(--muted);
      line-height: 1.8;
      margin-bottom: 8px;
      transition: color 0.3s ease;
      font-size: 15px;
    }
    
    .info-card:hover .info-detail {
      color: #d0d0e0;
    }
    
    .info-detail a {
      color: var(--gold);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .info-detail a:hover {
      text-decoration: underline;
      color: white;
    }
    
    /* ===== SOCIAL LINKS ===== */
    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 25px;
    }
    
    .social-link {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #b8b6c8;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    
    .social-link i {
      font-size: 20px;
      transition: all 0.3s ease;
    }
    
    .social-link:hover {
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
      transform: translateY(-5px);
    }
    
    .social-link:hover i {
      color: white;
    }
    
    /* ===== CONTACT FORM ===== */
    .contact-form {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 40px;
      padding: 45px;
      animation: fadeInRight 0.8s ease;
      position: relative;
      overflow: hidden;
    }
    
    .contact-form::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(123,44,191,0.05) 0%, transparent 70%);
      animation: gradientShift 15s ease infinite;
      pointer-events: none;
    }
    
    .form-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 30px;
      position: relative;
    }
    
    .form-group {
      margin-bottom: 25px;
      animation: fadeInUp 0.5s ease backwards;
      position: relative;
      z-index: 2;
    }
    
    .form-group:nth-child(1) { animation-delay: 0.1s; }
    .form-group:nth-child(2) { animation-delay: 0.2s; }
    .form-group:nth-child(3) { animation-delay: 0.3s; }
    .form-group:nth-child(4) { animation-delay: 0.4s; }
    .form-group:nth-child(5) { animation-delay: 0.5s; }
    
    .form-label {
      display: block;
      margin-bottom: 8px;
      color: var(--muted);
      font-size: 14px;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    
    .form-control {
      width: 100%;
      padding: 16px 20px;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 16px;
      color: white;
      font-size: 15px;
      transition: all 0.3s ease;
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--gold);
      background: rgba(255,255,255,0.05);
      transform: translateY(-2px);
      box-shadow: 0 10px 20px -10px rgba(200,161,74,0.3);
    }
    
    .form-control::placeholder {
      color: #666;
      opacity: 1;
    }
    
    select.form-control {
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23c8a14a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
      background-repeat: no-repeat;
      background-position: right 20px center;
      background-size: 16px;
      color: white;
    }
    
    select.form-control option {
      background: #1a1a2a;
      color: white;
    }
    
    textarea.form-control {
      resize: vertical;
      min-height: 140px;
    }
    
    .btn-submit {
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      border: none;
      color: white;
      padding: 18px 30px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.5s ease 0.6s both;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 15px 30px -10px rgba(123,44,191,0.4);
    }
    
    .btn-submit::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255,255,255,0.3), transparent);
      transform: translate(-50%, -50%);
      transition: width 0.6s ease, height 0.6s ease;
    }
    
    .btn-submit:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .btn-submit:hover {
      transform: translateY(-3px);
      box-shadow: 0 20px 40px -10px rgba(123,44,191,0.5);
    }
    
    /* ===== MAP SECTION ===== */
    .map-wrapper {
      margin: 60px 0;
    }
    
    .map-section {
      border-radius: 40px;
      overflow: hidden;
      border: 1px solid rgba(200,161,74,0.2);
      height: 450px;
      animation: fadeInUp 0.8s ease;
      transition: all 0.3s ease;
    }
    
    .map-section:hover {
      border-color: rgba(200,161,74,0.5);
      box-shadow: 0 30px 50px -20px rgba(0,0,0,0.5);
    }
    
    .map-section iframe {
      width: 100%;
      height: 100%;
      transition: transform 0.5s ease;
    }
    
    .map-section:hover iframe {
      transform: scale(1.02);
    }
    
    /* ===== FAQ SECTION ===== */
    .faq-wrapper {
      margin: 60px 0 40px;
    }
    
    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }
    
    .section-title {
      font-size: 42px;
      font-weight: 700;
      margin-bottom: 15px;
      animation: fadeInLeft 0.8s ease;
    }
    
    .section-subtitle {
      color: var(--muted);
      font-size: 18px;
      max-width: 600px;
      margin: 0 auto;
      animation: fadeInRight 0.8s ease;
    }
    
    .faq-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 25px;
    }
    
    .faq-item {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 30px;
      transition: all 0.4s ease;
      animation: fadeInUp 0.6s ease backwards;
      position: relative;
      overflow: hidden;
    }
    
    .faq-item:nth-child(1) { animation-delay: 0.1s; }
    .faq-item:nth-child(2) { animation-delay: 0.2s; }
    .faq-item:nth-child(3) { animation-delay: 0.3s; }
    .faq-item:nth-child(4) { animation-delay: 0.4s; }
    
    .faq-item:hover {
      transform: translateY(-8px);
      border-color: rgba(200,161,74,.5);
      box-shadow: 0 20px 30px -15px rgba(0,0,0,0.5);
    }
    
    .faq-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #7b2cbf, #c8a14a);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }
    
    .faq-item:hover::before {
      transform: translateX(100%);
    }
    
    .faq-question {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 12px;
      color: var(--gold);
      transition: color 0.3s ease;
    }
    
    .faq-item:hover .faq-question {
      color: white;
    }
    
    .faq-answer {
      color: var(--muted);
      font-size: 15px;
      line-height: 1.7;
      transition: color 0.3s ease;
    }
    
    .faq-item:hover .faq-answer {
      color: #d0d0e0;
    }
    
    /* ===== CTA SECTION ===== */
    .cta-wrapper {
      margin: 60px 0;
    }
    
    .cta-section {
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.1));
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 50px;
      padding: 60px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .cta-section h2 {
      font-size: 42px;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    .cta-section p {
      color: var(--muted);
      font-size: 18px;
      max-width: 600px;
      margin: 0 auto 30px;
    }
    
    .btn-cta {
      background: linear-gradient(90deg, var(--accent), var(--gold));
      color: white;
      border: none;
      padding: 16px 40px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 15px 30px rgba(123,44,191,0.3);
      text-decoration: none;
    }
    
    .btn-cta:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(123,44,191,0.4);
    }
    
    /* ===== MOBILE RESPONSIVE ===== */
    @media screen and (max-width: 1100px) {
        .contact-grid {
            gap: 30px;
        }
        
        .faq-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media screen and (max-width: 768px) {
        .container {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }
        
        .contact-hero {
            min-height: auto;
            margin: 15px 0 20px;
        }
        
        .contact-hero-content {
            padding: 40px 20px;
        }
        
        .contact-hero-title {
            font-size: 42px;
        }
        
        .contact-hero-subtitle {
            font-size: 16px;
        }
        
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .info-card {
            padding: 25px;
        }
        
        .contact-form {
            padding: 30px 25px;
        }
        
        .form-title {
            font-size: 24px;
        }
        
        .map-section {
            height: 300px;
        }
        
        .section-title {
            font-size: 32px;
        }
        
        .cta-section {
            padding: 40px 20px;
        }
        
        .cta-section h2 {
            font-size: 28px;
        }
    }
    
    @media screen and (max-width: 480px) {
        .contact-hero-title {
            font-size: 32px;
        }
        
        .hero-pill {
            font-size: 11px;
        }
        
        .info-card {
            padding: 20px;
        }
        
        .contact-form {
            padding: 25px 20px;
        }
        
        .form-title {
            font-size: 22px;
        }
        
        .map-section {
            height: 250px;
        }
        
        .cta-section h2 {
            font-size: 24px;
        }
    }
    
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }
  </style>
</head>
<body>

<?php include "includes/navbar.php"; ?>

<!-- Success Popup -->
<div class="popup-overlay" id="successPopup">
  <div class="popup-card">
    <div class="popup-icon">
      <span class="material-symbols-rounded">check_circle</span>
    </div>
    <h2 class="popup-title">Message Sent!</h2>
    <p class="popup-message">Thank you for contacting us. We'll get back to you within 24 hours.</p>
    <button class="popup-button" onclick="closePopup()">OK</button>
  </div>
</div>

<main>
  
  <!-- ===== HERO SECTION (with slowZoom animation from Wedding page) ===== -->
  <section class="contact-hero">
    <img class="contact-hero-image" src="https://images.pexels.com/photos/1813272/pexels-photo-1813272.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Contact">
    <div class="contact-hero-overlay"></div>
    <div class="contact-hero-content">
      <span class="hero-pill">📞 LET'S CONNECT</span>
      <h1 class="contact-hero-title display-heading">
        Get in <span class="gradient-text">Touch</span>
      </h1>
      <p class="contact-hero-subtitle">
        We'd love to hear from you. Reach out anytime our team is here to help.
      </p>
    </div>
  </section>

  <!-- Error Message -->
  <?php if ($error): ?>
    <div class="container">
      <div class="error-message"><?= $error ?></div>
    </div>
  <?php endif; ?>

  <!-- ===== CONTACT GRID ===== -->
  <div class="container">
    <div class="contact-wrapper">
      <div class="contact-grid">
        <!-- Left Column - Info -->
        <div>
          <div class="info-card">
            <div class="info-icon">
              <span class="material-symbols-rounded">location_on</span>
            </div>
            <h3 class="info-title">Visit Us</h3>
            <p class="info-detail">No. 123, Galle Road</p>
            <p class="info-detail">Colombo 03, Sri Lanka</p>
          </div>
          
          <div class="info-card">
            <div class="info-icon">
              <span class="material-symbols-rounded">call</span>
            </div>
            <h3 class="info-title">Call Us</h3>
            <p class="info-detail">
              <a href="tel:+94773086768">+94 77 308 6768</a>
            </p>
            <p class="info-detail">Mon - Fri, 9:00 AM - 8:00 PM</p>
          </div>
          
          <div class="info-card">
            <div class="info-icon">
              <span class="material-symbols-rounded">mail</span>
            </div>
            <h3 class="info-title">Email Us</h3>
            <p class="info-detail">
              <a href="mailto:support@salonconnect.lk">support@salonconnect.lk</a>
            </p>
            <p class="info-detail">
              <a href="mailto:info@salonconnect.lk">info@salonconnect.lk</a>
            </p>
          </div>
          
          <div class="info-card">
            <h3 class="info-title">Follow Us</h3>
            <div class="social-links">
              <a href="https://facebook.com/salonconnect" target="_blank" class="social-link" title="Facebook">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="https://instagram.com/salonconnect" target="_blank" class="social-link" title="Instagram">
                <i class="fab fa-instagram"></i>
              </a>
              <a href="https://linkedin.com/company/salonconnect" target="_blank" class="social-link" title="LinkedIn">
                <i class="fab fa-linkedin-in"></i>
              </a>
              <a href="https://twitter.com/salonconnect" target="_blank" class="social-link" title="Twitter">
                <i class="fab fa-twitter"></i>
              </a>
            </div>
          </div>
        </div>
        
        <!-- Right Column - Form -->
        <div>
          <div class="contact-form">
            <h2 class="form-title">Send us a message</h2>
            
            <form method="post" id="contactForm">
              <div class="form-group">
                <label class="form-label">Your Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label class="form-label">Phone Number (Optional)</label>
                <input type="tel" name="phone" class="form-control" placeholder="Enter your phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              </div>
              
              <div class="form-group">
                <label class="form-label">Subject</label>
                <select name="subject" class="form-control">
                  <option value="General Inquiry" <?= (($_POST['subject'] ?? '') == 'General Inquiry') ? 'selected' : '' ?>>General Inquiry</option>
                  <option value="Booking Question" <?= (($_POST['subject'] ?? '') == 'Booking Question') ? 'selected' : '' ?>>Booking Question</option>
                  <option value="Partner with us" <?= (($_POST['subject'] ?? '') == 'Partner with us') ? 'selected' : '' ?>>Partner with us</option>
                  <option value="Technical Support" <?= (($_POST['subject'] ?? '') == 'Technical Support') ? 'selected' : '' ?>>Technical Support</option>
                  <option value="Other" <?= (($_POST['subject'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" placeholder="Write your message here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>
              
              <button type="submit" class="btn-submit" id="submitBtn">
                <span class="material-symbols-rounded">send</span>
                Send Message
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MAP SECTION ===== -->
  <div class="container">
    <div class="map-wrapper">
      <div class="map-section">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126743.5853225555!2d79.848028!3d6.9270786!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25bcfd6c8b7d3%3A0xb9f1d5c0f0e1e1e!2sColombo%2C%20Sri%20Lanka!5e0!3m2!1sen!2s!4v1620000000000!5m2!1sen!2s" allowfullscreen="" loading="lazy"></iframe>
      </div>
    </div>
  </div>

  <!-- ===== FAQ SECTION ===== -->
  <div class="container">
    <div class="faq-wrapper">
      <div class="section-header">
        <h2 class="section-title display-heading">Frequently Asked <span class="gradient-text">Questions</span></h2>
        <p class="section-subtitle">Quick answers to common questions</p>
      </div>
      
      <div class="faq-grid">
        <div class="faq-item">
          <div class="faq-question">How do I book an appointment?</div>
          <div class="faq-answer">Simply browse our salons, select your preferred service, choose a date and time, and confirm your booking. It's that easy!</div>
        </div>
        
        <div class="faq-item">
          <div class="faq-question">Can I cancel or reschedule?</div>
          <div class="faq-answer">Yes! You can manage your bookings from your profile dashboard. Cancellations are free up to 24 hours before appointment.</div>
        </div>
        
        <div class="faq-item">
          <div class="faq-question">How do I become a partner salon?</div>
          <div class="faq-answer">Contact our partnerships team via the form above. We'd love to discuss how we can work together.</div>
        </div>
        
        <div class="faq-item">
          <div class="faq-question">Is my payment information secure?</div>
          <div class="faq-answer">Absolutely! We use industry-standard encryption and never store your payment details on our servers.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== CTA SECTION ===== -->
  <div class="container">
    <div class="cta-wrapper">
      <div class="cta-section">
        <h2 class="display-heading">Ready to experience <span class="gradient-text">luxury?</span></h2>
        <p>Browse our premium salons and book your appointment today.</p>
        <a href="featured-salons.php" class="btn-cta">
          <span class="material-symbols-rounded">search</span>
          Explore Salons
        </a>
      </div>
    </div>
  </div>

</main>

<?php include "includes/footer.php"; ?>

<script>
// Show popup if message was sent successfully
<?php if ($showPopup): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('successPopup').classList.add('show');
});
<?php endif; ?>

// Close popup function
function closePopup() {
    document.getElementById('successPopup').classList.remove('show');
}

// Add loading animation to form submission
document.getElementById('contactForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').classList.add('loading');
});

// Close popup when clicking outside
document.getElementById('successPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closePopup();
    }
});
</script>
</body>
</html>