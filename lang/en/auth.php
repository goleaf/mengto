<?php

declare(strict_types=1);

return [
    'brand' => 'PawCircle',
    'guest' => 'Guest',
    'logout' => 'Sign out',
    'connection' => [
        'offline' => 'You are offline. Changes cannot be submitted until the connection returns.',
    ],
    'form' => [
        'unsaved' => 'You have unsaved changes.',
    ],
    'accessibility' => [
        'skip_to_content' => 'Skip to content',
    ],
    'fields' => [
        'name' => 'Name',
        'email' => 'Email address',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'locale' => 'Language',
        'timezone' => 'Time zone',
    ],
    'locales' => [
        'en' => 'English',
        'lt' => 'Lithuanian',
        'ru' => 'Russian',
    ],
    'login' => [
        'title' => 'Sign in',
        'description' => 'Use your account to access private pet care, health, and device information.',
        'failed' => 'These credentials do not match an active account.',
        'account_unavailable' => 'This account cannot access protected features.',
        'throttled' => 'Too many attempts. Try again in :seconds seconds.',
        'forgot_password' => 'Forgot password?',
        'remember' => 'Keep me signed in',
        'submit' => 'Sign in',
        'submitting' => 'Signing in…',
        'no_account' => 'New to PawCircle?',
        'register' => 'Create an account',
    ],
    'register' => [
        'title' => 'Create your account',
        'description' => 'Your private information stays restricted to people you authorize.',
        'timezone_help' => 'Use an IANA time zone such as Europe/Vilnius.',
        'password_help' => 'Use at least 12 characters with uppercase, lowercase, and a number.',
        'submit' => 'Create account',
        'submitting' => 'Creating account…',
        'has_account' => 'Already have an account?',
        'login' => 'Sign in',
    ],
    'password' => [
        'forgot_title' => 'Reset your password',
        'forgot_description' => 'Enter your email. If an account exists, we will send a secure reset link.',
        'link_sent' => 'If an account exists for that address, a password reset link has been sent.',
        'send_link' => 'Send reset link',
        'sending' => 'Sending…',
        'back_to_login' => 'Back to sign in',
        'reset_title' => 'Choose a new password',
        'reset_description' => 'Enter the email associated with the reset link and choose a strong password.',
        'reset_submit' => 'Reset password',
        'resetting' => 'Resetting…',
        'reset_success' => 'Your password has been reset. You can now sign in.',
    ],
    'confirm_password' => [
        'title' => 'Confirm your password',
        'description' => 'Enter your password again before continuing to this protected action.',
        'failed' => 'The provided password does not match your account.',
        'throttled' => 'Too many attempts. Try again in :seconds seconds.',
        'submit' => 'Confirm password',
        'submitting' => 'Confirming…',
    ],
    'verification' => [
        'title' => 'Verify your email',
        'description' => 'Open the verification link sent to your email before using protected features.',
        'resend' => 'Send another verification email',
        'sending' => 'Sending…',
        'sent' => 'A new verification email has been sent.',
        'success' => 'Your email address has been verified.',
    ],
];
