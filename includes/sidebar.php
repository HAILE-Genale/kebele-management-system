<?php
// includes/sidebar.php - Reusable sidebar navigation
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <img src="assets/images/ethiopia-flag.png" alt="Ethiopian Flag" style="width:50px; height:auto; margin-bottom:8px; border-radius:3px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
        <h3>BEKKE AGALO KEBELE</h3>
        <p style="font-size:0.7em; color:#888; margin-top:3px;">Database Management System</p>
    </div>
    <ul class="nav-links">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="persons.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='persons.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Kebele Members</a></li>
        <li><a href="births.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='births.php' ? 'active' : ''; ?>"><i class="fas fa-baby"></i> Births</a></li>
        <li><a href="deaths.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='deaths.php' ? 'active' : ''; ?>"><i class="fas fa-cross"></i> Deaths</a></li>
        <li><a href="marriages.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='marriages.php' ? 'active' : ''; ?>"><i class="fas fa-ring"></i> Marriages</a></li>
        <li><a href="divorces.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='divorces.php' ? 'active' : ''; ?>"><i class="fas fa-file-contract"></i> Divorces</a></li>
        <li><a href="generate.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='generate.php' ? 'active' : ''; ?>"><i class="fas fa-certificate"></i> Certificates</a></li>
        <li><a href="analytics.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='analytics.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Analytics</a></li>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin'): ?>
        <li><a href="admin/users.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='users.php' ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i> User Mgmt</a></li>
        <?php endif; ?>
    </ul>
</nav>
