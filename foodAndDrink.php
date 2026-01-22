<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays - Food & Drink</title>
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
    h2 {
        margin-top: 7rem;
        color: white;
        background-color: rgba(95, 62, 4, 1);
        border-radius: 10px;
        text-align: center;
        width: 1160px;
    }

    .restaurantsAndCafes {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
        text-align: center;
        padding: 0;
        margin-bottom: 4rem;
    }

    .restaurantAndCafe {
        flex: 1 1 500px;
        width: 100%;
        max-width: 550px;
        margin: 1rem;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        border: 1px solid #ccc;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        background-color: rgba(255, 255, 255, 1);
        opacity: 0;
        transform: translateY(50px);
        animation: slideUp 0.8s ease-out forwards;
    }

    @keyframes slideUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .restaurantAndCafe:nth-child(1) {
        animation-delay: 0.2s
    }

    .restaurantAndCafe:nth-child(2) {
        animation-delay: 0.4s
    }

    .restaurantAndCafe:nth-child(3) {
        animation-delay: 0.6s
    }

    .restaurantAndCafe:nth-child(4) {
        animation-delay: 0.8s
    }

    .restaurantAndCafe:nth-child(5) {
        animation-delay: 1s
    }

    .restaurantAndCafe:nth-child(6) {
        animation-delay: 1.2s
    }

    .restaurantAndCafe h3 {
        padding-top: 1rem;
    }

    .restaurantAndCafe p,
    .restaurantAndCafe a {
        width: 100%;
        max-width: 550px;
        padding: 0 1rem 1rem 1rem;
    }

    .image {
        width: 600px;
        height: 300px;
        flex-shrink: 0;
        width: 100%;
    }

    .restaurantAndCafe img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px 10px 0 0;
    }

    .restaurantAndCafe a {
        text-decoration: none;
        font-family: roboto, sans-serif;
        color: black;
        font-size: 1.2rem;
    }

    .restaurantAndCafe a:hover {
        color: rgba(219, 103, 8, 0.842);
    }

    @media (max-width: 1100px) {
        h2 {
            width: 750px;
        }

        .image {
            height: 300px;
        }

        .restaurantAndCafe {
            margin: 0;
        }

        .restaurantAndCafe p,
        .restaurantAndCafe a {
            max-width: 500px;
            padding: 0 0.5rem 1rem 0.5rem;
        }
    }

    @media (max-width: 768px) {
        h2 {
            width: 550px;
            font-size: 1.6rem;
            margin-top: 7rem;
        }

        .image {
            height: 250px;
        }

        .restaurantAndCafe h3 {
            font-size: 1.3rem;
        }

        .restaurantAndCafe p,
        .restaurantAndCafe a {
            font-size: 1rem;
        }
    }

    @media (max-width: 568px) {
        h2 {
            width: 375px;
            margin-top: 5.5rem;
            font-size: 1.3rem;
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
    $restaurantsAndCafes = [
        [
            "image" => "images/Fare Cravin Cafe.avif",
            "name" => "Fare Cravin' Café",
            "description" => "A tranquil and cosy café with classic Aussie menu.<br>Open Monday to Friday 7am – 2pm. Saturday and Sunday 8am – 2pm.",
            "address" => "209 Brisbane Water Dr, Point Clare NSW 2250"
        ],
        [
            "image" => "images/Little Piggy Eat & Drink Cafe.avif",
            "name" => "This Little Piggy Eat & Drink Café",
            "description" => "A cute and artist café with healthy menu.<br>Open Monday to Friday 6:30am to 1:30pm and Saturday 7:30am – 1pm.",
            "address" => "7/51 Brisbane Water Drive &, Talinga Ave, Point Clare NSW 2250"
        ],
        [
            "image" => "images/Locomotive Station.avif",
            "name" => "Locomotive Station",
            "description" => "Known as best coffee shop on the Central Coast, serving coffee with cocktails, pies, pastries and housemade sweets<br>Open Tuesday to Saturday 8:00am to 1:00pm.",
            "address" => "2D Kateena Ave, Tascott NSW 2250"
        ],
        [
            "image" => "images/Crown Indian Restaurant.avif",
            "name" => "Crown Indian Restaurant",
            "description" => "A tranquil and cosy café with classic Aussie menu.<br>Open every day 4pm to 9:30pm, except Tuesday.",
            "address" => "Shop 2/51 Brisbane Water Dr, Point Clare NSW 2250"
        ],
        [
            "image" => "images/Point Clare Chinese Take Away.avif",
            "name" => "Point Clare Chinese Take Away",
            "description" => "A casual Chinese restaurant with affordable menu, opened over 20 years.<br>Open every day 11:30 am – 9pm, except Sunday.",
            "address" => "43 Brisbane Water Dr, Point Clare NSW 2250"
        ],
        [
            "image" => "images/Hungry Wolfs Tascott.avif",
            "name" => "Hungry Wolfs Tascott",
            "description" => "A casual pizzeria restaurant with selections of pizzas and pastas, opened over 20 years.<br>Open every day 4:30 pm – 9pm, except Monday.",
            "address" => "2E-E Kateena Ave, Tascott NSW 2250"
        ]
    ]
    ?>
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
                <li class="home"><a href="home">Home</a></li>
                <li class="information"><a href="information">Guest Information</a></li>
                <li class="attractions"><a href="attractions">Attractions</a></li>
                <li class="foodAndDrink"><a href="foodAndDrink" class="active">Food & Drink</a></li>
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
        <h2>Local Restaurants & Cafés</h2>
        <div class="restaurantsAndCafes">
            <?php foreach ($restaurantsAndCafes as $restaurantAndCafe): ?>
            <div class="restaurantAndCafe">
                <div class="image"><img src="<?php echo $restaurantAndCafe["image"] ?>"
                        alt="<?php echo $restaurantAndCafe["name"] ?>"></div>
                <h3><?php echo $restaurantAndCafe["name"] ?></h3>
                <p><?php echo $restaurantAndCafe["description"] ?></p>
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($restaurantAndCafe["address"]); ?>"
                    target="_blank">Address:
                    <?php echo $restaurantAndCafe["address"]; ?>
                </a>


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
        <img src="images/author.png" alt="author" class="author">
    </footer>
    <script src="script.js"></script>
</body>

</html>
