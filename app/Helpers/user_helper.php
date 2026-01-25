<?php

if (! function_exists('generateUserShortName')) {
    function generateUserShortName( $user ) {
        $first_name = $user['firstname'] ?? '';
        $last_name = $user['lastname'] ?? '';

        return strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    }
}

if (! function_exists('generateUserFullName')) {
    function generateUserFullName( $user ) {
        $first_name = $user['firstname'] ?? '';
        $middle_name = $user['middlename'] ?? '';
        $last_name = $user['lastname'] ?? '';

        return  $first_name . ' ' . $middle_name . ' ' . $last_name;
    }
}