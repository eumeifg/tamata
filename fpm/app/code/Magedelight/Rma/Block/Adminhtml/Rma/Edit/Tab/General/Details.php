<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Request Details Block at RMA page
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magedelight\Rma\Block\Adminhtml\Rma\Edit\Tab\General;

/**
 * @api
 * @since 100.0.2
 */
class Details extends \Magento\Rma\Block\Adminhtml\Rma\Edit\Tab\General\Details
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Vendor\Helper\Data $rbVendorHelperData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $data
        );
        $this->request = $request;
        $this->rbVendorHelperData = $rbVendorHelperData;
    }
    
    /**
     * Get Vendor Name
     *
     * @return string
     */
    public function getVendorName()
    {
        $vendorName = "";
        $vendorId = $this->getVendorId();
        if (isset($vendorId)) {
            $this->_coreRegistry->unregister('admin_rma_create_venodr_id');
            $this->_coreRegistry->register("admin_rma_create_venodr_id", $vendorId);
            $vendorName = $this->rbVendorHelperData->getVendorNameById($vendorId);
        } else {
            $vendorName = $this->escapeHtml($this->getRmaData('vendor_name'));
        }
        return $vendorName;
    }
    
    public function getVendorId()
    {
        return $this->request->getParam('do_as_vendor');
    }
}
