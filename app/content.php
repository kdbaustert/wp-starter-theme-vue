<?php

/**
 * Project content: post types, taxonomies and blocks.
 */

namespace App;

/**
 * Register custom post types and taxonomies.
 *
 * Both lists start empty. Add entries keyed by slug, with the arguments
 * register_post_type() and register_taxonomy() take, e.g.
 *
 *     'project' => [
 *         'label' => __('Projects', 'sage'),
 *         'public' => true,
 *         'has_archive' => true,
 *         'show_in_rest' => true,
 *         'menu_icon' => 'dashicons-portfolio',
 *         'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
 *     ],
 *
 * Templates follow the normal hierarchy as Blade views, e.g.
 * resources/views/single-project.blade.php.
 *
 * @link https://developer.wordpress.org/reference/functions/register_post_type/
 * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
 *
 * @return void
 */
add_action('init', function () {
    $postTypes = [
        //
    ];

    // Taxonomies also name the post types they attach to, e.g.
    // 'project_type' => ['object_type' => ['project'], 'args' => [...]].
    $taxonomies = [
        //
    ];

    foreach ($postTypes as $postType => $args) {
        register_post_type($postType, $args);
    }

    foreach ($taxonomies as $taxonomy => $config) {
        register_taxonomy($taxonomy, $config['object_type'], $config['args']);
    }
});

/**
 * Register every block in resources/blocks.
 *
 * Each block is a folder holding a block.json and an index.jsx editor script.
 * The front end is rendered by the Blade view of the same name in
 * resources/views/blocks, which receives $attributes, $content and $block.
 *
 * @return void
 */
add_action('init', function () {
    foreach (glob(resource_path('blocks/*/block.json')) as $metadata) {
        $view = 'blocks.'.basename(dirname($metadata));

        register_block_type(dirname($metadata), [
            'render_callback' => fn ($attributes, $content, $block) => view($view, [
                'attributes' => $attributes,
                'content' => $content,
                'block' => $block,
            ])->render(),
        ]);
    }
});
