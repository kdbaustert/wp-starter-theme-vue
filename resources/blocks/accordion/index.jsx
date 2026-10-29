import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor'
import { Button, PanelBody, ToggleControl } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const { items, alwaysOpen } = attributes
    const blockProps = useBlockProps()

    const updateItem = (index, changes) => {
      setAttributes({ items: items.map((item, i) => (i === index ? { ...item, ...changes } : item)) })
    }

    const removeItem = index => {
      setAttributes({ items: items.filter((item, i) => i !== index) })
    }

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Behaviour', 'sage')}>
            <ToggleControl
              label={__('Allow several items open at once', 'sage')}
              checked={alwaysOpen}
              onChange={value => setAttributes({ alwaysOpen: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...blockProps}>
          {items.map((item, index) => (
            <div className="accordion-item" key={index}>
              <RichText
                tagName="h3"
                className="accordion-heading"
                value={item.title}
                placeholder={__('Question', 'sage')}
                allowedFormats={[]}
                onChange={value => updateItem(index, { title: value })}
              />
              <RichText
                tagName="div"
                className="accordion-answer"
                value={item.content}
                placeholder={__('Answer', 'sage')}
                onChange={value => updateItem(index, { content: value })}
              />
              <Button isDestructive variant="link" onClick={() => removeItem(index)}>
                {__('Remove item', 'sage')}
              </Button>
            </div>
          ))}

          <Button
            variant="secondary"
            onClick={() => setAttributes({ items: [...items, { title: '', content: '' }] })}
          >
            {__('Add item', 'sage')}
          </Button>
        </div>
      </>
    )
  },

  // Rendered in PHP by resources/views/blocks/accordion.blade.php.
  save: () => null,
})
