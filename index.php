<?php
require 'db.php';

// Quick check to confirm db.php was found and executed
if (!isset($pdo)) {
    die("Error: db.php was required but the PDO object was not created. Check your db.php file.");
}

// Fetch featured cars (e.g., top 3 highest rated)
try {
    $stmt = $pdo->query("SELECT * FROM cars ORDER BY rating DESC LIMIT 3");
    $featured_cars = $stmt->fetchAll();
} catch (PDOException $e) {
    // If the 'cars' table doesn't exist, this will catch the error.
    $featured_cars = [];
    $db_error = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar Clone - Home</title>
    <style>
        /* ... (CSS from previous responses is omitted here for brevity, but should be included) ... */
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --dark-text: #343a40;
            --accent-green: #28a745;
        }

        body { font-family: 'Arial', sans-serif; margin: 0; padding: 0; background-color: var(--light-bg); color: var(--dark-text); }
        .header { background-color: var(--dark-text); color: white; padding: 20px 0; text-align: center; }
        .header h1 { margin: 0; font-size: 2.5em; color: var(--primary-color); }
        .search-container { background: linear-gradient(135deg, var(--primary-color), #0056b3); padding: 40px 20px; text-align: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .search-form { background-color: white; border-radius: 8px; display: flex; flex-wrap: wrap; justify-content: center; padding: 20px; max-width: 1200px; margin: auto; gap: 15px; }
        .form-group { display: flex; flex-direction: column; min-width: 200px; flex-grow: 1; }
        .form-group label { text-align: left; margin-bottom: 5px; font-weight: bold; color: var(--dark-text); }
        .form-group input, .form-group select { padding: 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 1em; box-sizing: border-box; }
        .search-btn { background-color: var(--accent-green); color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: background-color 0.3s ease; align-self: flex-end; margin-top: 15px; min-width: 150px; }
        .search-btn:hover { background-color: #1e7e34; }
        .content-section { padding: 40px 20px; max-width: 1200px; margin: auto; }
        .content-section h2 { text-align: center; color: var(--primary-color); margin-bottom: 30px; font-size: 2em; }
        .featured-cars { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .car-card { background-color: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .car-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); }
        .car-card img { width: 100%; height: 200px; object-fit: cover; }
        .car-info { padding: 15px; }
        .car-info h3 { margin-top: 0; color: var(--dark-text); }
        .car-info .price { font-size: 1.5em; font-weight: bold; color: var(--primary-color); margin: 10px 0; }
        .car-info .details { display: flex; justify-content: space-between; font-size: 0.9em; color: var(--secondary-color); }
        .error-box { padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        @media (max-width: 768px) {
            .search-form { flex-direction: column; }
            .form-group { min-width: 100%; }
            .search-btn { width: 100%; margin-top: 20px; }
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>RentA**Pro**Car</h1>
        <p>Your journey starts here. Search, compare, and book your perfect rental car.</p>
    </header>
    
    <?php if (isset($db_error)): ?>
        <div class="error-box">
            Database connection is successful, but the **`cars` table was not found** or data access failed. Please ensure you have run the **`db_setup_v3.sql`** script!
        </div>
    <?php endif; ?>

    <div class="search-container">
        <form class="search-form" id="searchForm">
            <div class="form-group"><label for="pickup_location">Pickup Location</label><input type="text" id="pickup_location" name="location" placeholder="e.g., Dubai International Airport" required></div>
            <div class="form-group"><label for="pickup_date">Pickup Date</label><input type="date" id="pickup_date" name="start_date" required></div>
            <div class="form-group"><label for="return_date">Return Date</label><input type="date" id="return_date" name="end_date" required></div>
            <button type="submit" class="search-btn">Search Cars</button>
        </form>
    </div>

    <div class="content-section">
        <h2>🔥 Top-Rated Deals & Featured Cars</h2>
        <div class="featured-cars">
            <?php if (count($featured_cars) > 0): ?>
                <?php foreach ($featured_cars as $car): ?>
                    <div class="car-card">
                        <img src="https://via.placeholder.com/400x200?text=<?php echo urlencode($car['brand'] . ' ' . $car['model']); ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                        <div class="car-info">
                            <h3><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                            <div class="price">$<?php echo number_format($car['price_per_day'], 2); ?> / day</div>
                            <div class="details">
                                <span>Type: <?php echo htmlspecialchars($car['car_type']); ?></span>
                                <span>Rating: <?php echo htmlspecialchars($car['rating']); ?> ⭐</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1 / -1;">No featured cars available at the moment. Please run `db_setup_v3.sql`.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        document.getElementById('searchForm').addEventListener('submit', function(event) {
            event.preventDefault(); 
            const location = document.getElementById('pickup_location').value;
            const startDate = document.getElementById('pickup_date').value;
            const endDate = document.getElementById('return_date').value;
            if (!location || !startDate || !endDate) {
                alert('Please fill in all search fields.');
                return;
            }
            // Correct Redirection path to listings.php
            const url = `listings.php?location=${encodeURIComponent(location)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
            window.location.href = url;
        });

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('pickup_date').setAttribute('min', today);
            document.getElementById('pickup_date').addEventListener('change', function() {
                document.getElementById('return_date').setAttribute('min', this.value);
            });
        });
    </script>
</body>
</html>
