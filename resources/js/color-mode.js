// Light, dark and auto colour modes for Bootstrap. An inline script in the
// head (see app/setup.php) applies the stored mode before the page paints;
// this wires up the header switch and follows the system setting in auto.

const STORAGE_KEY = 'color-mode'
const darkQuery = window.matchMedia('(prefers-color-scheme: dark)')

function storedMode() {
  try {
    return localStorage.getItem(STORAGE_KEY) || 'auto'
  } catch {
    return 'auto'
  }
}

function storeMode(mode) {
  try {
    localStorage.setItem(STORAGE_KEY, mode)
  } catch {
    // Private windows can block storage; the mode still applies to this page.
  }
}

function applyMode(mode) {
  const dark = mode === 'dark' || (mode === 'auto' && darkQuery.matches)

  document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light')
}

function showMode(switcher, mode) {
  const label = switcher.querySelector('[data-color-mode-label]')

  switcher.querySelectorAll('[data-color-mode-value]').forEach((button) => {
    const selected = button.dataset.colorModeValue === mode

    button.classList.toggle('active', selected)
    button.setAttribute('aria-pressed', String(selected))

    if (selected && label) {
      label.textContent = button.textContent.trim()
    }
  })
}

export function initColorModes() {
  const switcher = document.querySelector('[data-color-mode-switch]')

  if (!switcher) {
    return
  }

  showMode(switcher, storedMode())

  switcher.addEventListener('click', (event) => {
    const button = event.target.closest('[data-color-mode-value]')

    if (!button) {
      return
    }

    const mode = button.dataset.colorModeValue

    storeMode(mode)
    applyMode(mode)
    showMode(switcher, mode)
  })

  darkQuery.addEventListener('change', () => {
    if (storedMode() === 'auto') {
      applyMode('auto')
    }
  })
}
