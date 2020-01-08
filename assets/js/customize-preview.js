/**
 * Scripts for working with customizer preview actions
 *
 * @package   Rootstrap Styles
 * @author    Sky Shabatura
 * @copyright Copyright (c) 2019, Sky Shabatura
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

import './Styles.js';
import './CustomProperty.js';


/**
 * Object for interfacing with rootstrap
 */
const rootstrap = {
    screens : () => {
        return Object.entries( parent.rootstrapData.screens );
    },
    style : (data) => {
        const style = new Styles( data );
    },
    var : (data) => {
        const customProperty = new CustomProperty( data );
    }
};