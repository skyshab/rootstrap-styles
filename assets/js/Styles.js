/**
 * Class for adding live styles in the customize preview.
 *
 * @package   Rootstrap Styles
 * @author    Sky Shabatura
 * @copyright Copyright (c) 2020, Sky Shabatura
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

class Styles {

    constructor( data ) {
        if ( !data.id || !data.selector ) return false;
        this.screen = data.screen;
        this.id = ( this.screen ) ? `${data.id}--${data.screen}` : data.id;
        this.selector = data.selector;
        this.styles = data.styles;
        this.insertStyleblock();
    }

    insertStyleblock() {
        const oldBlock = document.getElementById( this.id );

        if( oldBlock ) {
            oldBlock.innerHTML = this.getStyleBlockContent();
        }
        else {
            document.head.insertBefore( this.getStyleBlock(), this.getHook() );
        }
    }

    openQuery() {
        if( !this.screen ) return '';
        const screens = rootstrapScreens;
        const screen = screens[this.screen];
        var query = '';

        if( screen.min || screen.max ) {
            query += '@media ';

            if( screen.min ) {
                query += `(min-width: ${screen.min})`;
                if( screen.max ) {
                    query += ' and ';
                }
            }

            if( screen.max ) {
                query += `(max-width: ${screen.max})`;
            }

            query += '{';
        }

        return query;
    }

    getStyles() {
        var styles = this.selector + '{';
        for (const [property, value] of Object.entries(this.styles) ) {
            if( !property || !value ) continue;
            styles += `${property}: ${value};`;
        }
        styles += '}';

        return styles;
    }

    closeQuery() {
        return ( this.screen ) ? '}' : '';
    }

    getStyleBlockContent() {
        return this.openQuery() + this.getStyles() + this.closeQuery();
    }

    getStyleBlock() {
        const styleblock = document.createElement("style");
        styleblock.setAttribute("id", this.id);
        styleblock.textContent = this.getStyleBlockContent();
        return styleblock;
    }

    getHook() {
        var screen = (this.screen) ? this.screen : 'default';
        return document.getElementById( "rootstrap-style-hook--" + screen );
    }
}

