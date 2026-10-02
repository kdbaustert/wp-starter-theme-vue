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
 * Give primary navigation items Bootstrap's navbar classes.
 *
 * @return array
 */
add_filter('nav_menu_css_class', function ($classes, $item, $args) {
    if ($args->theme_location !== 'primary_navigation') {
        return $classes;
    }

    $classes[] = 'nav-item';

    return $classes;
}, 10, 3);

/**
 * Give primary navigation links Bootstrap's nav-link and active classes.
 *
 * WordPress already sets aria-current="page" on the current item.
 *
 * @return array
 */
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
    if ($args->theme_location !== 'primary_navigation') {
        return $atts;
    }

    $atts['class'] = $item->current ? 'nav-link active' : 'nav-link';

    return $atts;
}, 10, 3);
