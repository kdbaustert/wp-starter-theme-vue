import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor'
import { Dashicon, PanelBody, SelectControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import metadata from './block.json'

// The Dashicons slugs block.json allows, labelled "chart-bar" -> "Chart bar".
const iconOptions = metadata.attributes.icon.enum.map(icon => ({
  value: icon,
  label: icon.charAt(0).toUpperCase() + icon.slice(1).replaceAll('-', ' '),
}))

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { icon, heading, text } = attributes
    const blockProps = useBlockProps({ className: 'col feature' })

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Icon', 'sage')}>
            <SelectControl
              label={__('Icon', 'sage')}
              value={icon}
              options={iconOptions}
              onChange={value => setAttributes({ icon: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          <Dashicon className="feature-icon" icon={icon} />
          <RichText
            tagName="h3"
            className="feature-heading h5"
            value={heading}
            placeholder={__('Heading', 'sage')}
            allowedFormats={[]}
            onChange={value => setAttributes({ heading: value })}
          />
          <RichText
            tagName="p"
            className="feature-text"
            value={text}
            placeholder={__('Write a few words…', 'sage')}
            onChange={value => setAttributes({ text: value })}
          />
        </div>
      </>
    )
  },

  // Rendered in PHP by resources/views/blocks/feature.blade.php.
  save: () => null,
})
