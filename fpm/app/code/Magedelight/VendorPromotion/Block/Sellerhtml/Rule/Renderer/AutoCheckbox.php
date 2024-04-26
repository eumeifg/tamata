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

use Magento\Backend\Block\AbstractBlock;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class AutoCheckbox extends AbstractBlock implements RendererInterface
{

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Data\Form\Element\Checkbox $elementCheckbox
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\Form\Element\Checkbox $elementCheckbox,
        array $data = []
    ) {
        $this->elementCheckbox = $elementCheckbox;
        parent::__construct($context, $data);
    }

    /**
     * Checkbox render function
     *
     * @param AbstractElement $element
     * @return string
     */

    public function render(AbstractElement $element)
    {
        $checkbox = $this->elementCheckbox->create(['data'=>$element->getData()]);
        $checkbox->setForm($element->getForm());

        $elementHtml = $checkbox->getElementHtml() . sprintf(
            '<label for="%s"><b>%s</b></label><p class="note">%s</p>&nbsp;',
            $element->getHtmlId(),
            $element->getLabel(),
            $element->getNote()
        );
        $html  = $elementHtml;

        return $html;
    }
}
