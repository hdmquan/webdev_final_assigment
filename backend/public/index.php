<?php
session_start();

$phone = '';
$password = '';
$phoneError = '';
$passwordError = '';
$visitsOutput = '';
$visitCount = 0;
$isLoggedIn = false;

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $valid = true;

    // Input validation
    if (!preg_match('/^\d{1,10}$/', $phone)) {
        $phoneError = 'Phone number must be digits only, max 10.';
        $valid = false;
    }

    if (empty($password)) {
        $passwordError = 'Password is required.';
        $valid = false;
    }

    if ($valid) {
        $con_alan = @mysqli_connect('mysql', 'admin', 'admin', 'db');
        if (!$con_alan) {
            die("Database connection failed");
        }

        // Login verification
        $stmt = mysqli_prepare($con_alan,
            "SELECT customer_id, given_name, family_name FROM customer_1557984 WHERE phone_number = ? AND AES_DECRYPT(password, 'mysecretkey') = ?"
        );
        mysqli_stmt_bind_param($stmt, "is", $phone, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            $_SESSION['cid'] = $user['customer_id'];
            $_SESSION['name'] = $user['given_name'] . ' ' . $user['family_name'];
            $isLoggedIn = true;

            // Insert new visit
            $cid = $_SESSION['cid'];
            $date = date('Y-m-d');
            $time = date('H:i:s');
            $insertStmt = mysqli_prepare($con_alan, "INSERT INTO visits_1557984 (customer_id, visit_date, visit_time) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($insertStmt, "iss", $cid, $date, $time);
            mysqli_stmt_execute($insertStmt);
        } else {
            $checkPhoneStmt = mysqli_prepare($con_alan, "SELECT * FROM customer_1557984 WHERE phone_number = ?");
            mysqli_stmt_bind_param($checkPhoneStmt, "i", $phone);
            mysqli_stmt_execute($checkPhoneStmt);
            $phoneResult = mysqli_stmt_get_result($checkPhoneStmt);

            if (mysqli_fetch_assoc($phoneResult)) {
                $passwordError = 'Incorrect password.';
            } else {
                $phoneError = 'Phone number not found.';
            }
        }
    }
}

