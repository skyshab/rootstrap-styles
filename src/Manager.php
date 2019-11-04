<?php
/**
 * This file contains a class for handling theme styles.
 *
 * @package   Rootstrap Theme Styles
 * @author    Sky Shabatura
 * @copyright Copyright (c) 2019, Sky Shabatura
 * @link      https://github.com/skyshab/rootstrap
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace Rootstrap\Styles;

use Hybrid\Contracts\Bootable;
use Rootstrap\Screens\Screens;
use WP_Customize_Manager;

/**
 * Class for theme styles.
 *
 * @since  1.0.0
 * @access public
 */
class Manager implements Bootable {

    /**
     * Stores theme stylesheet handle.
     *
     * @since  1.0.0
     * @access public
     * @var    string
     */
    public $handle;

    /**
     * Stores Styles Collection.
     *
     * @since  1.0.0
     * @access public
     * @var    object
     */
    public $styles;

    /**
     * Store associated stylesheet handle and Styles object on instantiation.
     *
     * @since 1.0.0
     * @param string $handle - theme stylesheet handle
     * @param object $screens - Screens Collection
     * @return void
     */
    public function __construct( string $handle, Screens $screens ) {

        // If no handle, bail
        if( ! $handle || ! $screens ) return;

        // Store the stylesheet handle
        $this->handle = $handle;

        // Create and store Styles Collection
        $this->styles = new Styles( $screens );
    }

    /**
     * Load resources.
     *
     * @since 1.0.0
     * @return object
     */
    public function boot() {

        // Action for interacting with the style object
        add_action( 'wp', [ $this, 'registerStyles' ] );

        // Add inline styles to front end
        add_action( 'wp_enqueue_scripts', [ $this, 'publicStyles' ], PHP_INT_MAX );

        // Add inline styles to customize preview
        add_action( 'wp_head', [ $this, 'previewStyles' ] );

        // Add customize preview style refresh action
        add_action( 'rootstrap/customize-register/partials', [ $this, 'partials' ] );
    }

    // Action for adding styles
    // Adding actions for this hook needs to happen prior to "wp"
    public function registerStyles() {
        do_action("rootstrap/styles/{$this->handle}", $this->styles);
    }

    // Register the inline styles after the theme stylesheet has been registered.
    public function publicStyles() {

        // We don't want to add this in the customize preview
        if( is_customize_preview() ) {
            return;
        }

        // Add the inline styles
        wp_add_inline_style( $this->handle, $this->styles() );
    }

    // Add styleblock to customize preview head.
    public function previewStyles() {

        // We only want to add this in the customize preview
        if( ! is_customize_preview() ) {
            return;
        }

        // Print a styleblock with classes for our script.
        printf('<style id="%s-inline-css" class="rootstrap-style-block">%s</style>', $this->handle, $this->styles() );

        // Print script for Chrome bug fix.
        echo "<script>window.addEventListener('resize',()=>{document.querySelectorAll('.rootstrap-style-block').forEach(block=>{block.title=''})});</script>";

    }

    // Get inline styles.
    // This should only be accessed after "wp"
    public function styles() {
        return $this->styles->getStyles();
    }

    /**
     * Refresh customize preview styles when these settings are changed.
     *
     * @since  1.0.0
     * @access public
     * @param object $manager - instance of WP_Customize_Manager
     * @return void
     */
    public function partials( WP_Customize_Manager $manager) {

        if( ! isset($manager->selective_refresh) ) {
            return;
        }

        // Define the styleblock id
        $selector = sprintf('#%s-inline-css', $this->handle);

        // Filter to add controls that trigger style refresh.
        $controls = apply_filters("rootstrap/styles/{$this->handle}/previewRefresh", []);

        // Add partials
        array_map(function($id) use ($manager, $selector) {
            $manager->selective_refresh->add_partial($id, [
                'selector'              => $selector,
                'render_callback'       => [$this, 'styles'],
                'container_inclusive'   => false,
                'settings'              => [$id],
                'fallback_refresh'      => true
            ]);
        }, $controls);
    }
}
