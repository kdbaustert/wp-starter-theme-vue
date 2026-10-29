# WP Starter Theme

A WordPress starter theme built on [Sage 11](https://roots.io/sage/), with Bootstrap 5 compiled from SCSS and Vue 3 for interactive pieces. It's meant to be the first commit of any project: a blog, a brochure site, a shop, or something with its own content types. Run one command to rebrand it, then switch off whatever the project doesn't need.

## Features

- **Blade templates through Acorn.** Sage's templating and Laravel's container, view composers and service providers, inside WordPress.
- **Vite builds.** Hot reload while you work, hashed and split files in production.
- **Bootstrap 5.3 from source.** Override any Sass variable, and list only the components and JavaScript plugins you use.
- **Vue 3 islands.** Mount single-file components on the elements you mark. Pages without one never download Vue.
- **Blocks with Blade rendering.** Add a folder with a `block.json`, an editor script and a Blade view, and it's registered. An example Callout block is included.
- **Post types and taxonomies.** One file with a list for each.
- **WooCommerce in Blade.** Shop templates render through the theme's layout when WooCommerce is active, and nothing happens when it isn't.
- **A rename script.** It sets the theme name, slug, text domain, block namespace and package names in one go.
- **A build that doesn't care where it lives.** Asset URLs are relative, so the same build works under Bedrock (`app/themes`) or a standard install (`wp-content/themes`), in any folder name.
- **Block editor styles.** Bootstrap typography, buttons, tables and alerts inside the editor, and a colour palette that matches Bootstrap's theme colours.
- **Google Analytics.** Paste a Measurement ID under Theme Settings → Google Analytics or set it as a constant, and pick the environments the tag loads on.
- **Site Settings.** Contact details, social profile links, the footer line, a comments switch and the Search Console verification code under Theme Settings → Site Settings.
- **Light and dark mode.** A switch in the navbar uses Bootstrap's colour modes and follows the visitor's system setting until they pick one.
- **A tidier head.** No emoji polyfill, generator tag, RSD link or oEmbed discovery links, and XML-RPC is off.
- **Custom code boxes.** Head and footer snippets for pixels and widgets, under Theme Settings → Custom Code.
- **Self-hosted fonts.** Drop WOFF2 files in `resources/fonts` and pick a body and heading font under Theme Settings → Typography.
- **Performance switches.** Preload the body font, and drop jQuery, the wp-embed script or the block library styles.
- **Redirects.** A from/to table with 301 and 302 types under Theme Settings → Redirects.

## Requirements

- PHP 8.3 or later
- WordPress 6.6 or later
- Composer 2
- Node.js 20.19+ or 22.12+

## Getting started

1. **Put the theme in your themes folder.** Name the folder after the project, because that name becomes the theme's slug.

   ```sh
   cd wp-content/themes          # or web/app/themes on Bedrock
   git clone git@github.com:kdbaustert/wp-starter-theme.git my-project
   cd my-project
   ```

2. **Install dependencies.**

   ```sh
   composer install
   npm install
   ```

3. **Rebrand it.** You'll be asked for a name, slug, description, author and URLs, with defaults filled in. The slug defaults to the folder name.

   ```sh
   npm run rename
   ```

   To skip the questions, pass the values directly:

   ```sh
   npm run rename -- --name "My Project" --slug my-project --description "..." \
     --author "Me" --author-uri https://example.com --uri https://example.com
   ```

   The starter's code uses `sage` as its text domain (`__('Search', 'sage')`) and as its block namespace (`sage/callout`). The script swaps the quoted text domain and the block names in `functions.php`, `index.php`, `app`, `resources/views`, `resources/blocks` and `resources/js` for your slug, so run it before you write any code. Strings and blocks you add afterwards need your slug, not `sage`.

4. **Point the dev server at your local site.**

   ```sh
   cp .env.example .env
   ```

   Then set `APP_URL` to your local WordPress address, for example `http://my-project.test`.

5. **Build the assets.**

   ```sh
   npm run dev      # dev server with hot reload, leave it running
   npm run build    # production build into public/build
   ```

6. **Activate the theme** under Appearance → Themes, assign menus to **Primary Navigation** and **Footer Navigation** under Appearance → Menus, and upload a logo under Appearance → Customize → Site Identity.

`public/build` is gitignored, so run `npm run build` as part of your deploy.

## Working with the theme

### Bootstrap

- **Change the look.** Set variables in `resources/css/_variables.scss`. Anything set there wins over Bootstrap's defaults, and you can call Bootstrap's functions such as `tint-color()`. If you change a theme colour, change the same entry in `theme.json` so the editor palette matches.
- **Drop components you don't use.** Each Sass component is one line in `resources/css/app.scss`. Each JavaScript plugin is one line in `resources/js/bootstrap.js`, and Popper is only bundled while dropdown, tooltip or popover is listed.
- **Use a plugin from code.** Import its class: `import { Modal } from '@scripts/bootstrap'`. Markup with `data-bs-*` attributes works without any code.
- **WordPress classes.** Core output such as `alignleft`, `screen-reader-text` and image captions is styled in `resources/css/_wordpress.scss`.
- **Core blocks.** `resources/css/_blocks.scss` maps core blocks onto Bootstrap, in the editor and on the site: Button is `.btn-primary` and its Outline style `.btn-outline-primary`, Table is `.table` and its Stripes style `.table-striped`, Quote is `.blockquote`, and List gets an Unstyled style.
- **Font sizes and spacing.** The editor's font size and spacing pickers in `theme.json` follow Bootstrap's type scale and `$spacers` 1 to 5. Change them alongside the Sass variables.

### Vue islands

Add a component to `resources/js/components`, then mark where it goes in any Blade view:

```blade
<div data-vue="ExampleComponent" data-props='@json(['title' => 'Hello'])'>
  Shown until Vue loads, and to visitors without JavaScript.
</div>
```

- The `data-vue` value is the component's file name. Props are JSON in `data-props`.
- Vue replaces the element's contents when it mounts, so whatever Blade renders inside works as a fallback.
- Only marked elements are handed to Vue. Post content is never compiled as a Vue template, so `{{ }}` typed into a post stays text.
- Each component is its own file, and Vue itself only loads on pages that contain an island.
- If a project doesn't need Vue, set `vue: false` in the `features` block at the top of `vite.config.js`. Vue then drops out of the build entirely.

### Blocks

Every folder in `resources/blocks` is registered as a block. The Callout example shows the layout:

```
resources/blocks/callout/block.json     name, attributes, settings
resources/blocks/callout/index.jsx      editor interface
resources/views/blocks/callout.blade.php    front-end output
```

To add a block, copy the folder and the view, then rename both. The Blade view receives `$attributes`, `$content` and `$block`. `@wordpress/*` imports in the editor script use WordPress's own copies, so there's nothing to install.

The theme ships these examples besides Callout:

- **Hero** and **Call to action.** Attribute-only blocks with a heading, text and a button.
- **Accordion.** Shows a repeated list in an attribute: add, edit and remove items in the editor, rendered as a Bootstrap accordion.
- **Card grid** with **Card.** Shows inner blocks. Cards can only go inside the grid, and the grid's column count is a setting.

### Post types and taxonomies

Add entries to the `$postTypes` and `$taxonomies` lists in `app/content.php`. The file has a commented example. Templates follow the usual hierarchy as Blade views, for example `resources/views/single-project.blade.php` or `archive-project.blade.php`.

### ACF fields

With [Advanced Custom Fields](https://www.advancedcustomfields.com/) active, every field group, post type, taxonomy and options page you save in the admin is written to `resources/acf-json` as a JSON file. Commit those files. ACF reads the folder on every site, so the fields work after a deploy without touching the database. To edit them in the admin on another copy of the site, open ACF → Field Groups and use **Sync available** to import the newer files.

### WooCommerce

Activate WooCommerce and the shop renders inside the theme's layout. This is handled by [generoi/sage-woocommerce](https://github.com/generoi/sage-woocommerce).

- **Shop templates.** The shop and product page live in `resources/views/woocommerce`.
- **Override any WooCommerce template.** Recreate its path under that folder as Blade. For example, `woocommerce/single-product/title.php` becomes `resources/views/woocommerce/single-product/title.blade.php`.
- **Product gallery zoom, lightbox and slider.** Add `add_theme_support('wc-product-gallery-zoom')` and the `-lightbox` and `-slider` equivalents to `app/setup.php`.
- **If the shop ignores the Blade views,** run `wp acorn package:discover`. That makes Acorn pick up the package.

### Menus

The primary menu renders as a Bootstrap navbar that collapses on small screens. It shows one level: dropdown submenus would need a custom nav walker. The footer menu renders as a row of plain links, also one level.

The navbar shows the logo from Appearance → Customize → Site Identity, or the site name when there isn't one.

### Images

`app/setup.php` registers two cropped sizes, `card` (720×405) and `hero` (1920×720), and adds them to the editor's size picker. Images uploaded before a size existed don't have it until you run `wp media regenerate`.

### Site Settings

Theme Settings → Site Settings holds:

- **Footer text.** `{year}` becomes the current year. Left empty, it's `© {year}` and the site name.
- **Contact details.** A phone number, email and address, shown in the footer. On the front page they also go into Organization schema (JSON-LD) along with the logo and social profiles. The schema is skipped when Yoast SEO or Rank Math is active, since both print their own.
- **Social profiles.** Every network with a URL gets a link in the footer. The list is `social_networks()` in `app/setup.php`.
- **Search Console.** Paste the HTML tag verification code, or the whole `<meta>` tag.
- **Colour modes.** Turns the light and dark switch on or off.
- **Comments.** Unticked, comments and pingbacks are closed on every post type, existing ones are hidden, and Comments leaves the admin menu and toolbar. Nothing is deleted, so ticking it again brings them back.

### Custom Code

Theme Settings → Custom Code has two boxes, one printed at the end of the `<head>` and one before `</body>`. Whatever you type goes out as written, so only users who can post unfiltered HTML (administrators on a single site, super admins on multisite) can edit them. Everyone else sees the boxes read-only.

### Fonts

Theme Settings → Typography lists the families found in `resources/fonts`. Each family is a folder of WOFF2 files named for their weight, with `-italic` for italics:

```
resources/fonts/inter/400.woff2
resources/fonts/inter/400-italic.woff2
resources/fonts/inter/700.woff2
```

Run `npm run build` after adding files. The folder name becomes the family name (`open-sans` shows as Open Sans). The body font sets Bootstrap's `--bs-body-font-family` and the heading font applies to `h1` to `h6`. The `@font-face` rules use `font-display: swap`. The block editor doesn't pick the fonts up.

### Performance

Theme Settings → Performance has four switches, all off by default:

- **Preload the body font.** Preloads the body font's `400.woff2`, so the Typography page needs a body font and that file.
- **Remove jQuery from the front end.** Scripts that depend on jQuery stop loading, so check the plugins first.
- **Remove the wp-embed script.** Posts from other WordPress sites still embed, but don't resize.
- **Remove the block library styles.** The theme only styles Button, Quote, List and Table itself. Columns, Cover, Gallery and similar blocks lose their layout CSS.

### Redirects

Theme Settings → Redirects takes an old path and a new one, which can be a path on this site or a full URL, and a 301 or 302. Paths match without regard to case or a trailing slash, and the visitor's query string is kept unless the target has its own. Rows with an empty field are dropped on save, and a path sent to itself is refused. Chains aren't followed or checked, so point each old path straight at its final address.

### Light and dark mode

The switch in the navbar sets Bootstrap's `data-bs-theme` attribute on `<html>`. Visitors start on Auto, which follows their system setting, and a choice is remembered in `localStorage`. An inline script in the head applies it before the page paints, so there's no flash of the light theme. The switch lives in `resources/js/color-mode.js`. Anything styled with Bootstrap's CSS variables and `-body` utilities follows the mode; hard-coded colours don't.

### Head cleanup

`app/filters.php` removes the emoji polyfill, the generator tag, the RSD link and the oEmbed discovery links from the front end, and turns XML-RPC off. It also removes the Events and News and Quick Draft widgets from the dashboard. Jetpack and the old WordPress mobile apps need XML-RPC, so delete the `xmlrpc_methods` and `wp_headers` filters if a site uses either.

### Google Analytics

Add your GA4 Measurement ID (`G-XXXXXXXXXX`) in one of two places:

- **In the admin,** under Theme Settings → Google Analytics. The same page has a switch that turns the tag off, the environment types it loads on, and an option to leave logged-in authors, editors and administrators untracked.
- **As a constant,** which wins over the admin field and locks it. In `wp-config.php`:

  ```php
  define('GA_MEASUREMENT_ID', 'G-XXXXXXXXXX');
  ```

  On Bedrock, add `Config::define('GA_MEASUREMENT_ID', env('GA_MEASUREMENT_ID'));` to `config/application.php` and set the value in `.env`. Leaving it empty falls back to the admin field.

Out of the box, the tag only loads when `wp_get_environment_type()` is `production`. WordPress reports production when nothing says otherwise, so set `WP_ENVIRONMENT_TYPE` to `local` or `staging` on other copies of the site. Bedrock does this for you from `WP_ENV`.

For visitors in the EU and UK, tick **Consent Mode** on the same page. The tag then starts with analytics and ad cookies denied and sends only cookieless pings until consent is granted. The theme has no cookie banner: add a consent plugin that supports Google Consent Mode v2, or call `gtag('consent', 'update', {...})` from your own banner.

### Translations

Strings use the theme's text domain, which the rename script changes from `sage` to your slug. With [WP-CLI](https://wp-cli.org/) installed:

```sh
npm run translate           # create the .pot file and update the .po files
npm run translate:compile   # build the .mo and JSON files
```

## Project structure

```
app/
  content.php            post types, taxonomies, block registration
  filters.php            WordPress filters, including menu classes and head cleanup
  options.php            custom code, fonts, performance switches, redirects
  setup.php              theme supports, menus, image sizes, sidebars, editor assets, analytics, site settings
  Providers/             Acorn service providers
  View/Composers/        data passed to Blade views
bin/rename.js            the rebrand script
resources/
  acf-json/              ACF field groups, saved from the admin
  blocks/                one folder per block
  css/                   Bootstrap config and theme styles
  js/
    app.js               front-end entry
    bootstrap.js         Bootstrap plugin list
    color-mode.js        light and dark mode switch
    islands.js           Vue island loader
    editor.js            block editor entry
    components/          Vue single-file components
  views/                 Blade templates
    woocommerce/         shop templates
theme.json               block editor settings, palette, font sizes and spacing
vite.config.js           build config and feature switches
```

## Credits

Built on [Sage](https://github.com/roots/sage) by Roots, MIT licensed. See `LICENSE.md`.
