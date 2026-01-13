<?php
session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$receivedMessage = '';
$receivedMessageErr = '';
include 'database_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $receivedMessageErr = "Security validation failed. Please try again.";
    } else {
        $name = trim($_POST['contact-name'] ?? '');
        $phone = trim($_POST['contact-phone'] ?? '');
        $email = trim($_POST['contact-email'] ?? '');
        $message = trim($_POST['contact-message'] ?? '');
        $status = 'new';

        if (!$name || !$phone || !$email || !$message) {
            $receivedMessageErr = 'There is an error in submitting your message.<br>Please check and resubmit again.';
        } else {
            // Check if connection exists
            if (!$connect) {
                $receivedMessageErr = 'Database connection failed: ' . mysqli_connect_error();
            } else {
                $contactTable = $connect->prepare("INSERT INTO contact (name, phone, email, message, status) VALUES (?,?,?,?,?)");

                if ($contactTable === false) {
                    $receivedMessageErr = 'Prepare failed: ' . $connect->error;
                } else {
                    $contactTable->bind_param("sssss", $name, $phone, $email, $message, $status);

                    if ($contactTable->execute()) {
                        $receivedMessage = 'Thank you for contacting us! We will get back to you as soon as possible.';
                    } else {
                        $receivedMessageErr = 'Error submitting message: ' . $contactTable->error;
                    }

                    $contactTable->close();

                    // Mail Notification
                    $headers = "From: noreply@your-domain-name\r\n";
                    $headers .= "Reply-To: noreply@your-domain-name\r\n";
                    $safeName = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
                    $safeEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
                    $safePhone = filter_var($phone, FILTER_SANITIZE_SPECIAL_CHARS);
                    $safeMessage = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);
                    mail(
                        'your-admin-email',
                        'New Contact - Sunny Spot Holidays',
                        'A new contact has been received at Sunny Spot Holidays! 

Guest: ' . $safeName . '
Phone: ' . $safePhone . '
Email: ' . $safeEmail . '
Message: ' . $safeMessage . '

Please log in to the admin dashboard to view full details and manage this contact.

This is an automated notification from sunnyspotholidays.com.au',
                        $headers
                    );
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Welcome to Suny Spot Holidays. a selection of cozy cabins and a spacious camping and caravan area in Tascott, NSW.">
    <meta name="keywords" content="cabin, accommodation, Tascott, caravan, holiday, contact">
    <title>Sunny Spot Holidays - Contact</title>

    <link href="styles.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Arima:wght@100..700&family=Dancing+Script:wght@400..700&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Arima:wght@100..700&family=Dancing+Script:wght@400..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <style>
        .main-contact {
            margin-top: 3rem;
            background-image: url(images/background.jpg);
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            position: relative;
            flex: 1;
        }

        main::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
        }

        #contact-form {
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            width: 100%;
            max-width: 1000px;
            padding: 1rem 3rem;
            background-color: white;
            margin: 2rem 2rem 2rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 15;
            ;
        }

        .contact-header-divider {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
        }

        .contact-icon {
            width: 60px;
            height: 60px;
        }

        iframe {
            margin-top: 1rem;
            margin-bottom: 4rem;
        }

        fieldset {
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            max-width: 900px;
            border: none;
            margin-top: -3rem;
        }

        .address {
            position: relative;
        }

        .address h4 {
            margin-bottom: 0.5rem;
        }

        .address img {
            width: 100px;
            height: 80px;
            position: absolute;
            top: -10px;
            right: -10px;
            animation: swing 1s ease-in-out;
        }

        @keyframes swing {
            0% {
                transform: rotateY(0deg);
            }

            20% {
                transform: rotateY(40deg);
            }

            40% {
                transform: rotateY(-25deg);
            }

            60% {
                transform: rotateY(20deg);
            }

            80% {
                transform: rotateY(-10deg);
            }

            100% {
                transform: rotateY(0deg);
            }
        }

        input,
        textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            margin: 0.5rem 0.25rem;
        }

        .send-us {
            width: 100px;
            border: none;
            border-radius: 6px;
            padding: 0.5rem;
            margin: auto;
            margin-top: 0.5rem;
            background-color: rgba(219, 103, 8, 0.842);
            color: white;
            cursor: pointer;
            font-family: roboto, sans-serif;
            font-weight: bold;
        }

        .send-us:hover {
            background-color: rgba(219, 103, 8, 1);
        }

        h4 {
            margin-bottom: -1rem;
            text-align: center;
            font-size: 1.3rem;
        }

        .address a {
            cursor: pointer;
            text-decoration: underline;
            color: rgba(219, 103, 8, 0.842)
        }

        .address a:hover {
            color: rgba(219, 103, 8, 1);
        }

        #contact-received-message {
            color: green;
            text-align: center;
            margin-top: 2rem;
        }

        #contact-received-message-err {
            color: red;
            text-align: center;
            margin-bottom: -1rem;
        }

        @media (max-width: 920px) {
            #contact-form {
                max-width: 800px;
                margin: 0 1rem 0 1rem;
                padding: 1rem;
            }

            iframe {
                width: 600px;
            }
        }

        @media (max-width: 768px) {
            .main-contact {
                margin-top: 4rem;
            }

            #contact-form {
                max-width: 650px;
                margin-top: 1rem;
                margin-bottom: 2rem;
            }

            fieldset {
                margin: -3rem 1rem 0.5rem 1rem;
                max-width: 600px;
            }

            h2 {
                font-size: 1.5rem;
            }

            .contact-icon {
                width: 35px;
                height: 35px;
            }

            h4 {
                font-size: 1.2rem;
            }

            p {
                font-size: 1rem;
            }

            iframe {
                width: 500px;
            }

        }

        @media (max-width: 568px) {
            .main-contact {
                margin-top: 4.2rem;
            }

            #contact-form {
                max-width: 500px;
            }

            fieldset {
                margin: -3rem 0 -1rem 0;
                max-width: 500px;
            }

            fieldset input {
                margin-top: 0.3rem;
                margin-bottom: 0.3rem;
            }

            h2 {
                font-size: 1.3rem;
            }

            h4 {
                font-size: 1rem;
            }

            p {
                font-size: 1rem;
            }


            iframe {
                width: 320px;
            }
        }
    </style>
    <script src="script.js" defer></script>
    <!-- Google tag (gtag.js) for sunnyspotholidays.com.au only -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-HH5R04T2BW"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-HH5R04T2BW', {
            'cookie_domain': 'sunnyspotholidays.com.au'
        });
    </script>
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
        <nav>
            <ul>
                <li class="home"><a href="index.php">Home</a></li>
                <li class="information"><a href="information.php">Guest Information</a></li>
                <li class="attractions"><a href="attractions.php">Attractions</a></li>
                <li class="foodAndDrink"><a href="foodAndDrink.php">Food & Drink</a></li>
                <li class="contact"><a href="contact.php" class="active">Contact Us</a></li>
            </ul>
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main class="main-contact">
        <form id="contact-form" action="contact" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="contact-header-divider">
                <img src="images/contact.gif" alt="contact-icon" class="contact-icon">
                <h2 class="contact-header">Contact Us</h2>
            </div>
            <fieldset class="contact">
                <p id="contact-received-message"><?php echo $receivedMessage; ?></p>
                <p id="contact-received-message-err"><?php echo $receivedMessageErr; ?></p>
                <input type="text" name="contact-name" id="contact-name" placeholder="Full Name" required />
                <input type="text" name="contact-phone" id="contact-phone" placeholder="Phone Number"
                    pattern="[0-9]{9,11}" required
                    oninvalid="this.setCustomValidity('Please enter a valid phone number')"
                    oninput="this.setCustomValidity('')" />
                <input type="email" name="contact-email" id="contact-email" placeholder="Email"
                    pattern="[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+" required
                    oninvalid="this.setCustomValidity('Please enter a valid email')"
                    oninput="this.setCustomValidity('')" />
                <textarea name="contact-message" id="contact-message" placeholder="Please leave your message"
                    required></textarea>
                <button type="submit" class="send-us" id="contactButton">Send Us</button>
            </fieldset>
            <div class="address">
                <h4>Our Address</h4>
                <img src="images/not-real.png" alt="not real logo">
                <p>50 Melaleuca Cres, Tascott NSW 2250<br>Phone: (02) 0000 0000<br>Email:
                    <a>admin@sunnyspotholidays.com.au</a>
                </p>
            </div>
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.084625552608!2d151.3209689!3d-33.4471017!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b0d4ac98a90c11b%3A0x8c27fe67499a642f!2s50%20Melaleuca%20Cres%2C%20Tascott%20NSW%202250!5e0!3m2!1sen!2sau!4v1757977007669!5m2!1sen!2sau"
                width="880" height="300" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </form>
    </main>
    <footer>
        <p>
            <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                50 Melaleuca Cres, Tascott NSW 2250
            </a>
        </p>
        <div class="social-media">
            <img src="images/facebook-icon.png" alt="icon-facebook">
            <img src="images/instagram-icon.png" alt="icon-instagram">
            <img src="images/twitter-icon.png" alt="icon-twitter" id="twitter">
        </div>
        <p>© 2025 Copyright Sunny Spot Holidays</p>
        <li id="login"><a href="login.php">Admin</a></li>
        <img src="images/author.png" alt="author" class="author">
    </footer>

</body>


</html>
