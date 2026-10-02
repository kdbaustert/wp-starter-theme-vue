import domReady from '@wordpress/dom-ready';

// Register every block's editor script. See app/content.php.
import.meta.glob('../blocks/*/index.jsx', { eager: true })

domReady(() => {
  //
});
