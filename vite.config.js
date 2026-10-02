import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

// Switch parts of the stack off for projects that don't need them. Bootstrap
// is trimmed separately: Sass components in resources/css/app.scss,
// JavaScript plugins in resources/js/bootstrap.js.
const features = {
  // Vue islands (resources/js/components). With this off, Vue is left out of
  // the build entirely and data-vue elements keep their server-rendered HTML.
  vue: true,
}

// Set APP_URL if it doesn't exist for Laravel Vite plugin
if (! process.env.APP_URL) {
  process.env.APP_URL = 'http://example.test';
}

export default defineConfig({
  base: './',
  plugins: [
    laravel({
      input: [
        'resources/css/app.scss',
        'resources/js/app.js',
        'resources/css/editor.scss',
        'resources/js/editor.js',
      ],
      refresh: true,
      assets: ['resources/images/**', 'resources/fonts/**'],
    }),

    // Let laravel-vite-plugin resolve relative asset URLs in templates to the
    // dev server, and leave absolute URLs pointing at the WordPress site.
    features.vue && vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),

    wordpressPlugin(),

    // Copy theme.json into public/build/assets. Its palette is maintained by
    // hand to match Bootstrap's $theme-colors, since there is no Tailwind
    // theme left for the plugin to read.
    wordpressThemeJson({
      disableTailwindColors: true,
      disableTailwindFonts: true,
      disableTailwindFontSizes: true,
      disableTailwindBorderRadius: true,
    }),
  ],
  define: {
    __THEME_VUE__: JSON.stringify(features.vue),
  },
  css: {
    preprocessorOptions: {
      scss: {
        // Bootstrap 5.3 still uses @import and Sass global functions, which
        // Dart Sass deprecates. Silence the noise from Bootstrap's own files
        // without hiding deprecations in ours.
        quietDeps: true,
        silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
      },
    },
  },
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
})
