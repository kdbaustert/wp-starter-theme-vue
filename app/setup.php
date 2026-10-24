<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.scss');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Get the Google Analytics Measurement ID.
 *
 * A non-empty GA_MEASUREMENT_ID constant wins over the Settings > General
 * field, so each environment can set its own ID in wp-config.php or .env.
 *
 * @return string
 */
function google_analytics_id(): string
{
    if (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID) {
        return (string) GA_MEASUREMENT_ID;
    }

    return (string) get_option('ga_measurement_id', '');
}

/**
 * Add the Google Analytics Measurement ID field to Settings > General.
 *
 * @return void
 */
add_action('admin_init', function () {
    register_setting('general', 'ga_measurement_id', [
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => function ($value) {
            // The field is disabled while the constant is set, so it isn't
            // submitted. Keep the stored ID for when the constant goes away.
            if ($value === null) {
                return get_option('ga_measurement_id', '');
            }

            $value = strtoupper(trim((string) $value));

            if ($value === '' || preg_match('/^G-[A-Z0-9]+$/', $value)) {
                return $value;
            }

            add_settings_error(
                'ga_measurement_id',
                'invalid_ga_measurement_id',
                __('The Google Analytics Measurement ID should look like G-XXXXXXXXXX.', 'sage')
            );

            return get_option('ga_measurement_id', '');
        },
    ]);

    add_settings_field('ga_measurement_id', __('Google Analytics ID', 'sage'), function () {
        $locked = defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID;

        printf(
            '<input name="ga_measurement_id" id="ga_measurement_id" type="text" class="regular-text code"'
            .' value="%s" placeholder="G-XXXXXXXXXX" %s>',
            esc_attr(google_analytics_id()),
            disabled($locked, true, false)
        );

        echo '<p class="description">'.esc_html($locked
            ? __('Set by the GA_MEASUREMENT_ID constant.', 'sage')
            : __('The tag only loads when the environment type is production.', 'sage')
        ).'</p>';
    }, 'general', 'default', ['label_for' => 'ga_measurement_id']);
});

/**
 * Load the Google Analytics tag on production sites.
 *
 * wp_get_environment_type() reports production unless WP_ENVIRONMENT_TYPE
 * says otherwise, so local and staging sites need it set to stay out.
 *
 * @link https://developers.google.com/tag-platform/gtagjs/install
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function () {
    $id = google_analytics_id();

    if (! $id || wp_get_environment_type() !== 'production') {
        return;
    }

    wp_enqueue_script(
        'google-analytics',
        'https://www.googletagmanager.com/gtag/js?id='.rawurlencode($id),
        [],
        null,
        ['strategy' => 'async']
    );

    wp_add_inline_script('google-analytics', implode("\n", [
        'window.dataLayer = window.dataLayer || [];',
        'function gtag(){dataLayer.push(arguments);}',
        "gtag('js', new Date());",
        sprintf("gtag('config', %s);", wp_json_encode($id)),
    ]), 'before');
});
