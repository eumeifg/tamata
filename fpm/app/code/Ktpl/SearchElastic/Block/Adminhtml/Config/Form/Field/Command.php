<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Command
 *
 * @package Ktpl\SearchElastic\Block\Adminhtml\Config\Form\Field
 */
abstract class Command extends Field
{
    /**
     * Prepare layout
     *
     * @return $this|Field
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Ktpl_ElasticSearch::config/form/field/command.phtml');
        }

        return $this;
    }

    /**
     * Render command element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get html element
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = $originalData['button_label'];
        $this->addData([
            'button_label' => __($buttonLabel),
            'html_id'      => $element->getHtmlId(),
            'ajax_url'     => $this->_urlBuilder->getUrl('searchelastic/command/' . $this->getAction()),
        ]);

        return $this->_toHtml();
    }

    /**
     * {@inheritdoc}
     */
    abstract protected function getAction();
}
