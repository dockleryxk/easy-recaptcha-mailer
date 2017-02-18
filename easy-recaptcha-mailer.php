<?php
/**
 * @author Richard Jeffords <rajeffords@gmail.com>
 *
 * It's simple -- verify the recaptcha server-side (here we are)
 * and then send an email of the contact form contents
 *
 * The POST request needs five fields:
 ****** name
 ***** email
 **** message
 *** subject
 ** g-recaptcha-response
 */

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /*****************************************
     ****** Add your information here ********
     *****************************************/
      $recaptchaSecret = '';
      $email_address = '';
     /****************************************
      ****************************************/

    header('Access-Control-Allow-Origin: *');
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
            'secret' => $recaptchaSecret,
            'response' => $_POST["g-recaptcha-response"],
            'remoteip' => $_SERVER["REMOTE_ADDR"]
            );

    // Use key 'http' even if you send the request to https://
    $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
                )
            );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // PHP sees JSON as a string
    $result = get_object_vars(json_decode($result));

    // If the API call fails or the key is bad
    if ($result === FALSE || !$result['success']) { 
        http_response_code(500);
        echo 'Recaptcha server error, try again later or email me directly at '. $email_address;
        exit;
    }

    // Get the form fields and remove whitespace
    $name = strip_tags(trim($_POST["name"]));
    $name = str_replace(array("\r","\n"),array(" "," "),$name);
    $subject = strip_tags(trim($_POST["subject"]));
    $subject = str_replace(array("\r","\n"),array(" "," "),$subject);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"]);

    // Check that data was sent to the mailer
    if (empty($name) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Set a 400 (bad request) response code and exit
        http_response_code(400);
        echo "There was a problem with your submission. Please complete the form and try again.";
        exit;
    }

    // Concatenate the email content
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Message:\n$message\n";

    // Build the email headers
    $email_headers = "From: Easy Recaptcha Mailers <erm@example.com>" . "\r\n" .
        'Reply-To: ' . $email . "\r\n";

    // Send the email
    if (mail($email_address, $subject, $email_content, $email_headers)) {
        // Set a 200 (okay) response code.
        http_response_code(200);
        echo "Success! Your message has been sent.";
    }
    else {
        // Set a 500 (internal server error) response code
        http_response_code(500);
        echo "Something went wrong with the mail server and I couldn't send your message. Try again later or email me directly at " . $email_address;
    }

}
else {
    // Not a POST request, set a 403 (forbidden) response code
    http_response_code(403);
    echo "There is either something terribly wrong, or you are up to no good.";
}
