# Red Cultural - Lesson Access Module

This module manages the individual purchase of lessons and the bridge between WooCommerce and LearnDash (SFWD_LMS).

## Features
*   **Individual Lesson Purchase**: Allows customers to buy single lessons without purchasing a full course.
*   **Custom Templates**: Overrides the default LearnDash lesson and course layouts with branded Red Cultural versions.
*   **Access Control**: Implements logic to check if a user should have access to a specific lesson based on their purchase history.
*   **Notification Integration**: Sends custom purchase confirmation notifications.

## Architecture
*   **[`class-rc-lesson-manager.php`](./class-rc-lesson-manager.php)**: The main bootstrap loader for this module.
*   **[`class-admin.php`](./class-admin.php)**: Admin interface for managing individual lesson products and metadata.
*   **[`class-frontend.php`](./class-frontend.php)**: Frontend display logic and template hooks.
*   **[`class-ajax.php`](./class-ajax.php)**: AJAX handlers for lesson-related frontend actions.
*   **[`class-woocommerce.php`](./class-woocommerce.php)**: Integration with WooCommerce (Product types, Cart, Checkout, Order processing).
*   **[`class-access-control.php`](./class-access-control.php)**: The core logic for verifying lesson access permissions.
*   **[`class-notifications.php`](./class-notifications.php)**: Handlers for sending custom branded notifications after a lesson purchase.
*   **[`pricing/`](./pricing/)**: Contains logic for calculating and displaying lesson prices.

## Assets
Located in **[`assets/`](./assets/)**, including the custom CSS and JS needed for the individual lesson purchase flow.
