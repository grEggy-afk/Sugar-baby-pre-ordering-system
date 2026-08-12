<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugar Baby - Log In</title>
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
            <h2>Welcome!</h2>
            <p class="subtitle">Please enter your details to sign in.</p>

            <form action="process_login.php" method="POST" id="loginForm">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="example@gmail.com" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>

                <div class="forgot-pass-wrapper">
                    <a href="#" id="forgotPasswordBtn">Forgot Password?</a>
                </div>

                <button type="submit" class="primary-btn">LOG IN</button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <button type="button" class="google-btn" id="googleAuthBtn">
                <svg width="18" height="18" viewBox="0 0 18 18">
                    <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.259h2.908c1.702-1.567 2.684-3.874 2.684-6.617z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/>
                    <path fill="#FBBC05" d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z"/>
                    <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
                </svg>
                Sign in with Google
            </button>

            <button type="button" class="facebook-btn" id="facebookAuthBtn">
                <svg width="18" height="18" viewBox="0 0 24 24">
                    <path fill="white" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.019 4.388 11.009 10.125 11.927v-8.437H7.078v-3.49h3.047V9.413c0-3.017 1.792-4.687 4.533-4.687 1.313 0 2.686.235 2.686.235v2.963h-1.514c-1.491 0-1.956.929-1.956 1.882v2.267h3.328l-.532 3.49h-2.796V24C19.612 23.082 24 18.092 24 12.073z"/>
                </svg>
                Continue with Facebook
            </button>

            <div class="auth-footer">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>