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

if (!function_exists('market_einspect_asset')) {
    function market_einspect_asset(string $path): string
    {
        return asset('assets/einspect/'.$path);
    }
}

if (!function_exists('market_section_image')) {
    function market_section_image(?string $section, string $module = 'HOMEPAGE'): string
    {
        $sectionName = str((string) $section)->trim()->title()->toString();
        if ($sectionName === '') {
            $sectionName = 'Fish';
        }

        $filename = $sectionName.' Section.png';
        $candidate = public_path('assets/einspect/'.$module.'/IMAGES/'.$filename);

        return file_exists($candidate)
            ? asset('assets/einspect/'.$module.'/IMAGES/'.$filename)
            : asset('assets/einspect/HOMEPAGE/'.$filename);
    }
}

if (!function_exists('market_collector_image')) {
    function market_collector_image(int|string|null $number = 1, string $module = 'CLERK'): string
    {
        $collectorNumber = max(1, min(8, (int) $number));
        $candidate = public_path("assets/einspect/{$module}/IMAGES/Collector {$collectorNumber}.png");

        return file_exists($candidate)
            ? asset("assets/einspect/{$module}/IMAGES/Collector {$collectorNumber}.png")
            : asset('assets/einspect/USERS/3-Collector Clerk.png');
    }
}
