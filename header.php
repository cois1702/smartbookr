<?php
// This file assumes $business_name, $business_logo, and $business_color are already defined in your session or PHP page
?>

<header>
    <div style="display:flex; align-items:center;">
        <?php if(!empty($business_logo)): ?>
            <img src="<?php echo htmlspecialchars($business_logo); ?>" alt="<?php echo htmlspecialchars($business_name); ?> Logo">
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($business_name); ?> Dashboard</h1>
    </div>
    <a href="logout.php">Logout</a>
</header>

<!-- Dynamic branding color -->
<style>
    :root { --primary-color: <?php echo htmlspecialchars($business_color ?? '#0077cc'); ?>; }
</style>
