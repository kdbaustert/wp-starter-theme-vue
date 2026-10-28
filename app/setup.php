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
        'footer_navigation' => __('Footer Navigation', 'sage'),
    ]);

    /**
     * Enable a logo under Appearance > Customize > Site Identity. It replaces
     * the site name in the navbar.
     *
     * @link https://developer.wordpress.org/themes/functionality/custom-logo/
     */
    add_theme_support('custom-logo', [
        'height' => 80,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
    ]);

    /**
     * Register image sizes for cards and full-width heroes. Images uploaded
     * before a size existed need `wp media regenerate` to get it.
     *
     * @link https://developer.wordpress.org/reference/functions/add_image_size/
     */
    add_image_size('card', 720, 405, true);
    add_image_size('hero', 1920, 720, true);

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
 * Offer the theme's image sizes in the block editor's size picker.
 *
 * @return array
 */
add_filter('image_size_names_choose', function ($sizes) {
    return $sizes + [
        'card' => __('Card', 'sage'),
        'hero' => __('Hero', 'sage'),
    ];
});

/**
 * Add an Unstyled style to the List block, Bootstrap's .list-unstyled. The
 * CSS is in resources/css/_blocks.scss.
 *
 * @return void
 */
add_action('init', function () {
    register_block_style('core/list', [
        'name' => 'unstyled',
        'label' => __('Unstyled', 'sage'),
    ]);
});

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
 * Whether the Google Analytics tag should load in this environment and for
 * the current visitor.
 *
 * @return bool
 */
function google_analytics_loads(): bool
{
    return get_option('ga_enabled')
        && in_array(wp_get_environment_type(), (array) get_option('ga_environments'), true)
        && ! google_analytics_skips_current_user();
}

/**
 * Whether the current visitor is staff and the page says not to track staff.
 *
 * Contributors can edit posts too, but publish_posts leaves them tracked so
 * the rule matches the authors, editors and administrators the page names.
 *
 * @return bool
 */
function google_analytics_skips_current_user(): bool
{
    return get_option('ga_exclude_staff') && current_user_can('publish_posts');
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

    register_setting('google_analytics', 'ga_exclude_staff', [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => fn ($value) => (bool) $value,
    ]);

    register_setting('google_analytics', 'ga_consent_mode', [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => fn ($value) => (bool) $value,
    ]);
});

/**
 * Add the Theme Settings > Google Analytics page. It runs after the Theme
 * Settings menu is added, so Site Settings stays the menu's first page.
 *
 * @return void
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'site-settings',
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
}, 20);

/**
 * Add the fields to the Theme Settings > Google Analytics page.
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

    add_settings_field('ga_exclude_staff', __('Logged-in staff', 'sage'), function () {
        printf(
            '<label><input name="ga_exclude_staff" id="ga_exclude_staff" type="checkbox" value="1" %s> %s</label>',
            checked(get_option('ga_exclude_staff'), true, false),
            esc_html__("Don't track users who can edit posts (authors, editors and administrators)", 'sage')
        );
    }, 'google-analytics', 'google_analytics');

    add_settings_field('ga_consent_mode', __('Consent Mode', 'sage'), function () {
        printf(
            '<label><input name="ga_consent_mode" id="ga_consent_mode" type="checkbox" value="1" %s> %s</label>'
            .'<p class="description">%s</p>',
            checked(get_option('ga_consent_mode'), true, false),
            esc_html__('Deny analytics and ad cookies until the visitor consents', 'sage'),
            esc_html__('Needed in the EU and UK. A cookie banner plugin that supports Google Consent Mode v2 grants consent; without one, GA only gets cookieless pings.', 'sage')
        );
    }, 'google-analytics', 'google_analytics');

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

    // The consent default has to come before the config call. wait_for_update
    // gives a banner time to apply a visitor's stored choice before the first hit.
    $consentDefault = get_option('ga_consent_mode')
        ? "gtag('consent', 'default', {ad_storage: 'denied', ad_user_data: 'denied',"
            ." ad_personalization: 'denied', analytics_storage: 'denied', wait_for_update: 500});"
        : '';

    wp_add_inline_script('google-analytics', implode("\n", array_filter([
        'window.dataLayer = window.dataLayer || [];',
        'function gtag(){dataLayer.push(arguments);}',
        $consentDefault,
        "gtag('js', new Date());",
        sprintf("gtag('config', %s);", wp_json_encode($id)),
    ])), 'before');
});

/**
 * The social networks Theme Settings > Site Settings has a profile URL field
 * for, keyed by the option key, in the order the footer lists them.
 *
 * @return array
 */
function social_networks(): array
{
    return [
        'facebook' => __('Facebook', 'sage'),
        'instagram' => __('Instagram', 'sage'),
        'linkedin' => __('LinkedIn', 'sage'),
        'youtube' => __('YouTube', 'sage'),
        'tiktok' => __('TikTok', 'sage'),
    ];
}

/**
 * Pull the token out of a Search Console verification value. Accepts the
 * token on its own or the whole <meta> tag Search Console hands out.
 *
 * @param  string  $value
 * @return string
 */
