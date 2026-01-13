<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays - General Information</title>
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
        .information-header-divider {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: -1.5rem;
        }

        .information-icon {
            width: 50px;
        }

        .divider {
            margin: 0.5rem;
            padding: 0.5rem 1.5rem;
            border-bottom: 4px solid rgba(92, 53, 3, 0.16);
        }

        .cabin-divider {
            column-count: 2;
            column-gap: 0.3rem;
        }

        .divider:last-child {
            border: none;
        }

        .divider li {
            font-size: 1.2rem;
            padding: 0.2rem;
            font-family: roboto, sans-serif;
        }

        .article-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin: 2rem;
        }

        article {
            width: 100%;
            max-width: 900px;
            flex: 1;
        }

        .image-details {
            position: relative;
        }

        .images {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .image {
            width: 500px;
            height: 400px;
            flex-shrink: 0;
            margin-top: 2rem;
        }

        .images-left {
            animation: slideFromLeft 1s ease-out forwards;
        }

        .images-right {
            animation: slideFromRight 1s ease-out forwards;
        }

        @keyframes slideFromLeft {
            from {
                opacity: 0;
                transform: translateX(-100px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideFromRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5);
        }

        .images p {
            width: 300px;
            font-size: 1rem;
            font-family: roboto, sans-serif;
            text-align: center;
            color: white;
            background-color: rgba(95, 62, 4, 1);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            font-weight: bold;
            padding: 1rem;
            position: absolute;
            top: 96%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @media (max-width: 1750px) {
            .image {
                width: 400px;
                height: 350px;
            }
        }

        @media (max-width: 1600px) {
            article {
                max-width: 1300px;
            }

            .images {
                display: none;
            }
        }

        @media (max-width: 1100px) {
            article {
                max-width: 1000px;
            }
        }

        @media (max-width: 768px) {
            .article-wrapper {
                flex-direction: column;
                align-items: center;
                max-width: 700px;
                padding: 1rem;
                margin: 1rem 0.5rem;
            }

            .information-icon {
                width: 35px;
            }

            h2 {
                font-size: 1.4rem;
            }

            .divider h3 {
                font-size: 1.1rem;
            }

            .divider ul li {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .article-wrapper {
                max-width: 480px;
                padding: 0.5rem;
                margin-top: 1.5rem;
                margin-bottom: -1.5rem;
            }
        }
    </style>
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
    <?php
    //Define all variables
    $receptionHours = "7am to 5pm";
    $phone = "02 0000 0000";
    $email = "info@sunnyspotholidays.com";
    $checkInInfo = [
        "Check-in from 2pm",
        "Check-out by 10am",
        "Late check-out for 1 hour may be available on request (fee apply)"
    ];
    $houseRules = [
        "No smoking inside cabins or facilities",
        "Pets allowed, but not in cabins",
        "Quiet hours: 11 pm to 7 am",
        "Please respect other guests and keep shared areas clean"
    ];
    $cabinFacilities = [
        "Air conditioner",
        "Linen",
        "Veranndah",
        "Bunk bed",
        "Ceiling fans",
        "Clock radio",
        "Dinning facilities",
        "Dishwasher",
        "DVD Player",
        "Foxtel",
        "Fridge/Freezer",
        "Hair dryer",
        "Ironing Facilities",
        "Microwave"
    ];
    $generalFacilities = [
        "Shared shower and toilet blocks",
        "Shared kitchen and BBQ areas",
        "Free Wi-Fi common areas",
        "On-site laundry (coin-operated)"
    ];
    $imagesLeft = [
        "images/cabin-room.jpg",
        "images/cabin-interior3.avif",
        "images/bunk bed.jpg"
    ];
    $leftCaptions = [
        "Spa Villa Guest Room",
        "Cabin Interior",
        "Bunk Bed",
    ];
    $imagesRight = [
        "images/kitchen.avif",
        "images/laundry.avif",
        "images/grill area.jpg"
    ];
    $rightCaptions = [
        "Kitchen",
        "Laundry",
        "Grill Area"
    ]
    ?>
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
                <li class="information"><a href="information.php" class="active">Guest Information</a></li>
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
    <main class="main-information">
        <div class="article-wrapper">
            <div class="images images-left">
                <?php foreach ($imagesLeft as $index => $imageLeft): ?>
                    <div class="image-details">
                        <div class="image">
                            <img src="<?php echo $imageLeft; ?>" alt="<?php echo $leftCaptions[$index]; ?>">
                        </div>
                        <p><?php echo $leftCaptions[$index]; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <article>
                <div class="reception"></div>
                <div class="information-header-divider">
                    <img src="images/information.gif" alt="information-icon" class="information-icon">
                    <h2>Guest Information<h2>
                </div>
                <div class="divider">
                    <h3>Reception and Contact</h3>
                    <ul>
                        <li>Reception available:
                            <?= $receptionHours; ?>
                        </li>
                        <li>Outside reception hours: on-call staff available</li>
                        <li>Phone number:
                            <?= $phone; ?>
                        </li>
                        <li>Email:
                            <?php echo $email; ?>
                        </li>
                    </ul>
                </div>
                <div class="divider">
                    <h3>Check-in and Check-out</h3>
                    <ul>
                        <?php foreach ($checkInInfo as $part) : ?>
                            <li><?= $part; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="divider">
                    <h3>House Rules</h3>
                    <ul>
                        <?php foreach ($houseRules as $rule) : ?>
                            <li><?= $rule; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="divider">
                    <h3>Cabin Facilities</h3>
                    <ul class="cabin-divider">
                        <?php foreach ($cabinFacilities as $cabinItem) : ?>
                            <li><?= $cabinItem; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="divider">
                    <h3>General Facilities</h3>
                    <ul>
                        <?php foreach ($generalFacilities as $generalItem) : ?>
                            <li><?= $generalItem; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </article>
            <div class="images images-right">
                <?php foreach ($imagesRight as $index => $imageRight): ?>
                    <div class="image-details">
                        <div class="image"><img src="<?php echo $imageRight; ?>"
                                alt="<?php echo $rightCaptions[$index]; ?>"></div>
                        <p><?php echo $rightCaptions[$index]; ?></p>
                    </div>
                <?php endforeach; ?>
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
    <script src="script.js"></script>
</body>


</html>