<?php
require 'db.php';

// 1. Get search parameters from URL
$location = $_GET['location'] ?? 'All Locations';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+3 days'));
$duration_days = (new DateTime($start_date))->diff(new DateTime($end_date))->days ?: 1;

// 2. Build the SQL query with filters and sorting
$where_clauses = [];
$params = [];
$filter_car_type = $_GET['car_type'] ?? '';
$filter_fuel_type = $_GET['fuel_type'] ?? '';
$filter_min_price = $_GET['min_price'] ?? 0;
$filter_max_price = $_GET['max_price'] ?? 1000;
$sort_by = $_GET['sort_by'] ?? 'price_asc';

if ($filter_car_type) {
    $where_clauses[] = "car_type = :car_type";
    $params[':car_type'] = $filter_car_type;
}
if ($filter_fuel_type) {
    $where_clauses[] = "fuel_type = :fuel_type";
    $params[':fuel_type'] = $filter_fuel_type;
}
if ($filter_min_price) {
    $where_clauses[] = "price_per_day >= :min_price";
    $params[':min_price'] = $filter_min_price;
}
if ($filter_max_price) {
    $where_clauses[] = "price_per_day <= :max_price";
    $params[':max_price'] = $filter_max_price;
}

$where_sql = count($where_clauses) > 0 ? ' WHERE ' . implode(' AND ', $where_clauses) : '';

$order_sql = match ($sort_by) {
    'price_desc' => ' ORDER BY price_per_day DESC',
    'rating_desc' => ' ORDER BY rating DESC',
    default => ' ORDER BY price_per_day ASC', // price_asc
};

$sql = "SELECT * FROM cars" . $where_sql . $order_sql;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $available_cars = $stmt->fetchAll();
} catch (PDOException $e) {
    // If the tables aren't set up, this will prevent a fatal error.
    $available_cars = []; 
}

