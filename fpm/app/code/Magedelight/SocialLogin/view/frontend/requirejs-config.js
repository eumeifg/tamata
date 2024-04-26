/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 *This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_SocialLogin
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

var config = {
    map: {
        "*": {
            'Magedelight_SocialLogin/js/sociallogin': 'Magedelight_SocialLogin/js/sociallogin',
            'Magedelight_SocialLogin/js/popup': 'Magedelight_SocialLogin/js/popup',
            'Magedelight_SocialLogin/js/reload-customer': 'Magedelight_SocialLogin/js/reload-customer',
            'Magento_Checkout/js/proceed-to-checkout':'Magedelight_SocialLogin/js/proceed-to-checkout'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/sidebar': {
                'Magedelight_SocialLogin/js/slider-mixin': true
            }
        }
    }
};
