<?php

return [
    'view_path' => 'vendor.accountflow.',
    'layout' => 'layouts.app',
    
    // Define middlewares to be used in the route file
    'middlewares' => [
        'auth',          // Example middleware for authentication
        'verified',      // Example middleware for email verification
        'role:admin',    // Example middleware for role-based access control
    ],
];
