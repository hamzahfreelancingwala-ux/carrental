<?php
require 'db.php';

// 1. Get the booking ID from the URL query string
$booking_id = $_GET['booking_id'] ?? null;
$booking_details = null;

if ($booking_id) {
    try {
        // SQL to join bookings and cars table to get all necessary details
        $sql = "SELECT 
                    b.*, 
                    c.brand, 
                    c.model, 
                    c.price_per_day
                FROM bookings b
                JOIN cars c ON b.car_id = c.car_id
                WHERE b.booking_id = :booking_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':booking_id' => $booking_id]);
        $booking_details = $stmt->fetch();
    } catch (PDOException $e) {
        // If there's a database error (e.g., table not found)
        die("Database Error while fetching booking details: " . $e->getMessage());
    }
}

if (!$booking_details) {
    // If no booking ID is provided or booking is not found, redirect
    echo "<script>alert('Booking not found. Please check your link.'); window.location.href = 'index.php';</script>";
    exit;
}

// Calculate rental duration
$start = new DateTime($booking_details['pickup_date']);
$end = new DateTime($booking_details['return_date']);
$duration_days = $start->diff($end)->days ?: 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed!</title>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --light-bg: #f4f7f6;
            --dark-text: #343a40;
            --accent-green: #28a745;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
            color: var(--dark-text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .confirmation-box {
            max-width: 700px;
            width: 90%;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            text-align: center;
            border-top: 5px solid var(--accent-green);
        }

        .icon-success {
            color: var(--accent-green);
            font-size: 4em;
            margin-bottom: 15px;
        }

        h1 {
            color: var(--accent-green);
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        p {
            font-size: 1.1em;
            color: var(--secondary-color);
            margin-bottom: 30px;
        }

        .details-grid {
            text-align: left;
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background-color: #f8f9fa;
        }

        .details-grid div {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e9ecef;
            font-size: 1em;
        }

        .details-grid div:last-child {
            border-bottom: none;
        }

        .details-grid strong {
            color: var(--dark-text);
        }

        .total-summary {
            padding: 15px 0;
            border-top: 2px solid var(--primary-color);
            margin-top: 20px;
        }

        .total-summary strong {
            font-size: 1.5em;
            color: var(--primary-color);
        }

        .home-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .home-btn:hover {
            background-color: #0056b3;
        }
    </style>
    </head>
<body>

    <div class="confirmation-box">
        <div class="icon-success">✔</div>
        <h1>Booking Confirmed!</h1>
        <p>Thank you, **<?php echo htmlspecialchars($booking_details['user_name']); ?>**! Your car rental is successfully reserved.</p>

        <h2>Booking Reference: #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></h2>

        <div class="details-grid">
            <div><strong>Car Reserved:</strong> <span><?php echo htmlspecialchars($booking_details['brand'] . ' ' . $booking_details['model']); ?></span></div>
            <div><strong>Pickup Location:</strong> <span><?php echo htmlspecialchars($booking_details['pickup_location']); ?></span></div>
            <div><strong>Rental Dates:</strong> <span><?php echo htmlspecialchars($booking_details['pickup_date']); ?> to <?php echo htmlspecialchars($booking_details['return_date']); ?></span></div>
            <div><strong>Duration:</strong> <span><?php echo $duration_days; ?> Day(s)</span></div>
            <div><strong>Renter Email:</strong> <span><?php echo htmlspecialchars($booking_details['user_email']); ?></span></div>
        </div>

        <div class="total-summary">
            Total Price Paid: <strong>$<?php echo number_format($booking_details['total_price'], 2); ?></strong>
        </div>

        <a href="index.php" class="home-btn">Return to Homepage</a>
    </div>

</body>
</html>
