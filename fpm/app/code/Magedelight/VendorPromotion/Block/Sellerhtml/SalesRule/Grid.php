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
namespace Magedelight\VendorPromotion\Block\Sellerhtml\SalesRule;

class Grid extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Config\Model\Config\Source\Website\OptionHash
     */
    protected $storeOptions;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magedelight\VendorPromotion\Block\Sellerhtml\Widget\Grid\Column\Renderer\Store
     */
    protected $storeRenderer;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Grid constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Quote\CollectionFactory $collectionFactory
     * @param \Magedelight\VendorPromotion\Block\Sellerhtml\Widget\Grid\Column\Renderer\Store $storeRenderer
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Config\Model\Config\Source\Website\OptionHash $storeOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\SalesRule\Model\ResourceModel\Rule\Quote\CollectionFactory $collectionFactory,
        \Magedelight\VendorPromotion\Block\Sellerhtml\Widget\Grid\Column\Renderer\Store $storeRenderer,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Config\Model\Config\Source\Website\OptionHash $storeOptions,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeRenderer = $storeRenderer;
        $this->storeOptions = $storeOptions;
        $this->date = $date;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return \Magedelight\Backend\Model\Auth\Session
     */
    public function getVendorSession()
    {
        return $this->authSession->getUser();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getSalesRules()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'vendor.salesrules.summary.pager'
            );

            $limit = $this->getRequest()->getParam('limit', false);
            $pager->setData('pagersfrm', 'confirmed');
            $pager->setTemplate('Magedelight_VendorPromotion::html/pager.phtml');

            if (!$limit) {
                $limit = 10;
                $pager->setPage(1);
            }
            $pager->setLimit($limit)->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    public function getSalesRules()
    {
        $params = $this->getRequest()->getParams();
        
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('vendor_id', ['finset'=>$this->getVendorSession()->getVendorId()]);
        
        if (!empty($params['name'])) {
            $collection->addFieldToFilter('name', ['like'=>"%".$params['name']."%"]);
        }
        
        if (!empty($params['q'])) {
            $collection->addFieldToFilter('name', ['like'=>"%".$params['q']."%"]);
        }
        
        if (!empty($params['start_date'])) {
            $collection->addFieldToFilter(
                'from_date',
                ['gteq' => $this->date->gmtDate(null, $params['start_date'])]
            );
        }

        if (!empty($params['end_date'])) {
            $collection->addFieldToFilter(
                'to_date',
                ['lteq' => $this->date->gmtDate(null, $params['end_date'])]
            );
        }
        
        if (isset($params['status']) && $params['status']!='') {
            $collection->addFieldToFilter('is_active', ['eq'=>$params['status']]);
        }
        
        if (!empty($params['sort_order']) && !empty($params['dir'])) {
            $collection->setOrder($params['sort_order'], $params['dir']);
        }
        $collection->getSelect()->joinLeft(
            ['srw' => $collection->getResource()->getTable('salesrule_website')],
            "main_table.row_id = srw.row_id",
            ['srw.website_id']
        );

        $collection->addFieldToFilter('srw.website_id', ['eq' => $this->_storeManager->getStore()->getWebsiteId()]);

        $this->setCollection($collection);
        return $collection;
    }

    protected function _addSortOrderToCollection($collection, $sortOrder, $direction)
    {
        $collection->getSelect()->order($sortOrder . ' ' . $direction);
    }
    
    /**
     *
     * @param array|int $origStores
     * @return string
     */
    public function renderStores($origStores)
    {
        return $this->storeRenderer->render($origStores);
    }
    
    /**
     *
     * @param array|int $origWebsiteId
     * @return string
     */
    public function renderWebsite($origWebsiteId)
    {
        return $this->storeRenderer->renderWebsite($origWebsiteId);
    }
    
    public function getStoreOptions()
    {
        return $this->storeOptions->toOptionArray();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function formatAmount($amount)
    {
        return $this->_priceHelper->currency($amount, true, false);
    }

    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
    }

    public function getEditUrl($ruleId)
    {
        return $this->getUrl('rbvendor/salesrule/edit', ['id'=>$ruleId, 'tab' => $this->getRequest()->getParam('tab')]);
    }
}
