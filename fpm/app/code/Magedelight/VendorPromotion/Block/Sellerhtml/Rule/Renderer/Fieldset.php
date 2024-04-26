<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Block\Sellerhtml\Rule\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\View\Element\Template;

class Fieldset extends Template implements RendererInterface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('Magedelight_VendorPromotion::salesrule/renderer/fieldset.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
    public function getChildElementHtml($elem)
    {
        return $this->getElement()->getForm()->getElement($elem)->toHtml();
    }
    public function getChildElement($elem)
    {
        return $this->getElement()->getForm()->getElement($elem);
    }
    public function isHidden($elem)
    {
        return $this->getElement()->getForm()->getElement($elem)->getIsHidden();
    }
    public function isRequired($elem)
    {
        return $this->getElement()->getForm()->getElement($elem)->getRequired();
    }
}
