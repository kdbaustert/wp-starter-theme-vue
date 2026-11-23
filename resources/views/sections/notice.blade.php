@php($notice = $notice())

@if ($notice)
  <div class="alert alert-primary alert-dismissible fade show rounded-0 border-0 mb-0 text-center" role="region" aria-label="{{ __('Notice', 'sage') }}" data-notice="{{ $notice['id'] }}">
    {{ $notice['text'] }}

    @isset($notice['url'])
      <a class="alert-link ms-1" href="{{ $notice['url'] }}">{{ $notice['label'] ?? __('Find out more', 'sage') }}</a>
    @endisset

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close', 'sage') }}"></button>
  </div>

  {{-- Remove a notice the visitor already closed before the page paints.
       resources/js/notice.js stores the id when they close it. --}}
  {!! wp_get_inline_script_tag(<<<'JS'
    (() => {
      const notice = document.querySelector('[data-notice]');
      try { if (localStorage.getItem('notice-dismissed') === notice.dataset.notice) notice.remove(); } catch (e) {}
    })();
    JS) !!}
@endif
