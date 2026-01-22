<?php

session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Include connection to database
include 'database_connect.php';
// Load cabins from database
$cabins = [];
$result = $connect->query("SELECT * FROM cabin");
while ($row = $result->fetch_assoc()) {
    $cabins[] = $row;
}
// Load all inclusion ids
$cabinInclusion = [];
$result2 = $connect->query("SELECT * FROM cabin_inclusion");
while ($row2 = $result2->fetch_assoc()) {
    $cabinInclusion[$row2['cabinID']][] = $row2['incID']; // [] is to define each cabin has an array of inclusions
}
// Load all inclusion names
$inclusions = [];
$result3 = $connect->query(("SELECT * FROM inclusion"));
while ($row3 = $result3->fetch_assoc()) {
    $inclusions[$row3['incID']] = $row3['incName']; // there is no [] here as each incID is each name of inclusion
}
// Load cabin inclusion names
$cabinInclusionNames = [];
foreach ($cabinInclusion as $cabinID => $incIDs) {
    $inclusionName = [];
    foreach ($incIDs as $id) {
        if (isset($inclusions[$id])) {
            $inclusionName[] = $inclusions[$id];
        }
    }
    $cabinInclusionNames[$cabinID] = $inclusionName; // use [] as $cabinInclusionNames contains all cabins with their inclusions not a single cabin with its inclusions
}
$receivedMessage = "";
$receivedMessageErr = "";
$bookingError = "";
$contactError = "";
// Connect booking to booking.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'booking') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $bookingError = "Security validation failed. Please try again.";
        } else {
            date_default_timezone_set('Australia/Sydney');
            $today = date('Y-m-d');
            $arrival = $_POST['arrival'];
            $departure = $_POST['departure'];
            if ($arrival < $today) {
                $bookingError = "Arrival date cannot be in the past.";
            } elseif ($departure <= $arrival) {
                $bookingError = "Departure must be after arrival.";
            } else {
                $_SESSION['arrival'] = $_POST['arrival'];
                $_SESSION['departure'] = $_POST['departure'];
                $_SESSION['cabinType'] = $_POST['cabin_type'];
                $_SESSION['numberOfGuest'] = $_POST['number_of_guest'];
                header("Location: booking.php");
                exit;
            }
        }
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'contact') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $contactError = "Security validation failed. Please try again.";
        } elseif (empty($_POST['g-recaptcha-response'])) {
            $contactError = "Please complete the CAPTCHA.";
        } else {
            $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'];
            $captchaResponse = $_POST['g-recaptcha-response'];
            $verifyURL = "https://www.google.com/recaptcha/api/siteverify";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $verifyURL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'secret' => $secretKey,
                'response' => $captchaResponse
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if (!$responseData || !$responseData['success']) {
                $contactError = "CAPTCHA verification failed. Please try again.";
            } else {
                $name = trim($_POST['contact-name'] ?? '');
                $phone = trim($_POST['contact-phone'] ?? '');
                $email = trim($_POST['contact-email'] ?? '');
                $message = trim($_POST['contact-message'] ?? '');
                $status = 'new';
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $contactError = "Invalid email format";
                } elseif (!$name || !$phone || !$email || !$message) {
                    $receivedMessageErr = 'There is an error in submitting your message.<br>Please check and resubmit again.';
                } else {
                    $contactTable = $connect->prepare("INSERT INTO contact (name, phone, email, message, status) VALUES (?,?,?,?,?)");
                    $contactTable->bind_param("sssss", $name, $phone, $email, $message, $status);
                    $contactTable->execute();
                    $receivedMessage = 'Thank you for contact us! We will get back to you as soon as possible.';
                    $contactTable->close();

                    // Mail Notification
                    $headers = "From: noreply@sunnyspotholidays.com.au\r\n";
                    $headers .= "Reply-To: noreply@sunnyspotholidays.com.au\r\n";

                    $safeName = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
                    $safeEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
                    $safePhone = filter_var($phone, FILTER_SANITIZE_SPECIAL_CHARS);
                    $safeMessage = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);

                    mail(
                        $_ENV['ADMIN_EMAIL'],
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
        content="Welcome to Sunny Spot Holidays. a selection of cozy cabins and a spacious camping and caravan area in Tascott, NSW.">
    <meta name="keywords" content="cabin, accommodation, Tascott, caravan, holiday, home">
    <title>Sunny Spot Holidays</title>
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
    <link rel="preload" as="image" href="images/background.jpg" fetchpriority="high">
    <style>
    .background {
        width: 100%;
        height: 600px;
        display: flex;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        filter: brightness(95%);
        object-fit: cover;
    }

    form.booking {
        width: 75%;
        background-color: rgba(48, 30, 6, 0.94);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        font-size: 1.1rem;
        margin-top: -9rem;
        z-index: 10;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 85px;
        padding: 1rem 2rem;
        gap: 2rem;
    }

    .booking .date-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-right: -1rem;
    }

    .booking .date-wrapper input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        pointer-events: auto;
    }


    .flatpickr-day,
    .flatpickr-current-month span,
    .flatpickr-current-month .numInputWrapper input,
    .flatpickr-weekday {
        font-family: arima, sans-serif !important;
    }

    .flatpickr-current-month .numInputWrapper input {
        font-weight: 500 !important;
    }

    .flatpickr-day.today,
    .flatpickr-day.today:focus,
    .flatpickr-day.selected,
    .flatpickr-day.selected:focus {
        background: rgba(255, 115, 0, 0.9) !important;
        border-color: rgba(255, 115, 0, 0.9) !important;
        color: white !important;
    }

    .booking .arrival-wrapper,
    .booking .departure-wrapper,
    .booking .cabin-wrapper,
    .booking .guest-wrapper {
        padding: 0.2rem 0.6rem;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 6px;
        display: flex;
        justify-content: left;
        align-items: center;
        margin: 0;
        gap: 0.8rem;
    }

    form.booking .arrival-wrapper,
    form.booking .departure-wrapper {
        width: 212px;
        position: relative;
    }

    .booking .cabin-wrapper {
        width: 270px;
    }

    .booking .guest-wrapper {
        width: 150px;
    }

    .booking .cabin-wrapper select,
    .booking .guest-wrapper select {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        pointer-events: auto;
    }


    .booking .cabin-wrapper select:hover,
    .booking .guest-wrapper select:hover {
        cursor: pointer;
    }

    .booking .cabin-wrapper select option:hover,
    .booking .guest-wrapper select option:hover {
        background-color: rgba(255, 115, 0, 0.9);
    }

    .input-wrapper {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .input-wrapper:hover {
        cursor: pointer;
    }

    .input-wrapper span {
        font-family: roboto, sans-serif;
        font-size: 0.8rem;
    }

    .booking option {
        font-family: roboto, sans-serif;
    }

    .booking button {
        width: 120px;
        height: 45px;
        background-color: rgba(255, 115, 0, 0.9);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: none;
        cursor: pointer;
        border-radius: 6px;
        color: white;
        text-decoration: none;
        font-family: roboto, sans-serif;
        font-weight: bold;
    }

    .booking button:hover {
        background-color: rgba(255, 115, 0, 1);
    }

    .bookingError {
        font-size: 0.9rem;
        width: auto;
        color: rgba(248, 245, 60, 1);
        text-align: center;
        border-radius: 10px;
    }

    .cabins-title {
        width: 1545px;
        margin-top: 5.5rem;
        padding: 1.5rem 0 0.5rem 0;
        background-color: rgba(95, 62, 4, 1);
        color: white;
        border-radius: 10px;
    }

    .cabin-slider {
        position: relative;
        width: 1565px;
        margin-top: 1.3rem;
        margin-bottom: -2rem;
    }

    .cabin-wrapper {
        width: 100%;
        overflow: hidden;
    }

    .cabins {
        display: flex;
        font-size: 1rem;

    }

    .arrow-left,
    .arrow-right {
        position: absolute;
        top: 41%;
        transform: translateY(-50%);
        font-size: 2rem;
        background: rgba(255, 255, 255, 0.7);
        color: rgba(95, 62, 4, 1);
        border: none;
        cursor: pointer;
        z-index: 10;
        border-radius: 5px;
    }

    .arrow-left {
        left: 1%;
    }

    .arrow-right {
        right: 1%;
    }

    .cabin {
        height: 460px;
        display: flex;
        justify-content: center;
        font-family: roboto, sans-serif;
        margin: 0 10px;
        flex: 0 0 500px;
        background-color: rgba(255, 255, 255, 1);
        border: 1px solid #ccc;
        border-radius: 10px;
    }

    .cabin-content {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        gap: 1rem;
    }

    .cabin-info {
        display: flex;
        width: 500px;
        flex-direction: column;
        font-family: roboto, sans-serif;
        flex: 1;
        padding: 0 1rem;
        margin-top: -0.5rem;
    }

    .cabin-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0;
    }

    .cabin-details {
        margin-top: -0.5rem;
        line-height: 0;
    }

    .image {
        width: 100%;
        height: 210px;
        flex-shrink: 0;
        object-fit: cover;
    }

    .cabin-content img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px 10px 0 0;
    }

    .cabin-prices {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 0;
        margin-top: 0.3rem;
    }

    p.cabin-type,
    .price-number {
        font-family: arima, sans-serif;
        font-size: 1.4rem;
        font-weight: bold;
        color: rgba(219, 103, 8, 0.842);
    }

    p.cabin-type {
        max-width: 220px;
        line-height: 1.9;
        margin-top: 0.8rem;
        margin-bottom: 0;
    }

    p.cabin-description {
        margin-top: 1rem;

    }

    p.cabin-inclusion {
        margin-bottom: 2rem;
    }

    .cabin-type {
        font-size: 1.75rem;
    }

    span.inclusion {
        font-family: roboto, sans-serif;
        font-weight: bold;
        color: rgba(219, 103, 8, 0.842);
    }

    article {
        margin-bottom: 2rem;
        width: 100%;
        max-width: 1545px;
        border-radius: 10px;
        opacity: 0;
        transform: translateY(100px);
        animation: slideUp 1s ease-out forwards;
    }

    @keyframes slideUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    h2 {
        text-align: center;
        margin-top: -0.75rem;
        margin-bottom: -0.2rem;
    }

    .highlight {
        color: rgba(219, 103, 8, 0.842);
        font-weight: bold;
    }

    .strengths {
        display: flex;
        width: 100%;
        justify-content: center;
        margin-top: 2rem;
        font-weight: 450;
        margin-bottom: 3rem;
    }

    .strengths img {
        width: 80px;
    }

    .nature,
    .cozy-living,
    .quality {
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    #contact-form {
        border: 1px solid #ccc;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        width: 100%;
        background-color: rgba(95, 62, 4, 1);
        color: white;
        margin-bottom: 2rem;
        padding-top: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .contact-divider {
        display: flex;
        justify-content: space-between;
        gap: 8rem;
        margin: 2rem 0;
    }

    fieldset {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
        max-width: 500px;
        border: none;
    }

    p.contactError {
        color: red;
        text-align: center;
        margin-bottom: 1rem;
    }

    p.contactSuccess {
        color: green;
        text-align: center;
        margin-bottom: 1rem;
    }

    .contact input,
    .contact textarea {
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
        margin: 0.5rem auto 1rem auto;
        background-color: rgba(255, 115, 0, 0.9);
        cursor: pointer;
        font-family: roboto, sans-serif;
        font-weight: bold;
        color: white;
    }

    .send-us:hover {
        background-color: rgba(255, 115, 0, 1);
    }

    iframe[src*="google.com/maps"] {
        margin-top: 0.75rem;
    }

    .g-recaptcha {
        display: none;
        justify-content: center;
        margin-top: 0.8rem;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 1630px) {
        form.booking {
            flex-direction: column;
            width: 500px;
            height: auto;
            margin-top: -18rem;
            padding-top: 1.5rem;
            gap: 1rem;
        }

        form.booking .date-wrapper {
            gap: 0.5rem;
            margin-left: -0.5rem;
        }

        form.booking .cabin-wrapper,
        form.booking .guest-wrapper {
            width: 440px;
            margin: 0;
        }

        form.booking button {
            height: 40px;
        }

        .cabins-title {
            width: 1200px;
        }

        .cabin-slider {
            width: 1200px;
        }

        .cabin {
            width: 1200px;
            height: 520px;
            margin: 0;
        }

        .cabin-content {
            width: 1200px;
        }

        .cabin-info {
            width: 1200px;
        }

        .image {
            width: 1200px;
            height: 320px;
        }

        article {
            width: 1200px;
        }

        .contact-divider {
            width: 1000px;
            gap: 0;
        }
    }

    @media (max-width: 1300px) {
        .booking {
            flex-direction: column;
            height: 400px;
            gap: 0;
            width: 700px;
            padding-top: 0;
        }

        .cabins-title {
            margin-top: 2rem;
            width: 1100px;
        }

        h2 {
            font-size: 1.7rem;
        }

        .cabin-slider {
            width: 1100px;
        }

        .cabin {
            width: 1100px;
            height: 550px;
        }

        .cabin-content {
            width: 1100px;
        }

        .cabin-info {
            width: 1100px;
        }

        .image {
            width: 1100px;
        }

        article {
            width: 1100px;
        }

        .contact-divider {
            width: 900px;
        }
    }

    @media (max-width: 1200px) {

        .booking {
            width: 600px;
        }

        .cabins-title {
            width: 900px;
        }

        .cabin-slider {
            width: 900px;
            padding: 0;
        }

        .cabin {
            width: 900px;
            height: 550px;
            padding: 0;
        }

        .cabin-content {
            width: 900px;
            padding: 0;
        }

        .cabin-info {
            width: 900px;
            margin-top: -10px;
        }

        .image {
            width: 900px;
            height: 300px;
        }

        .cabin-content img {
            border-radius: 10px 10px 0 0;
        }

        .cabin-prices {
            margin-left: auto;
            align-items: flex-end;
        }

        article {
            padding: 1.5rem;
            width: 900px;
        }

        .contact-divider {
            width: 900px;
            margin: auto 1rem;
            justify-content: center;
        }

        fieldset {
            max-width: 800px;
        }

        iframe[src*="google.com/maps"] {
            display: none;
        }
    }

    @media (max-width: 900px) {

        .cabins-title {
            width: 700px;
            margin-bottom: 0;
        }

        .cabin-slider {
            width: 700px;
        }

        .cabin {
            width: 700px;
            height: 550px;
        }

        .cabin-content {
            width: 700px;
        }

        .cabin-info {
            width: 700px;
        }

        .image {
            width: 700px;
            height: 300px;
        }

        article {
            width: 700px;
            margin-top: 3rem;
        }

        .contact-divider {
            width: 700px;
        }

        fieldset {
            max-width: 600px;
        }
    }

    @media (max-width: 768px) {
        .background {
            height: 400px;
        }

        form.booking {
            margin-top: -9rem;
        }

        .cabins-title {
            width: 500px;
            margin-top: 1rem;
            margin-bottom: -1.3rem;
            border-radius: 10px 10px 0 0;
        }

        h2 {
            font-size: 1.5rem;
        }

        .cabin-slider {
            width: 500px;
        }

        .cabins {
            border-radius: 0;
        }

        .cabin {
            width: 500px;
            height: 450px;
            border-top: none;
            border-radius: 0 0 10px 10px;
        }

        .cabin-content {
            width: 500px;
            border-radius: 0;
        }

        .cabin-info {
            width: 500px;
        }

        .image {
            width: 500px;
            height: 210px;
        }

        .image img {
            border-radius: 0;
        }

        .cabin-details .cabin-type,
        .price-number {
            font-size: 1.3rem;
        }

        .cabin-description {
            font-size: 1rem;
        }

        article {
            width: 500px;
        }

        article p {
            font-size: 1rem;
        }

        .strengths p {
            font-size: 0.9rem;
            text-align: center;
        }

        .strengths img {
            width: 60px;
        }

        #contact-form {
            margin: -1.5rem 0 1rem 0;
        }

        .contact-divider {
            width: 500px;
        }

        fieldset {
            max-width: 400px;
        }
    }

    @media (max-width: 568px) {

        html,
        body {
            overflow-x: hidden;
        }

        .background {
            height: 300px;
        }

        form.booking {
            width: 365px;
        }

        form.booking .arrival-wrapper,
        form.booking .departure-wrapper {
            width: 152px;
        }

        form.booking .cabin-wrapper,
        form.booking .guest-wrapper {
            width: 320px;
        }

        h2 {
            font-size: 1.2rem;
        }

        button {
            width: 100px;
            font-size: 0.8rem;
        }

        .cabins-title,
        .cabin-slider,
        .cabin-header,
        .cabin-content,
        .cabin-info {
            width: 375px;
        }

        .cabins-title {
            margin-bottom: -1.3rem;
        }

        .cabin {
            flex: 0 0 375px;
            border-top: none;
            height: auto;
            margin: 0;
        }

        .cabins {
            gap: 0;
            padding: 0;
        }

        .cabin-content,
        .cabin-info {
            width: 375px;
            margin: 0;
        }

        .arrow-left {
            left: 6%;
        }

        .arrow-right {
            right: 6%;
        }

        .image {
            width: 375px;
            height: 250px;
            margin-left: -1px;
        }

        .cabin-info,
        .cabin-type,
        .price-number,
        p.cabin-description,
        .cabin-inclusion {
            font-size: 1rem;
            width: 375px;
        }

        .cabin-prices,
        .cabin-description,
        .cabin-inclusion {
            padding-right: 2rem;
        }

        .cabin-type {
            width: 180px;
        }

        .cabin-inclusion {
            height: auto;
        }

        article {
            width: 375px;
        }

        article h2 {
            margin-top: 0.1rem;
        }

        article p {
            text-align: center;
        }

        fieldset.contact {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .contact input,
        .contact textarea {
            width: 280px;
            display: block;
            align-content: center;
            font-size: 0.8rem;
        }

        .send-us {
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        .g-recaptcha {
            transform: scale(0.9);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        const arrivalShow = document.getElementById("arrivalShow");
        const departureShow = document.getElementById("departureShow");

        function formatDate(date) {
            return flatpickr.formatDate(date, "d M Y");
        }

        // Arrival
        const arrivalPicker = flatpickr("#arrival", {
            defaultDate: today,
            minDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                arrivalShow.textContent = instance.formatDate(selectedDates[0], "d M Y");
                departurePicker.set("minDate", selectedDates[0].fp_incr(1));
            },
            onReady: function(selectedDates, dateStr, instance) {
                arrivalShow.textContent = instance.formatDate(instance.selectedDates[0], "d M Y");
            }
        });

        // Departure 
        const departurePicker = flatpickr("#departure", {
            defaultDate: tomorrow,
            minDate: tomorrow,
            onChange: function(selectedDates, dateStr, instance) {
                departureShow.textContent = instance.formatDate(selectedDates[0], "d M Y");
            },
            onReady: function(selectedDates, dateStr, instance) {
                departureShow.textContent = instance.formatDate(instance.selectedDates[0], "d M Y");
            }
        });

        // Open the right calendar
        document.querySelector(".arrival-wrapper").addEventListener("click", function() {
            arrivalPicker.open();
        });

        document.querySelector(".departure-wrapper").addEventListener("click", function() {
            departurePicker.open();
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Cabin Type
        const cabinSelect = document.querySelector(".cabin_type");
        const cabinDisplay = document.getElementById("cabinShow");
        cabinDisplay.textContent = cabinSelect.value;

        cabinSelect.addEventListener("change", function() {
            cabinDisplay.textContent = this.value;
        });

        document.querySelectorAll(".cabin-wrapper").forEach(wrapper => {
            wrapper.addEventListener("click", function() {
                const select = wrapper.querySelector("select");
                select.focus(); // focus to open dropdown on some mobile browsers
                select.click(); // try to trigger the dropdown

            });
        });

        // Guest Number
        const guestSelect = document.querySelector(".number_of_guest");
        const guestDisplay = document.getElementById("guestShow");

        // Set initial display
        if (guestSelect.value === "1") {
            guestDisplay.textContent = guestSelect.value + " person";
        } else {
            guestDisplay.textContent = guestSelect.value + " people";
        }

        // Update display on change
        guestSelect.addEventListener("change", function() {
            if (this.value === "1") {
                guestDisplay.textContent = this.value + " person";
            } else {
                guestDisplay.textContent = this.value + " people";
            }
        });

        document.querySelectorAll(".guest-wrapper").forEach(wrapper => {
            wrapper.addEventListener("click", function() {
                const select = wrapper.querySelector("select");
                select.focus();
                select.click();
            });
        });
    });
    document.addEventListener("DOMContentLoaded", () => {
        const captcha = document.querySelector(".g-recaptcha");
        const contactName = document.getElementById("contact-name");
        if (!captcha || !contactName) return;

        // Show captcha if name field already has value (e.g. after form error)
        if (contactName.value.trim() !== "") {
            captcha.style.display = "flex";
        }

        contactName.addEventListener("input", () => {
            if (contactName.value.trim() !== "") {
                captcha.style.display = "flex";
            } else {
                captcha.style.display = "none";
            }
        });
    })
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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
        <img src="images/background.jpg" alt="front view of cabins" class="background" fetchpriority="high">
        <form action="" method="POST" class="booking">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="form_type" value="booking">
            <?php if (!empty($bookingError)): ?>
            <div class="bookingError"><?php echo htmlspecialchars($bookingError); ?></div>
            <?php endif; ?>
            <div class="date-wrapper">
                <?php
                $today = date('Y-m-d');
                $todayShow = date('d M Y', strtotime($today));
                $tomorrow = date('Y-m-d', strtotime('+1 day'));
                $tomorrowShow = date('d M Y', strtotime($tomorrow)); ?>

                <div class="arrival-wrapper">
                    <span class="icon"><img src="images/calendar.png" alt="calendar icon"
                            style="width:20px; height:20px;"></span>
                    <div class="input-wrapper">
                        <span>Arrival</span>
                        <span style="font-size: 16px; width: 100%;"
                            id="arrivalShow"><?php echo htmlspecialchars($todayShow); ?></span>
                        <input type="text" id="arrival" name="arrival" aria-label="Arrival"
                            style="opacity: 0; height: 1px;" required>
                    </div>
                </div>
                <div id="arrivalError"></div>
                <div class="departure-wrapper">
                    <span class="icon"><img src="images/calendar.png" alt="calendar icon"
                            style="width:20px; height:20px;"></span>
                    <div class="input-wrapper">
                        <span>Departure</span>
                        <span style="font-size: 16px;"
                            id="departureShow"><?php echo htmlspecialchars($tomorrowShow); ?></span>
                        <input type="text" id="departure" name="departure" aria-label="Departure"
                            style="opacity: 0;height: 1px;" required>
                    </div>
                </div>
                <div id="departureError"></div>
            </div>
            <div class="cabin-wrapper" style="position: relative;">
                <span><img src="images/cabin.png" alt="guest icon"
                        style="width:20px; height:20px; margin-top: 5px;"></span>
                <div class="input-wrapper">
                    <span>Cabin Type</span>
                    <span id="cabinShow" style="font-size: 16px; width: 100%;"></span>
                    <select class="cabin_type" name="cabin_type" aria-label="Cabin Type"
                        style="border:none; font-size: 1rem;" required>
                        <option value="Standard cabin sleeps 4">Standard Cabin sleeps 4</option>
                        <option value="Standard open plan cabin sleeps 4">Standard Open Plan Cabin sleeps 4</option>
                        <option value="Deluxe cabin sleeps 4">Deluxe Cabin sleeps 4</option>
                        <option value="Villa sleeps 4">Villa sleeps 4</option>
                        <option value="Spa villa sleep 4">Spa Villa sleeps 4</option>
                        <option value="Slab powered site">Slab Powered Site</option>
                    </select>
                </div>
            </div>
            <div class="guest-wrapper" style="position: relative;">
                <span><img src="images/guest.png" alt="guest icon"
                        style="width:20px; height:23px; margin-top: 5px;"></span>
                <div class="input-wrapper">
                    <span>Guest</span>
                    <span id="guestShow" style="font-size: 16px; width: 100%;"></span>
                    <select class="number_of_guest" name="number_of_guest" aria-label="Guest Number"
                        style="border:none; font-size: 1rem;" required>
                        <option value="1">1 Person</option>
                        <option value="2">2 People</option>
                        <option value="3">3 People</option>
                        <option value="4">4 People</option>
                        <option value="5">5 People</option>
                        <option value="6">6 People</option>
                    </select>
                </div>
            </div>
            <button type="submit">Book Now</button>
        </form>
        <div class="cabins-title">
            <h2>Explore Our Cabins</h2>
        </div>
        <div class="cabin-slider">
            <button class="arrow-left">&#10094;</button>
            <div class="cabin-wrapper">
                <div class="cabins">
                    <?php foreach ($cabins as $cabin): ?>
                    <div class="cabin">
                        <div class="cabin-content">
                            <div class="image"><img src="images/<?php echo htmlspecialchars($cabin["photo"]); ?>"
                                    alt="<?php echo htmlspecialchars($cabin["cabinType"]); ?>"></div>
                            <div class="cabin-info">
                                <div class="cabin-header">
                                    <div class="cabin-details">
                                        <p class="cabin-type"><?php echo htmlspecialchars($cabin["cabinType"]); ?></p>
                                    </div>
                                    <div class="cabin-prices">
                                        <p><span class="price-number">AUD
                                                $<?php echo htmlspecialchars($cabin["pricePerNight"]); ?></span>/night
                                        </p>
                                        <p><span class="price-number">AUD
                                                $<?php echo htmlspecialchars($cabin["pricePerWeek"]); ?></span>/week
                                        </p>
                                    </div>
                                </div>
                                <p class="cabin-description"><?php echo htmlspecialchars($cabin["cabinDescription"]); ?>
                                </p>
                                <?php if (!empty($cabinInclusionNames[$cabin['cabinID']])) : ?>
                                <p class="cabin-inclusion"><span class="inclusion">
                                        Inclusions: </span><?php echo implode(
                                                                        ', ',
                                                                        $cabinInclusionNames[$cabin['cabinID']]
                                                                    ); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="arrow-right">&#10095;</button>
        </div>
        <article class="index">
            <h2>About Us</h2>
            <p><span class="highlight">Sunny Spot Holidays</span> is owned by Jack Jones and offers a perfect getaway
                on the stunning Central Coast of
                New South Wales. Our site features a selection of cozy cabins and a spacious camping and caravan area,
                providing something for every type of traveller.<br><br>
                At <span class="highlight">Sunny Spot Holidays</span>, we focus on comfort, relaxation, and a warm,
                friendly atmosphere. Whether you
                are staying in a cabin or setting up at our caravan site, our goal is to make your stay enjoyable and
                memorable, surrounded by the natural beauty of the Central Coast.</p>
            <div class="strengths">
                <div class="nature">
                    <img src="images/nature-icon.png" alt="nature icon" loading="lazy">
                    <p>Immerse Yourself in Nature</p>
                </div>
                <div class="cozy-living">
                    <img src="images/wooden-house-icon.png" alt="wooden house icon" loading="lazy">
                    <p>Comfortable & Cozy Stays</p>
                </div>
                <div class="quality">
                    <img src="images/quality-icon.png" alt="quality icon" loading="lazy">
                    <p>Friendly, Exceptional Service</p>
                </div>
            </div>
            <form id="contact-form" action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="contact-divider">
                    <fieldset class="contact">
                        <h2 class="contact-header">Contact Us</h2>
                        <?php if (!empty($contactError)): ?>
                        <p class="contactError"><?php echo htmlspecialchars($contactError); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($receivedMessage)): ?>
                        <p class="contactSuccess"><?php echo htmlspecialchars($receivedMessage); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($receivedMessageErr)): ?>
                        <p class="contactError"><?php echo $receivedMessageErr; ?></p>
                        <?php endif; ?>
                        <input type="hidden" name="form_type" value="contact">
                        <input type="text" name="contact-name" id="contact-name" placeholder="Full Name"
                            value="<?php echo htmlspecialchars($_POST['contact-name'] ?? ''); ?>" required />
                        <input type="text" name="contact-phone" id="contact-phone" placeholder="Phone Number"
                            value="<?php echo htmlspecialchars($_POST['contact-phone'] ?? ''); ?>" pattern="[0-9]{9,11}"
                            required oninvalid="this.setCustomValidity('Please enter a valid phone number')"
                            oninput="this.setCustomValidity('')" />
                        <input type="email" name="contact-email" id="contact-email" placeholder="Email"
                            value="<?php echo htmlspecialchars($_POST['contact-email'] ?? ''); ?>"
                            pattern="[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+" required
                            oninvalid="this.setCustomValidity('Please enter a valid email')"
                            oninput="this.setCustomValidity('')" />
                        <textarea name="contact-message" id="contact-message" placeholder="Please leave your message"
                            required><?php echo htmlspecialchars($_POST['contact-message'] ?? ''); ?></textarea>
                        <div class="g-recaptcha"
                            data-sitekey="<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY']); ?>"></div>
                        <button type="submit" class="send-us" id="contactButton">Send Us</button>
                    </fieldset>
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.084625552608!2d151.3209689!3d-33.4471017!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b0d4ac98a90c11b%3A0x8c27fe67499a642f!2s50%20Melaleuca%20Cres%2C%20Tascott%20NSW%202250!5e0!3m2!1sen!2sau!4v1757977007669!5m2!1sen!2sau"
                        width="880" height="300" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

            </form>
        </article>
    </main>
    <footer>
        <p>
            <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                50 Melaleuca Cres, Tascott NSW 2250
            </a>
        </p>
        <div class="social-media">
            <img src="images/facebook-icon.png" alt="icon-facebook" loading="lazy">
            <img src="images/instagram-icon.png" alt="icon-instagram" loading="lazy">
            <img src="images/twitter-icon.png" alt="icon-twitter" id="twitter" loading="lazy">
        </div>
        <p>© 2025 Copyright Sunny Spot Holidays</p>
        <img src="images/author.png" alt="author" class="author">
    </footer>
</body>

</html>
