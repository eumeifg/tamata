<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;

class SendTestMail extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Path to block template
     */
    const CHECK_TEMPLATE = 'system/config/button.phtml';
    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::CHECK_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'url' => $this->getActionUrl(),
                'html_id' => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }

    public function getActionUrl()
    {
        return $this->getUrl('abandonedcart/emailqueue/testmail');
    }
}
