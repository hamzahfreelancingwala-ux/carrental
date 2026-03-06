<?php
require 'db.php';

// 1. Get car and booking details from URL
$car_id = $_GET['car_id'] ?? null;
$location = $_GET['location'] ?? 'N/A';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$total_price = $_GET['total_price'] ?? 0.00;
$duration_days = (new DateTime($start_date))->diff(new DateTime($end_date))->days ?: 1;

// 2. Fetch Car Details
$car = null;
if ($car_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE car_id = :car_id");
        $stmt->execute([':car_id' => $car_id]);
        $car = $stmt->fetch();
    } catch (PDOException $e) {
        // Handle error
    }
}

if (!$car) {
    echo "<script>alert('Car not found or invalid link.'); window.location.href = 'listings.php';</script>";
    exit;
}

// 3. Handle Form Submission (Final Booking) - Re-checking logic for robust redirection
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $car_id_post = $_POST['car_id'] ?? null;
    $pickup_location_post = $_POST['pickup_location'] ?? '';
    $pickup_date_post = $_POST['pickup_date'] ?? '';
    $return_date_post = $_POST['return_date'] ?? '';
    $total_price_post = $_POST['total_price'] ?? 0.00;

    if (empty($user_name) || empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL) || empty($car_id_post)) {
        $error_message = "Please fill in all required fields (Full Name, valid Email).";
    } else {
        try {
            // Insert booking record
            $sql = "INSERT INTO bookings (car_id, user_name, user_email, pickup_location, pickup_date, return_date, total_price) 
                    VALUES (:car_id, :user_name, :user_email, :location, :start_date, :end_date, :total_price)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':car_id' => $car_id_post,
                ':user_name' => $user_name,
                ':user_email' => $user_email,
                ':location' => $pickup_location_post,
                ':start_date' => $pickup_date_post,
                ':end_date' => $return_date_post,
                ':total_price' => $total_price_post
            ]);

            $booking_id = $pdo->lastInsertId();

            // Use a clean header redirect for success
            header("Location: confirmation.php?booking_id={$booking_id}");
            exit;

        } catch (PDOException $e) {
            $error_message = "A Database Error Occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Booking</title>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --light-bg: #f4f7f6;
            --dark-text: #343a40;
            --accent-green: #28a745;
            --warning-red: #dc3545;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 2.2em;
        }

        .booking-layout {
            display: flex;
            gap: 30px;
        }

        /* --- Car Summary --- */
        .car-summary {
            flex-basis: 40%;
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 6px;
        }

        .car-summary h2 {
            margin-top: 0;
            color: var(--dark-text);
            font-size: 1.5em;
        }

        .car-summary img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .summary-details div {
            padding: 8px 0;
            border-bottom: 1px dashed #ced4da;
            display: flex;
            justify-content: space-between;
        }

        .summary-details strong {
            color: var(--dark-text);
        }
        
        .summary-total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid var(--primary-color);
            text-align: right;
        }

        .summary-total .total-price {
            font-size: 2em;
            font-weight: bold;
            color: var(--warning-red);
        }

        /* --- Booking Form --- */
        .booking-form-section {
            flex-basis: 60%;
        }

        .booking-form-section h2 {
            color: var(--dark-text);
            font-size: 1.5em;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }

        .submit-btn {
            background-color: var(--accent-green);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 4px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background-color: #1e7e34;
        }

        .error-message {
            background-color: #f8d7da;
            color: var(--warning-red);
            padding: 10px;
            border-radius: 4px;
            border: 1px solid var(--warning-red);
            margin-bottom: 15px;
            text-align: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .booking-layout {
                flex-direction: column;
            }
            .car-summary, .booking-form-section {
                flex-basis: 100%;
            }
        }
    </style>
    </head>
<body>

    <div class="container">
        <header class="header">
            <h1>Finalize Your Rental</h1>
        </header>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="booking-layout">
            <div class="car-summary">
                <h2>Your Selection</h2>
                <img src="https://via.placeholder.com/400x200?text=<?php echo urlencode($car['brand'] . ' ' . $car['model']); ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                
                <h3><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                
                <div class="summary-details">
                    <div><strong>Rental Period:</strong> <span><?php echo $duration_days; ?> Day(s)</span></div>
                    <div><strong>Pickup Location:</strong> <span><?php echo htmlspecialchars($location); ?></span></div>
                    <div><strong>Pickup Date:</strong> <span><?php echo htmlspecialchars($start_date); ?></span></div>
                    <div><strong>Return Date:</strong> <span><?php echo htmlspecialchars($end_date); ?></span></div>
                    <div><strong>Price per Day:</strong> <span>$<?php echo number_format($car['price_per_day'], 2); ?></span></div>
                    <div><strong>Car Type:</strong> <span><?php echo htmlspecialchars($car['car_type']); ?></span></div>
                </div>

                <div class="summary-total">
                    <div>Total Estimated Price:</div>
                    <div class="total-price">$<?php echo number_format($total_price, 2); ?></div>
                    <small>*Includes all fees and taxes.</small>
                </div>
            </div>

            <div class="booking-form-section">
                <h2>Renter Information</h2>
                <form action="booking.php?car_id=<?php echo $car_id; ?>&location=<?php echo urlencode($location); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&total_price=<?php echo $total_price; ?>" method="POST">
                    
                    <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($car_id); ?>">
                    <input type="hidden" name="pickup_location" value="<?php echo htmlspecialchars($location); ?>">
                    <input type="hidden" name="pickup_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($total_price); ?>">

                    <div class="form-group">
                        <label for="user_name">Full Name*</label>
                        <input type="text" id="user_name" name="user_name" required placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label for="user_email">Email Address*</label>
                        <input type="email" id="user_email" name="user_email" required placeholder="example@email.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="(Optional) For quick contact">
                    </div>

                    <button type="submit" class="submit-btn">CONFIRM AND PAY $<?php echo number_format($total_price, 2); ?></button>

                </form>
            </div>
        </div>
    </div>
</body>
</html>
