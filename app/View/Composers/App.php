<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class App extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*',
    ];

    /**
     * Retrieve the site name.
     */
    public function siteName(): string
    {
        return get_bloginfo('name', 'display');
    }

    /**
     * Retrieve the custom logo as an <img> tag, or an empty string when no
     * logo is set. The header supplies the link, so get_custom_logo(), which
     * wraps the image in its own, isn't used.
     */
    public function logo(): string
    {
        return wp_get_attachment_image(get_theme_mod('custom_logo'), 'full', false, [
            'class' => 'custom-logo',
            'alt' => $this->siteName(),
            // The logo is above the fold; core's get_custom_logo() skips lazy loading too.
            'loading' => false,
        ]);
    }

    /**
     * Retrieve the social profile URLs set under Appearance > Site Settings,
     * keyed by the network's label.
     */
    public function socialLinks(): array
    {
        $urls = (array) get_option('site_social_links');
        $links = [];

        foreach (\App\social_networks() as $network => $label) {
            if (! empty($urls[$network])) {
                $links[$label] = $urls[$network];
            }
        }

        return $links;
    }

    /**
     * Retrieve the footer text, with {year} replaced by the current year.
     */
    public function footerText(): string
    {
        $text = get_option('site_footer_text') ?: '© {year} '.get_bloginfo('name');

        return str_replace('{year}', wp_date('Y'), $text);
    }

    /**
     * Whether visitors get the light and dark colour mode switch.
     */
    public function colorModes(): bool
    {
        return (bool) get_option('site_color_modes');
    }
}
