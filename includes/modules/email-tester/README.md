# Red Cultural - Email Tester Module

This module provides an admin utility for testing WooCommerce system emails and custom notifications.

## Features
*   **Email Previews**: Allows administrators to trigger test emails to themselves to verify branding, layout, and dynamic content.
*   **WooCommerce Integration**: Specifically designed to test "New Order" and "Bank Transfer" emails.
*   **Branding Verification**: Ensures logos and custom styles are correctly rendered in different email clients.

## Architecture
*   **[`class-rc-email-tester.php`](./class-rc-email-tester.php)**: The main loader and admin interface handler. It registers the "Probar correos" submenu under the "Red Cultural" main menu.
*   **[`class-rc-wc-emails.php`](./class-rc-wc-emails.php)**: Handles the logic for hooking into WooCommerce's email system to override or supplement default email behavior for testing purposes.

## Usage
Go to **Red Cultural > Probar correos** to select an email type and send a test message.
