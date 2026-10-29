import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, MediaUpload, MediaUploadCheck, RichText, useBlockProps } from '@wordpress/block-editor'
import { Button, PanelBody, TextControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { imageId, imageUrl, title, text, linkLabel, linkUrl } = attributes
    const blockProps = useBlockProps({ className: 'col' })

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Image', 'sage')}>
            <MediaUploadCheck>
              <MediaUpload
                allowedTypes={['image']}
                value={imageId}
                onSelect={media => setAttributes({ imageId: media.id, imageUrl: media.url })}
                render={({ open }) => (
                  <Button variant="secondary" onClick={open}>
                    {imageId ? __('Replace image', 'sage') : __('Choose image', 'sage')}
                  </Button>
                )}
              />
            </MediaUploadCheck>
            {imageId > 0 && (
              <Button isDestructive variant="link" onClick={() => setAttributes({ imageId: 0, imageUrl: '' })}>
                {__('Remove image', 'sage')}
              </Button>
            )}
          </PanelBody>
          <PanelBody title={__('Link', 'sage')}>
            <TextControl
              label={__('Label', 'sage')}
              value={linkLabel}
              onChange={value => setAttributes({ linkLabel: value })}
            />
            <TextControl
              label={__('URL', 'sage')}
              type="url"
              value={linkUrl}
              onChange={value => setAttributes({ linkUrl: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          <div className="card h-100">
            {imageUrl && <img className="card-img-top" src={imageUrl} alt="" />}
            <div className="card-body">
              <RichText
                tagName="h3"
                className="card-title h5"
                value={title}
                placeholder={__('Title', 'sage')}
                allowedFormats={[]}
                onChange={value => setAttributes({ title: value })}
              />
              <RichText
                tagName="p"
                className="card-text"
                value={text}
                placeholder={__('Write a few words…', 'sage')}
                onChange={value => setAttributes({ text: value })}
              />
              {linkLabel && <span className="btn btn-primary">{linkLabel}</span>}
            </div>
          </div>
        </div>
      </>
    )
  },

  // Rendered in PHP by resources/views/blocks/card.blade.php.
  save: () => null,
})
