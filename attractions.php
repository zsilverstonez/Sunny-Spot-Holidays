<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Welcome to Suny Spot Holidays. a selection of cozy cabins and a spacious camping and caravan area in Tascott, NSW.">
    <meta name="keywords" content="attraction, where to visit, Tascott, museum, park, attractions">
    <title>Sunny Spot Holidays - Attractions</title>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-QGCC41L25H"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-QGCC41L25H');
</script>
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
    main {
        padding-bottom: 2rem;
    }

    h2 {
        margin-top: 7rem;
        background-color: rgba(95, 62, 4, 1);
        border-radius: 10px;
        color: white;
        width: 1400px;
        text-align: center;
    }

    #attractions {
        margin-top: -2rem;
    }

    .attraction {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: flex-start;
        margin: 3rem auto;
        max-width: 1400px;
        gap: 2rem;
        border: 1px solid #ccc;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        background-color: rgba(255, 255, 255, 1);
        opacity: 0;
        transform: translateY(50px);
        animation: slideUp 0.8s ease-out forwards;
    }

    .attraction:nth-child(1) {
        animation-delay: 0.2s;
    }

    .attraction:nth-child(2) {
        animation-delay: 0.4s;
    }

    .attraction:nth-child(3) {
        animation-delay: 0.6s;
    }

    .attraction:nth-child(4) {
        animation-delay: 0.8s;
    }

    .attraction:nth-child(5) {
        animation-delay: 1s;
    }

    .attraction:nth-child(6) {
        animation-delay: 1.2s;
    }

    @keyframes slideUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .image {
        width: 500px;
        height: 300px;
        flex-shrink: 0;
    }

    .attraction img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px 0 0 10px;
    }

    .attraction-content {
        display: flex;
        flex-direction: column;
        padding: 1rem 1rem 1rem 0;
    }

    .attraction-content a {
        font-size: 1.1rem;
        font-family: roboto, sans-serif;
        color: rgba(219, 103, 8, 0.842);
        text-decoration: none;
    }

    .attraction-content a:hover {
        text-decoration: underline;
    }

    @media (max-width: 1500px) {
        h2 {
            width: 1100px;
        }

        .attraction {
            width: 1100px;
        }
    }

    @media (max-width: 1100px) {
        h2 {
            width: 750px;
            margin-bottom: 1.2rem;
        }

        .attraction {
            width: 750px;
            height: 500px;
            flex-direction: column;
            gap: 0;
            margin: 2rem 0;
        }

        .attraction-content {
            max-width: 750px;
            padding: 1.5rem;
        }

        .image {
            width: 100%;
        }

        .attraction img {
            border-radius: 10px 10px 0 0;
        }
    }

    @media (max-width: 768px) {
        h2 {
            width: 550px;
            margin-bottom: 1.2rem;
        }

        .attraction {
            width: 550px;
            height: 400px;
            margin: 2rem 0;
        }

        .attraction-content {
            max-width: 550px;
            padding: 0.5rem 1rem 2rem 1rem;
        }

        .image {
            width: 100%;
            height: 200px;
        }

        p,
        .attraction-content a {
            font-size: 1rem;
        }
    }

    @media (max-width: 568px) {
        h2 {
            width: 375px;
            margin-top: 5.5rem;
            margin-bottom: 1.2rem;
        }

        h3 {
            font-size: 1.1rem;
        }

        .attraction {
            width: 375px;
            height: auto;
            margin: 2rem 0;
        }

        .attraction-content {
            max-width: 375px;
            padding: 0.5rem 1rem 1rem 1rem;
        }

        .image {
            width: 100%;
            height: 200px;
        }

        p,
        .attraction-content a {
            font-size: 0.9rem;
        }

        main {
            padding: 0;
        }
    }
    </style>
   <!-- Google tag (gtag.js) for sunnyspotholidays.com.au only -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-HH5R04T2BW"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-HH5R04T2BW', {
    'cookie_domain': 'sunnyspotholidays.com.au'
  });
</script>
</head>

<body>
    <?php
    $attractions = [
        [
            "image" => "images/Polytec Stadium.avif",
            "name" => "Polytec Stadium",
            "description" => "A 20,000-plus seat venue in Goslord, NSW. It hosts numerous events and programs, including A-League and NRL matches.Since July 1, 2025, it has been a leading Australian decorative surfaces brand owned by the Borg Group.",
            "link" => "https://polytecstadium.com.au/"
        ],
        [
            "image" => "images/Australian Reptile Park.avif",
            "name" => "Australian Reptile Park",
            "description" => "The Australian Reptile Park is a wildlife sanctuary park that is located in Somersby near Gosford on the Central Coast of New South Wales, Australia. The Park has one of the largest reptile collections in Australia, with close to 50 species on display.<br> Open every day 9am – 5pm.",
            "link" => "https://www.reptilepark.com.au/"
        ],
        [
            "image" => "images/Carawah Reserve.avif",
            "name" => "Carawah Reserve",
            "description" => "A stunning natural area known for its diverse landscapes and abundant wildlife. The reserve features lush forests, tranquil waterways, and scenic walking trails that offer visitors a chance to immerse themselves in nature.<br>Open 24 hours.",
            "link" => "https://www.ausmyway.com.au/attraction/NSW_306"
        ],
        [
            "image" => "images/Gosford Regional Gallery.avif",
            "name" => "Gosford Regional Gallery",
            "description" => "The Gosford Regional Gallery is one of the Central Coast’s leading tourist attractions. It provides an important cultural and educational resource for the community as well as a one of the region’s great family attractions.<br>Open 9.30am - 4.00pm.",
            "link" => "https://gosfordregionalgallery.com/"
        ],
        [
            "image" => "images/Gosford Glyphs.avif",
            "name" => "Gosford Glyphs",
            "description" => "The Gosford Glyphs, also known as Kariong Hieroglyphs, are a group of approximately 300 Egyptian-style hieroglyphs located in Kariong, Australia. They are found in an area known for its Aboriginal petroglyphs, between Gosford and Woy Woy, New South Wales, within the Brisbane Water National Park.",
            "link" => "https://gosfordregionalgallery.com/"
        ],
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
                <li class="information"><a href="information">Guest Information</a></li>
                <li class="attractions"><a href="attractions" class="active">Attractions</a></li>
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
        <h2>Local Attractions</h2>
        <div id="attractions">
            <?php foreach ($attractions as $attraction): ?>
            <div class="attraction">
                <div class="image">
                    <img src="<?php echo $attraction["image"]; ?>" alt=" <?php echo $attraction["name"]; ?>">
                </div>
                <div class="attraction-content">
                    <h3><?php echo $attraction["name"]; ?></h3>
                    <p><?php echo $attraction["description"]; ?></p>
                    <a href="<?php echo $attraction["link"]; ?>" target="_blank">More Info</a>
                </div>
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