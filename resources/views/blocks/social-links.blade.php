@if ($socialLinks())
  <ul {!! get_block_wrapper_attributes(['class' => 'nav social-links', 'aria-label' => __('Social media', 'sage')]) !!}>
    @foreach ($socialLinks() as $label => $url)
      <li class="nav-item">
        <a class="nav-link ps-0" href="{{ $url }}" rel="me">{{ $label }}</a>
      </li>
    @endforeach
  </ul>
@endif
