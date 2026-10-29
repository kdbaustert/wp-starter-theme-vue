<?php

/**
 * Theme settings pages: Custom Code, Typography, Performance and Redirects.
 */

namespace App;

use Illuminate\Foundation\ViteException;
use Illuminate\Support\Facades\Vite;

/**
 * Build the callback for a Theme Settings page that is a plain settings form.
 *
 * @param  string  $optionGroup  The group passed to register_setting().
 * @param  string  $page  The page slug fields and sections are added to.
 */
function settings_page(string $optionGroup, string $page): callable
{
    return function () use ($optionGroup, $page) {
        echo '<div class="wrap"><h1>'.esc_html(get_admin_page_title()).'</h1>';

        // Only pages under Settings print save notices on their own.
        settings_errors();

        echo '<form action="options.php" method="post">';
        settings_fields($optionGroup);
        do_settings_sections($page);
        submit_button();
        echo '</form></div>';
    };
}

/*
|--------------------------------------------------------------------------
| Custom Code
|--------------------------------------------------------------------------
*/

/**
 * The code boxes on Theme Settings > Custom Code, keyed by option name.
 *
 * @return array<string, string>
 */
function custom_code_fields(): array
{
    return [
        'site_code_head' => __('In the head', 'sage'),
        'site_code_footer' => __('Before the closing body tag', 'sage'),
    ];
}

add_action('init', function () {
    foreach (array_keys(custom_code_fields()) as $option) {
        register_setting('custom_code', $option, [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => function ($value) use ($option) {
                // Raw script tags are what this page is for, so only users
                // who may post unfiltered HTML can change them. Everyone else
                // keeps what's stored.
                if (! current_user_can('unfiltered_html')) {
                    return get_option($option, '');
                }

                return trim((string) $value);
            },
        ]);
    }
});

add_action('admin_menu', function () {
    add_submenu_page(
        'site-settings',
        __('Custom Code', 'sage'),
        __('Custom Code', 'sage'),
        'manage_options',
        'custom-code',
        settings_page('custom_code', 'custom-code')
    );
}, 20);

add_action('admin_init', function () {
    add_settings_section('custom_code', '', function () {
        echo '<p>'.esc_html__('Tracking pixels, chat widgets and other snippets, printed on every page of the site as typed.', 'sage').'</p>';

        if (! current_user_can('unfiltered_html')) {
            echo '<p><strong>'.esc_html__('Your account can\'t post unfiltered HTML, so these boxes are read-only.', 'sage').'</strong></p>';
        }
    }, 'custom-code');

    foreach (custom_code_fields() as $option => $label) {
        add_settings_field($option, $label, function () use ($option) {
            printf(
                '<textarea name="%1$s" id="%1$s" class="large-text code" rows="8" %2$s>%3$s</textarea>',
                esc_attr($option),
                disabled(! current_user_can('unfiltered_html'), true, false),
                esc_textarea(get_option($option))
            );
        }, 'custom-code', 'custom_code', ['label_for' => $option]);
    }
});

/**
 * Print the custom code boxes. Priority 99 puts them after the theme's own
 * tags and enqueued assets.
 *
 * @return void
 */
add_action('wp_head', fn () => print_custom_code('site_code_head'), 99);
add_action('wp_footer', fn () => print_custom_code('site_code_footer'), 99);

function print_custom_code(string $option): void
{
    $code = get_option($option);

    if ($code) {
        echo $code."\n";
    }
}

/*
|--------------------------------------------------------------------------
| Typography
|--------------------------------------------------------------------------
*/

/**
 * The font families found in resources/fonts, keyed by folder name.
 *
 * Each family is a folder of WOFF2 files named for their weight, with
 * "-italic" added for italics: resources/fonts/inter/400.woff2,
 * resources/fonts/inter/700-italic.woff2.
 *
 * @return array<string, array{name: string, files: array<int, array{weight: int, style: string, file: string}>}>
 */
