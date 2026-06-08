<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',


    //User registration messages:
    'register_success' => 'You have successfully registered. You may proceed to insert additional information.',
    'email_or_phone_already_registered' => 'The email or phone number you have provided have already been registered.',
    'phone_number_already_registered' => 'Phone number already exists in our records.',
    'verification_email_resent' => 'The email verification link has been resent to your email address.',

    //Worker registration messages:
    'employee_not_announced' => 'Your employer has not registered you as a worker yet.',
    'employee_already_registered' => 'A worker has already claimed this job.',
    'employee_identity_mismatch' => 'The first or last name does not match our records.',

    //Email verification email:
    'verify_email_subject' => 'Verify your email address',
    'hello' => 'Hello',
    'verify_email_body' => 'Please click the button below to verify your email address.',
    'verify_email_button' => 'Verify Email Address',
    'no_further_action' => 'If you did not create an account, no further action is required.',
    'regards' => 'Regards',
    'trouble_clicking' => 'If you\'re having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:',

    //Email verification messages:
    'email_verified' => 'Email verified successfully.',
    'email_not_verified' => 'Email not verified.',
    'email_already_verified' => 'Email is already verified.',
    'verification_sent' => 'Verification email sent.',
    'verification_sent_again' => 'Verification email sent again.',
    'invalid_verification_link' => 'Invalid verification link.',
    'expired_verification_link' => 'Verification link expired.',

    //User login messages:
    'user_not_found' => 'User not found.',
    'username_password_mismatch' => 'The username or password you entered is incorrect.',
    'account_rejected' => 'Your account was rejected due to suspicion of fraud or incorrect information.',
    'login_success' => 'You have successfully logged in.',

    //User logout messages:
    'logout_success' => 'Logged out successfully.',

    //User delete messages:
    'delete_success' => 'Deleted successfully.',
];
