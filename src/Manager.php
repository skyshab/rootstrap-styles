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
use function Rootstrap\vendor_path;

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
     * @return void
     */
    public function boot() {

        // Action for interacting with the style object
        add_action( 'wp', [ $this, 'registerStyles' ] );

        // Add inline styles to front end
        add_action( 'wp_enqueue_scripts', [ $this, 'publicStyles' ], PHP_INT_MAX );

        // Add inline styles to customize preview
        add_action( 'wp_head', [ $this, 'previewStyles' ] );

        // Enqueue customize preview scripts
        add_action( 'customize_preview_init', [ $this, 'customizePreview'  ] );
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
        wp_add_inline_style( $this->handle, $this->styles->getStyles() );
    }

    // Add styleblock to customize preview head.
    public function previewStyles() {

        // We only want to add this in the customize preview
        if( ! is_customize_preview() ) {
            return;
        }

        echo $this->styles->getCustomizePreview();
    }

    /**
     * Enqueue customize preview scripts
     *
     * If filters are applied defining file locations, load scripts.
     *
     * @since 1.0.0
     */
    public function customizePreview() {

        $resources = vendor_path() . '/skyshab/rootstrap-styles/dist';
        wp_enqueue_script( 'rootstrap-customize-preview', "{$resources}/js/customize-preview.js", [], filemtime( get_template_directory().'/style.css' ) );
    }
}
