<section {!! get_block_wrapper_attributes(['class' => 'hero']) !!}>
  @if ($attributes['heading'])
    <h2 class="hero-heading">{!! wp_kses($attributes['heading'], []) !!}</h2>
  @endif

  @if ($attributes['text'])
    <p class="hero-text">{!! wp_kses_post($attributes['text']) !!}</p>
  @endif

  @if ($attributes['buttonLabel'] && $attributes['buttonUrl'])
    <a class="btn btn-primary btn-lg" href="{!! esc_url($attributes['buttonUrl']) !!}">{{ $attributes['buttonLabel'] }}</a>
  @endif
</section>
