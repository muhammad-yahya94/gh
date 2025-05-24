<?php
include_once 'auth_check.php'; // Include authentication check
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h4>Gadget Hub</h4>
        <button class="btn btn-sm btn-outline-light toggle-sidebar d-lg-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="categories.php" class="<?= $currentPage == 'categories.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
        </li>

        <li>
            <a href="ad_posts.php" class="<?= $currentPage == 'ad_posts.php' ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i>
                <span>Ad Posts</span>
            </a>
        </li>

        <li>
            <a href="#usersSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($currentPage, ['all_users.php', 'add_new_user.php']) ? 'true' : 'false' ?>" 
               class="dropdown-toggle <?= in_array($currentPage, ['all_users.php', 'add_new_user.php']) ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="submenu collapse <?= in_array($currentPage, ['all_users.php', 'add_new_user.php']) ? 'show' : '' ?>" id="usersSubmenu">
                <li>
                    <a href="all_users.php" class="<?= $currentPage == 'all_users.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-friends"></i>
                        <span>All Users</span>
                    </a>
                </li>
                <li>
                    <a href="add_new_user.php" class="<?= $currentPage == 'add_new_user.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-plus"></i>
                        <span>Add New</span>
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <a href="reports.php" class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-flag"></i>
                <span>Reports</span>
            </a>
        </li>

        <li>
            <a href="messages.php" class="<?= $currentPage == 'messages.php' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i>
                <span>Messages</span>
            </a>
        </li>

        <li>
            <a href="settings.php" class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>

        <li class="mt-auto">
            <a href="logout.php" class="text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
