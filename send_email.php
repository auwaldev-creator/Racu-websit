<?php
header('Content-Type: application/json');

// Set your email address here
$to_email = "ahmadibrahimjalingo@gmail.com";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $outlet_name = filter_var($_POST['outlet_name'], FILTER_SANITIZE_STRING);
    $surname = filter_var($_POST['surname'], FILTER_SANITIZE_STRING);
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $other_name = filter_var($_POST['other_name'], FILTER_SANITIZE_STRING);
    $nin_number = filter_var($_POST['nin_number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $qualification = filter_var($_POST['qualification'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $state = filter_var($_POST['state'], FILTER_SANITIZE_STRING);
    $lga = filter_var($_POST['lga'], FILTER_SANITIZE_STRING);
    $longitude = filter_var($_POST['longitude'], FILTER_SANITIZE_STRING);
    $latitude = filter_var($_POST['latitude'], FILTER_SANITIZE_STRING);
    $device_tag_id = filter_var($_POST['device_tag_id'], FILTER_SANITIZE_STRING);
    
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
    
    // Create email headers
    $headers = "From: Racu International <noreply@racuinternational.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Handle file attachments
    $attachments = [];
    
    // Process photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_name = $_FILES['photo']['name'];
        $attachments[] = ['tmp_name' => $photo_tmp, 'name' => $photo_name];
    }
    
    // Process signature upload
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] == UPLOAD_ERR_OK) {
        $signature_tmp = $_FILES['signature']['tmp_name'];
        $signature_name = $_FILES['signature']['name'];
        $attachments[] = ['tmp_name' => $signature_tmp, 'name' => $signature_name];
    }
    
    // If there are attachments, we need to use a different approach
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
                $file_size = filesize($attachment['tmp_name']);
                
                // Skip files larger than 2MB
                if ($file_size > 2 * 1024 * 1024) {
                    continue;
                }
                
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
            echo json_encode(['success' => false, 'message' => 'Server error: Unable to send email']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
