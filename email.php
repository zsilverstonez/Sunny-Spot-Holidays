<?php
session_start();
include '../database_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $email = $_POST["email"] ?? "";

    // Connect to database and prepare statement to check staffID
    $accountCheck = $connect->prepare("SELECT staffID FROM account WHERE username=? AND email=?");
    $accountCheck->bind_param("ss", $username, $email);
    $accountCheck->execute();
    $result = $accountCheck->get_result();
    // Show message if username and email not exist
    if ($result->num_rows === 0) {
        $message = "Username or Email is not valid";
    } else {
        $row = $result->fetch_assoc();
        $staffID = $row["staffID"];
        // Send reset code to email
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", time() + 300); // Set 5 mins expiration
        // Delete old token if existing
        $deleteToken = $connect->prepare("DELETE FROM password_resets WHERE staffID=?");
        $deleteToken->bind_param("i", $staffID);
        $deleteToken->execute();
        $deleteToken->close();
        // Insert to database
        $insertToken = $connect->prepare("INSERT INTO password_resets (staffID, token, expiry) VALUES (?,?,?)");
        $insertToken->bind_param("iss", $staffID, $token, $expiry);
        $insertToken->execute();
        $insertToken->close();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['DB_USER'];
            $mail->Password   = $_ENV['DB_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom($_ENV['FROM_EMAIL'], 'Sunny Spot Holidays');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Code';
            $mail->Body = "
<p>Dear $username,</p>

<p>We received a request to reset your password. Please click the link below to reset your password:</p>

<p>
    <a href='url-to-the-reset-password-folder?token=$token' 
       style='color: #007bff; text-decoration: none; font-weight: bold;'>
       Reset Your Password
    </a>
</p>

<p>This link will expire in <strong>5 minutes</strong>.</p>

<p>If you did not request this change, you can safely ignore this email.</p>

<p>Kind regards,<br>
Sunny Spot Holidays Support Team</p>
";

            $mail->send();
            $message = "Password reset code sent to your email.<br>Please check your email.";
        } catch (Exception $e) {
            $message = "Mailer Error. Please try again.";
             error_log("PHPMailer Error: " . $mail->ErrorInfo);
        }
    }
    $accountCheck->close();
}
// Close connection to database
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays - Log In</title>
    <link href="styles.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Arima:wght@100..700&family=Dancing+Script:wght@400..700&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Arima:wght@100..700&family=Dancing+Script:wght@400..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <style>
    .main-admin {
        padding-bottom: 4rem;
        padding-top: 2rem;
    }

    article {
        width: 100%;
        margin-top: 4rem;
        max-width: 550px;
        padding: 1rem;
    }

    .main-admin h2 {
        display: flex;
        justify-content: center;
        margin-top: 0;
        margin-bottom: -0.2rem;
    }

    fieldset {
        display: flex;
        width: 100%;
        flex-direction: column;
        justify-content: center;
        gap: 0.2rem;
        border: none;
    }

    input,
    button {
        padding: 0.5rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        margin: 0.25rem;
    }

    button {
        background-color: rgba(219, 103, 8, 0.842);
        color: white;
        cursor: pointer;
    }

    button:hover {
        background-color: rgba(219, 103, 8, 1);
    }

    .error {
        text-align: center;
        font-style: italic;
        padding: 0;
        margin: 0;
        margin-top: 0.5rem;
        color: rgba(219, 103, 8, 0.842);
    }

    a.back {
        text-align: center;
        text-decoration: none;
        width: 100%;
        max-width: 150px;
        display: block;
        margin: 0 auto;
        background-color: rgba(219, 103, 8, 0.842);
        border-radius: 10px;
        padding: 0.5rem 1rem;
        color: white;
        cursor: pointer;
        margin-top: 2rem;
        margin-bottom: 0.5rem;
    }

    a:hover {
        background-color: rgba(219, 103, 8, 1);
    }
    </style>
</head>

<body>
    <header>
        <div class="header-divider">
            <a href="index.php"><img src="images/sun.gif" alt="Sunny-logo" class="sunny-logo"></a>
            <div class="title-divider">
                <a href="index.php" class="title">
                    <h1>Sunny Spot Holidays</h1>
                </a>
                <h3>This is a mock website only!</h3>
            </div>
        </div>
    </header>
    <main class="main-admin">
        <article>
            <h2>Reset Password</h2>
            <?php if (!empty($message)): ?>
            <p class="error"><?php echo $message; ?></p>
            <?php endif; ?>
            <?php
            $formHide = "";
            $linkLogIn = "style='display:none;'";
            $successMessage = "A password reset link has been sent to your email.";
            if (!empty($message) && strpos($message, $successMessage) !== false) {
                $formHide = "style='display:none;'";
                $linkLogIn = "";
            }
            ?>
            <form method="post" action="" <?php echo $formHide; ?>>
                <fieldset class="login">
                    <input type="text" name="username" id="username" placeholder="Enter Username" required>
                    <input type="text" name="email" id="email" placeholder="Enter Email" required>
                    <button type="submit">Submit</button>
                </fieldset>
            </form>
            <a class="back" href="login.php" <?php echo $linkLogIn; ?>>Back to Log in</a>
        </article>
    </main>
    <footer>
        <p>
            <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                50 Melaleuca Cres, Tascott NSW 2250
            </a>
        </p>
        <p>© 2025 Copyright Sunny Spot Holidays</p>
        <li id="login"><a href="login.php">Admin</a></li>
        <img src="images/author.png" alt="author" class="author">
    </footer>
    <script src="script.js"></script>
</body>

</html>
