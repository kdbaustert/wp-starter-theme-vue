<header class="banner">
  <nav class="navbar navbar-expand-lg bg-body-tertiary" aria-label="{{ __('Primary', 'sage') }}">
    <div class="container">
      <a class="navbar-brand brand" href="{{ home_url('/') }}">
        {!! $siteName !!}
      </a>

      @if (has_nav_menu('primary_navigation'))
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#primary-navigation"
          aria-controls="primary-navigation"
          aria-expanded="false"
          aria-label="{{ __('Toggle navigation', 'sage') }}"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="primary-navigation">
          {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'container' => false,
            'menu_class' => 'navbar-nav ms-auto',
            'depth' => 1,
            'echo' => false,
          ]) !!}
        </div>
      @endif
    </div>
  </nav>
</header>
