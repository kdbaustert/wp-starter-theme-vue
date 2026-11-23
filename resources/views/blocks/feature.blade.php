{{-- Dashicons aren't loaded on the front end unless a block asks for them. --}}
@php(wp_enqueue_style('dashicons'))

<div {!! get_block_wrapper_attributes(['class' => 'col feature']) !!}>
  <span class="feature-icon dashicons dashicons-{{ $attributes['icon'] }}" aria-hidden="true"></span>

  @if ($attributes['heading'])
    <h3 class="feature-heading h5">{!! wp_kses($attributes['heading'], []) !!}</h3>
  @endif

  @if ($attributes['text'])
    <p class="feature-text">{!! wp_kses_post($attributes['text']) !!}</p>
  @endif
</div>
