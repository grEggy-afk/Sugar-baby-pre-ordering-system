<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugar Baby - Sign Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Holtwood+One+SC&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="desktop-auth-page">

    <div class="auth-wrapper">
        <!-- Left Section - Branding -->
        <div class="auth-banner">
            <img src="images/SUGAR BABY 2.png" alt="Drink Icon" class="banner-drink-icon">
            <img src="images/Group 1.png" alt="Sugar Baby Text Logo" class="banner-text-logo">
        </div>

        <!-- Right Form Section -->
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="subtitle">Join Sugar Baby today</p>

            <form action="process_signup.php" method="POST" id="signupForm">
                <!-- Full Name Field -->
                <div class="input-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" name="fullname" id="fullname" placeholder="Juan Dela Cruz" required>
                </div>

                <!-- Phone Number Field -->
                <div class="input-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" placeholder="09123456789" required>
                </div>

                <!-- Email Field -->
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="example@gmail.com" required>
                </div>

                <!-- Password Field -->
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Create a strong password" required>
                </div>

                <button type="submit" class="primary-btn">SIGN UP</button>

                <div class="divider">
                    <span>OR</span>
                </div>

                <button type="button" class="google-btn" id="googleSignUpBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    Sign up with Google
                </button>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Log In</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>