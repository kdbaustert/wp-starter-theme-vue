import { registerBlockType } from '@wordpress/blocks'
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor'
import { PanelBody, RangeControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import metadata from './block.json'
import card from '../card/block.json'

const template = [[card.name], [card.name], [card.name]]

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { columns } = attributes
    const blockProps = useBlockProps({ className: `row row-cols-1 row-cols-md-${columns} g-4` })
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
      allowedBlocks: [card.name],
      template,
      orientation: 'horizontal',
    })

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Layout', 'sage')}>
            <RangeControl
              label={__('Columns', 'sage')}
              value={columns}
              min={1}
              max={4}
              onChange={value => setAttributes({ columns: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...innerBlocksProps} />
      </>
    )
  },

  // Only the cards are saved. The Blade view wraps them in the grid row.
  save: () => <InnerBlocks.Content />,
})
