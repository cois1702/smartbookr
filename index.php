<?php
// index.php (The Dynamic Business Directory/Landing Page)
include "db.php"; 

try {
    // Fetch ID, name, logo_url, and brand_color for all businesses with a logo
    $stmt = $pdo->query("SELECT id, business_name, logo_url, brand_color FROM businesses WHERE logo_url IS NOT NULL AND logo_url != ''");
    $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error, but proceed to display the page without the directory if the fetch fails
    error_log("Error fetching businesses for directory: " . $e->getMessage());
    $businesses = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartBookr — Business Selector</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* CSS specific to the Business Selector Layout */
        body { margin-top: 0; } 
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; text-align: center; }
        
        /* New Header Style for a Directory */
        header { 
            background: #fff;
            padding: 15px 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            justify-content: space-between;
            display: flex; 
            align-items: center;
        }
        header h1 { color: #0077cc; margin: 0; }
        header nav a { 
            background: none; 
            color: #0077cc; 
            padding: 0; 
            border-radius: 0; 
            font-weight: bold;
        }

        /* Directory Grid - Set up for 4 columns */
        .business-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 30px; /* Space between cards */
            justify-content: flex-start; /* Start from the left */
            margin-top: 50px;
        }
        .business-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            
            /* FINAL PADDING ADJUSTMENT: Outside space reduced to 5px */
            padding: 5px; 
            
            /* Keep 4-column layout */
            width: calc(25% - 22.5px); 
            
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden; 
        }
        .business-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .business-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .business-logo {
            /* FINAL LOGO SIZE ADJUSTMENT: Increased max-height to 180px */
            max-height: 180px; 
            max-width: 100%;
            height: auto;
            margin-bottom: 10px; 
            border-radius: 5px;
            object-fit: contain;
        }
        .business-name {
            font-size: 1em; 
            font-weight: bold;
            color: #333;
            padding-top: 5px;
            border-top: 1px solid #eee;
        }
        
        /* Responsive: Fallback to 2 columns on medium screens and 1 column on small screens */
        @media(max-width: 992px) {
            .business-card {
                 width: calc(50% - 15px); /* 2 columns */
            }
        }
        @media(max-width: 576px) {
            header { padding: 15px 20px; }
            .business-card { 
                width: 100%; /* 1 column */
                max-width: 350px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>SmartBookr</h1>
        <nav>
            <a href="login.php">Business Login</a>
        </nav>
    </header>
    
    <div class="container">
        
        <h2>Select a Business to Book an Appointment:</h2>

        <?php if (!empty($businesses)): ?>
            <div class="business-grid">
                <?php foreach ($businesses as $biz): ?>
                    <?php 
                        // Link the card to the booking page using the business ID
                        $booking_url = 'booking.php?business_id=' . $biz['id'];
                        // Use brand color for accent
                        $card_style = !empty($biz['brand_color']) ? 'border-top: 5px solid ' . htmlspecialchars($biz['brand_color']) . ';' : '';
                    ?>
                    <div class="business-card" style="<?php echo $card_style; ?>">
                        <a href="<?php echo $booking_url; ?>">
                            <?php if (!empty($biz['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($biz['logo_url']); ?>" alt="<?php echo htmlspecialchars($biz['business_name']); ?> Logo" class="business-logo">
                            <?php else: ?>
                                <div class="business-logo" style="height: 180px; line-height: 180px; background: #eee; font-size: 0.8em;">No Logo</div>
                            <?php endif; ?>
                            <div class="business-name"><?php echo htmlspecialchars($biz['business_name']); ?></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <section>
                <h2>Smart Online Booking & Reminders</h2>
                <p>Manage appointments easily for your business. Try our free demo or sign up now!</p>
                <a href="signup.php" class="cta">Get Started</a>
            </section>
        <?php endif; ?>

        <footer style="margin-top: 60px; padding-bottom: 20px;">
            <p>© SmartBookr</p>
        </footer>
    </div>
</body>
</html>