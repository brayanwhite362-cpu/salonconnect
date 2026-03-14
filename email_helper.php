<?php
function sendBookingConfirmation($email, $name, $bookingDetails) {
    $to = $email;
    $subject = "✅ Booking Confirmed - SalonConnect";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; background: #0b0b12; }
            .container { max-width: 600px; margin: 0 auto; background: #0b0b12; border: 1px solid rgba(255,255,255,.1); border-radius: 20px; padding: 30px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 32px; font-weight: 700; color: white; }
            .logo span { color: #c8a14a; }
            .content { color: #f5f4ff; }
            .details { background: rgba(255,255,255,.05); padding: 25px; border-radius: 15px; margin: 20px 0; }
            .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.1); }
            .detail-row:last-child { border-bottom: none; }
            .label { color: #b8b6c8; }
            .value { color: #c8a14a; font-weight: 600; }
            .button { display: inline-block; background: linear-gradient(90deg, #7b2cbf, #9d4edd); color: white; padding: 12px 30px; text-decoration: none; border-radius: 40px; margin: 20px 0; }
            .footer { text-align: center; color: #b8b6c8; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.1); }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>Salon<span>Connect</span></div>
                <h2 style='color: #c8a14a; margin-top: 10px;'>Booking Confirmed!</h2>
            </div>
            <div class='content'>
                <p>Hello <strong>$name</strong>,</p>
                <p>Your booking has been confirmed. We're looking forward to seeing you!</p>
                
                <div class='details'>
                    <div class='detail-row'>
                        <span class='label'>Salon:</span>
                        <span class='value'>{$bookingDetails['salon_name']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Service:</span>
                        <span class='value'>{$bookingDetails['service_name']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Date:</span>
                        <span class='value'>{$bookingDetails['date']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Time:</span>
                        <span class='value'>{$bookingDetails['time']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Duration:</span>
                        <span class='value'>{$bookingDetails['duration']} mins</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Price:</span>
                        <span class='value'>LKR {$bookingDetails['price']}</span>
                    </div>
                </div>
                
                <div style='text-align: center;'>
                    <a href='https://salonconnect.rf.gd/my_bookings.php' class='button'>View My Bookings</a>
                </div>
                
                <p style='margin-top: 20px;'>Need to make changes? You can manage your booking from your account.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing SalonConnect!</p>
                <p>&copy; 2026 SalonConnect. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: SalonConnect <bookings@salonconnect.com>\r\n";
    $headers .= "Reply-To: support@salonconnect.com\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function sendBookingReminder($email, $name, $bookingDetails) {
    $to = $email;
    $subject = "⏰ Reminder: Your Appointment Tomorrow - SalonConnect";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; background: #0b0b12; }
            .container { max-width: 600px; margin: 0 auto; background: #0b0b12; border: 1px solid rgba(255,255,255,.1); border-radius: 20px; padding: 30px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 32px; font-weight: 700; color: white; }
            .logo span { color: #c8a14a; }
            .content { color: #f5f4ff; }
            .details { background: rgba(255,255,255,.05); padding: 20px; border-radius: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #b8b6c8; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.1); }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>Salon<span>Connect</span></div>
                <h2 style='color: #c8a14a;'>Appointment Reminder</h2>
            </div>
            <div class='content'>
                <p>Hello <strong>$name</strong>,</p>
                <p>This is a friendly reminder that you have an appointment tomorrow:</p>
                
                <div class='details'>
                    <p><strong>Salon:</strong> {$bookingDetails['salon_name']}</p>
                    <p><strong>Service:</strong> {$bookingDetails['service_name']}</p>
                    <p><strong>Date:</strong> {$bookingDetails['date']}</p>
                    <p><strong>Time:</strong> {$bookingDetails['time']}</p>
                </div>
                
                <p>We look forward to seeing you!</p>
                <p>Need to reschedule? <a href='https://salonconnect.rf.gd/my_bookings.php' style='color: #c8a14a;'>Manage your booking</a></p>
            </div>
            <div class='footer'>
                <p>&copy; 2026 SalonConnect</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: SalonConnect <reminders@salonconnect.com>\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function sendAdminNotification($bookingDetails) {
    $to = "admin@salonconnect.com";
    $subject = "📢 New Booking - SalonConnect";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #0b0b12; }
            .container { max-width: 600px; margin: 0 auto; background: #0b0b12; border: 1px solid rgba(255,255,255,.1); border-radius: 20px; padding: 30px; }
            .header { text-align: center; margin-bottom: 20px; }
            .logo { font-size: 28px; font-weight: 700; color: white; }
            .logo span { color: #c8a14a; }
            .details { background: rgba(255,255,255,.05); padding: 20px; border-radius: 15px; margin: 20px 0; }
            .detail-row { padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.1); }
            .detail-row:last-child { border-bottom: none; }
            .label { color: #b8b6c8; font-weight: 600; }
            .value { color: #c8a14a; float: right; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>Salon<span>Connect</span></div>
                <h2 style='color: #c8a14a;'>📢 New Booking Received</h2>
            </div>
            <div class='details'>
                <div class='detail-row'>
                    <span class='label'>Customer:</span>
                    <span class='value'>{$bookingDetails['customer_name']}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Salon:</span>
                    <span class='value'>{$bookingDetails['salon_name']}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Service:</span>
                    <span class='value'>{$bookingDetails['service_name']}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Date:</span>
                    <span class='value'>{$bookingDetails['date']}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Time:</span>
                    <span class='value'>{$bookingDetails['time']}</span>
                </div>
                <div class='detail-row'>
                    <span class='label'>Booking ID:</span>
                    <span class='value'>#{$bookingDetails['booking_id']}</span>
                </div>
            </div>
            <div style='text-align: center; margin-top: 30px;'>
                <a href='https://salonconnect.rf.gd/admin/bookings.php' 
                   style='background: linear-gradient(90deg, #7b2cbf, #9d4edd); color: white; padding: 12px 30px; text-decoration: none; border-radius: 40px; display: inline-block;'>
                    View All Bookings
                </a>
            </div>
            <div class='footer' style='text-align: center; color: #b8b6c8; font-size: 12px; margin-top: 30px;'>
                <p>&copy; 2026 SalonConnect Admin</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: SalonConnect System <system@salonconnect.com>\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// NEW: Function to notify salon owner
function sendOwnerNotification($email, $ownerName, $bookingDetails) {
    $to = $email;
    $subject = "💰 New Booking for Your Salon - SalonConnect";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; background: #0b0b12; }
            .container { max-width: 600px; margin: 0 auto; background: #0b0b12; border: 1px solid rgba(255,255,255,.1); border-radius: 20px; padding: 30px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 32px; font-weight: 700; color: white; }
            .logo span { color: #c8a14a; }
            .content { color: #f5f4ff; }
            .details { background: rgba(255,255,255,.05); padding: 20px; border-radius: 15px; margin: 20px 0; }
            .booking-info { border-left: 3px solid #c8a14a; padding-left: 15px; margin: 10px 0; }
            .button { display: inline-block; background: linear-gradient(90deg, #7b2cbf, #9d4edd); color: white; padding: 12px 30px; text-decoration: none; border-radius: 40px; margin: 20px 0; }
            .footer { text-align: center; color: #b8b6c8; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.1); }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>Salon<span>Connect</span></div>
                <h2 style='color: #c8a14a;'>New Booking Received! 🎉</h2>
            </div>
            <div class='content'>
                <p>Hello <strong>$ownerName</strong>,</p>
                <p>Great news! You have a new booking for your salon:</p>
                
                <div class='details'>
                    <h3 style='color: #c8a14a; margin-top: 0;'>Booking Details:</h3>
                    <div class='booking-info'>
                        <p><strong>Customer:</strong> {$bookingDetails['customer_name']}</p>
                        <p><strong>Service:</strong> {$bookingDetails['service_name']}</p>
                        <p><strong>Date:</strong> {$bookingDetails['date']}</p>
                        <p><strong>Time:</strong> {$bookingDetails['time']}</p>
                        <p><strong>Duration:</strong> {$bookingDetails['duration']} mins</p>
                        <p><strong>Price:</strong> LKR {$bookingDetails['price']}</p>
                    </div>
                </div>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='https://salonconnect.rf.gd/owner/bookings.php' class='button'>
                        View All Bookings
                    </a>
                </div>
            </div>
            <div class='footer'>
                <p>&copy; 2026 SalonConnect</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: SalonConnect <notifications@salonconnect.com>\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>