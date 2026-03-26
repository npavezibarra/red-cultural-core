# Red Cultural - Templates Module

This module handles custom template routing, admin settings, navigation injection, and email customization.

## Architecture

The module follows a modular architecture to prevent "God Object" patterns and improve maintainability.

### Main Loader
*   **[`class-rc-templates.php`](./class-rc-templates.php)**: The entry point. Bootstraps all sub-modules and handles global filters.

### Sub-Modules
All sub-modules are located in their respective sub-directories:

*   **[`admin/`](./admin/)**: Admin panels and settings handling.
*   **[`routing/`](./routing/)**: Logic for overriding default WP/WooCommerce template loading.
*   **[`ui/`](./ui/)**:
    *   `class-rc-templates-ui.php`: Navigation and menu injection.
    *   `class-rc-templates-assets.php`: CSS/JS and asset enqueuing.
    *   `class-rc-templates-auth-modal.php`: Authentication UI component.
*   **[`emails/`](./emails/)**: Custom branded email templates (Welcome, Password Reset).
*   **[`handlers/`](./handlers/)**: Frontend AJAX and form submission logic.
*   **[`shortcodes/`](./shortcodes/)**: All shortcodes defined by this module.

## For AI Agents & Developers
When modifying this module:
1.  **Identify the responsibility**: Determine which sub-module handles the logic you want to change.
2.  **Avoid cross-contamination**: Keep logic localized. For example, don't put CSS in `Router` or AJAX handlers in `UI`.
3.  **Hooks**: Most hooks are registered in the `init()` method of each sub-module.
4.  **Static Methods**: Modules use static methods for logic to allow easy access without instantiating loaders.
