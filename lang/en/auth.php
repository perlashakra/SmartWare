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

    //Worker registration messages:
    'employee_not_announced' => 'Your employer has not registered you as a worker yet.',
    'employee_already_registered' => 'A worker has already claimed this job.',
    'employee_identity_mismatch' => 'The first or last name does not match our records.',

    //Email verification messages:
    'email_verified' => 'Email verified successfully.',
    'verification_sent' => 'Verification email sent.',

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
