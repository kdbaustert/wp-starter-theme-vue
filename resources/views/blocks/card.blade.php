<div {!! get_block_wrapper_attributes(['class' => 'col']) !!}>
  <div class="card h-100">
    @if ($attributes['imageId'])
      {!! wp_get_attachment_image($attributes['imageId'], 'medium_large', false, ['class' => 'card-img-top']) !!}
    @endif

    <div class="card-body">
      @if ($attributes['title'])
        <h3 class="card-title h5">{!! wp_kses($attributes['title'], []) !!}</h3>
      @endif

      @if ($attributes['text'])
        <p class="card-text">{!! wp_kses_post($attributes['text']) !!}</p>
      @endif

      @if ($attributes['linkLabel'] && $attributes['linkUrl'])
        <a class="btn btn-primary" href="{!! esc_url($attributes['linkUrl']) !!}">{{ $attributes['linkLabel'] }}</a>
      @endif
    </div>
  </div>
</div>
