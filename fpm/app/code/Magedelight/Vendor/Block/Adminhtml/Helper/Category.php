<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Adminhtml\Helper;

use Magento\Backend\Helper\Data as DataHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\Escaper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * @method mixed getValue()
 */
class Category extends Multiselect
{

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * Backend data
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param CollectionFactory $collectionFactory
     * @param DataHelper $backendData
     * @param LayoutInterface $layout
     * @param EncoderInterface $jsonEncoder
     * @param AuthorizationInterface $authorization
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DataHelper $backendData,
        LayoutInterface $layout,
        EncoderInterface $jsonEncoder,
        AuthorizationInterface $authorization,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->backendData = $backendData;
        $this->layout = $layout;
        $this->jsonEncoder = $jsonEncoder;
        $this->authorization = $authorization;
        $this->request = $request;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Get no display
     *
     * @return bool
     */
    public function getNoDisplay()
    {
        $isNotAllowed = !$this->authorization->isAllowed('Magento_Catalog::categories');
        return $this->getData('no_display') || $isNotAllowed;
    }

    /**
     * Get values for select
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValues()
    {
        $collection = $this->_getCategoriesCollection();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        $collection->addAttributeToSelect('name');
        $collection->addIdFilter($values);
        $options = [];
        foreach ($collection as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }

    /**
     * Get categories collection
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function _getCategoriesCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Attach category suggest widget initialization
     * @return string
     */
    public function getAfterElementHtml()
    {
        if (!$this->isAllowed()) {
            return '';
        }
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search category');
        $selectorOptions = $this->jsonEncoder->encode($this->_getSelectorOptions());

        /** @var Magento\Backend\Block\Widget\Button $button */
        /*
        $newCategoryCaption = __('New Category');
        $button = $this->layout->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'add_category_button',
                'label' => $newCategoryCaption,
                'title' => $newCategoryCaption,
                'onclick' => 'jQuery("#new-category").modal("openModal")',
                'disabled' => $this->getDisabled(),
            ]
        );
        */
        $return = <<<HTML
    <input id="{$htmlId}-suggest" placeholder="$suggestPlaceholder" />
    <script>
        require(["jquery", "mage/mage"], function($){
            $('#{$htmlId}-suggest').mage('treeSuggest', {$selectorOptions});
        });
    </script>
HTML;
        return $return;// . $button->toHtml();
    }

    /**
     * Get selector options
     * @return array
     */
    protected function _getSelectorOptions()
    {
        $store = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if ($this->request->getParam('store')) {
            $store = $this->request->getParam('store');
        }
        return [
            'source' => $this->backendData->getUrl('catalog/category/suggestCategories', ['store'=>$store]),
            'valueField' => '#' . $this->getHtmlId(),
            'className' => 'category-select',
            'multiselect' => true,
            'showAll' => true
        ];
    }

    /**
     * Whether permission is granted
     *
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->authorization->isAllowed('Magento_Catalog::categories');
    }
}
