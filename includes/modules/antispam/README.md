# Red Cultural - Anti-Spam Module

This module provides a unified interface for protecting forms against spam using Google reCAPTCHA v3 or Cloudflare Turnstile.

## Features
*   **Multi-Provider**: Supports both Google reCAPTCHA v3 and Cloudflare Turnstile.
*   **Fail-Safe**: Verification is designed to fail-open in case of API communication errors to prevent user lockout, while logging the incident for admin review.
*   **Integration**: Easy to integrate into any form using `RC_Anti_Spam::render_form_fields()` and `RC_Anti_Spam::verify()`.

## Architecture
*   **[`class-rc-antispam.php`](./class-rc-antispam.php)**: The main and only class for this module. It handles:
    *   Settings retrieval from the database.
    *   Asset enqueuing (External APIs).
    *   Server-side verification via `wp_remote_post`.
    *   Widget and JS rendering for frontend forms.

## Settings
Settings are managed in the **Red Cultural > Contact Forms** admin page (handled by the Templates module).
