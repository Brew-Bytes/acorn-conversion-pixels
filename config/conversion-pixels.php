<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    */

    'enabled' => env('PIXELS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    */

    'environments' => ['production'],

    /*
    |--------------------------------------------------------------------------
    | Privacy guards
    |--------------------------------------------------------------------------
    */

    'exclude_logged_in' => true,
    'respect_dnt' => true,

    /*
    |--------------------------------------------------------------------------
    | Consent gating — REQUIRED by default
    |--------------------------------------------------------------------------
    |
    | Unlike acorn-analytics, pixels default to consent.required => true.
    | Advertising / conversion pixels almost universally require explicit
    | marketing-bucket opt-in under GDPR / CCPA / similar regulations.
    | No pixel scripts inject and no events forward until the configured
    | JS event fires on `window`.
    |
    | Set `required => false` only if you have legitimate-interest grounds
    | or your consent UI handles the gating before this package even loads.
    |
    */

    'consent' => [
        'required' => true,
        'event' => 'cookie-consent:granted',
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Pixel (Facebook / Instagram)
    |--------------------------------------------------------------------------
    */

    'meta-pixel' => [
        'id' => env('META_PIXEL_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LinkedIn Insight Tag
    |--------------------------------------------------------------------------
    |
    | LinkedIn's Insight Tag is conversion-ID-driven: configure conversions
    | in LinkedIn Campaign Manager, then map your event names to those
    | conversion IDs here. The bridge fires `lintrk('track', { conversion_id })`
    | when a mapped event arrives.
    |
    */

    'linkedin-insight' => [
        'id' => env('LINKEDIN_PARTNER_ID'),
        'conversions' => [
            // 'newsletter_signup' => 12345678,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TikTok Pixel
    |--------------------------------------------------------------------------
    */

    'tiktok-pixel' => [
        'id' => env('TIKTOK_PIXEL_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pinterest Tag
    |--------------------------------------------------------------------------
    */

    'pinterest-tag' => [
        'id' => env('PINTEREST_TAG_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | X (Twitter) Pixel
    |--------------------------------------------------------------------------
    */

    'x-pixel' => [
        'id' => env('X_PIXEL_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reddit Pixel
    |--------------------------------------------------------------------------
    */

    'reddit-pixel' => [
        'id' => env('REDDIT_PIXEL_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Snap Pixel
    |--------------------------------------------------------------------------
    */

    'snap-pixel' => [
        'id' => env('SNAP_PIXEL_ID'),
    ],

];
