<div {!! get_block_wrapper_attributes(['class' => "alert alert-{$attributes['variant']}", 'role' => 'note']) !!}>
  {!! wp_kses_post($attributes['message']) !!}
</div>
