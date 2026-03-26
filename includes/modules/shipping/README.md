# Red Cultural - Shipping Module

This module implements custom shipping methods and regional fee calculations for WooCommerce.

## Features
*   **Regional Shipping**: Provides a custom shipping method based on Chilean regions.
*   **Commune-Based Logic**: Integrates with a list of Chilean communes for accurate calculation.
*   **Dynamic Fees**: Allows setting custom shipping fees per region from the admin panel.
*   **Checkout Integration**: Adds custom fields and logic to the WooCommerce checkout flow.

## Architecture
*   **[`class-rc-shipping-manager.php`](./class-rc-shipping-manager.php)**: The main bootstrap loader for this module.
*   **[`class-rcs-shipping-method.php`](./class-rcs-shipping-method.php)**: Implements the core `WC_Shipping_Method` class.
*   **[`class-rcs-admin.php`](./class-rcs-admin.php)**: Admin interface for managing region-based prices.
*   **[`class-rcs-checkout.php`](./class-rcs-checkout.php)**: Handles checkout field validation and data storage.
*   **[`class-rcs-communes.php`](./class-rcs-communes.php)**: Standardized list of communes and regions.
*   **[`class-rcs-fees.php`](./class-rcs-fees.php)**: Logic for calculating the final shipping fee based on user selection.
*   **[`class-rcs-rates.php`](./class-rcs-rates.php)**: Handles the formatting and display of shipping rates.
*   **[`class-rcs-account.php`](./class-rcs-account.php)**: Updates to the "My Account" area related to shipping addressing.

## Configuration
Shipping fees are managed in **WooCommerce > Settings > Shipping > Red Cultural Region**.