// Check if session is active and fetch visits
if (isset($_SESSION['cid'])) {
    $isLoggedIn = true;
    $con_alan = @mysqli_connect('mysql', 'admin', 'admin', 'db');
    $visitQuery = mysqli_prepare($con_alan, "SELECT visit_date, visit_time FROM visits_1557984 WHERE customer_id = ?");
    mysqli_stmt_bind_param($visitQuery, "i", $_SESSION['cid']);
    mysqli_stmt_execute($visitQuery);
    $visitResult = mysqli_stmt_get_result($visitQuery);

    $visitsOutput .= "<h3>Welcome, {$_SESSION['name']}!</h3><ul>";
    while ($visit = mysqli_fetch_assoc($visitResult)) {
        $visitsOutput .= "<li>{$visit['visit_date']} at {$visit['visit_time']}</li>";
        $visitCount++;
    }
    $visitsOutput .= "</ul><p><strong>Total visits: $visitCount</strong></p>";
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Customer Login" />
    <meta name="keywords" content="" />
    <link rel="canonical" href="http://localhost:8000/index.php" />

    <!-- OpenGraph Tags -->
    <meta property="og:title" content="Customer Login" />
    <meta property="og:description" content="Login to view your visits" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="http://localhost:8000/index.php" />
    <meta property="og:image" content="/assets/images/logo-small.png" />
    <meta property="og:image:secure_url" content="/assets/images/logo-small.png" />

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicons/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicons/favicon-32x32.png?v1" />
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicons/favicon-16x16.png" />
    <link rel="manifest" href="/assets/favicons/site.webmanifest" />
    <meta name="msapplication-TileColor" content="#da532c" />
    <meta name="theme-color" content="#ffffff" />

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css" />
    <link rel="stylesheet" href="/assets/css/venobox.min.css" />
    <link rel="stylesheet" href="/assets/css/animate.min.css" />
    <link rel="stylesheet" href="/assets/css/keyframe-animation.css" />
    <link rel="stylesheet" href="/assets/css/odometer.min.css" />
    <link rel="stylesheet" href="/assets/css/nice-select.css" />
    <link rel="stylesheet" href="/assets/css/carouselTicker.css" />
    <link rel="stylesheet" href="/assets/css/swiper.min.css" />
    <link rel="stylesheet" href="/assets/css/main.css" />

    <title>Customer Login</title>

    <script>
        function validateForm() {
            const phone = document.getElementById('phone').value;
            if (!/^\d+$/.test(phone)) {
                alert("Phone number must contain only digits.");
                return false;
            }
            return true;
        }
    </script>
    <style>
        .error { color: red; font-size: 0.9em; margin-left: 10px; }
    </style>
</head>
<body>
    <script defer src="/assets/js/vendor/jquary-3.6.0.min.js"></script>
    <script defer src="/assets/js/vendor/bootstrap-bundle.js"></script>
    <script defer src="/assets/js/vendor/imagesloaded-pkgd.js"></script>
    <script defer src="/assets/js/vendor/waypoints.min.js"></script>
    <script defer src="/assets/js/vendor/venobox.min.js"></script>
    <script defer src="/assets/js/vendor/odometer.min.js"></script>
    <script defer src="/assets/js/vendor/meanmenu.js"></script>
    <script defer src="/assets/js/vendor/jquery.isotope.js"></script>
    <script defer src="/assets/js/vendor/wow.min.js"></script>
    <script defer src="/assets/js/vendor/swiper.min.js"></script>
    <script defer src="/assets/js/vendor/split-type.min.js"></script>
    <script defer src="/assets/js/vendor/gsap.min.js"></script>
    <script defer src="/assets/js/vendor/scroll-trigger.min.js"></script>
    <script defer src="/assets/js/vendor/scroll-smoother.js"></script>
    <script defer src="/assets/js/vendor/jquery.carouselTicker.js"></script>
    <script defer src="/assets/js/vendor/nice-select.js"></script>
    <script defer src="/assets/js/ajax-form.js"></script>
    <script defer src="/assets/js/slider.js"></script>
    <script defer src="/assets/js/contact.js"></script>
    <script defer src="/assets/js/main.js"></script>

    <main id="main">
      <div id="smooth-warpper">
        <div id="smooth-content">
            <?php include 'assets/components/header.html'; ?>
            <?php include 'assets/components/banner.html'; ?>
            <?php include 'assets/components/theme-toggle.html'; ?>

            <link rel="stylesheet" href="assets/css/alan_style.css">

            <section class="advertisement">
                <div class="container">
                    <h2>Customer Login</h2>
                    <?php if (!$isLoggedIn): ?>
                        <form class="appointment-form" id="loginForm" method="POST" onsubmit="return validateForm()">
                            <label class="form-label" for="phone">Phone Number:</label>
                            <input class="form-control" type="text" id="phone" name="phone" maxlength="10" required value="<?= htmlspecialchars($phone) ?>">
                            <?php if ($phoneError): ?><span class="error"><?= $phoneError ?></span><?php endif; ?><br><br>

                            <label class="form-label" for="password">Password:</label>
                            <input class="form-control" type="password" id="password" name="password" required value="<?= htmlspecialchars($password) ?>">
                            <?php if ($passwordError): ?><span class="error"><?= $passwordError ?></span><?php endif; ?><br><br>

                            <input class="btn btn-primary" type="submit" value="Login">
                        </form>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <?= $visitsOutput ?>
                        <div id="logoutContainer">
                            <form method="POST">
                                <input type="hidden" name="logout" value="1">
                                <button class="btn btn-primary" type="submit">Logout</button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </section>
            <?php include 'assets/components/footer.html'; ?>
        </div>
      </div>
    </main>

</body>
</html>
