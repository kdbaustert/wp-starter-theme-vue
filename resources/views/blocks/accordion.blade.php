@php($accordionId = wp_unique_id('accordion-'))

<div {!! get_block_wrapper_attributes(['class' => 'accordion', 'id' => $accordionId]) !!}>
  @foreach ($attributes['items'] as $index => $item)
    @continue(empty($item['title']))

    <div class="accordion-item">
      <h3 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accordionId }}-{{ $index }}" aria-expanded="false" aria-controls="{{ $accordionId }}-{{ $index }}">
          {!! wp_kses($item['title'], []) !!}
        </button>
      </h3>
      <div id="{{ $accordionId }}-{{ $index }}" class="accordion-collapse collapse" @unless ($attributes['alwaysOpen']) data-bs-parent="#{{ $accordionId }}" @endunless>
        <div class="accordion-body">{!! wp_kses_post($item['content'] ?? '') !!}</div>
      </div>
    </div>
  @endforeach
</div>
