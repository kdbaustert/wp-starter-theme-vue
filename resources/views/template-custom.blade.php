{{--
  Template Name: Custom Template
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @include('partials.page-header')
    @include('partials.content-page')
  @endwhile

  <div data-vue="ExampleComponent" data-props='@json(['title' => __('Vue island', 'sage')])'>
    {{ __('Loading…', 'sage') }}
  </div>
@endsection
