/**
 * Script for interfacing with Rootstrap Styles in the customize preview.
 *
 * @package   Rootstrap Styles
 * @author    Sky Shabatura
 * @copyright Copyright (c) 2019, Sky Shabatura
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

const rootstrap = {
    style: (data) => {
        const style = new Styles( data );
    },
    customProperty: (data) => {
        const customProperty = new CustomProperty( data );
    }
};