<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OLX Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #343a40;
            --sidebar-color: #e9ecef;
            --sidebar-active-bg: #007bff;
            --header-height: 56px;
        }
        body { overflow-x: hidden; }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            color: var(--sidebar-color);
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
        }
        .sidebar-menu { padding: 0; list-style: none; }
        .sidebar-menu li { position: relative; }
        .sidebar-menu li a {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--sidebar-color);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar-menu li a.active { background: var(--sidebar-active-bg); }
        .sidebar-menu li a i { margin-right: 10px; width: 20px; text-align: center; }
        .sidebar-menu .submenu { padding-left: 20px; list-style: none; display: none; }
        .sidebar-menu .submenu.show { display: block; }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }
        .header {
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar-collapsed { margin-left: -250px; }
        .content-expanded { margin-left: 0; }
        .badge-sm { font-size: 0.65em; padding: 0.25em 0.4em; }
        .card-header { background-color: #f8f9fa; }
        .table thead th { background-color: #f1f3f5; }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        .btn-outline-primary { border-color: #007bff; color: #007bff; }
        .btn-outline-primary:hover { background-color: #007bff; color: white; }
        .empty-state { text-align: center; padding: 2rem; }
        .thumbnail-img { width: 50px; height: 50px; object-fit: cover; }
        .stat-card { border-left: 4px solid #007bff; }
        .list-group-item { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <nav class="header navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-link toggle-sidebar d-none d-lg-block">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center ms-auto">
                    <div class="dropdown me-3">
                        <a href="#" class="dropdown-toggle" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><a class="dropdown-item" href="#"><small>New message received</small></a></li>
                            <li><a class="dropdown-item" href="#"><small>Ad approved</small></a></li>
                            <li><a class="dropdown-item" href="#"><small>Ad inquiry</small></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">View all</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="User">
                            <span>User Name</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.html"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.html"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.html"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Welcome, User Name!</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Stats and Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card stat-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">My Ads</h5>
                            <p class="card-text">You have <strong>5</strong> active ads.</p>
                            <a href="my_ads.html" class="btn btn-outline-primary">View Ads</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Messages</h5>
                            <p class="card-text">You have <strong>3</strong> unread messages.</p>
                            <a href="messages.html" class="btn btn-outline-primary">View Messages</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Favorites</h5>
                            <p class="card-text">You have <strong>4</strong> favorite ads.</p>
                            <a href="favorites.html" class="btn btn-outline-primary">View Favorites</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <a href="publish_ad.html" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i> Publish New Ad</a>
                                <a href="categories.html" class="btn btn-outline-primary"><i class="fas fa-tags me-2"></i> Browse Categories</a>
                                <a href="profile.html" class="btn btn-outline-primary"><i class="fas fa-user me-2"></i> Update Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Ads -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Ads</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" id="recentAdsContainer">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentAdsTableBody">
                                        <!-- Populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="empty-state d-none" id="recentAdsEmptyState">
                                <h5>No Recent Ads</h5>
                                <p>You haven't posted any ads yet. Start by publishing a new ad!</p>
                                <a href="publish_ad.html" class="btn btn-primary">Publish Ad</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Notifications</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group" id="notificationsList">
                                <!-- Populated by JavaScript -->
                            </ul>
                            <div class="empty-state d-none" id="notificationsEmptyState">
                                <h5>No Notifications</h5>
                                <p>You have no recent notifications.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Mock data
        const recentAds = [
            { id: 1, title: "iPhone 12", category: "Mobile Phones", price: 700, status: "Active", image: "https://via.placeholder.com/50" },
            { id: 2, title: "Laptop Dell XPS", category: "Electronics", price: 1500, status: "Pending", image: "https://via.placeholder.com/50" },
            { id: 3, title: "Honda Civic 2019", category: "Cars", price: 20000, status: "Active", image: "https://via.placeholder.com/50" }
        ];

        const notifications = [
            { id: 1, message: "New message received for iPhone 12", date: "2025-04-27" },
            { id: 2, message: "Your ad 'Laptop Dell XPS' is pending approval", date: "2025-04-26" },
            { id: 3, message: "New inquiry for Honda Civic 2019", date: "2025-04-25" }
        ];

        // Populate recent ads table
        function populateRecentAdsTable() {
            const tbody = $("#recentAdsTableBody");
            const container = $("#recentAdsContainer");
            const emptyState = $("#recentAdsEmptyState");
            tbody.empty();

            if (recentAds.length === 0) {
                container.addClass("d-none");
                emptyState.removeClass("d-none");
                return;
            }

            container.removeClass("d-none");
            emptyState.addClass("d-none");

            recentAds.forEach(ad => {
                const statusClass = ad.status === "Active" ? "bg-success" :
                                   ad.status === "Pending" ? "bg-warning" : "bg-danger";
                tbody.append(`
                    <tr>
                        <td><img src="${ad.image}" class="thumbnail-img" alt="${ad.title}"></td>
                        <td>${ad.title}</td>
                        <td>${ad.category}</td>
                        <td>$${ad.price}</td>
                        <td><span class="badge ${statusClass}">${ad.status}</span></td>
                        <td>
                            <a href="ad_details.html?id=${ad.id}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                `);
            });
        }

        // Populate notifications list
        function populateNotificationsList() {
            const list = $("#notificationsList");
            const emptyState = $("#notificationsEmptyState");
            list.empty();

            if (notifications.length === 0) {
                list.addClass("d-none");
                emptyState.removeClass("d-none");
                return;
            }

            list.removeClass("d-none");
            emptyState.addClass("d-none");

            notifications.forEach(notif => {
                list.append(`
                    <li class="list-group-item">
                        <span>${notif.message}</span>
                        <small class="text-muted">${notif.date}</small>
                    </li>
                `);
            });
        }

        // Initialize
        $(document).ready(function() {
            $('.toggle-sidebar').click(function() {
                $('.sidebar').toggleClass('sidebar-collapsed');
                $('.main-content').toggleClass('content-expanded');
            });
            $('.sidebar-menu li a').click(function() {
                $('.sidebar-menu li a').removeClass('active');
                $(this).addClass('active');
            });
            populateRecentAdsTable();
            populateNotificationsList();
        });
    </script>
</body>
</html>