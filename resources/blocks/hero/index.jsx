import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor'
import { PanelBody, TextControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { heading, text, buttonLabel, buttonUrl } = attributes
    const blockProps = useBlockProps({ className: 'hero' })

    return (
      <>
        <InspectorControls>
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
            className="hero-heading"
            value={heading}
            placeholder={__('Heading', 'sage')}
            allowedFormats={[]}
            onChange={value => setAttributes({ heading: value })}
          />
          <RichText
            tagName="p"
            className="hero-text"
            value={text}
            placeholder={__('Write a short introduction…', 'sage')}
            onChange={value => setAttributes({ text: value })}
          />
          {buttonLabel && <span className="btn btn-primary btn-lg">{buttonLabel}</span>}
        </section>
      </>
    )
  },

  // Rendered in PHP by resources/views/blocks/hero.blade.php.
  save: () => null,
})
