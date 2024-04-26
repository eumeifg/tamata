<?php

/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
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





namespace Magedelight\SocialLogin\Block\System\Config\Form\Field\Linkedin;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magedelight\SocialLogin\Helper\Data as Linkedinhelper;
use Magento\Backend\Block\Template\Context;

/**
 * Backend system config datetime field renderer.
 */
class Redirect extends FormField
{
    /**
     */
    public $linkedinHelper;

    /**
     * @param Context        $context
     * @param Linkedinhelper $linkedinHelper
     */
    public function __construct(
        Context $context,
        Linkedinhelper $linkedinHelper
    ) {
        $this->linkedinHelper = $linkedinHelper;
        parent::__construct($context);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _getElementHtml(AbstractElement $element)
    {
        $html_id = $element->getHtmlId();
        $redirectUrl = $this->linkedinHelper->getAuthUrl('linkedin');
        //$redirectUrl = str_replace('index.php/', '', $redirectUrl);
        $html = '<input style="opacity:1;" readonly id="'.$html_id.'" '
                . 'class="input-text admin__control-text" '
                . 'value="'.$redirectUrl.'" onclick="this.select()" '
                . 'type="text">';

        return $html;
    }
    // @codingStandardsIgnoreEnd
}
