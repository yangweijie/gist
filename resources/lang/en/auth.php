<?php

return [
    // Page titles
    'titles' => [
        'login' => 'Sign in to your account',
        'register' => 'Create new account',
        'forgot_password' => 'Forgot Password',
        'reset_password' => 'Reset Password',
        'verify_email' => 'Verify Email',
        'two_factor' => 'Two Factor Authentication',
    ],

    // Form fields
    'fields' => [
        'name' => 'Name',
        'email' => 'Email Address',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'remember_me' => 'Remember Me',
        'verification_code' => 'Verification Code',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'Enter your name',
        'email' => 'Enter email address',
        'password' => 'Enter password',
        'password_confirmation' => 'Enter password again',
        'current_password' => 'Enter current password',
        'new_password' => 'Enter new password',
        'verification_code' => 'Enter verification code',
    ],

    // Button text
    'buttons' => [
        'login' => 'Sign In',
        'register' => 'Sign Up',
        'logout' => 'Sign Out',
        'forgot_password' => 'Forgot Password?',
        'reset_password' => 'Reset Password',
        'send_reset_link' => 'Send Reset Link',
        'verify_email' => 'Verify Email',
        'resend_verification' => 'Resend Verification Email',
        'github_login' => 'Sign in with GitHub',
        'github_register' => 'Sign up with GitHub',
        'back_to_login' => 'Back to Sign In',
        'back_to_register' => 'Back to Sign Up',
    ],

    // Link text
    'links' => [
        'already_registered' => 'Already have an account?',
        'not_registered' => 'Don\'t have an account?',
        'login_here' => 'Sign in here',
        'register_here' => 'Sign up here',
        'forgot_password' => 'Forgot your password?',
        'remember_password' => 'Remember your password?',
    ],

    // Success messages
    'success' => [
        'login' => 'Successfully signed in!',
        'register' => 'Registration successful!',
        'logout' => 'Successfully signed out',
        'password_reset' => 'Password reset successful!',
        'password_reset_sent' => 'Password reset link sent to your email',
        'email_verified' => 'Email verified successfully!',
        'verification_sent' => 'Verification email sent',
    ],

    // Error messages
    'errors' => [
        'failed' => 'These credentials do not match our records',
        'password' => 'The password is incorrect',
        'throttle' => 'Too many login attempts. Please try again in :seconds seconds',
        'email_not_verified' => 'Please verify your email address first',
        'account_disabled' => 'Your account has been disabled',
        'invalid_token' => 'Invalid or expired reset token',
        'email_not_found' => 'Email address not found',
        'weak_password' => 'Password is not strong enough',
        'password_mismatch' => 'Password confirmation does not match',
        'email_taken' => 'This email address is already in use',
        'github_error' => 'GitHub authorization failed',
        'github_email_taken' => 'This GitHub email is already used by another account',
    ],

    // Hints
    'hints' => [
        'password_requirements' => 'Password must be at least 8 characters with letters and numbers',
        'email_verification' => 'We have sent a verification link to your email',
        'password_reset_info' => 'Enter your email address and we will send you a reset link',
        'github_benefits' => 'Sign in with GitHub to sync your Gists',
        'secure_login' => 'We use secure encryption to protect your account',
    ],

    // GitHub related
    'github' => [
        'connect' => 'Connect GitHub',
        'disconnect' => 'Disconnect GitHub',
        'connected' => 'GitHub Connected',
        'not_connected' => 'GitHub Not Connected',
        'sync_gists' => 'Sync GitHub Gists',
        'import_gists' => 'Import GitHub Gists',
        'permissions' => 'Requires access to your GitHub Gist permissions',
        'username' => 'GitHub Username',
        'profile' => 'GitHub Profile',
    ],

    // Verification related
    'verification' => [
        'email_sent' => 'Verification email sent to :email',
        'email_verified' => 'Email verified',
        'email_not_verified' => 'Email not verified',
        'resend_email' => 'Resend verification email',
        'check_email' => 'Please check your email and click the verification link',
        'expired' => 'Verification link has expired',
        'invalid' => 'Invalid verification link',
    ],

    // Security related
    'security' => [
        'two_factor' => 'Two Factor Authentication',
        'enable_2fa' => 'Enable Two Factor Authentication',
        'disable_2fa' => 'Disable Two Factor Authentication',
        'backup_codes' => 'Backup Codes',
        'recovery_codes' => 'Recovery Codes',
        'authenticator_app' => 'Authenticator App',
        'scan_qr_code' => 'Scan QR Code',
        'enter_code' => 'Enter verification code',
        'invalid_code' => 'Invalid verification code',
        'codes_generated' => 'Backup codes generated',
        'save_codes' => 'Please save these backup codes',
    ],

    // Session related
    'session' => [
        'expired' => 'Session has expired, please sign in again',
        'invalid' => 'Invalid session',
        'timeout' => 'Session will expire soon',
        'extend' => 'Extend session',
        'active_sessions' => 'Active sessions',
        'logout_other_devices' => 'Sign out other devices',
        'current_device' => 'Current device',
        'last_activity' => 'Last activity',
    ],
];
