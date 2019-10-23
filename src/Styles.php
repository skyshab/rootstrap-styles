<?php
/**
 * Rootstrap style class.
 *
 * Utility for generating styleblocks using screens defined by our application
 *
 * @package   Rootstrap
 * @author    Sky Shabatura
 * @copyright Copyright (c) 2019, Sky Shabatura
 * @link      https://github.com/skyshab/rootstrap
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace Rootstrap\Styles;

use Rootstrap\Screens\Screens;
use Rootstrap\Styles\Contracts\Styles as Contract;

class Styles implements Contract {

    /**
     * Stores style objects
     *
     * @since  1.0.0
     * @access protected
     * @var    array
     */
    protected $styles;

    /**
     * Stores Custom Property objects
     *
     * @since  1.0.0
     * @access protected
     * @var    array
     */
    protected $custom_properites;

    /**
     * Stores Screens
     *
     * @since  1.0.0
     * @access protected
     * @var    array
     */
    protected $screens;

    /**
     * Setup our Styles object
     *
     * Todo: make this only accept instances of a screens object
     *
     * @since 1.0.0
     * @param object $screens - a Screens object
     * @return void
     */
    public function __construct( $screens = null ) {
        if( $screens === null ) {
            $screens = new Screens();
            $screens->add( 'default', [] );
        }
        $this->screens = $screens;
    }

    /**
     * Sort the screens by min widths
     *
     * @since 1.0.0
     * @return void
     */
    private function sortScreens() {

        $min_array = [];

        // create a new array with min value to be sorted
        foreach ( $this->screens->all() as $name => $screen ) {
            $min = ( 'default' === $name ) ? 0 : $screen->min();
            $min_array[$name] = floatval( $min );
        }

        // sort min array by value
        asort( $min_array );

        return array_keys( $min_array );
    }

    /**
     * Wrap styles with media query, when applicable
     *
     * @since 1.0.0
     * @param string $styles - the styles to wrap
     * @param string $screen - the screen to use for query
     * @return string
     */
    private function query( $styles = false, $screen_name = 'default' ) {

        // if there are no styles to work with, return empty string
        if( !$styles || '' === $styles ) return '';

        // get the screen object
        $screen = $this->screens->get( $screen_name );

        // set our min and max widths
        if( 'default' !== $screen_name && $screen ) {
            $min = $screen->min();
            $max = $screen->max();
        }
        else {
            $min = $max = false;
        }

        // initiate our output string
        $output = '';

        // if there's a media query for our string,
        // wrap our styles
        if( $min || $max ) {

            // begin query
            $output .= '@media ';

            // add min width
            if( $min ) {
                $output .= sprintf( '(min-width: %s)', esc_attr( $min ) );
                // if also a max with, add 'and'
                if( $max ) $output .= esc_attr( ' and ' );
            }

            // add max width
            if( $max ) $output .= sprintf( '(max-width: %s)', esc_attr( $max ) );

            // wrap styles
            $output .= sprintf( '{%s}', $styles );

            // return our styles wrapped in query
            return $output;
        }

        // otherwise, return the styles with no query
        else return $styles;
    }

    /**
     * Get the styles from a particular screen
     *
     * @since 1.0.0
     * @param string    $screen - the screen name
     * @return string
     */
    private function screenStyles( $screen = 'default' ) {

        $has_styles = ( isset( $this->styles[$screen] ) && is_array( $this->styles[$screen] ) ) ? true : false;
        $has_custom_properties = ( isset( $this->custom_properties[$screen] ) && is_array( $this->custom_properties[$screen] ) ) ? true : false;

        // Check if this screen has any styles or vars set. If not return empty string
        if( !$has_styles && !$has_custom_properties  ) return '';

        // initiate outputy string
        $output = '';

        // if there are styles, add each to the output string
        if( $has_styles ) {
            foreach( $this->styles[$screen] as $style ) {
                $output .= $style->get();
            }
        }

        // if there are vars, add each to the output string
        if( $has_custom_properties ) {

            $root_properties = '';
            $contextual_properties = '';

            // loop through variable declarations
            foreach( $this->custom_properties[$screen] as $var ) {

                if( $var->has_selector() ) {
                    $contextual_properties .= $var->get();
                }
                else {
                    $root_properties .= $var->get();
                }
            }

            // add root level vars
            if( '' !== $root_properties ) {
                $output .= sprintf( ':root { %s }', $root_properties );
            }

            // add contextual vars
            if( '' !== $contextual_properties ) {
                $output .= $contextual_properties;
            }
        }

        // return the styles wrapped in a media query
        return $this->query( $output, $screen );
    }

    /**
     * Add a new style.
     *
     * @since  1.0.0
     * @access public
     * @param  array   $args
     * @return void
     */
    public function add( $args ) {
        $style = new Style( $args );
        $this->styles[$style->screen()][] = $style;
    }

    /**
     * Add a new custom property.
     *
     * @since  1.0.0
     * @access public
     * @param  array   $args
     * @return void
     */
    public function customProperty( $args ) {
        $custom_property = new CustomProperty( $args );
        $this->custom_properties[$custom_property->screen()][] = $custom_property;
    }

    /**
     * Add a new screen.
     *
     * @since  1.0.0
     * @access public
     * @param  string    $name
     * @param  array    $args
     * @return void
     */
    public function addScreen( $name, $args = []) {
        $this->screens->add( $name, $args );
    }

    /**
     * Get the styles from all screens
     *
     * @since 1.0.0
     * @access public
     * @return string
     */
    public function getStyles() {

        $styles = '';

        // loop through each screen
        foreach ( $this->sortScreens() as $name ) {

            // if no styles or vars for this screen, skip to next one
            if( !isset($this->styles[$name]) && !isset($this->custom_properties[$name]) ) continue;

            // add the styles
            $styles .= $this->screenStyles( $name );
        }

        return $styles;
    }

    /**
     * Get styles wrapped in a <style> element
     *
     * @since 1.0.0
     * @param string $id - the id to add to the styleblock
     * @return string
     */
    public function getStyleblock( $id = false ) {

        // open styleblock
        $block = ($id) ? sprintf( '<style id="%s">', esc_attr( $id ) ) : '<style>';

        // get styles
        $block .= $this->getStyles();

        // close styleblock
        $block .= '</style>';

        // return styleblock
        return $block;
    }
}
