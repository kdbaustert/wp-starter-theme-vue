<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Bootstrap link classes for each menu location the theme styles.
 *
 * @return array<string, string>
 */
function nav_link_classes()
{
    return [
        'primary_navigation' => 'nav-link',
        'footer_navigation' => 'nav-link px-2 text-body-secondary',
    ];
}

/**
 * Give the theme's menu items Bootstrap's nav-item class.
 *
 * @return array
 */
add_filter('nav_menu_css_class', function ($classes, $item, $args) {
    if (! isset(nav_link_classes()[$args->theme_location])) {
        return $classes;
    }

    $classes[] = 'nav-item';

    return $classes;
}, 10, 3);

/**
 * Give the theme's menu links Bootstrap's nav-link and active classes.
 *
 * WordPress already sets aria-current="page" on the current item.
 *
 * @return array
 */
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
    $classes = nav_link_classes()[$args->theme_location] ?? null;

    if (! $classes) {
        return $atts;
    }

    $atts['class'] = $item->current ? $classes.' active' : $classes;

    return $atts;
}, 10, 3);

/**
 * Drop the emoji polyfill and the generator and RSD tags from the front end.
 * Every supported browser renders emoji natively, the generator tag only
 * advertises the WordPress version, and RSD exists for XML-RPC clients.
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');

/**
 * Turn XML-RPC off. The xmlrpc_enabled filter only blocks the methods that
 * need a login, so empty the method list instead. Jetpack and the old mobile
 * apps need XML-RPC; remove these two filters if the site uses either.
 *
 * @return array
 */
add_filter('xmlrpc_methods', '__return_empty_array');

add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);

    return $headers;
});
