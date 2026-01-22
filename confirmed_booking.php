<?php
session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include connection to database
include 'database_connect.php';

// Get booked cabin type from session
$cabinType = $_SESSION['cabinType'] ?? '';

//Declare empty variable
$photo = NULL;

// Connect to cabin table to get photos
if (!empty($cabinType)) {
    $photoStatement = $connect->prepare("SELECT photo FROM cabin WHERE cabinType LIKE CONCAT('%', ?, '%')");
    // Declare variable type
    $photoStatement->bind_param("s", $cabinType);
    // Execute getting photo data
    $photoStatement->execute();
    $result = $photoStatement->get_result();
    // use if in case there is no cabin in database
    if ($row = $result->fetch_assoc()) {
        $photo = $row['photo'];
    }
    // Close getting photo data
    $photoStatement->close();
}

// Declare variables and get data from session
$booking = [
    'arrival' => $_SESSION['arrival'] ?? '',
    'departure' => $_SESSION['departure'] ?? '',
    'cabinType' => $_SESSION['cabin_type'] ?? '',
    'numberOfGuest' => $_SESSION['number_of_guest'] ?? '',
    'firstName' => $_SESSION['firstName'] ?? '',
    'lastName' => $_SESSION['lastName'] ?? '',
    'phone' => $_SESSION['phone'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'message' => $_SESSION['message'] ?? ''
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
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
        position: relative;
        flex: 1;
    }

    main::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.35);
    }

    .confirm-booking-content {
        width: 900px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: white;
        border-radius: 10px;
        position: relative;
        margin: 2rem;
        padding: 2rem;
    }

    .confirm-booking-content h2 {
        margin-bottom: -5px;
        color: rgba(219, 103, 8, 1);
    }

    .thank-message {
        color: rgba(219, 103, 8, 1);
        font-style: italic;
    }

    .booked-email {
        margin-bottom: 1rem;
    }

    .booked-photo img {
        width: 500px;
        height: 300px;
        border-radius: 10px;
        margin: 1rem 0;
    }

    .confirm-booking p {
        margin: 0.2rem 0;
        line-height: 1.4;
    }

    #success-message {
        color: rgba(219, 103, 8, 1);
        font-style: italic;
    }

    #close-booking-button {
        border: 1px solid #ccc;
        border-radius: 6px;
        background-color: rgba(219, 103, 8, 1);
        color: white;
        padding: 0.5rem;
        margin: auto;
        margin-top: 0.5rem;
        width: 100px;
        cursor: pointer;
    }

    @media (max-width: 568px) {
        .confirm-booking-content {
            width: 400px;
        }

        .booked-photo img {
            width: 340px;
            height: 220px;
        }

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
            <a href="home"><img src="images/sun.gif" alt="Sunny-logo" class="sunny-logo"></a>
            <div class="title-divider">
                <a href="home" class="title">
                    <h1>Sunny Spot Holidays</h1>
                </a>
                <h3>This is a mock website only!</h3>
            </div>
        </div>
        <nav>
            <ul>
                <li class="home"><a href="home" class="active">Home</a></li>
                <li class="information"><a href="information">Guest Information</a></li>
                <li class="attractions"><a href="attractions">Attractions</a></li>
                <li class="foodAndDrink"><a href="foodAndDrink">Food & Drink</a></li>
                <li class="contact"><a href="contact">Contact Us</a></li>
            </ul>
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main>
        <div class="confirm-booking">
            <div class="confirm-booking-content">
                <h2>Your Booking Details</h2>
                <div class="booked-photo">
                    <?php if (!empty($photo)): ?>
                    <img src="images/<?php echo htmlspecialchars($photo); ?>"
                        alt="<?php echo htmlspecialchars($booking['cabinType'] ?? ''); ?> photo">
                    <?php else: ?>
                    <p class="empty-photo">testCabin.jpg</p>
                    <?php endif; ?>
                </div>
                <div class="booked-cabin">
                    <p><strong>Cabin Type:</strong> <?php echo htmlspecialchars($booking['cabinType'] ?? ''); ?></p>
                </div>
                <div class="booked-arrival">
                    <p><strong>Arrival:</strong>
                        <span><?php echo !empty($booking['arrival']) ? date('d - m - Y', strtotime($booking['arrival'])) : ''; ?>
                        </span>
                    </p>
                </div>
                <div class="booked-departure">
                    <p><strong>Departure:</strong>
                        <span><?php echo !empty($booking['departure']) ? date('d - m - Y', strtotime($booking['departure'])) : ''; ?>
                        </span>
                    </p>
                </div>
                <div class="booked-number-of-guest">
                    <p><strong>Number of Guests:</strong>
                        <?php echo htmlspecialchars($booking['numberOfGuest'] ?? ''); ?></p>
                </div>
                <div class="booked-name">
                    <p><strong>Full Name:</strong>
                        <?php echo htmlspecialchars(($booking['firstName'] ?? '') . ' ' . ($booking['lastName'] ?? '')); ?>
                    </p>
                </div>
                <div class="booked-phone">
                    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($booking['phone'] ?? ''); ?></p>
                </div>
                <div class="booked-email">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email'] ?? ''); ?></p>
                </div>
                <p class="thank-message">Thank you for your booking!</p>
                <p class="thank-message">We will email you the booking details shortly.</p>
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
        <img src="images/author.png" alt="author" class="author">
    </footer>
</body>


</html>
