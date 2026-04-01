# Red Cultural - Email Tester & Custom WooCommerce Emails Module

This module handles the customization, triggering, and testing of WooCommerce system emails and custom notifications for Red Cultural.

## Features
*   **Custom Branding**: Replaces default WooCommerce designs with a premium, minimal aesthetic (logo, typography, and clear layouts).
*   **Silencio de WooCommerce**: Disables ALL standard WooCommerce notifications (Admin & Customer) at a high-priority level (`plugins_loaded:1`) to ensure zero duplication and consistent branding.
*   **Botón de Acceso Inteligente**: Customer confirmation emails include dynamic action buttons (e.g., "Ir al Curso", "Ir a la Lección") based on the order contents.
*   **Email Previews**: Admin utility to trigger test versions of all system emails (Admin Notifications, Customer Confirmations, Bank Transfers, Welcomes).

## Architecture
*   **[`class-rc-email-tester.php`](./class-rc-email-tester.php)**: Manages the "Probar correos" admin interface and test triggering logic.
*   **[`class-rc-wc-emails.php`](./class-rc-wc-emails.php)**: The core engine that silences default WC emails and implements the manual sending of branded templates.
*   **Templates**: Located in `templates/emails/` within the plugin root.
    *   `wc-admin-new-order.php`: Professional admin notification.
    *   `wc-customer-processing.php`: Confirmation with dynamic buttons.
    *   `wc-bank-transfer-on-hold.php`: Instructions for bank transfers.

## Logical Logic
The module uses `plugins_loaded` at priority `1` to hook early and return empty recipients/disabled flags for standard WooCommerce emails (`new_order`, `customer_on_hold_order`, `customer_processing_order`, etc.), effectively hijacking the email flow for custom branded delivery.

## Usage
Go to **Red Cultural > Probar correos** to select an email type and verify any template before or after changes.
