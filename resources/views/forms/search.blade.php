<form role="search" method="get" class="search-form" action="{{ home_url('/') }}">
  <div class="input-group">
    <input
      type="search"
      class="form-control"
      placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'sage') !!}"
      aria-label="{{ _x('Search for:', 'label', 'sage') }}"
      value="{!! get_search_query() !!}"
      name="s"
    >

    <button class="btn btn-outline-secondary" type="submit">{{ _x('Search', 'submit button', 'sage') }}</button>
  </div>
</form>