function search_console_token(string $value): string
{
    if (preg_match('/content=["\']([^"\']*)["\']/', $value, $matches)) {
        $value = $matches[1];
    }

    return sanitize_text_field($value);
}

/**
 * Register the site settings. Registered on init so their defaults also
 * apply on the front end.
 *
 * @return void
 */
add_action('init', function () {
    register_setting('site_settings', 'site_social_links', [
        'type' => 'array',
        'default' => [],
        'sanitize_callback' => fn ($value) => array_filter(array_map(
            'esc_url_raw',
            array_intersect_key((array) $value, social_networks())
        )),
    ]);

    register_setting('site_settings', 'site_footer_text', [
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    register_setting('site_settings', 'site_contact', [
        'type' => 'array',
        'default' => [],
        'sanitize_callback' => function ($value) {
            $value = (array) $value;

            return array_filter([
                'phone' => sanitize_text_field($value['phone'] ?? ''),
                'email' => sanitize_email($value['email'] ?? ''),
                'address' => sanitize_textarea_field($value['address'] ?? ''),
            ]);
        },
    ]);

    register_setting('site_settings', 'site_comments', [
        'type' => 'boolean',
        'default' => true,
        'sanitize_callback' => fn ($value) => (bool) $value,
    ]);

    register_setting('site_settings', 'site_search_console', [
        'type' => 'string',
        'default' => '',
        'sanitize_callback' => fn ($value) => search_console_token((string) $value),
    ]);

    register_setting('site_settings', 'site_color_modes', [
        'type' => 'boolean',
        'default' => true,
        'sanitize_callback' => fn ($value) => (bool) $value,
    ]);
});

/**
 * Add the Theme Settings menu, just below Appearance, with Site Settings as
 * its first page.
 *
 * @return void
 */
add_action('admin_menu', function () {
    add_menu_page(
        __('Site Settings', 'sage'),
        __('Theme Settings', 'sage'),
        'manage_options',
        'site-settings',
        function () {
            echo '<div class="wrap"><h1>'.esc_html(get_admin_page_title()).'</h1>';

            settings_errors();

            echo '<form action="options.php" method="post">';
            settings_fields('site_settings');
            do_settings_sections('site-settings');
            submit_button();
            echo '</form></div>';
        },
        'dashicons-admin-settings',
        61
    );

    // Without this, the menu's first item repeats "Theme Settings".
    add_submenu_page(
        'site-settings',
        __('Site Settings', 'sage'),
        __('Site Settings', 'sage'),
        'manage_options',
        'site-settings'
    );
});

/**
 * Add the fields to the Theme Settings > Site Settings page.
 *
 * @return void
 */
add_action('admin_init', function () {
    add_settings_section('site_settings_footer', __('Footer', 'sage'), '', 'site-settings');
    add_settings_section('site_settings_contact', __('Contact details', 'sage'), function () {
        echo '<p>'.esc_html__('Shown in the footer and given to search engines on the front page.', 'sage').'</p>';
    }, 'site-settings');
    add_settings_section('site_settings_social', __('Social profiles', 'sage'), function () {
        echo '<p>'.esc_html__('Profiles with a URL are linked in the footer.', 'sage').'</p>';
    }, 'site-settings');
    add_settings_section('site_settings_comments', __('Comments', 'sage'), '', 'site-settings');
    add_settings_section('site_settings_head', __('Site head', 'sage'), '', 'site-settings');

    add_settings_field('site_footer_text', __('Footer text', 'sage'), function () {
        printf(
            '<input name="site_footer_text" id="site_footer_text" type="text" class="large-text" value="%s" placeholder="%s">'
            .'<p class="description">%s</p>',
            esc_attr(get_option('site_footer_text')),
            esc_attr('© {year} '.get_bloginfo('name')),
            esc_html__('{year} becomes the current year. Leave it empty for the placeholder text.', 'sage')
        );
    }, 'site-settings', 'site_settings_footer', ['label_for' => 'site_footer_text']);

    $contact = (array) get_option('site_contact');

    add_settings_field('site_contact_phone', __('Phone', 'sage'), function () use ($contact) {
        printf(
            '<input name="site_contact[phone]" id="site_contact_phone" type="tel" class="regular-text" value="%s">',
            esc_attr($contact['phone'] ?? '')
        );
    }, 'site-settings', 'site_settings_contact', ['label_for' => 'site_contact_phone']);

    add_settings_field('site_contact_email', __('Email', 'sage'), function () use ($contact) {
        printf(
            '<input name="site_contact[email]" id="site_contact_email" type="email" class="regular-text" value="%s">',
            esc_attr($contact['email'] ?? '')
        );
    }, 'site-settings', 'site_settings_contact', ['label_for' => 'site_contact_email']);

    add_settings_field('site_contact_address', __('Address', 'sage'), function () use ($contact) {
        printf(
            '<textarea name="site_contact[address]" id="site_contact_address" class="regular-text" rows="3">%s</textarea>',
            esc_textarea($contact['address'] ?? '')
        );
    }, 'site-settings', 'site_settings_contact', ['label_for' => 'site_contact_address']);

    $links = (array) get_option('site_social_links');

    foreach (social_networks() as $network => $label) {
        add_settings_field("site_social_links_{$network}", $label, function () use ($network, $links) {
            printf(
                '<input name="site_social_links[%1$s]" id="site_social_links_%1$s" type="url" class="regular-text code" value="%2$s" placeholder="https://">',
                esc_attr($network),
                esc_attr($links[$network] ?? '')
            );
        }, 'site-settings', 'site_settings_social', ['label_for' => "site_social_links_{$network}"]);
    }

    add_settings_field('site_comments', __('Allow comments', 'sage'), function () {
        printf(
            '<label><input name="site_comments" id="site_comments" type="checkbox" value="1" %s> %s</label>'
            .'<p class="description">%s</p>',
            checked(get_option('site_comments'), true, false),
            esc_html__('Let visitors comment and send pingbacks', 'sage'),
            esc_html__('Unticked, comments are closed and hidden everywhere and leave the admin menu. Existing comments stay in the database.', 'sage')
        );
    }, 'site-settings', 'site_settings_comments');

    add_settings_field('site_search_console', __('Search Console', 'sage'), function () {
        printf(
            '<input name="site_search_console" id="site_search_console" type="text" class="regular-text code" value="%s">'
            .'<p class="description">%s</p>',
            esc_attr(get_option('site_search_console')),
            esc_html__('The HTML tag verification code. Pasting the whole <meta> tag works too.', 'sage')
        );
    }, 'site-settings', 'site_settings_head', ['label_for' => 'site_search_console']);

    add_settings_field('site_color_modes', __('Colour modes', 'sage'), function () {
        printf(
            '<label><input name="site_color_modes" id="site_color_modes" type="checkbox" value="1" %s> %s</label>',
            checked(get_option('site_color_modes'), true, false),
            esc_html__('Let visitors switch between light and dark mode', 'sage')
        );
    }, 'site-settings', 'site_settings_head');
});

/**
 * Print the Search Console verification tag.
 *
 * @link https://support.google.com/webmasters/answer/9008080
 *
 * @return void
 */
add_action('wp_head', function () {
    $token = get_option('site_search_console');

    if ($token) {
        printf('<meta name="google-site-verification" content="%s">'."\n", esc_attr($token));
    }
}, 1);

/**
 * Describe the site's organisation to search engines on the front page, from
 * the site name, logo, contact details and social profiles. Yoast and Rank
 * Math print their own Organization markup, so this steps aside for them.
 *
 * @link https://developers.google.com/search/docs/appearance/structured-data/organization
 *
 * @return void
 */
add_action('wp_head', function () {
    if (! is_front_page() || defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
        return;
    }

    $contact = (array) get_option('site_contact');

    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'logo' => wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full'),
        'telephone' => $contact['phone'] ?? null,
        'email' => $contact['email'] ?? null,
        'address' => $contact['address'] ?? null,
        'sameAs' => array_values(array_intersect_key((array) get_option('site_social_links'), social_networks())),
    ]);

    // JSON_HEX_TAG escapes < and >, so no value can close the script tag.
    wp_print_inline_script_tag(
        wp_json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG),
        ['type' => 'application/ld+json']
    );
});

