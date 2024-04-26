<?php
/**
 * @category   CAT
 * @package    CAT_OfferPage
 * @author     mohd.salman0306@gmail.com
 * @copyright  This file was generated by using Module Creator(http://code.vky.co.in/magento-2-module-creator/) provided by VKY <viky.031290@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace CAT\OfferPage\Block;

use Magento\Framework\View\Element\Template\Context;
use CAT\OfferPage\Model\OfferPageFactory;
/**
 * OfferPage List block
 */
class OfferPageListData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var OfferPage
     */
    protected $_offerpage;

    public function __construct(
        Context $context,
        OfferPageFactory $offerpage
    ) {
        $this->_offerpage = $offerpage;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Offer Lists'));

        if ($this->getOfferPageCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'cat.offerpage.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getOfferPageCollection()
            );
            $this->setChild('pager', $pager);
            $this->getOfferPageCollection()->load();
        }
        return parent::_prepareLayout();
    }

    public function getOfferPageCollection()
    {
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;

        $offerpage = $this->_offerpage->create();
        $collection = $offerpage->getCollection();
        $collection->addFieldToFilter('status','1');
        $collection->addFieldToFilter('device','0');
        //$offerpage->setOrder('offerpage_id','ASC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getMediaUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
