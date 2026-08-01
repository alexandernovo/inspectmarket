<?php

if (!function_exists('barangay')) {
    function barangays()
    {
        return config('barangay');
    }
}

if (!function_exists('market_role_avatar')) {
    function market_role_avatar(?string $role, ?string $profile = null): string
    {
        if ($profile) {
            return asset('storage/'.$profile);
        }

        $avatar = match (strtoupper((string) $role)) {
            'ADMINISTRATOR' => 'A-Administrator.png',
            'TREASURER' => 'B-Treasurer.png',
            'CLERK' => 'C-Clerk.png',
            'INSPECTOR' => 'D-Inspector.png',
            'TENANT' => '5-Tenants.png',
            default => 'Logo.png',
        };

        return $avatar === 'Logo.png'
            ? asset('assets/einspect/HOMEPAGE/'.$avatar)
            : asset('assets/einspect/USERS/'.$avatar);
    }
}

if (!function_exists('market_notification_avatar')) {
    function market_notification_avatar(?string $type): string
    {
        $avatar = match (strtoupper((string) $type)) {
            'PAYMENT', 'STALL_APPLICATION' => 'B-Treasurer.png',
            'INSPECTION' => 'D-Inspector.png',
            'CASH_TICKET' => 'C-Clerk.png',
            'MESSAGE' => '5-Tenants.png',
            default => 'Logo.png',
        };

        return $avatar === 'Logo.png'
            ? asset('assets/einspect/HOMEPAGE/'.$avatar)
            : asset('assets/einspect/USERS/'.$avatar);
    }
}
