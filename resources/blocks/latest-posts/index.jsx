import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, useBlockProps } from '@wordpress/block-editor'
import { Disabled, PanelBody, RangeControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import ServerSideRender from '@wordpress/server-side-render'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { postsToShow, columns } = attributes

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Layout', 'sage')}>
            <RangeControl
              label={__('Number of posts', 'sage')}
              value={postsToShow}
              min={1}
              max={12}
              onChange={value => setAttributes({ postsToShow: value })}
            />
            <RangeControl
              label={__('Columns', 'sage')}
              value={columns}
              min={1}
              max={4}
              onChange={value => setAttributes({ columns: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...useBlockProps()}>
          <Disabled>
            <ServerSideRender
              block={metadata.name}
              attributes={attributes}
              EmptyResponsePlaceholder={() => <p>{__('There are no published posts yet.', 'sage')}</p>}
            />
          </Disabled>
        </div>
      </>
    )
  },

  // Rendered in PHP by resources/views/blocks/latest-posts.blade.php.
  save: () => null,
})
