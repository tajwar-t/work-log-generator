<?php

if (!function_exists('avatarUrl')) {
    /**
     * Returns the correct public URL for an avatar file stored in public/avatars/.
     *
     * On this Hostinger setup:
     *   - Files are at:  /public/avatars/filename.webp
     *   - APP_URL is:    https://domain.com  (no /public suffix)
     *   - So the URL is: https://domain.com/public/avatars/filename.webp
     *
     * We build the URL by appending /public/avatars/ to APP_URL directly,
     * bypassing asset() which does not know the /public/ prefix is needed.
     */
    function avatarUrl(string $filename): string
    {
        $base = rtrim(config('app.url'), '/');
        return $base . '/public/avatars/' . $filename;
    }
}