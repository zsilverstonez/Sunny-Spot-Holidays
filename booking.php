<?php
session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include connection to database
include 'database_connect.php';

$messageErr = "";
// Declare variables and get data from session
$arrival = $_SESSION['arrival'] ?? '';
$departure = $_SESSION['departure'] ?? '';
$cabin_type = $_SESSION['cabin_type'] ?? '';
$number_of_guest = $_SESSION['number_of_guest'] ?? '';
$first_name = $last_name = $phone = $email = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $messageErr = "Security validation failed. Please try again.";
    } else {
        // Get input values for all booking variables
        $arrival = $_POST['arrival'] ?? '';
        $departure = $_POST['departure'] ?? '';
        $cabin_type = $_POST['cabin_type'] ?? '';
        $number_of_guest = $_POST['number_of_guest'] ?? '';
        $first_name = trim($_POST['booking-first-name'] ?? '');
        $last_name = trim($_POST['booking-last-name'] ?? '');
        $phone = trim($_POST['booking-phone'] ?? '');
        $email = trim($_POST['booking-email'] ?? '');
        $message = trim($_POST['booking-message'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $messageErr = "Invalid email format";
        } else {
            // Save inputs values to session
            $_SESSION['arrival'] = $arrival;
            $_SESSION['departure'] = $departure;
            $_SESSION['cabin_type'] = $cabin_type;
            $_SESSION['number_of_guest'] = $number_of_guest;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['phone'] = $phone;
            $_SESSION['email'] = $email;
            $_SESSION['message'] = $message;

            if (isset($_POST['cabin_type']) && isset($_POST['arrival']) && isset($_POST['departure']) && isset($_POST['booking-first-name']) && isset($_POST['booking-last-name']) && isset($_POST['booking-phone']) && isset($_POST['number_of_guest']) && isset($_POST['booking-email'])) {
                $bookingTable = $connect->prepare("INSERT INTO booking (first_name, last_name, arrival, departure, cabin_type, phone, email, number_of_guest, message, booking_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $bookingTable->bind_param("sssssssis", $first_name, $last_name, $arrival, $departure, $cabin_type, $phone, $email, $number_of_guest, $message);
                $bookingTable->execute();
                $bookingID = $connect->insert_id;
                $bookingTable->close();

                // Mail Notification
                $headers = "From: noreply@sunnyspotholidays.com.au\r\n";
                $headers .= "Reply-To: noreply@sunnyspotholidays.com.au\r\n";

                $safeName = filter_var($first_name . ' ' . $last_name, FILTER_SANITIZE_SPECIAL_CHARS);
                $safeArrival = filter_var(date('d/m/Y', strtotime($arrival)), FILTER_SANITIZE_SPECIAL_CHARS);
                $safeDeparture = filter_var(date('d/m/Y', strtotime($departure)), FILTER_SANITIZE_SPECIAL_CHARS);
                $safeCabinType = filter_var($cabin_type, FILTER_SANITIZE_SPECIAL_CHARS);
                $safeNumberOfGuest = filter_var($number_of_guest, FILTER_SANITIZE_NUMBER_INT);
                $safeMessage = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);
                mail(
                    'zsilverstonez@gmail.com',
                    'New Booking - Sunny Spot Holidays',
                    'A new booking has been received at Sunny Spot Holidays! 

Guest: ' . $safeName . '
Check-in: ' . $safeArrival . '
Check-out: ' . $safeDeparture . '
Cabin Type: ' . $safeCabinType . '
Number of Guest: ' . $safeNumberOfGuest . '
Message: ' . (empty($safeMessage) ? 'None' : $safeMessage) . '

Please log in to the admin dashboard to view full details and manage this booking.

This is an automated notification from sunnyspotholidays.com.au',
                    $headers
                );
                header("Location: confirmed_booking.php");
                exit;
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
    <title>Sunny Spot Holidays - Booking</title>
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
        main {
            margin-top: 4rem;
            background-image: url(images/background.jpg);
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        main::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .booked {
            width: 100%;
            max-width: 900px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0 1rem;
            margin-bottom: 2rem;
        }

        .booked p {
            font-size: 1rem;
        }

        .booked input,
        .booked select {
            padding: 0 auto;
            width: 100%;
            color: black;
        }

        .booked input {
            font-family: Arial, sans-serif;
        }

        .booked select {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            font-family: 'Roboto', sans-serif;
        }

        #booking-form {
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            width: 100%;
            max-width: 1100px;
            background-color: white;
            margin: 2rem auto 2.5rem auto;
            padding-bottom: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 15;
        }

        .booked-divider {
            display: flex;
            width: 100%;
            gap: 3rem;
            margin-top: -0.5rem;
        }

        .booking-divider {
            width: 100%;
            display: flex;
            flex-direction: column;
        }


        fieldset {
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            max-width: 900px;
            border: none;
            gap: 0.1rem;
            margin-top: -1rem;
        }

        h3 {
            margin-bottom: 0.5rem;
        }

        input,
        textarea {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            margin: 0.25rem 0.25rem;
        }


        #booking-button {
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: rgba(219, 103, 8, 0.842);
            color: white;
            padding: 0.5rem;
            margin: auto;
            margin-top: 0.5rem;
            width: 100px;
            cursor: pointer;
            font-family: roboto, sans-serif;
            font-weight: bold;
        }

        #booking-button:hover {
            background-color: rgba(219, 103, 8, 1);
        }

        .message {
            font-family: roboto, sans-serif;
            color: rgba(219, 103, 8, 1);
            font-weight: bold;
        }

        .confirm-booking {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 100;
            justify-content: center;
            align-items: center;
            background: rgba(0, 0, 0, 0.5);
        }

        .confirm-booking-content {
            width: 90%;
            max-width: 900px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: white;
            border-radius: 10px;
            position: relative;
            margin: 0 2rem;
            padding: 2rem;
        }

        .confirm-booking-content h2 {
            margin-bottom: 0.5rem;
            color: rgba(219, 103, 8, 1);
        }

        .confirm-booking-content p {
            justify-content: flex-start;
            line-height: 1.6rem;
            padding: 0;
            margin: 0;
        }

        #confirm-booking p {
            margin: 0.2rem 0;
        }

        #confirm-booking-button,
        #cancel-booking-button {
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: rgba(219, 103, 8, 1);
            color: white;
            padding: 0.5rem;
            margin: auto;
            margin-top: 0.5rem;
            width: 100px;
            cursor: pointer;
            font-family: roboto, sans-serif;
            font-weight: bold;
        }

        #cancel-booking-button {
            background-color: rgba(14, 13, 13, 0.82);
        }

        #cancel-booking-button:hover {
            background-color: rgba(14, 13, 13, 1);
        }

        #confirm-booking-button:hover {
            background-color: rgba(238, 112, 8, 1);
        }

        @media (max-width: 768px) {

            .title-divider {
                margin-top: 0.5rem;
            }

        }

        @media (max-width: 568px) {
            .confirm-booking-content p {
                font-size: 0.9rem;
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
                <li class="home"><a href="index.php" class="active">Home</a></li>
                <li class="information"><a href="information.php">Guest Information</a></li>
                <li class="attractions"><a href="attractions.php">Attractions</a></li>
                <li class="foodAndDrink"><a href="foodAndDrink.php">Food & Drink</a></li>
                <li class="contact"><a href="contact.php">Contact Us</a></li>
            </ul>
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main>
        <form id="booking-form" action="" method="post" class="booking">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <h2 class="booking-header">Booking Details</h2>
            <div class="booked">
                <h3>Your Booking:</h3>
                <div class="booked-divider">
                    <div class="booking-divider">
                        <p>Arrival:</p>
                        <input type="date" id="arrival" name="arrival" aria-label="Arrival"
                            min="<?php echo date("Y-m-d") ?>" value="<?php echo htmlspecialchars($arrival); ?>"
                            style="margin-top: -0.2px" required>
                        <p>Departure:</p>
                        <input type="date" id="departure" name="departure" aria-label="Departure"
                            value="<?php echo htmlspecialchars($departure); ?>" style="margin-top: -1.2px" required>
                    </div>
                    <div class="booking-divider">
                        <p>Cabin Type:</p>
                        <select id="cabin_type" class="cabin_type" name="cabin_type" aria-label="Cabin Type">
                            <option value="Standard cabin sleeps 4"
                                <?php echo ($cabin_type == "Standard cabin sleeps 4") ? "selected" : "" ?>>Standard
                                Cabin
                                sleeps 4
                            </option>
                            <option value="Standard open plan cabin sleeps 4"
                                <?php echo ($cabin_type == "Standard open plan cabin sleeps 4") ? "selected" : "" ?>>
                                Standard
                                Open Plan
                                Cabin sleeps 4</option>
                            <option value="Deluxe cabin sleeps 4"
                                <?php echo ($cabin_type == "Deluxe cabin sleeps 4") ? "selected" : "" ?>>
                                Deluxe Cabin sleeps 4
                            </option>
                            <option value="Villa sleeps 4"
                                <?php echo ($cabin_type == "Villa sleeps 4") ? "selected" : "" ?>>Villa sleeps 4
                            </option>
                            <option value="Spa villa sleeps 4"
                                <?php echo ($cabin_type == "Spa villa sleeps 4") ? "selected" : "" ?>>Spa
                                Villa sleeps 4</option>
                            <option value="Slab Powered Site"
                                <?php echo ($cabin_type == "Slab Powered Site") ? "selected" : "" ?>>Slab Powered Site
                            </option>
                        </select>
                        <p>Number of Guest:</p>
                        <select id="number_of_guest" class="number_of_guest" name="number_of_guest"
                            aria-label="Guest Number">
                            <option value="1" <?php echo ($number_of_guest == "1") ? "selected" : "" ?>>1
                                Person
                            </option>
                            <option value="2" <?php echo ($number_of_guest == "2") ? "selected" : "" ?>>2
                                People
                            </option>
                            <option value="3" <?php echo ($number_of_guest == "3") ? "selected" : "" ?>>3
                                People
                            </option>
                            <option value="4" <?php echo ($number_of_guest == "4") ? "selected" : "" ?>>4
                                People
                            </option>
                            <option value="5" <?php echo ($number_of_guest == "5") ? "selected" : "" ?>>5
                                People
                            </option>
                            <option value="6" <?php echo ($number_of_guest == "6") ? "selected" : "" ?>>6
                                People
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <fieldset class="personal-details">
                <h3>Your Contact Details:</h3>
                <input type="text" name="booking-first-name" id="booking-first-name" placeholder="First Name"
                    required />
                <input type="text" name="booking-last-name" id="booking-last-name" placeholder="Last Name" required />
                <input type="tel" name="booking-phone" id="booking-phone" placeholder="Phone Number" pattern="\d{9,11}"
                    required oninvalid="this.setCustomValidity('Please enter a valid phone number')"
                    oninput="this.setCustomValidity('')" />
                <input type="email" name="booking-email" id="booking-email" placeholder="Email" required
                    oninvalid="this.setCustomValidity('Please enter a valid email')"
                    oninput="this.setCustomValidity('')" />
                <textarea name="booking-message" id="booking-message" placeholder="Please leave any message"></textarea>
                <button type="button" id="booking-button">Book Now</button>
            </fieldset>
        </form>
        <div class="confirm-booking">
            <?php if (!empty($messageErr)): ?>
                <p class="messageErr"><?php echo htmlspecialchars($messageErr); ?></p>
            <?php endif; ?>
            <div class="confirm-booking-content">
                <h2>Confirm Your Booking</h2>
                <p class="booked-cabin"></p>
                <p class="booked-arrival"></p>
                <p class="booked-departure"></p>
                <p class="booked-number-of-guest"></p>
                <p class="booked-name"></p>
                <p class="booked-phone"></p>
                <p class="booked-email"></p>
                <p class="booked-message"></p>
                <button type="submit" id="confirm-booking-button">Confirm</button>
                <button type="button" id="cancel-booking-button">Cancel</button>
            </div>
        </div>
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
