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
namespace Magedelight\Vendor\Block\Sellerhtml\Request;

/**
 * @author Rocket Bazaar Core Team
 */
class Grid extends \Magedelight\Backend\Block\Template
{
    const LIMIT = 10;

    /**
     * @var \Magedelight\Vendor\Model\CategoryRequestFactory
     */
    protected $categoryRequestFactory;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magedelight\Vendor\Model\Category\Request\Source\Status
     */
    protected $requestStatuses;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Vendor\Model\CategoryRequestFactory $categoryRequestFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Vendor\Model\Category\Request\Source\Status $requestStatuses
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Vendor\Model\CategoryRequestFactory $categoryRequestFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Vendor\Model\Category\Request\Source\Status $requestStatuses
    ) {
        $this->categoryRequestFactory = $categoryRequestFactory;
        $this->requestStatuses = $requestStatuses;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCollection($this->getCategoryRequests());
    }

    /**
     *
     * @return Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!empty($this->getCollection())) {

            /** @var \Magento\Theme\Block\Html\Pager */
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.category.request.list.pager'
            );
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            $pager->setLimit(self::LIMIT)
                ->setCollection($this->getCollection());
            $this->setChild('pager', $pager);

            $this->getCollection()->load();
            return $this;
        }
    }

    /**
     *
     * @return array
     */
    public function getCategoryRequests()
    {
        $collection = $this->categoryRequestFactory->create()->getCollection();
        $collection->addFieldToFilter('vendor_id', $this->authSession->getUser()->getVendorId());
        $sortOrder = $this->getRequest()->getParam('sort_order', 'main_table.created_at');
        $direction = $this->getRequest()->getParam('dir', 'DESC');
        $collection->getSelect()->order($sortOrder . ' ' . $direction);
        return $collection;
    }

    /**
     *
     * @return  string
     */
    public function getOptionText($status = null)
    {
        return $this->requestStatuses->getOptionText($status);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     *
     * @return  string
     */
    public function getDeleteUrl($id = null)
    {
        if ($id) {
            return $this->getUrl(
                'rbvendor/categories_request/delete/',
                ['id' => $id, 'tab'=>$this->getRequest()->getParam('tab')]
            );
        }
    }

    /**
     *
     * @return  string
     */
    public function getViewUrl($id = '')
    {
        return $this->getUrl(
            'rbvendor/categories_request/view',
            ['id' => $id, 'tab'=>$this->getRequest()->getParam('tab')]
        );
    }
}
