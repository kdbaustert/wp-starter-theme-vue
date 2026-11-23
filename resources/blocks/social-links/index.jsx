import { registerBlockType } from '@wordpress/blocks'
import { useBlockProps } from '@wordpress/block-editor'
import { Disabled } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import ServerSideRender from '@wordpress/server-side-render'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit() {
    return (
      <div {...useBlockProps()}>
        <Disabled>
          <ServerSideRender
            block={metadata.name}
            EmptyResponsePlaceholder={() => (
              <p>{__('Add profile URLs under Theme Settings > Site Settings to show them here.', 'sage')}</p>
            )}
          />
        </Disabled>
      </div>
    )
  },

  // Rendered in PHP by resources/views/blocks/social-links.blade.php.
  save: () => null,
})
