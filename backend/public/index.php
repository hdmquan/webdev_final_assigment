<?php
$phone = '';
$password = '';
$phoneError = '';
$passwordError = '';
$visitsOutput = '';
$visitCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        // All query use prepared statement to prevent SQL injection :D

        $stmt = mysqli_prepare($con_alan,
            "SELECT customer_id, given_name, family_name FROM customer_1557984 WHERE phone_number = ? AND AES_DECRYPT(password, 'mysecretkey') = ?"
        );
        mysqli_stmt_bind_param($stmt, "is", $phone, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            $cid = $user['customer_id'];
            $name = "{$user['given_name']} {$user['family_name']}";

            // Insert a new visit
            $date = date('Y-m-d');
            $time = date('H:i:s');
            $insertStmt = mysqli_prepare($con_alan, "INSERT INTO visits_1557984 (customer_id, visit_date, visit_time) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($insertStmt, "iss", $cid, $date, $time);
            mysqli_stmt_execute($insertStmt);

            // Fetch all visits
            $visitQuery = mysqli_prepare($con_alan, "SELECT visit_date, visit_time FROM visits_1557984 WHERE customer_id = ?");
            mysqli_stmt_bind_param($visitQuery, "i", $cid);
            mysqli_stmt_execute($visitQuery);
            $visitResult = mysqli_stmt_get_result($visitQuery);

            $visitsOutput .= "<h3>Welcome, $name!</h3><ul>";
            while ($visit = mysqli_fetch_assoc($visitResult)) {
                $visitsOutput .= "<li>{$visit['visit_date']} at {$visit['visit_time']}</li>";
                $visitCount++;
            }
            $visitsOutput .= "</ul><p><strong>Total visits: $visitCount</strong></p>";

            // Clear form fields after successful login
            $phone = '';
            $password = '';
        } else {
            // Check if phone exists
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
?>

<!DOCTYPE html>
<html>
<head>
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
    <h2>Customer Login</h2>
    <form id="loginForm" method="POST" onsubmit="return validateForm()">
        <label for="phone">Phone Number:</label>
        <input type="text" id="phone" name="phone" maxlength="10" required value="<?= htmlspecialchars($phone) ?>">
        <?php if ($phoneError): ?><span class="error"><?= $phoneError ?></span><?php endif; ?><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required value="<?= htmlspecialchars($password) ?>">
        <?php if ($passwordError): ?><span class="error"><?= $passwordError ?></span><?php endif; ?><br><br>

        <input type="submit" value="Login">
    </form>

    <?= $visitsOutput ?>
</body>
</html>
