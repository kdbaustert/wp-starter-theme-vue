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

    return (string) get_option('ga_measurement_id');
}

/**
 * Whether the Google Analytics tag should load in this environment.
 *
 * @return bool
 */
function google_analytics_loads(): bool
{
    return get_option('ga_enabled')
        && in_array(wp_get_environment_type(), (array) get_option('ga_environments'), true);
}

/**
 * The environment types the tag can load on, keyed by the value
 * wp_get_environment_type() returns.
 *
 * @return array
 */
function google_analytics_environment_choices(): array
{
    return [
        'production' => __('Production', 'sage'),
        'staging' => __('Staging', 'sage'),
        'development' => __('Development', 'sage'),
        'local' => __('Local', 'sage'),
    ];
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
 * Register the Google Analytics settings.
 *
 * Registered on init rather than admin_init so their defaults also apply on
 * the front end, where the tag reads them.
 *
 * @return void
 */
add_action('init', function () {
    register_setting('google_analytics', 'ga_enabled', [
        'type' => 'boolean',
        'default' => true,
        // An unticked checkbox isn't submitted, so it arrives as null.
        'sanitize_callback' => fn ($value) => (bool) $value,
    ]);

    register_setting('google_analytics', 'ga_measurement_id', [
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

    register_setting('google_analytics', 'ga_environments', [
        'type' => 'array',
        'default' => ['production'],
        'sanitize_callback' => fn ($value) => array_values(
            array_intersect((array) $value, array_keys(google_analytics_environment_choices()))
        ),
    ]);
});

/**
 * Add the Appearance > Google Analytics page.
 *
 * @return void
 */
add_action('admin_menu', function () {
    add_theme_page(
        __('Google Analytics', 'sage'),
        __('Google Analytics', 'sage'),
        'manage_options',
        'google-analytics',
        function () {
            echo '<div class="wrap"><h1>'.esc_html(get_admin_page_title()).'</h1>';

            // Only pages under Settings show saved and error notices on their own.
            settings_errors();

            echo '<form action="options.php" method="post">';
            settings_fields('google_analytics');
            do_settings_sections('google-analytics');
            submit_button();
            echo '</form></div>';
        }
    );
});

/**
 * Add the fields to the Appearance > Google Analytics page.
 *
 * @return void
 */
add_action('admin_init', function () {
    add_settings_section('google_analytics', '', '', 'google-analytics');

    add_settings_field('ga_enabled', __('Load the tag', 'sage'), function () {
        printf(
            '<label><input name="ga_enabled" id="ga_enabled" type="checkbox" value="1" %s> %s</label>',
            checked(get_option('ga_enabled'), true, false),
            esc_html__('Add the Google Analytics tag to the site', 'sage')
        );
    }, 'google-analytics', 'google_analytics');

    add_settings_field('ga_measurement_id', __('Measurement ID', 'sage'), function () {
        $locked = defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID;

        printf(
            '<input name="ga_measurement_id" id="ga_measurement_id" type="text" class="regular-text code"'
            .' value="%s" placeholder="G-XXXXXXXXXX" %s>',
            esc_attr(google_analytics_id()),
            disabled($locked, true, false)
        );

        if ($locked) {
            echo '<p class="description">'.esc_html__('Set by the GA_MEASUREMENT_ID constant.', 'sage').'</p>';
        }
    }, 'google-analytics', 'google_analytics', ['label_for' => 'ga_measurement_id']);

    add_settings_field('ga_environments', __('Environments', 'sage'), function () {
        $selected = (array) get_option('ga_environments');

        echo '<fieldset><legend class="screen-reader-text">'.esc_html__('Environments', 'sage').'</legend>';

        foreach (google_analytics_environment_choices() as $value => $label) {
            printf(
                '<label><input name="ga_environments[]" type="checkbox" value="%s" %s> %s</label><br>',
                esc_attr($value),
                checked(in_array($value, $selected, true), true, false),
                esc_html($label)
            );
        }

        printf(
            '<p class="description">%s</p></fieldset>',
            esc_html(sprintf(
                /* translators: %s is the site's environment type, such as local or production */
                __('The tag only loads on the environment types ticked here. This site is %s.', 'sage'),
                wp_get_environment_type()
            ))
        );
    }, 'google-analytics', 'google_analytics');
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