function self_hosted_fonts(): array
{
    $families = [];

    foreach (glob(resource_path('fonts/*/*.woff2')) as $path) {
        if (! preg_match('/^(\d{3})(-italic)?$/', basename($path, '.woff2'), $parts)) {
            continue;
        }

        $slug = basename(dirname($path));

        // The slug ends up inside CSS, so only plain folder names count.
        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            continue;
        }

        $families[$slug]['name'] = ucwords(str_replace('-', ' ', $slug));
        $families[$slug]['files'][] = [
            'weight' => (int) $parts[1],
            'style' => empty($parts[2]) ? 'normal' : 'italic',
            'file' => basename($path),
        ];
    }

    ksort($families);

    return $families;
}

/**
 * The built URL of one font file, or null while the build doesn't have it,
 * which is the case for a file added since the last npm run build.
 */
function font_url(string $slug, string $file): ?string
{
    try {
        return Vite::asset("resources/fonts/{$slug}/{$file}");
    } catch (ViteException) {
        return null;
    }
}

/**
 * The font family saved for one role, or null when it's unset or its files
 * are gone.
 *
 * @param  string  $role  "body" or "heading".
 * @return array|null The family from self_hosted_fonts(), plus its "slug".
 */
function chosen_font(string $role): ?array
{
    $slug = get_option("site_font_{$role}");
    $family = self_hosted_fonts()[$slug] ?? null;

    return $family ? $family + ['slug' => $slug] : null;
}

add_action('init', function () {
    foreach (['body', 'heading'] as $role) {
        register_setting('typography', "site_font_{$role}", [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => fn ($value) => isset(self_hosted_fonts()[$value]) ? $value : '',
        ]);
    }
});

add_action('admin_menu', function () {
    add_submenu_page(
        'site-settings',
        __('Typography', 'sage'),
        __('Typography', 'sage'),
        'manage_options',
        'typography',
        settings_page('typography', 'typography')
    );
}, 20);

add_action('admin_init', function () {
    add_settings_section('typography', '', function () {
        printf(
            '<p>%s</p>',
            esc_html__('Self-hosted fonts from resources/fonts. Add a folder per family holding WOFF2 files named for their weight (400.woff2, 700.woff2, 400-italic.woff2), then run npm run build.', 'sage')
        );
    }, 'typography');

    $roles = [
        'body' => __('Body font', 'sage'),
        'heading' => __('Heading font', 'sage'),
    ];

    foreach ($roles as $role => $label) {
        add_settings_field("site_font_{$role}", $label, function () use ($role) {
            $selected = get_option("site_font_{$role}");

            printf('<select name="site_font_%1$s" id="site_font_%1$s">', esc_attr($role));
            printf('<option value="">%s</option>', esc_html__('Bootstrap default', 'sage'));

            foreach (self_hosted_fonts() as $slug => $family) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($slug),
                    selected($selected, $slug, false),
                    esc_html($family['name'])
                );
            }

            echo '</select>';
        }, 'typography', 'typography', ['label_for' => "site_font_{$role}"]);
    }
});

/**
 * The CSS for the chosen fonts: an @font-face rule per file, then the
 * Bootstrap body variable and a heading rule pointing at them.
 */
function typography_css(): string
{
    $fontFaces = [];
    $rules = [];
    $declared = [];

    foreach (['body', 'heading'] as $role) {
        $family = chosen_font($role);

        if (! $family) {
            continue;
        }

        $stack = sprintf('"%s", var(--bs-font-sans-serif)', $family['name']);

        $rules[] = $role === 'body'
            ? ":root{--bs-body-font-family:{$stack}}"
            : "h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5,.h6{font-family:{$stack}}";

        // One family can serve both roles, but it only needs declaring once.
        if (isset($declared[$family['slug']])) {
            continue;
        }

        $declared[$family['slug']] = true;

        foreach ($family['files'] as $font) {
            $url = font_url($family['slug'], $font['file']);

            if ($url) {
                $fontFaces[] = sprintf(
                    '@font-face{font-family:"%s";font-style:%s;font-weight:%d;font-display:swap;src:url("%s") format("woff2")}',
                    $family['name'],
                    $font['style'],
                    $font['weight'],
                    esc_url($url)
                );
            }
        }
    }

    return implode("\n", array_merge($fontFaces, $rules));
}

