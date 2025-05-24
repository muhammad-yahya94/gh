<?php
include_once 'user_auth_check.php'; // Include user authentication check
$currentPage = basename($_SERVER['PHP_SELF']); // e.g., 'dashboard.php'
?>

<div class="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"> <a href="index.php">gadget hub User</a>  </h4>
        <button class="btn btn-sm btn-outline-light toggle-sidebar d-lg-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li>
            <?php
            $adsPages = ['publish_ad.php', 'my_ads.php'];
            $adsOpen = in_array($currentPage, $adsPages) ? 'show' : '';
            $adsActive = in_array($currentPage, $adsPages) ? 'active' : '';
            ?>
            <a href="#adsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $adsOpen ? 'true' : 'false' ?>" class="dropdown-toggle <?= $adsActive ?>">
                <i class="fas fa-bullhorn"></i> Ads
            </a>
            <ul class="submenu collapse <?= $adsOpen ?>" id="adsSubmenu">
                <li><a href="publish_ad.php" class="<?= $currentPage == 'publish_ad.php' ? 'active' : '' ?>"><i class="fas fa-plus-circle"></i> Publish Ad</a></li>
                <li><a href="my_ads.php" class="<?= $currentPage == 'my_ads.php' ? 'active' : '' ?>"><i class="fas fa-list"></i> My Ads</a></li>
            </ul>
        </li>
        <li><a href="settings.php" class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Settings</a></li>
        <li><a href="messages.php" class="<?= $currentPage == 'messages.php' ? 'active' : '' ?>"><i class="fas fa-comments"></i> Messages</a></li>
        <li><a href="favorites.php" class="<?= $currentPage == 'favorites.php' ? 'active' : '' ?>"><i class="fas fa-heart"></i> Favorites</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>
