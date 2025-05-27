<?php
session_start();
require_once 'db.php'; // PDO database connection

// Get search parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$category = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : 0;
$location = isset($_GET['location']) && $_GET['location'] !== '' ? trim($_GET['location']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$ads_per_page = 6;
$offset = ($page - 1) * $ads_per_page;

// Build SQL query for ads
$sql = "SELECT a.id, a.title, a.price, a.location, a.image_path, a.description, c.name AS category_name
        FROM ads a
        JOIN categories c ON a.category_id = c.id   
        WHERE a.status = :status";
$params = [':status' => 'active'];

// Search query
if ($query) {
    $sql .= " AND (a.title LIKE :query_title OR a.description LIKE :query_description)";
    $params[':query_title'] = "%$query%";
    $params[':query_description'] = "%$query%";
}

// Category filter
if ($category) {
    $sql .= " AND a.category_id = :category";
    $params[':category'] = $category;
}

// Location filter
if ($location) {
    $sql .= " AND a.location = :location";
    $params[':location'] = $location;
}

// Price range filter
if ($min_price > 0) {
    $sql .= " AND a.price >= :min_price";
    $params[':min_price'] = $min_price;
}
if ($max_price > 0) {
    $sql .= " AND a.price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Condition filter
if ($condition === 'New' || $condition === 'Used') {
    $sql .= " AND a.description LIKE :condition";
    $params[':condition'] = "%$condition%";
}

// Sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY a.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY a.price DESC";
        break;
    default:
        $sql .= " ORDER BY a.created_at DESC";
        break;
}

// Pagination (added after preparation)
$count_sql = "SELECT COUNT(*) as total FROM ads a WHERE a.status = :status";
$count_params = [':status' => 'active'];
if ($query) {
    $count_sql .= " AND (a.title LIKE :query_title OR a.description LIKE :query_description)";
    $count_params[':query_title'] = "%$query%";
    $count_params[':query_description'] = "%$query%";
}
if ($category) {
    $count_sql .= " AND a.category_id = :category";
    $count_params[':category'] = $category;
}
if ($location) {
    $count_sql .= " AND a.location = :location";
    $count_params[':location'] = $location;
}
if ($min_price > 0) {
    $count_sql .= " AND a.price >= :min_price";
    $count_params[':min_price'] = $min_price;
}
if ($max_price > 0) {
    $count_sql .= " AND a.price <= :max_price";
    $count_params[':max_price'] = $max_price;
}
if ($condition === 'New' || $condition === 'Used') {
    $count_sql .= " AND a.description LIKE :condition";
    $count_params[':condition'] = "%$condition%";
}

try {
    // Execute count query
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total_ads = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_ads / $ads_per_page);

    // Fetch ads
    $stmt = $pdo->prepare($sql); // Prepare without LIMIT/OFFSET
    $stmt->execute($params);
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Apply LIMIT and OFFSET manually (not as parameters)
    $ads = array_slice($ads, $offset, $ads_per_page);
    error_log("Fetched " . count($ads) . " ads"); // Debug count
} catch (PDOException $e) {
    error_log("Query error: " . $e->getMessage());
    die("Query failed: " . $e->getMessage());
}

// Fetch categories for filter
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Categories query error: " . $e->getMessage());
    $categories = [];
}

