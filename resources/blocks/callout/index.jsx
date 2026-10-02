import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor'
import { PanelBody, SelectControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { message, variant } = attributes
    const blockProps = useBlockProps({ className: `alert alert-${variant}` })

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Style', 'sage')}>
            <SelectControl
              label={__('Variant', 'sage')}
              value={variant}
              options={metadata.attributes.variant.enum.map(value => ({ label: value, value }))}
              onChange={value => setAttributes({ variant: value })}
            />
          </PanelBody>
        </InspectorControls>

        <RichText
          {...blockProps}
          tagName="div"
          value={message}
          placeholder={__('Write a message…', 'sage')}
          onChange={value => setAttributes({ message: value })}
        />
      </>
    )
  },

  // Rendered in PHP by resources/views/blocks/callout.blade.php.
  save: () => null,
})