// Helper function to maintain filter state in form
function getSelected($key, $value) {
    return (isset($_GET[$key]) && $_GET[$key] == $value) ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rental Cars</title>
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
        }

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 20px;
            display: flex;
            gap: 20px;
        }
        
        .header {
            background-color: var(--dark-text);
            color: white;
            padding: 15px 0;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2em;
            color: var(--primary-color);
        }

        /* --- Sidebar (Filters) --- */
        .sidebar {
            width: 300px;
            min-width: 250px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar h3 {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .filter-group input[type="number"] {
            margin-top: 5px;
        }

        .filter-group button {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .filter-group button:hover {
            background-color: #0056b3;
        }


        /* --- Main Content (Car Listings) --- */
        .main-content {
            flex-grow: 1;
        }

        .search-summary {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-info span {
            font-weight: bold;
            color: var(--primary-color);
        }

        .sort-control label {
            margin-right: 10px;
            font-weight: bold;
        }

        .sort-control select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .car-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .car-item {
            display: flex;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .car-item-image {
            flex-basis: 350px;
            min-width: 350px;
        }

        .car-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .car-item-details {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            justify-content: space-between;
        }

        .details-left h3 {
            color: var(--dark-text);
            margin-top: 0;
            font-size: 1.8em;
        }

        .details-left p {
            font-size: 0.9em;
            color: var(--secondary-color);
        }

        .specs-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
            max-width: 400px;
        }

        .spec-item {
            font-size: 0.9em;
        }

        .spec-item strong {
            color: var(--primary-color);
            margin-right: 5px;
        }

        .details-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }

        .price-total {
            font-size: 1em;
            color: var(--secondary-color);
            margin-top: 5px;
        }

        .price-per-day {
            font-size: 2.5em;
            font-weight: bold;
            color: var(--accent-green);
            line-height: 1;
        }

        .price-per-day span {
            font-size: 0.5em;
            font-weight: normal;
            color: var(--dark-text);
        }

        .book-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .book-btn:hover {
            background-color: #0056b3;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                position: static;
                margin-bottom: 20px;
            }
            .car-item {
                flex-direction: column;
            }
            .car-item-image {
                min-width: 100%;
                height: 250px;
            }
            .car-item-details {
                flex-direction: column;
                gap: 20px;
            }
            .details-right {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                border-top: 1px solid #eee;
                padding-top: 15px;
            }
            .price-info {
                text-align: left;
            }
        }
    </style>
    </head>
<body>

    <header class="header">
        <h1>Available Cars for Rent</h1>
    </header>

    <div class="container">
        
        <aside class="sidebar">
            <h3>Refine Your Search</h3>
            <form action="listings.php" method="GET" id="filterForm">
                <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>" id="sortInput">


                <div class="filter-group">
                    <label for="car_type">Car Type</label>
                    <select name="car_type" id="car_type">
                        <option value="">All Types</option>
                        <option value="Sedan" <?php echo getSelected('car_type', 'Sedan'); ?>>Sedan</option>
                        <option value="SUV" <?php echo getSelected('car_type', 'SUV'); ?>>SUV</option>
                        <option value="Hatchback" <?php echo getSelected('car_type', 'Hatchback'); ?>>Hatchback</option>
                        <option value="Van" <?php echo getSelected('car_type', 'Van'); ?>>Van</option>
                        <option value="Truck" <?php echo getSelected('car_type', 'Truck'); ?>>Truck</option>
                        <option value="Luxury" <?php echo getSelected('car_type', 'Luxury'); ?>>Luxury</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="fuel_type">Fuel Type</label>
                    <select name="fuel_type" id="fuel_type">
                        <option value="">All Fuels</option>
                        <option value="Petrol" <?php echo getSelected('fuel_type', 'Petrol'); ?>>Petrol</option>
                        <option value="Diesel" <?php echo getSelected('fuel_type', 'Diesel'); ?>>Diesel</option>
                        <option value="Electric" <?php echo getSelected('fuel_type', 'Electric'); ?>>Electric</option>
                        <option value="Hybrid" <?php echo getSelected('fuel_type', 'Hybrid'); ?>>Hybrid</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Price Range ($/Day)</label>
                    <input type="number" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? 0); ?>" min="0">
                    <input type="number" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? 1000); ?>" max="1000">
                </div>
                
                <div class="filter-group">
                    <button type="submit">Apply Filters</button>
                </div>
            </form>
        </aside>

        <main class="main-content">
            <div class="search-summary">
                <div class="summary-info">
                    Showing <strong><?php echo count($available_cars); ?></strong> cars for pickup at <span><?php echo htmlspecialchars($location); ?></span> from <span><?php echo htmlspecialchars($start_date); ?></span> to <span><?php echo htmlspecialchars($end_date); ?></span> (<?php echo $duration_days; ?> day(s))
                </div>
                <div class="sort-control">
                    <label for="sort_select">Sort By:</label>
                    <select id="sort_select" onchange="applySort(this.value)">
                        <option value="price_asc" <?php echo getSelected('sort_by', 'price_asc'); ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo getSelected('sort_by', 'price_desc'); ?>>Price (High to Low)</option>
                        <option value="rating_desc" <?php echo getSelected('sort_by', 'rating_desc'); ?>>Best Rated</option>
                    </select>
                </div>
            </div>

            <div class="car-list">
                <?php if (count($available_cars) > 0): ?>
                    <?php foreach ($available_cars as $car): 
                        $total_price = $car['price_per_day'] * $duration_days;
                    ?>
                        <div class="car-item">
                            <div class="car-item-image">
                                <img src="https://via.placeholder.com/400x250?text=<?php echo urlencode($car['brand'] . ' ' . $car['model']); ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                            </div>
                            <div class="car-item-details">
                                <div class="details-left">
                                    <h3><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                                    <p><?php echo htmlspecialchars($car['description']); ?></p>
                                    <div class="specs-grid">
                                        <div class="spec-item"><strong>Type:</strong> <?php echo htmlspecialchars($car['car_type']); ?></div>
                                        <div class="spec-item"><strong>Fuel:</strong> <?php echo htmlspecialchars($car['fuel_type']); ?></div>
                                        <div class="spec-item"><strong>Trans:</strong> <?php echo htmlspecialchars($car['transmission']); ?></div>
                                        <div class="spec-item"><strong>Seats:</strong> <?php echo htmlspecialchars($car['seating_capacity']); ?></div>
                                        <div class="spec-item"><strong>Rating:</strong> <?php echo htmlspecialchars($car['rating']); ?> ⭐</div>
                                    </div>
                                </div>
                                <div class="details-right">
                                    <div class="price-info">
                                        <div class="price-per-day">
                                            $<?php echo number_format($car['price_per_day'], 2); ?> <span>/ day</span>
                                        </div>
                                        <div class="price-total">
                                            Total Est.: $<?php echo number_format($total_price, 2); ?>
                                        </div>
                                    </div>
                                    <button class="book-btn" onclick="redirectToBooking(<?php echo $car['car_id']; ?>, '<?php echo htmlspecialchars($location); ?>', '<?php echo htmlspecialchars($start_date); ?>', '<?php echo htmlspecialchars($end_date); ?>', <?php echo $total_price; ?>)">
                                        Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 50px; background-color: white; border-radius: 8px;">
                        <h2>No Cars Found</h2>
                        <p>Try adjusting your filters or changing your search dates/location. Ensure your database tables are loaded.</p>
                        <a href="index.php" class="book-btn" style="display: inline-block; margin-top: 20px;">Start a New Search</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Function to handle booking redirection
        function redirectToBooking(carId, location, startDate, endDate, totalPrice) {
            const url = `booking.php?car_id=${carId}&location=${encodeURIComponent(location)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&total_price=${totalPrice}`;
            window.location.href = url;
        }

        // Function to handle sorting
        function applySort(sortByValue) {
            document.getElementById('sortInput').value = sortByValue;
            document.getElementById('filterForm').submit();
        }
    </script>
</body>
</html>
