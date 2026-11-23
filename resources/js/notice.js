// Remember which notice bar the visitor closed. Bootstrap's Alert plugin does
// the closing; an inline script after the bar (see
// resources/views/sections/notice.blade.php) removes it on later visits.

const STORAGE_KEY = 'notice-dismissed'

export function initNotice() {
  const notice = document.querySelector('[data-notice]')

  if (!notice) {
    return
  }

  notice.addEventListener('close.bs.alert', () => {
    try {
      localStorage.setItem(STORAGE_KEY, notice.dataset.notice)
    } catch {
      // Private windows can block storage; the bar just comes back next time.
    }
  })
}
