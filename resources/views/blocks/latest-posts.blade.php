@php($posts = get_posts([
  'numberposts' => $attributes['postsToShow'],
  // A post shouldn't list itself.
  'exclude' => is_singular('post') ? [get_the_ID()] : [],
]))

@if ($posts)
  <div {!! get_block_wrapper_attributes(['class' => "row row-cols-1 row-cols-md-{$attributes['columns']} g-4"]) !!}>
    @foreach ($posts as $post)
      <div class="col">
        <article class="card h-100">
          @if (has_post_thumbnail($post))
            {!! get_the_post_thumbnail($post, 'medium_large', ['class' => 'card-img-top']) !!}
          @endif

          <div class="card-body">
            <h3 class="card-title h5">
              <a class="stretched-link link-body-emphasis text-decoration-none" href="{{ get_permalink($post) }}">{!! get_the_title($post) !!}</a>
            </h3>
            <p class="card-text small text-body-secondary">
              <time datetime="{{ get_post_time('c', true, $post) }}">{{ get_the_date('', $post) }}</time>
            </p>
            <p class="card-text">{{ get_the_excerpt($post) }}</p>
          </div>
        </article>
      </div>
    @endforeach
  </div>
@endif
