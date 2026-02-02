<?php
require_once __DIR__ . '/../../config/dbcon.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    // Inside your PHP block at the top
$maint_message = $settings['maint_message'] ?? "We're perfecting your experience.";
$recovery_time = $settings['recovery_time'] ?? ''; 
// If start_time is missing from DB, we fallback to 1 hour before recovery_time
$start_time    = $settings['maint_start_time'] ?? date('Y-m-d H:i:s', strtotime($recovery_time . ' -1 hour'));

} catch (Exception $e) {
    $maint_message = "System optimization in progress.";
    $recovery_time = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Upgrade | Empire Shop</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <style>
        :root {
            --brand-dark: #0f172a;
            --brand-gold: #c5a059;
            --brand-gold-light: #dfc28d;
            --text-muted: #64748b;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--brand-dark);
            margin: 0;
            overflow: hidden;
        }

        /* Animated Background Orbs for Depth */
        .bg-orb {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
            animation: move 20s infinite alternate;
        }
        .orb-1 { background: var(--brand-gold); top: -10%; left: -10%; }
        .orb-2 { background: #cbd5e1; bottom: -10%; right: -10%; animation-delay: -5s; }

        @keyframes move {
            from { transform: translate(0, 0); }
            to { transform: translate(100px, 100px); }
        }

        .maint-card {
            max-width: 500px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.1);
            border-radius: 32px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            padding: 3.5rem 2.5rem;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: var(--brand-dark);
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .status-dot {
            height: 8px;
            width: 8px;
            background-color: #10b981; /* Green implies "working/active" */
            border-radius: 50%;
            margin-right: 10px;
            position: relative;
        }

        .status-dot::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: inherit;
            border-radius: 50%;
            animation: pulse-ring 1.5s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.7); opacity: 1; }
            100% { transform: scale(2.5); opacity: 0; }
        }

        .icon-box {
            position: relative;
            margin-bottom: 2rem;
            display: inline-block;
        }

        .svg-icon {
            width: 80px;
            height: 80px;
            color: var(--brand-gold);
            stroke-width: 1.25;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h2 {
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 1rem;
            color: var(--brand-dark);
            font-size: 2rem;
        }

        p {
            font-size: 1rem;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        /* Refined Progress Bar */
        .progress-wrapper {
            background: #f1f5f9;
            height: 6px;
            border-radius: 100px;
            margin: 2.5rem 0;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            width: 65%;
            background: linear-gradient(90deg, var(--brand-gold), var(--brand-gold-light));
            border-radius: 100px;
            transition: width 0.5s ease;
        }

        .countdown {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            color: var(--brand-dark);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            display: inline-block;
        }

        .footer-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.8;
        }

        .contact-link {
            color: var(--brand-gold);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .contact-link:hover { color: var(--brand-dark); }

        @media (max-width: 576px) {
            .maint-card { padding: 2.5rem 1.5rem; }
            h2 { font-size: 1.6rem; }
        }
    </style>    
</head>
<body>
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>

    <div class="maint-card">
        <div class="status-badge">
            <span class="status-dot"></span> Optimization Active
        </div>

        <h2>Refining Empire Shop</h2>
        <p><?php echo htmlspecialchars($maint_message); ?></p>

        <div class="fw-bold mb-2" style="font-size: 0.9rem; color: var(--brand-gold);">
            <span id="percent-text">0</span>% Complete
        </div>

        <div class="progress-wrapper">
            <div class="progress-fill" id="progress-bar" style="position: relative;">
                <div class="shimmer"></div>
            </div>
        </div>

        <div class="countdown" id="countdown">Initializing...</div>

        <div class="footer-text">
            Expected Recovery: <strong><?php echo !empty($recovery_time) ? date("h:i A", strtotime($recovery_time)) : 'TBD'; ?></strong><br>
            Inquiry: <a href="mailto:empirebsit2025@gmail.com" class="contact-link">empirebsit2025@gmail.com</a>
        </div>
    </div>

    <style>
        /* Add this to your <style> section for the "Loading" shimmer effect */
        .progress-fill {
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        
        .shimmer {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: loading-shimmer 2s infinite;
        }

        @keyframes loading-shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>

    <script>
        const startTime = new Date("<?php echo $start_time; ?>").getTime();
        const endTime = new Date("<?php echo $recovery_time; ?>").getTime();
        
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progress-bar');
        const percentText = document.getElementById('percent-text');

        function updateUI() {
            const now = new Date().getTime();
            const totalDuration = endTime - startTime;
            const timeElapsed = now - startTime;
            
            // Calculate Percentage
            let percentage = (timeElapsed / totalDuration) * 100;
            percentage = Math.max(0, Math.min(percentage, 100));
            
            // Update UI elements
            if (progressBar) progressBar.style.width = percentage + "%";
            if (percentText) percentText.innerText = Math.floor(percentage);

            // Calculate Countdown
            const remaining = endTime - now;

            if (remaining > 0) {
                const h = String(Math.floor(remaining / (1000 * 60 * 60))).padStart(2, '0');
                const m = String(Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                const s = String(Math.floor((remaining % (1000 * 60)) / 1000)).padStart(2, '0');
                countdownElement.innerHTML = `${h}:${m}:${s}`;
            } else {
                countdownElement.innerHTML = "SYSTEM READY";
                if (percentText) percentText.innerText = "100";
                setTimeout(() => window.location.reload(), 2000);
            }
        }

        if (endTime && startTime) {
            setInterval(updateUI, 1000);
            updateUI();
        } else {
            countdownElement.innerHTML = "PREPARING...";
        }
    </script>
</body>