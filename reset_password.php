 <?php
    session_start();
    // Include connection to database
    include '../database_connect.php';
    // Declare empty error message
    $message = "";
    $messageErr = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"] ?? "";
        $codeEntered = $_POST["code"] ?? "";
        $password = $_POST["password"] ?? "";

        // Check if the reset code matches and is not expired
        if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_user'])) {
            $message = "No reset request found.<br>Please request a new reset code.";
        } elseif ($_SESSION['reset_user'] !== $username) {
            $message = "Username does not match the reset request.";
        } elseif ($_SESSION['reset_code'] != $codeEntered) {
            $message = "Reset code is incorrect.";
        } elseif (time() > $_SESSION['reset_expire']) {
            $message = "Reset code has expired.<br>Please request a new one.";
        } else {
            // Hash password before storing
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            // Update password in database
            $passwordUpdate = $connect->prepare("UPDATE account SET password=? WHERE username=?");
            $passwordUpdate->bind_param("ss", $hashedPassword, $username);
            $passwordUpdate->execute();
            $passwordUpdate->close();
            $message = "Your password has been updated successfully!<br>You can now log in.";
        }
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
     <link href="../styles.css?v=<?php echo time(); ?>" rel="stylesheet">
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
         color: rgba(219, 103, 8, 0.842);
     }
     </style>
     <script>
     // Display password message
     document.addEventListener("DOMContentLoaded", () => {
         const password = document.getElementById("password");
         const password2 = document.getElementById("password2");
         const passwordMessage = document.querySelector(".password-message");

         function checkPassword() {
             if (password.value !== password2.value) {
                 passwordMessage.textContent = "Passwords must match";
                 return;
             } else {
                 passwordMessage.textContent = "";
                 return;
             }
         }

         password.addEventListener("input", checkPassword);
         password2.addEventListener("input", checkPassword);
     })
     </script>
 </head>

 <body>
     <header>
         <div class="header-divider">
             <a href="../home"><img src="../images/sun.gif" alt="Sunny-logo" class="sunny-logo"></a>
             <div class="title-divider">
                 <a href="../home" class="title">
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
             <p class="error">
                 <?php echo str_replace('&lt;br&gt;', '<br>', htmlspecialchars($message, ENT_QUOTES, 'UTF-8')); ?></p>
             <?php endif; ?>
             <form method="post" action="">
                 <fieldset class="login">
                     <input type="text" name="username" id="username" placeholder="Enter Username" required>
                     <input type="text" name="code" id="code" placeholder="Enter Reset Code" required>
                     <input type="password" name="password" id="password" placeholder="Enter Password" required>
                     <input type="password" name="password2" id="password2" placeholder="Confirm Your Password"
                         required>
                     <p class="password-message"
                         style="margin: 0; padding-left: 0.5rem; font-style: italic; font-size: 12px; color: rgba(219, 103, 8, 0.842);">
                     </p>
                     <button type="submit">Submit</button>
                     <a class="back" href="login.php" style="text-align: center; width: 100%;">Back to Log in</a>
                 </fieldset>

             </form>
         </article>
     </main>
     <footer>
         <p>
             <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                 50 Melaleuca Cres, Tascott NSW 2250
             </a>
         </p>
         <p>© 2025 Copyright Sunny Spot Holidays</p>
         <img src="../images/author.png" alt="author" class="author">
     </footer>
     <script src="../script.js"></script>
 </body>

 </html>
