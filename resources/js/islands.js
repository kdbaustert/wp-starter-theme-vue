import { createApp } from 'vue'

// Each component is its own chunk, fetched only when a page asks for it.
const components = import.meta.glob('./components/*.vue')

/**
 * Mount a Vue app on every element marked up as an island:
 *
 *   <div data-vue="ExampleComponent" data-props='@json(['title' => 'Hi'])'>
 *     Server-rendered fallback, replaced once Vue mounts.
 *   </div>
 *
 * Only these elements are handed to Vue. Post content and plugin output
 * elsewhere on the page is never compiled as a template.
 */
export function mountIslands(root = document) {
  for (const el of root.querySelectorAll('[data-vue]')) {
    const name = el.dataset.vue
    const load = components[`./components/${name}.vue`]

    if (!load) {
      console.warn(`[islands] No component "${name}" in resources/js/components`)
      continue
    }

    let props
    try {
      props = JSON.parse(el.dataset.props || '{}')
    } catch (error) {
      console.warn(`[islands] Invalid data-props on "${name}"`, error)
      continue
    }

    whenReady(el, () =>
      load().then(module => createApp(module.default, props).mount(el))
    )
  }
}

/**
 * Decide when an island's component is downloaded and mounted.
 *
 * TODO: mounts eagerly for now. Swap in a strategy that suits the site.
 */
function whenReady(el, mount) {
  mount()
}
