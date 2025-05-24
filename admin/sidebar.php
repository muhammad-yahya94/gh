<?php
include_once 'auth_check.php'; // Include authentication check
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">gadget hub Admin</h4>
        <button class="btn btn-sm btn-outline-light toggle-sidebar d-lg-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

        <li><a href="categories.php" class="<?= $currentPage == 'categories.php' ? 'active' : '' ?>"><i class="fas fa-tags"></i> Categories</a></li>

        <?php
        // Ad Posts Link
        $adPages = ['ad_posts.php'];
        $adMenuOpen = in_array($currentPage, $adPages);
        ?>
        <li><a href="ad_posts.php" class="<?= $currentPage == 'ad_posts.php' ? 'active' : '' ?>"><i class="fas fa-bullhorn"></i> Ad Posts</a></li>

        <?php
        $userPages = ['all_users.php', 'add_new_user.php'];
        $userMenuOpen = in_array($currentPage, $userPages);
        ?>
        <li>
            <a href="#usersSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $userMenuOpen ? 'true' : 'false' ?>" class="dropdown-toggle <?= $userMenuOpen ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <ul class="submenu collapse <?= $userMenuOpen ? 'show' : '' ?>" id="usersSubmenu">
                <li><a href="all_users.php" class="<?= $currentPage == 'all_users.php' ? 'active' : '' ?>"><i class="fas fa-user-friends"></i> All Users</a></li>
                <li><a href="add_new_user.php" class="<?= $currentPage == 'add_new_user.php' ? 'active' : '' ?>"><i class="fas fa-user-plus"></i> Add New</a></li>
            </ul>
        </li>

        <!-- <?php
      //  $orderPages = ['all_orders.php', 'transactions.php'];
      //  $orderMenuOpen = in_array($currentPage, $orderPages);
        ?>
        <li>
            <a href="#ordersSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $orderMenuOpen ? 'true' : 'false' ?>" class="dropdown-toggle <?= $orderMenuOpen ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <ul class="submenu collapse <?= $orderMenuOpen ? 'show' : '' ?>" id="ordersSubmenu">
                <li><a href="all_orders.php" class="<?= $currentPage == 'all_orders.php' ? 'active' : '' ?>"><i class="fas fa-list"></i> All Orders</a></li>
                <li><a href="transactions.php" class="<?= $currentPage == 'transactions.php' ? 'active' : '' ?>"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
            </ul>
        </li> -->

        <li><a href="reports.php" class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>"><i class="fas fa-flag"></i> Reports</a></li>
        <li><a href="messages.php" class="<?= $currentPage == 'messages.php' ? 'active' : '' ?>"><i class="fas fa-comments"></i> Messages</a></li>

       <li><a href="settings.php" class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Settings</a></li>
       <li><a href="logout.php" class="<?= $currentPage == 'logout.php' ? 'active' : '' ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>
