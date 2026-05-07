# Acorn Conversion Pixels

A theme-agnostic [Acorn](https://roots.io/acorn/) package that wires advertising
pixels for [Sage](https://roots.io/sage/) themes — Meta, LinkedIn, TikTok,
Pinterest, X (Twitter), Reddit, and Snap. Drop in your IDs, ship to production,
no per-project script tag boilerplate.

Built as a sibling to [`brew-bytes/acorn-analytics`](https://github.com/Brew-Bytes/acorn-analytics):
fire `analytics:event` once and the unified bridge fans the event out to every
configured pixel.

## What it does

| Pixel | Global | Env var | Custom event API |
| --- | --- | --- | --- |
| Meta (Facebook / Instagram) | `fbq` | `META_PIXEL_ID` | `fbq('trackCustom', name, data)` |
| LinkedIn Insight | `lintrk` | `LINKEDIN_PARTNER_ID` | conversion-ID routed (see config) |
| TikTok | `ttq` | `TIKTOK_PIXEL_ID` | `ttq.track(name, data)` |
| Pinterest | `pintrk` | `PINTEREST_TAG_ID` | `pintrk('track', name, data)` |
| X (Twitter) | `twq` | `X_PIXEL_ID` | `twq('event', name, data)` |
| Reddit | `rdt` | `REDDIT_PIXEL_ID` | `rdt('track', name, data)` |
| Snap | `snaptr` | `SNAP_PIXEL_ID` | `snaptr('track', name, data)` |

Empty/missing IDs no-op silently — only configured pixels render.

## Differences from `acorn-analytics`

This package shares the same modular shape and depends on `acorn-analytics` for
the `analytics:event` bridge, but it's deliberately stricter on consent:

- **`consent.required => true` by default.** Pixel scripts don't inject and
  events don't forward until the configured JS event fires on `window`.
  Advertising / conversion pixels almost universally require explicit
  marketing-bucket opt-in under GDPR / CCPA / similar regs.
- **No noscript fallback in consent-required mode.** The `<noscript>` image
  fallback for Meta Pixel would fire unconditionally — opposite of what
  consent gating is supposed to do.

## Installation

```bash
composer require brew-bytes/acorn-conversion-pixels
```

`acorn-analytics` comes along automatically as a dependency. Add the IDs you
want to your `.env`:

```env
META_PIXEL_ID=123456789012345
LINKEDIN_PARTNER_ID=1234567
TIKTOK_PIXEL_ID=ABCDEF1234567890
```

## Custom events

Same `analytics:event` API as `acorn-analytics`:

```js
window.dispatchEvent(new CustomEvent('analytics:event', {
    detail: { name: 'purchase', value: 49.99 }
}));
```

…or:

```js
window.AcornAnalytics.track('purchase', { value: 49.99 });
```

The bridge fans events out to every pixel that's loaded:

- **Meta:** `fbq('trackCustom', 'purchase', { value: 49.99 })`
- **TikTok:** `ttq.track('purchase', { value: 49.99 })`
- **Pinterest:** `pintrk('track', 'purchase', { value: 49.99 })`
- **X:** `twq('event', 'purchase', { value: 49.99 })`
- **Reddit:** `rdt('track', 'purchase', { value: 49.99 })`
- **Snap:** `snaptr('track', 'purchase', { value: 49.99 })`

### Standard event names

Each platform has its own "standard event" vocabulary (Meta has `Purchase`,
TikTok has `CompletePayment`, etc.). For v0.1 the package passes event names
through verbatim — manage name translation in each platform's dashboard:

- [Meta Events Manager](https://www.facebook.com/events_manager)
- [TikTok Events Manager](https://ads.tiktok.com/i18n/events_manager)
- [Pinterest Tag Events Setup](https://help.pinterest.com/business/article/pinterest-tag)

A future v0.2 may add an explicit `mappings` config block for stricter
platform-side standard-event matching.

### LinkedIn (special case)

LinkedIn's Insight Tag doesn't have a generic custom-events API — conversions
are configured **by ID** in LinkedIn Campaign Manager and fired via
`lintrk('track', { conversion_id })`. Map your event names to LinkedIn
conversion IDs in the config:

```php
'linkedin-insight' => [
    'id' => env('LINKEDIN_PARTNER_ID'),
    'conversions' => [
        'newsletter_signup' => 12345678,
        'purchase' => 87654321,
    ],
],
```

The package outputs a small dispatcher that routes mapped events to the
correct conversion ID. Unmapped events simply don't fire to LinkedIn.

## Cookie consent integration

Defaults to `consent.required => true`. Configure the JS event your consent
UI emits:

```php
'consent' => [
    'required' => true,
    'event' => 'cookie-consent:granted',
],
```

Common consent platforms:

| Platform     | Event name                  |
| ------------ | --------------------------- |
| Custom UI    | `cookie-consent:granted`    |
| OneTrust     | `OneTrustGroupsUpdated`     |
| Cookiebot    | `CookiebotOnAccept`         |
| Cookieyes    | `cookieyes:accepted`        |

To opt out of consent gating (e.g., your consent UI handles loading order
itself), set `consent.required => false`. **Don't ship advertising pixels
without consent gating in GDPR/CCPA jurisdictions** unless you have legal
clearance.

## A note on PII

The bridge passes the `analytics:event` `detail` payload through to each
pixel verbatim. If you're using `acorn-analytics`'s auto-tracking module,
the default `phone_click` and `email_click` events include the literal
phone number / email address from the clicked link.

For a typical business site this is your **own** published contact info —
not visitor PII. For sites where contact links come from user input
(vCard exports, marketplace seller profiles, etc.), those values *would*
be visitor PII. Meta in particular flags PII in custom event params and
can throttle / suspend pixels that send unhashed contact info.

If your site mixes user-input contact links with pixel forwarding, either
disable the relevant `auto-tracking` sub-features in `acorn-analytics`,
or extend the bridge to redact / hash sensitive fields before dispatch.

## What it doesn't do

- **Server-side conversion APIs** (Meta CAPI, LinkedIn CAPI, TikTok Events
  API). These need a separate package because they need an HTTP client,
  access-token management, PII hashing, and event-ID deduplication. May
  ship as `acorn-server-side-conversions` later.
- **A consent UI.** This package only listens for events. Use any third-party
  consent platform.
- **Standard-event name translation.** v0.1 passes event names through; manage
  mapping in each platform's dashboard.

## Requirements

- PHP 8.1+
- Acorn 4.x or 5.x
- WordPress 6.0+
- `brew-bytes/acorn-analytics ^0.2`

## License

MIT &copy; Brew & Bytes