/**
 * Turn comments and pingbacks off everywhere when Theme Settings > Site
 * Settings says so. Runs late on init so post types registered earlier lose comment
 * support too, which also drops the Discussion box and comment columns.
 *
 * @return void
 */
add_action('init', function () {
    if (get_option('site_comments')) {
        return;
    }

    foreach (get_post_types() as $postType) {
        remove_post_type_support($postType, 'comments');
        remove_post_type_support($postType, 'trackbacks');
    }

    add_filter('comments_open', '__return_false');
    add_filter('pings_open', '__return_false');
    add_filter('comments_array', '__return_empty_array');
    add_filter('get_comments_number', '__return_zero');

    add_action('admin_menu', fn () => remove_menu_page('edit-comments.php'));
    add_action('admin_bar_menu', fn ($adminBar) => $adminBar->remove_node('comments'), 100);
}, 100);

/**
 * Set Bootstrap's colour mode before the page paints, so a dark mode visitor
 * doesn't see a flash of the light theme. resources/js/color-mode.js handles
 * the switch and reads the same localStorage key.
 *
 * @link https://getbootstrap.com/docs/5.3/customize/color-modes/
 *
 * @return void
 */
add_action('wp_head', function () {
    if (! get_option('site_color_modes')) {
        return;
    }

    wp_print_inline_script_tag(<<<'JS'
    (() => {
      let mode = 'auto';
      try { mode = localStorage.getItem('color-mode') || 'auto'; } catch (e) {}
      const dark = mode === 'dark' || (mode === 'auto' && matchMedia('(prefers-color-scheme: dark)').matches);
      document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
    })();
    JS);
}, 1);
