<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
      <a class="visually-hidden-focusable" href="#main">
        {{ __('Skip to content', 'sage') }}
      </a>

      @include('sections.header')

      <div class="container py-5">
        <div class="row g-5">
          <main id="main" class="main col-lg">
            @yield('content')
          </main>

          @hasSection('sidebar')
            <aside class="sidebar col-lg-4">
              @yield('sidebar')
            </aside>
          @endif
        </div>
      </div>

      @include('sections.footer')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
