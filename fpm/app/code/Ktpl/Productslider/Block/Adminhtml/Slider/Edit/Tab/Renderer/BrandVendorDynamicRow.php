<?php

namespace Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;
use Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab\Renderer\BrandVendor;

class BrandVendorDynamicRow extends AbstractElement
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * Responsive constructor.
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        LayoutInterface $layout,
        array $data = []
    ) {
        $this->layout = $layout;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<div id="' . $this->getHtmlId() . '">';
        $html .= $this->layout->createBlock(BrandVendor::class)
            ->setElement($this)
            ->toHtml();
        $html .= '</div>';

        return $html;
    }
}