add_action('wp_head', function () {
    $css = typography_css();

    if ($css) {
        printf("<style id=\"theme-fonts\">\n%s\n</style>\n", $css);
    }
}, 8);

/*
|--------------------------------------------------------------------------
| Performance
|--------------------------------------------------------------------------
*/

/**
 * The switches on Theme Settings > Performance, keyed by option name. All
 * start off, because each one can break a plugin or a block.
 *
 * @return array<string, array{label: string, description: string}>
 */
function performance_switches(): array
{
    return [
        'perf_preload_font' => [
            'label' => __('Preload the body font', 'sage'),
            'description' => __('Starts the download of the body font\'s 400 weight before the stylesheet is parsed. Needs a body font on the Typography page.', 'sage'),
        ],
        'perf_remove_jquery' => [
            'label' => __('Remove jQuery from the front end', 'sage'),
            'description' => __('Leave this off if any plugin needs jQuery. Scripts that depend on it stop loading.', 'sage'),
        ],
        'perf_remove_embed' => [
            'label' => __('Remove the wp-embed script', 'sage'),
            'description' => __('Only used to resize posts from other WordPress sites embedded here. They still embed, without the resizing.', 'sage'),
        ],
        'perf_remove_block_styles' => [
            'label' => __('Remove the block library styles', 'sage'),
            'description' => __('The theme styles the Button, Quote, List and Table blocks itself. Columns, Cover, Gallery and similar blocks lose their layout CSS, so check them before turning this on.', 'sage'),
        ],
    ];
}

add_action('init', function () {
    foreach (array_keys(performance_switches()) as $option) {
        register_setting('performance', $option, [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => fn ($value) => (bool) $value,
        ]);
    }
});

add_action('admin_menu', function () {
    add_submenu_page(
        'site-settings',
        __('Performance', 'sage'),
        __('Performance', 'sage'),
        'manage_options',
        'performance',
        settings_page('performance', 'performance')
    );
}, 20);

add_action('admin_init', function () {
    add_settings_section('performance', '', '', 'performance');

    foreach (performance_switches() as $option => $switch) {
        add_settings_field($option, $switch['label'], function () use ($option, $switch) {
            printf(
                '<label><input name="%1$s" id="%1$s" type="checkbox" value="1" %2$s> %3$s</label><p class="description">%4$s</p>',
                esc_attr($option),
                checked(get_option($option), true, false),
                esc_html__('On', 'sage'),
                esc_html($switch['description'])
            );
        }, 'performance', 'performance');
    }
});

add_action('wp_enqueue_scripts', function () {
    if (get_option('perf_remove_jquery')) {
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-migrate');
    }

    if (get_option('perf_remove_embed')) {
        wp_deregister_script('wp-embed');
    }

    if (get_option('perf_remove_block_styles')) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('classic-theme-styles');
    }
}, 100);

add_action('wp_head', function () {
    $family = chosen_font('body');

    if (! get_option('perf_preload_font') || ! $family) {
        return;
    }

    $url = font_url($family['slug'], '400.woff2');

    if ($url) {
        // Fonts are fetched in anonymous CORS mode, so the preload has to be too.
        printf('<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>'."\n", esc_url($url));
    }
}, 2);

/*
|--------------------------------------------------------------------------
| Redirects
|--------------------------------------------------------------------------
*/

/**
 * The redirect status codes the Redirects page offers.
 *
 * @return array<int, string>
 */
function redirect_statuses(): array
{
    return [
        301 => __('301 Permanent', 'sage'),
        302 => __('302 Temporary', 'sage'),
    ];
}

/**
 * Lowercase a path and give it one leading slash and no trailing one.
 */
function normalize_redirect_path(string $path): string
{
    return '/'.trim(strtolower($path), '/');
}

