import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor'
import { PanelBody, SelectControl, TextControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { heading, text, buttonLabel, buttonUrl, variant } = attributes
    const blockProps = useBlockProps({ className: `cta text-bg-${variant}` })

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Style', 'sage')}>
            <SelectControl
              label={__('Background', 'sage')}
              value={variant}
              options={metadata.attributes.variant.enum.map(value => ({ label: value, value }))}
              onChange={value => setAttributes({ variant: value })}
            />
          </PanelBody>
          <PanelBody title={__('Button', 'sage')}>
            <TextControl
              label={__('Label', 'sage')}
              value={buttonLabel}
              onChange={value => setAttributes({ buttonLabel: value })}
            />
            <TextControl
              label={__('Link', 'sage')}
              type="url"
              value={buttonUrl}
              onChange={value => setAttributes({ buttonUrl: value })}
            />
          </PanelBody>
        </InspectorControls>

        <section {...blockProps}>
          <RichText
            tagName="h2"
            className="cta-heading"
            value={heading}
            placeholder={__('Heading', 'sage')}
            allowedFormats={[]}
            onChange={value => setAttributes({ heading: value })}
          />
          <RichText
            tagName="p"
            className="cta-text"
            value={text}
            placeholder={__('Say what happens next…', 'sage')}
            onChange={value => setAttributes({ text: value })}
          />
          {buttonLabel && <span className="btn btn-light btn-lg">{buttonLabel}</span>}
        </section>
      </>
    )
  },

  // Rendered in PHP by resources/views/blocks/cta.blade.php.
  save: () => null,
})
