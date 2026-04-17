<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$pref = getPreferences($conn, $userId);


$savedColor = $pref['theme_color'] ?? '#0d6efd';
$colors = explode('|', $savedColor);
$c1 = $colors[0];
$c2 = $colors[1] ?? $c1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - MindFlow</title>
    <link rel="stylesheet" href="../Assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        .content { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding-top: 50px; }
        
        .settings-layout { display: flex; width: 100%; max-width: 650px; justify-content: center; z-index: 2; position: relative;}
        .settings-col { flex: 1; width: 100%; }
        

        .settings-glass-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        body.dark .settings-glass-card {
            background: rgba(30, 34, 51, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .settings-glass-card h3 { font-size: 1.3rem; font-weight: 600; margin-bottom: 30px; text-align: center; color: var(--text-dark, #333); }
        body.dark .settings-glass-card h3 { color: #fff; }

        .theme-toggle-container { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; }
        .theme-row { display: grid; gap: 15px; }
        .theme-row.row-2 { grid-template-columns: 1fr 1fr; }
        .theme-row.row-3 { grid-template-columns: 1fr 1fr 1fr; }
        
        .theme-row label { cursor: pointer; position: relative; }
        .theme-row input[type="radio"] { display: none; }
        .theme-row span { 
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
            padding: 15px 10px; border-radius: 20px; font-size: 0.95rem; font-weight: 600; 
            color: #666; background: rgba(255, 255, 255, 0.5); transition: 0.3s;
            border: 2px solid transparent;
        }
        body.dark .theme-row span { color: #aaa; background: rgba(0, 0, 0, 0.2); }
        

        .theme-row input[type="radio"]:checked + span { 
            background: #fff; color: var(--user-color, #0d6efd); 
            border-color: var(--user-color, #0d6efd);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); transform: translateY(-3px);
        }
        body.dark .theme-row input[type="radio"]:checked + span { 
            background: #2b303e; color: var(--user-color, #fff); 
        }

        .theme-row span i { font-size: 1.2rem; }

        .btn-modern-save {
            background: var(--user-color, #0d6efd) !important;
            color: #fff !important;
            border: none; padding: 15px 35px; border-radius: 50px; width: 100%;
            font-size: 1.05rem; font-weight: 600; cursor: pointer; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important; transition: 0.3s; margin-top: 10px;
        }
        .btn-modern-save:hover { filter: brightness(90%); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important; }


        .color-picker-wrap { 
            display: flex; flex-direction: column; gap: 20px; 
            background: rgba(255,255,255,0.7); padding: 25px; border-radius: 25px; 
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 25px; 
        }
        body.dark .color-picker-wrap { background: rgba(0,0,0,0.2); }
        
        .color-inputs { display: flex; align-items: center; justify-content: center; gap: 20px; width: 100%; }
        
        input[type="color"] { width: 55px; height: 55px; border-radius: 50%; border: none; cursor: pointer; padding: 0; background: transparent; transition: 0.2s; }
        input[type="color"]:hover { transform: scale(1.1); }
        input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
        input[type="color"]::-webkit-color-swatch { border: 3px solid #fff; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
    </style>
</head>

<body class="<?= htmlspecialchars($pref['theme_mode']) ?>">

    <div class="main-wrapper">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <?php include "sidebar.php"; ?>

        <div class="content">
            <h2 class="page-title" style="margin-bottom: 30px; text-align: center; z-index: 2;">Themes Settings</h2>

            <form action="save_preferences.php" method="POST" style="width: 100%; display: flex; justify-content: center;">
                <input type="hidden" name="font_size" value="<?= htmlspecialchars($pref['font_size'] ?? 16) ?>">
                <input type="hidden" name="font_style" value="<?= htmlspecialchars($pref['font_style'] ?? "'Inter', sans-serif") ?>">

                <div class="settings-layout">
                    <div class="settings-col">
                        
                        <div class="settings-glass-card">
                            <h3>Choose Your Vibe</h3>

                            <div class="theme-toggle-container">
                                <div class="theme-row row-2">
                                    <label>
                                        <input type="radio" name="theme_mode" value="light" <?= $pref['theme_mode']=='light'?'checked':'' ?> onchange="toggleMode(this.value)">
                                        <span><i class="fa-solid fa-sun"></i> Light</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="theme_mode" value="dark" <?= $pref['theme_mode']=='dark'?'checked':'' ?> onchange="toggleMode(this.value)">
                                        <span><i class="fa-solid fa-moon"></i> Dark</span>
                                    </label>
                                </div>
                                
                                <div class="theme-row row-3">
                                    <label>
                                        <input type="radio" name="theme_mode" value="hologram" <?= $pref['theme_mode']=='hologram'?'checked':'' ?> onchange="toggleMode(this.value)">
                                        <span><i class="fa-solid fa-wand-magic-sparkles"></i> Holo</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="theme_mode" value="custom" <?= $pref['theme_mode']=='custom'?'checked':'' ?> onchange="toggleMode(this.value)">
                                        <span><i class="fa-solid fa-palette"></i> Custom</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="theme_mode" value="gradient" <?= $pref['theme_mode']=='gradient'?'checked':'' ?> onchange="toggleMode(this.value)">
                                        <span><i class="fa-solid fa-layer-group"></i> Gradient</span>
                                    </label>
                                </div>
                            </div>

                            <div id="customColorArea" class="color-picker-wrap" style="display: <?= in_array($pref['theme_mode'], ['custom', 'gradient']) ? 'flex' : 'none' ?>;">
                                <span id="colorTitle" style="font-size: 1em; font-weight: 600; color: var(--text-dark, #333); text-align: center;">
                                    <?= $pref['theme_mode'] == 'gradient' ? 'Gradient color scheme' : 'Choose a custom solid color' ?>
                                </span>
                                
                                <div class="color-inputs">
                                    <input type="color" id="c1" value="<?= htmlspecialchars($c1) ?>" oninput="updateColor()">
                                    <i class="fas fa-arrow-right" id="gradArrow" style="color: #999; display: <?= $pref['theme_mode'] == 'gradient' ? 'block' : 'none' ?>;"></i>
                                    <input type="color" id="c2" value="<?= htmlspecialchars($c2) ?>" oninput="updateColor()" style="display: <?= $pref['theme_mode'] == 'gradient' ? 'block' : 'none' ?>;">
                                </div>
                                
                                <button type="button" id="aiBtn" style="display: <?= $pref['theme_mode'] == 'gradient' ? 'block' : 'none' ?>; border: 2px dashed var(--user-color, #0d6efd); background: rgba(255,255,255,0.5); color: var(--user-color, #0d6efd); padding: 12px; border-radius: 50px; cursor: pointer; font-size: 0.9rem; font-weight: bold; transition: 0.3s;" onclick="autoGradient()">
                                    <i class="fas fa-magic"></i> AI palette
                                </button>

                                <input type="hidden" name="theme_color" id="finalColor" value="<?= htmlspecialchars($savedColor) ?>">
                            </div>

                            <button type="submit" class="btn-modern-save"><i class="fa-solid fa-check"></i> Save Settings</button>
                        </div>
                        </div>
                </div>
            </form>
            <img src="../Assets/images/web_img/ocean.png" alt="ocean" class="ocean-bg">
        </div>
    </div>

    <script>
        function toggleMode(val) {
            document.body.className = val;

            const area = document.getElementById('customColorArea');
            const c2 = document.getElementById('c2');
            const arrow = document.getElementById('gradArrow');
            const aiBtn = document.getElementById('aiBtn');
            const title = document.getElementById('colorTitle');

            if (val === 'custom' || val === 'gradient') {
                area.style.display = 'flex';
                if (val === 'gradient') {
                    title.innerText = 'Gradient color scheme';
                    c2.style.display = 'block';
                    arrow.style.display = 'block';
                    aiBtn.style.display = 'block';
                } else {
                    title.innerText = 'Choose a custom solid color';
                    c2.style.display = 'none';
                    arrow.style.display = 'none';
                    aiBtn.style.display = 'none';
                }
                updateColor();
            } else {
                area.style.display = 'none';
                let previewStyle = document.getElementById('live-preview-css');
                if (previewStyle) previewStyle.innerHTML = '';
                document.documentElement.style.removeProperty('--user-color');
                document.documentElement.style.removeProperty('--user-color-2');
            }
        }

        function updateColor() {
            const mode = document.querySelector('input[name="theme_mode"]:checked').value;
            const c1 = document.getElementById('c1').value;
            const c2 = document.getElementById('c2').value;
            
            let previewStyle = document.getElementById('live-preview-css');
            if(!previewStyle) {
                previewStyle = document.createElement('style');
                previewStyle.id = 'live-preview-css';
                document.head.appendChild(previewStyle);
            }
            
            if (mode === 'gradient') {
                document.getElementById('finalColor').value = c1 + '|' + c2;
                document.documentElement.style.setProperty('--user-color', c1);
                document.documentElement.style.setProperty('--user-color-2', c2); 
                
                previewStyle.innerHTML = `
                    body.gradient, body.gradient .main-wrapper, body.gradient .content { 
                        background: linear-gradient(135deg, ${c1} 0%, ${c2} 100%) !important; 
                        background-attachment: fixed !important;
                    }
                `;
            } else if (mode === 'custom') {
                document.getElementById('finalColor').value = c1;
                document.documentElement.style.setProperty('--user-color', c1);
                document.documentElement.style.removeProperty('--user-color-2');
                
                previewStyle.innerHTML = `
                    body.custom, body.custom .main-wrapper, body.custom .content { 
                        background-color: color-mix(in srgb, ${c1} 12%, #ffffff) !important; 
                    }
                `;
            }
        }

        function autoGradient() {
            const hex = document.getElementById('c1').value;
            
            let r = parseInt(hex.substring(1,3), 16) / 255;
            let g = parseInt(hex.substring(3,5), 16) / 255;
            let b = parseInt(hex.substring(5,7), 16) / 255;
            let max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;

            if(max == min){
                h = s = 0;
            } else {
                let d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch(max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                h /= 6;
            }

            h = (h + (40/360)) % 1; 

            let r1, g1, b1;
            if(s == 0) { r1 = g1 = b1 = l; } 
            else {
                let hue2rgb = function hue2rgb(p, q, t){
                    if(t < 0) t += 1;
                    if(t > 1) t -= 1;
                    if(t < 1/6) return p + (q - p) * 6 * t;
                    if(t < 1/2) return q;
                    if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                }
                let q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                let p = 2 * l - q;
                r1 = hue2rgb(p, q, h + 1/3);
                g1 = hue2rgb(p, q, h);
                b1 = hue2rgb(p, q, h - 1/3);
            }
            
            let toHex = function(x) {
                let hex = Math.round(x * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            
            let c2Hex = "#" + toHex(r1) + toHex(g1) + toHex(b1);
            document.getElementById('c2').value = c2Hex;
            updateColor();
        }

        window.onload = function() {
            if(document.querySelector('input[name="theme_mode"]:checked').value === 'custom' || document.querySelector('input[name="theme_mode"]:checked').value === 'gradient') {
                updateColor();
            }
        }
    </script>
    <script src="../Assets/js/sidebar.js"></script>
</body>
</html>