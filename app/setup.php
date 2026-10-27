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
 * Whether the Google Analytics settings live on an ACF options page.
 *
 * Options pages need ACF Pro. Without it, the Measurement ID falls back to
 * the Settings > General field and the tag loads on production only.
 *
 * @return bool
 */
function google_analytics_uses_acf(): bool
{
    return function_exists('acf_add_options_page') && function_exists('acf_add_local_field_group');
}

/**
 * Get the Google Analytics Measurement ID.
 *
 * A non-empty GA_MEASUREMENT_ID constant wins over the admin field, so each
 * environment can set its own ID in wp-config.php or .env.
 *
 * @return string
 */
function google_analytics_id(): string
{
    if (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID) {
        return (string) GA_MEASUREMENT_ID;
    }

    if (google_analytics_uses_acf()) {
        // Field keys, not names, so the defaults apply before the page is saved.
        return (string) get_field('field_ga_measurement_id', 'option');
    }

    return (string) get_option('ga_measurement_id', '');
}

/**
 * Whether the Google Analytics tag should load in this environment.
 *
 * @return bool
 */
function google_analytics_loads(): bool
{
    if (! google_analytics_uses_acf()) {
        return wp_get_environment_type() === 'production';
    }

    $environments = (array) get_field('field_ga_environments', 'option');

    return get_field('field_ga_enabled', 'option')
        && in_array(wp_get_environment_type(), $environments, true);
}

/**
 * Whether a trimmed, uppercased value is a GA4 Measurement ID. Empty is
 * allowed, since it clears the ID.
 *
 * @param  string  $id
 * @return bool
 */
function google_analytics_id_is_valid(string $id): bool
{
    return $id === '' || preg_match('/^G-[A-Z0-9]+$/', $id) === 1;
}

/**
 * Add the Google Analytics Measurement ID field to Settings > General.
 *
 * @return void
 */
add_action('admin_init', function () {
    if (google_analytics_uses_acf()) {
        return;
    }

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

            if (google_analytics_id_is_valid($value)) {
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
 * Register the Settings > Google Analytics options page and its fields.
 *
 * @link https://www.advancedcustomfields.com/resources/options-page/
 *
 * @return void
 */
add_action('acf/init', function () {
    if (! google_analytics_uses_acf()) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Google Analytics', 'sage'),
        'menu_slug' => 'google-analytics',
        'parent_slug' => 'options-general.php',
        'capability' => 'manage_options',
        'autoload' => true,
    ]);

    acf_add_local_field_group([
        'key' => 'group_google_analytics',
        'title' => __('Google Analytics', 'sage'),
        'fields' => [
            [
                'key' => 'field_ga_enabled',
                'name' => 'ga_enabled',
                'label' => __('Load the tag', 'sage'),
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 1,
            ],
            [
                'key' => 'field_ga_measurement_id',
                'name' => 'ga_measurement_id',
                'label' => __('Measurement ID', 'sage'),
                'type' => 'text',
                'placeholder' => 'G-XXXXXXXXXX',
            ],
            [
                'key' => 'field_ga_environments',
                'name' => 'ga_environments',
                'label' => __('Environments', 'sage'),
                'instructions' => __('The tag only loads when the environment type is one of these.', 'sage'),
                'type' => 'checkbox',
                'choices' => [
                    'production' => __('Production', 'sage'),
                    'staging' => __('Staging', 'sage'),
                    'development' => __('Development', 'sage'),
                    'local' => __('Local', 'sage'),
                ],
                'default_value' => ['production'],
                'return_format' => 'value',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'google-analytics',
                ],
            ],
        ],
    ]);
});

/**
 * Lock the ACF Measurement ID field while the constant is set.
 *
 * A disabled input isn't submitted, so the stored ID survives for when the
 * constant goes away.
 *
 * @return array
 */
add_filter('acf/prepare_field/key=field_ga_measurement_id', function ($field) {
    if (! (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID)) {
        return $field;
    }

    $field['disabled'] = 1;
    $field['value'] = (string) GA_MEASUREMENT_ID;
    $field['instructions'] = __('Set by the GA_MEASUREMENT_ID constant.', 'sage');

    return $field;
});

/**
 * Reject an ACF Measurement ID that isn't G-XXXXXXXXXX.
 *
 * @return bool|string
 */
add_filter('acf/validate_value/key=field_ga_measurement_id', function ($valid, $value) {
    if ($valid !== true || google_analytics_id_is_valid(strtoupper(trim((string) $value)))) {
        return $valid;
    }

    return __('The Google Analytics Measurement ID should look like G-XXXXXXXXXX.', 'sage');
}, 10, 2);

/**
 * Store the ACF Measurement ID trimmed and uppercased.
 *
 * @return string
 */
add_filter('acf/update_value/key=field_ga_measurement_id', function ($value) {
    return strtoupper(trim((string) $value));
});

/**
 * Load the Google Analytics tag where google_analytics_loads() allows it.
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

    if (! $id || ! google_analytics_loads()) {
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
