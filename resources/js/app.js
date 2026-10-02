import './bootstrap'

// __THEME_VUE__ is set from the features switch in vite.config.js. Vue is
// only downloaded on pages that contain an island.
if (__THEME_VUE__ && document.querySelector('[data-vue]')) {
  import('./islands').then(({ mountIslands }) => mountIslands())
}