// Fetch unique locations for filter
try {
    $stmt = $pdo->query("SELECT DISTINCT location FROM ads WHERE location IS NOT NULL AND location != '' ORDER BY location");
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Locations query error: " . $e->getMessage());
    $locations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - GadgetHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="search-result.css">
</head>
<body>

    <?php include 'partials/header.php'; ?>

    <!-- Search Results -->
    <section class="search-container-fluid py-4">    
        <div class="container-fluid">
            <div class="row">
                <!-- Filters Column -->
                <div class="col-lg-3 mb-4">
                    <div class="filter-card">
                        <h4 class="filter-title">Filters</h4>
                        <form method="GET" action="search-result.php">
                            <input type="hidden" name="query" value="<?= htmlspecialchars($query) ?>">
                            <div class="filter-section">
                                <label class="filter-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-section">
                                <label class="filter-label">Price Range</label>
                                <div class="row g-2">
                                    <div class="col">
                                        <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= $min_price > 0 ? $min_price : '' ?>">
                                    </div>
                                    <div class="col">
                                        <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= $max_price > 0 ? $max_price : '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="filter-section">
                                <label class="filter-label">Location</label>
                                <select name="location" class="form-select">
                                    <option value="">All Locations</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= htmlspecialchars($loc) ?>" <?= $location === $loc ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($loc) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-section">
                                <label class="filter-label">Condition</label>
                                <select name="condition" class="form-select">
                                    <option value="">Any</option>
                                    <option value="New" <?= $condition === 'New' ? 'selected' : '' ?>>New</option>
                                    <option value="Used" <?= $condition === 'Used' ? 'selected' : '' ?>>Used</option>
                                </select>
                            </div>
                            <button type="submit" class="filter-btn">Apply Filters</button>
                        </form>
                    </div>
                </div>

                <!-- Results Column -->
                <div class="col-lg-9">
                    <div class="search-header">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <form method="GET" action="search-result.php">
                                    <div class="input-group">
                                        <input type="text" name="query" class="form-control search-input" placeholder="Search for ads..." value="<?= htmlspecialchars($query) ?>">
                                        <button type="submit" class="mt-2 filter-btn"><i class="fas fa-search"></i></button>
                                    </div>
                                    <input type="hidden" name="category" value="<?= $category ?>">
                                    <input type="hidden" name="location" value="<?= htmlspecialchars($location) ?>">
                                    <input type="hidden" name="min_price" value="<?= $min_price ?>">
                                    <input type="hidden" name="max_price" value="<?= $max_price ?>">
                                    <input type="hidden" name="condition" value="<?= $condition ?>">
                                    <input type="hidden" name="sort" value="<?= $sort ?>">
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form method="GET" action="search-result.php">
                                    <select name="sort" class="form-select sort-select" onchange="this.form.submit()">
                                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort by: Newest</option>
                                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                                    </select>
                                    <input type="hidden" name="query" value="<?= htmlspecialchars($query) ?>">
                                    <input type="hidden" name="category" value="<?= $category ?>">
                                    <input type="hidden" name="location" value="<?= htmlspecialchars($location) ?>">
                                    <input type="hidden" name="min_price" value="<?= $min_price ?>">
                                    <input type="hidden" name="max_price" value="<?= $max_price ?>">
                                    <input type="hidden" name="condition" value="<?= $condition ?>">
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php if (empty($ads)): ?>
                            <div class="col-12">
                                <p class="text-center">No ads found matching your criteria.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($ads as $ad): ?>
                                <div class="col">
                                    <div class="product-card">
                                        <img src="<?= htmlspecialchars($ad['image_path']) ?>" class="product-img" alt="<?= htmlspecialchars($ad['title']) ?>">
                                        <div class="product-body">
                                            <h5 class="product-title"><?= htmlspecialchars($ad['title']) ?></h5>
                                            <p class="product-price">$<?= number_format($ad['price'], 2) ?></p>
                                            <p class="product-meta">
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ad['location']) ?> • 
                                                <?= stripos($ad['title'], 'new') !== false || stripos($ad['description'], 'new') !== false ? 'New' : 'Used' ?>
                                            </p>
                                            <a href="ad-details.php?id=<?= $ad['id'] ?>" class="product-link">View Details <i class="fas fa-arrow-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="pagination">
                            <ul class="pagination">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?query=<?= urlencode($query) ?>&category=<?= $category ?>&location=<?= urlencode($location) ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>&condition=<?= $condition ?>&sort=<?= $sort ?>&page=<?= $page - 1 ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?query=<?= urlencode($query) ?>&category=<?= $category ?>&location=<?= urlencode($location) ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>&condition=<?= $condition ?>&sort=<?= $sort ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?query=<?= urlencode($query) ?>&category=<?= $category ?>&location=<?= urlencode($location) ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>&condition=<?= $condition ?>&sort=<?= $sort ?>&page=<?= $page + 1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'partials/footer.php'; ?>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icon = document.querySelector('.theme-toggle i');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');
        }
    </script>
</body>
</html>