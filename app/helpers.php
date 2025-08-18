<?php

if (!function_exists('app_setting')) {
    /**
     * Get application setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function app_setting(string $key, $default = null)
    {
        try {
            return \App\Models\Setting::get($key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('app_settings')) {
    /**
     * Get all application settings for views
     *
     * @return array
     */
    function app_settings(): array
    {
        try {
            return [
                'app_name' => app_setting('app_name', config('app.name', 'POS System')),
                'app_logo' => app_setting('app_logo'),
                'company_name' => app_setting('company_name', 'Your Company Name'),
                'company_address' => app_setting('company_address', 'Your Company Address'),
                'company_phone' => app_setting('company_phone', 'Your Phone Number'),
                'receipt_footer' => app_setting('receipt_footer', 'Terima kasih atas kunjungan Anda!')
            ];
        } catch (\Exception $e) {
            return [
                'app_name' => config('app.name', 'POS System'),
                'app_logo' => null,
                'company_name' => 'Your Company Name',
                'company_address' => 'Your Company Address',
                'company_phone' => 'Your Phone Number',
                'receipt_footer' => 'Terima kasih atas kunjungan Anda!'
            ];
        }
    }
}