add_action('init', function () {
    register_setting('redirects', 'site_redirects', [
        'type' => 'array',
        'default' => [],
        'sanitize_callback' => function ($rows) {
            $redirects = [];

            foreach ((array) $rows as $row) {
                $from = wp_parse_url(trim((string) ($row['from'] ?? '')), PHP_URL_PATH);
                $to = trim((string) ($row['to'] ?? ''));

                if (! $from || ! $to) {
                    continue;
                }

                $from = normalize_redirect_path($from);
                $to = str_starts_with($to, '/') ? '/'.ltrim(esc_url_raw($to), '/') : esc_url_raw($to, ['http', 'https']);

                // Loops would never finish loading, so refuse a path sent to itself.
                if (! $to || (str_starts_with($to, '/') && normalize_redirect_path($to) === $from)) {
                    continue;
                }

                $status = (int) ($row['status'] ?? 301);

                // A path listed twice keeps its last row.
                $redirects[$from] = [
                    'from' => $from,
                    'to' => $to,
                    'status' => isset(redirect_statuses()[$status]) ? $status : 301,
                ];
            }

            return array_values($redirects);
        },
    ]);
});

add_action('admin_menu', function () {
    add_submenu_page(
        'site-settings',
        __('Redirects', 'sage'),
        __('Redirects', 'sage'),
        'manage_options',
        'redirects',
        function () {
            $redirects = (array) get_option('site_redirects');

            // Three empty rows to add to, so there's always somewhere to type.
            $rows = array_merge($redirects, array_fill(0, 3, ['from' => '', 'to' => '', 'status' => 301]));

            echo '<div class="wrap"><h1>'.esc_html(get_admin_page_title()).'</h1>';
            settings_errors();

            echo '<p>'.esc_html__('Send an old path to a new page on this site or to another site. Clear both fields to delete a row; save to get more empty rows.', 'sage').'</p>';
            echo '<form action="options.php" method="post">';
            settings_fields('redirects');

            echo '<table class="widefat striped"><thead><tr>';
            printf('<th>%s</th><th>%s</th><th>%s</th>', esc_html__('From', 'sage'), esc_html__('To', 'sage'), esc_html__('Type', 'sage'));
            echo '</tr></thead><tbody>';

            foreach ($rows as $index => $row) {
                echo '<tr>';
                printf(
                    '<td><input name="site_redirects[%1$d][from]" type="text" class="large-text code" value="%2$s" placeholder="/old-page" aria-label="%3$s"></td>',
                    $index,
                    esc_attr($row['from']),
                    esc_attr__('From path', 'sage')
                );
                printf(
                    '<td><input name="site_redirects[%1$d][to]" type="text" class="large-text code" value="%2$s" placeholder="/new-page or https://" aria-label="%3$s"></td>',
                    $index,
                    esc_attr($row['to']),
                    esc_attr__('To', 'sage')
                );
                printf('<td><select name="site_redirects[%d][status]" aria-label="%s">', $index, esc_attr__('Type', 'sage'));

                foreach (redirect_statuses() as $status => $label) {
                    printf('<option value="%d" %s>%s</option>', $status, selected($row['status'], $status, false), esc_html($label));
                }

                echo '</select></td></tr>';
            }

            echo '</tbody></table>';
            submit_button();
            echo '</form></div>';
        }
    );
}, 20);

/**
 * Send visitors on to the new address when the request matches a saved path.
 * The query string travels along, so campaign links keep their tags. Runs
 * before WordPress's own canonical redirect.
 *
 * @return void
 */
add_action('template_redirect', function () {
    $redirects = (array) get_option('site_redirects');

    if (! $redirects) {
        return;
    }

    $requestPath = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';

    // A site in a subfolder serves /sub/old-page for the saved /old-page.
    $requestPath = normalize_redirect_path(substr($requestPath, strlen((string) wp_parse_url(home_url(), PHP_URL_PATH))));

    foreach ($redirects as $redirect) {
        if ($redirect['from'] !== $requestPath) {
            continue;
        }

        $target = str_starts_with($redirect['to'], '/') ? home_url($redirect['to']) : $redirect['to'];
        $query = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_QUERY);

        if ($query && ! str_contains($target, '?')) {
            $target .= '?'.$query;
        }

        // The target is whatever an administrator saved, including other sites.
        wp_redirect($target, $redirect['status']);
        exit;
    }
}, 1);
