<section {!! get_block_wrapper_attributes(['class' => "cta text-bg-{$attributes['variant']}"]) !!}>
  @if ($attributes['heading'])
    <h2 class="cta-heading">{!! wp_kses($attributes['heading'], []) !!}</h2>
  @endif

  @if ($attributes['text'])
    <p class="cta-text">{!! wp_kses_post($attributes['text']) !!}</p>
  @endif

  @if ($attributes['buttonLabel'] && $attributes['buttonUrl'])
    <a class="btn btn-light btn-lg" href="{!! esc_url($attributes['buttonUrl']) !!}">{{ $attributes['buttonLabel'] }}</a>
  @endif
</section>
