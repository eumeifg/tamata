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

define(["jquery","prototype"], function ($) {
    var Popup = new Class.create();
    if (Popup) {

        var disablePrototypeJS = function (method, pluginsToDisable)
            {
                var handler = function (event) {
                    event.target[method] = undefined;
                    setTimeout(function () {
                        delete event.target[method];
                    }, 0);
                };
                pluginsToDisable.each(function (plugin) {
                    $(window).on(method + '.bs.' + plugin, handler);
                });
            },
            pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover', 'tab'];
        disablePrototypeJS('show', pluginsToDisable);
        disablePrototypeJS('hide', pluginsToDisable);
    }

    Popup.prototype = {
        initialize: function (w, h, l, t) {
            this.screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft;
            this.screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop;
            this.outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth;
            this.outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22);
            this.width = w ? w : 500;
            this.height = h ? h : 270;
            this.left = l ? l : parseInt(this.screenX + ((this.outerWidth - this.width) / 2), 10);
            this.top = t ? t : parseInt(this.screenY + ((this.outerHeight - this.height) / 2.5), 10);
            this.features = (
                'width=' + this.width +
                ',height=' + this.height +
                ',left=' + this.left +
                ',top=' + this.top
            );
        },
        openPopup: function (url, title) {
            window.open(url, title, this.features);
        },
        closePopup: function () {
            window.close();
        }
    };
    return Popup;
});