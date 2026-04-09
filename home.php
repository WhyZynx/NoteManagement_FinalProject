<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="custom-navbar navbar navbar-expand-lg">
        <div class="logo">NotesFlow</div>

        <div class="d-flex align-items-center gap-3 nav-right">
            <div class="d-flex align-items-center gap-1 lang">
                <i class="fa-solid fa-globe"></i>
                <span>EN</span>
            </div>
            <a href="login.php" class="btn signUp-btn">Login</a>
            <a href="register.php" class="btn signUp-btn">Sign Up</a>
        </div>

    </nav>

    <header class="hero-section">
        <div class="hero-content">
            <div class="text-group">
                <h1 class="hero-title">Free your mind</h1>
                <p class="hero-subtitle">
                    Move your thoughts from your head to a safe space, exactly the way you imagined them.
                </p>
            </div>
            <a href="login.php" class="btn-start">Let's start</a>
        </div>
        
        <div class="hero-image">
            <img src="web_img/duck.png" alt="NotesFlow Illustration">
        </div>
    </header>

    <section class="features-intro">
        <div class="container">
            <div class="section-header">
                <h2>Make the most of your ideas and your time</h2>
                <p>Capture everything that is, was, or could be important and access it anytime, anywhere.</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-text">
                <h3>Deep Focus</h3>
                <p>Enjoy a distraction-free space designed to help you think clearly. Our minimalist interface stays out of your way so your creativity can flow.</p>
            </div>
            <div class="image-box box-cream">
                <img src="web_img/writing.png" alt="Focus">
            </div>
        </div>

        <div class="feature-item reverse">
            <div class="image-box box-mint">
                <img src="web_img/Mobile note list-amico.png" alt="Seamless">
            </div>
            <div class="feature-text">
                <h3>Seamless Flow</h3>
                <p>Your notes follow you everywhere. Whether you're on your phone or laptop, your latest thoughts are always synced and ready when you are.</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-text">
                <h3>Total Privacy</h3>
                <p>Rest easy knowing your personal ideas are encrypted and locked. Your data is yours alone, kept safe with industry-standard security.</p>
            </div>
            <div class="image-box box-blue">
                <img src="web_img/GDPR-amico.png" alt="Privacy">
            </div>
        </div>
    </section>
</main>
</body>
</html>