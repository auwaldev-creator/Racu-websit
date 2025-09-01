<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Set your email address here
$to_email = "ahmadibrahimjalingo@gmail.com";

// Increase script timeout for file uploads
set_time_limit(120);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 1 for debugging, 0 for production
ini_set('log_errors', 1);

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $outlet_name = sanitize_input($_POST['outlet_name']);
    $surname = sanitize_input($_POST['surname']);
    $first_name = sanitize_input($_POST['first_name']);
    $other_name = sanitize_input($_POST['other_name']);
    $nin_number = sanitize_input($_POST['nin_number']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $qualification = sanitize_input($_POST['qualification']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $state = sanitize_input($_POST['state']);
    $lga = sanitize_input($_POST['lga']);
    $longitude = sanitize_input($_POST['longitude']);
    $latitude = sanitize_input($_POST['latitude']);
    $device_tag_id = sanitize_input($_POST['device_tag_id']);
    
    // Validate required fields
    if (empty($outlet_name) || empty($surname) || empty($first_name) || empty($nin_number) || empty($email) || empty($phone) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Create email subject
    $subject = "MTN Username Creation Request from $first_name $surname";
    
    // Create email content
    $email_content = "MTN USERNAME CREATION REQUEST\n";
    $email_content .= "==============================\n\n";
    $email_content .= "Outlet Name: $outlet_name\n";
    $email_content .= "Surname: $surname\n";
    $email_content .= "First Name: $first_name\n";
    $email_content .= "Other Name: $other_name\n";
    $email_content .= "NIN Number: $nin_number\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Qualification: $qualification\n";
    $email_content .= "Phone Number: $phone\n";
    $email_content .= "Address: $address\n";
    $email_content .= "State: $state\n";
    $email_content .= "LGA: $lga\n";
    $email_content .= "Longitude: $longitude\n";
    $email_content .= "Latitude: $latitude\n";
    $email_content .= "Device Tag ID: $device_tag_id\n";
    $email_content .= "Submission Date: " . date('Y-m-d H:i:s') . "\n";
    $email_content .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    // Create email headers
    $headers = "From: Racu International <noreply@racuinternational.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Handle file attachments
    $attachments = [];
    $max_file_size = 2 * 1024 * 1024; // 2MB
    
    // Process photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['photo']['size'] <= $max_file_size) {
            $photo_tmp = $_FILES['photo']['tmp_name'];
            $photo_name = $_FILES['photo']['name'];
            $attachments[] = ['tmp_name' => $photo_tmp, 'name' => $photo_name];
        }
    }
    
    // Process signature upload
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['signature']['size'] <= $max_file_size) {
            $signature_tmp = $_FILES['signature']['tmp_name'];
            $signature_name = $_FILES['signature']['name'];
            $attachments[] = ['tmp_name' => $signature_tmp, 'name' => $signature_name];
        }
    }
    
    // If there are attachments, use multipart email
    if (!empty($attachments)) {
        // Boundary for multipart email
        $boundary = md5(time());
        $headers = "From: Racu International <noreply@racuinternational.com>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        
        // Email body
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($email_content));
        
        // Add attachments
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['tmp_name'])) {
                $file_content = file_get_contents($attachment['tmp_name']);
                $file_encoded = chunk_split(base64_encode($file_content));
                
                $body .= "--$boundary\r\n";
                $body .= "Content-Type: application/octet-stream; name=\"" . $attachment['name'] . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"" . $attachment['name'] . "\"\r\n\r\n";
                $body .= $file_encoded . "\r\n";
            }
        }
        
        $body .= "--$boundary--";
        
        // Send email with attachments
        if (mail($to_email, $subject, $body, $headers)) {
            echo json_encode(['success' => true, 'message' => 'Application submitted with attachments']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Server error: Unable to send email with attachments']);
        }
    } else {
        // Send email without attachments
        if (mail($to_email, $subject, $email_content, $headers)) {
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Server error: Unable to send email. Please check your server mail configuration.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Please use POST.']);
}
?>
