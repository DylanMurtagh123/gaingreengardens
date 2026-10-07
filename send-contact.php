<?php
// -----------------------------------------------
// Gain Green Gardens - Contact Form Handler
// -----------------------------------------------

$recipient_email = "esdaille77@yahoo.com";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.html");
    exit;
}

// Honeypot anti-spam: real users leave 'website' empty; bots often fill every field
if (!empty($_POST['website'])) {
    // Pretend success so the bot moves on, but don't email
    header("Location: thank-you.html");
    exit;
}

// Helper function to clean normal text fields
function clean_input($value) {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

// Collect and sanitise inputs
$full_name     = isset($_POST['name'])    ? clean_input($_POST['name'])    : '';
$phone_number  = isset($_POST['phone'])   ? clean_input($_POST['phone'])   : '';
$email_address = isset($_POST['email'])   ? trim($_POST['email'])          : '';
$service       = isset($_POST['service']) ? clean_input($_POST['service']) : '';
$message_text  = isset($_POST['message']) ? clean_input($_POST['message']) : '';

// Prevent header injection
$email_address = str_replace(array("\r", "\n"), '', $email_address);

// Basic validation
$errors = [];

if (empty($full_name)) {
    $errors[] = "Full name is required.";
}

if (empty($phone_number)) {
    $errors[] = "Phone number is required.";
}

if (empty($email_address) || !filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email address is required.";
}

if (empty($service)) {
    $errors[] = "Please select a service.";
}

if (empty($message_text)) {
    $errors[] = "Please enter a message.";
}

// If validation fails, go back with errors
if (!empty($errors)) {
    $error_string = urlencode(implode(" | ", $errors));
    $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'contact.html';
    header("Location: " . $back . "?status=error&message=" . $error_string);
    exit;
}

// Build the email
$subject  = "New Gardening Enquiry from $full_name - Gain Green Gardens Website";

$message  = "You have received a new enquiry from the Gain Green Gardens website.\n\n";
$message .= "----------------------------------------\n";
$message .= "CONTACT DETAILS\n";
$message .= "----------------------------------------\n";
$message .= "Name:     $full_name\n";
$message .= "Phone:    $phone_number\n";
$message .= "Email:    $email_address\n\n";
$message .= "----------------------------------------\n";
$message .= "ENQUIRY DETAILS\n";
$message .= "----------------------------------------\n";
$message .= "Service:  $service\n\n";
$message .= "Message:\n$message_text\n\n";
$message .= "----------------------------------------\n";
$message .= "Sent: " . date('D, j M Y - H:i') . "\n";
$message .= "This email was sent from the contact form on the Gain Green Gardens website.\n";

// Email headers
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'gaingreengardens.co.uk';

$headers  = "From: noreply@" . $host . "\r\n";
$headers .= "Reply-To: " . $email_address . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send the email
$mail_sent = mail($recipient_email, $subject, $message, $headers);

// Redirect after sending
if ($mail_sent) {
    $qs = http_build_query(['name' => $full_name]);
    header("Location: thank-you.html?" . $qs);
} else {
    $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'contact.html';
    header("Location: " . $back . "?status=error&message=" . urlencode("Sorry, there was a problem sending your message. Please try calling us directly on 07835 897102."));
}

exit;
?>
