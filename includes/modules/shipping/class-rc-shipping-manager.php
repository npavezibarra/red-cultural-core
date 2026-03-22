<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manager and Utility class for Shipping Module
 * Formerly Red_Cultural_Shipping in the standalone plugin.
 */
final class Red_Cultural_Shipping
{
    const OPTION_REGION_PRICES = 'rcs_region_prices';
    const SHIPPING_METHOD_ID   = 'red_cultural_region';

    public static function init()
    {
        if (!class_exists('WooCommerce')) {
            return;
        }

        if (!defined('RCS_PLUGIN_URL')) {
            define('RCS_PLUGIN_URL', RC_CORE_URL);
        }
        if (!defined('RCS_VERSION')) {
            define('RCS_VERSION', RC_CORE_VERSION);
        }

        // Load Shipping Classes
        require_once RC_CORE_PATH . 'includes/modules/shipping/class-rcs-communes.php';
        require_once RC_CORE_PATH . 'includes/modules/shipping/class-rcs-admin.php';
        require_once RC_CORE_PATH . 'includes/modules/shipping/class-rcs-checkout.php';
        require_once RC_CORE_PATH . 'includes/modules/shipping/class-rcs-account.php';
        require_once RC_CORE_PATH . 'includes/modules/shipping/class-rcs-fees.php';
        require_once RC_CORE_PATH . 'includes/modules/shipping/class-rcs-rates.php';

        add_action('woocommerce_shipping_init', array(__CLASS__, 'include_shipping_method_class'));
        add_filter('woocommerce_shipping_methods', array(__CLASS__, 'register_shipping_method'));

        RCS_Checkout::init();
        RCS_Account::init();
        RCS_Fees::init();
        RCS_Rates::init();

        if (is_admin()) {
            RCS_Admin::init();
        }
    }

    public static function include_shipping_method_class()
    {
        require_once RC_CORE_PATH . 'includes/modules/shipping/class-rcs-shipping-method.php';
    }

    public static function register_shipping_method($methods)
    {
        $methods[self::SHIPPING_METHOD_ID] = 'RCS_Shipping_Method';
        return $methods;
    }

    // --- Static Utility Methods from original Red_Cultural_Shipping ---

    public static function log($message, array $context = array())
    {
        if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }
        $payload = $context ? ' ' . wp_json_encode($context) : '';
        error_log('Red_Cultural_Shipping: ' . $message . $payload);
    }

    public static function get_region_prices()
    {
        $prices = get_option(self::OPTION_REGION_PRICES, array());
        return is_array($prices) ? $prices : array();
    }

    public static function get_region_price_by_state_code($state_code)
    {
        $state_code = strtoupper(trim((string) $state_code));
        if ('' === $state_code) return null;

        $prices = self::get_region_prices();
        $candidates = array($state_code);

        if (0 === strpos($state_code, 'CL-')) {
            $candidates[] = substr($state_code, 3);
        } else {
            $candidates[] = 'CL-' . $state_code;
        }

        foreach ($candidates as $c) {
            if (isset($prices[$c]) && is_numeric($prices[$c])) {
                return (float) $prices[$c];
            }
        }
        return null;
    }

    public static function resolve_state_code_from_city($city)
    {
        if (!class_exists('RCS_Communes')) return '';
        $commune = RCS_Communes::find_by_name($city);
        if (!$commune || !isset($commune['region_name'])) return '';
        return RCS_Communes::map_region_name_to_state_code($commune['region_name']);
    }

    public static function determine_customer_region_cost()
    {
        if (!function_exists('WC') || !WC() || !WC()->customer) return null;
        $customer = WC()->customer;
        $shipping_city  = trim((string)$customer->get_shipping_city());
        $billing_city   = trim((string)$customer->get_billing_city());
        $shipping_state = strtoupper(trim((string)$customer->get_shipping_state()));
        $billing_state  = strtoupper(trim((string)$customer->get_billing_state()));
        $city  = '' !== $shipping_city ? $shipping_city : $billing_city;
        $state = '' !== $shipping_state ? $shipping_state : $billing_state;
        return self::determine_region_cost($city, $state);
    }

    public static function determine_region_cost($city, $state_code)
    {
        $city = trim((string)$city);
        $state_code = strtoupper(trim((string)$state_code));

        $resolved_state = '';
        if ('' !== $city) {
            $resolved_state = strtoupper(self::resolve_state_code_from_city($city));
        }

        if ('' === $resolved_state) {
            $resolved_state = $state_code;
        }

        if ('' !== $resolved_state) {
            $cost = self::get_region_price_by_state_code($resolved_state);
            return null === $cost ? 0.0 : (float)$cost;
        }
        return null;
    }
}
