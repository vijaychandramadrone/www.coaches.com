<?php
phpinfo();

return;

echo "Sending mail...";
$err = "";
if (send_mail($err)) {
    echo "mail sent successfully!";
} else {
    echo "mail failed: $err";
}
echo "...done...";

function send_mail(&$err) {
    require_once "Mail.php";
$host = "smtp7.modelsmith.com";
$port = "2225";
$username = "msmithclient";
$password = "3eDSw2";
$from = "<engage@coactive.com>";
$to = "jeremy@jeremystover.com";
$subject = "test email";
$body="this is a test from $host";

    $headers = array (
        'From' => $from,
        'To' => $to,
        'Reply-To' => $to,
        'Subject' => $subject,
        'MIME-Version' => "1.0",
        'Content-type' => "text/html; charset=iso-8859-1"
    );

    $smtp = Mail::factory('smtp',
        array ('host' => $host,
            'port' => $port,
            'auth' => false,
            'username' => $username,
            'password' => $password
        )
    );

    $mail = $smtp->send($to, $headers, $body);

    if (PEAR::isError($mail)) {
        $err = $mail->getMessage();
        return false;
    } else {
        return true;
    }
}

?>