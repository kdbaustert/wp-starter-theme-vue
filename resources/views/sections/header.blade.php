<header class="banner">
  <nav class="navbar navbar-expand-lg bg-body-tertiary" aria-label="{{ __('Primary', 'sage') }}">
    <div class="container">
      <a class="navbar-brand brand" href="{{ home_url('/') }}">
        @if ($logo())
          {!! $logo() !!}
        @else
          {!! $siteName !!}
        @endif
      </a>

      @if ($colorModes())
        <div class="dropdown order-lg-last ms-auto ms-lg-3 me-2 me-lg-0" data-color-mode-switch>
          <button
            class="btn btn-sm btn-outline-secondary dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <span class="visually-hidden">{{ __('Colour mode:', 'sage') }}</span>
            <span data-color-mode-label>{{ __('Auto', 'sage') }}</span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end">
            @foreach (['light' => __('Light', 'sage'), 'dark' => __('Dark', 'sage'), 'auto' => __('Auto', 'sage')] as $mode => $label)
              <li>
                <button class="dropdown-item" type="button" data-color-mode-value="{{ $mode }}" aria-pressed="false">
                  {{ $label }}
                </button>
              </li>
            @endforeach
          </ul>
        </div>
      @endif

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
