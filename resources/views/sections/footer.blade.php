<footer class="content-info border-top py-4">
  <div class="container">
    @if (has_nav_menu('footer_navigation') || $socialLinks())
      <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
        @if (has_nav_menu('footer_navigation'))
          <nav aria-label="{{ __('Footer', 'sage') }}">
            {!! wp_nav_menu([
              'theme_location' => 'footer_navigation',
              'container' => false,
              'menu_class' => 'nav',
              'depth' => 1,
              'echo' => false,
            ]) !!}
          </nav>
        @endif

        @if ($socialLinks())
          <ul class="nav" aria-label="{{ __('Social media', 'sage') }}">
            @foreach ($socialLinks() as $label => $url)
              <li class="nav-item">
                <a class="nav-link px-2 text-body-secondary" href="{{ $url }}" rel="me">{{ $label }}</a>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    @endif

    @php(dynamic_sidebar('sidebar-footer'))

    <p class="small text-body-secondary mb-0">{{ $footerText }}</p>
  </div>
</footer>
