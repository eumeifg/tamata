<?php

namespace Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class Messages extends \Magento\Framework\View\Element\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Custom Coupons Error Messages');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Custom Coupons Error Messages');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }


}
