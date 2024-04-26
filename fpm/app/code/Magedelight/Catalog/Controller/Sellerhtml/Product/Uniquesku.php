<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Controller\Sellerhtml\Product;

class Uniquesku extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequest;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
    ) {
        $this->productRequest = $productRequestFactory->create();
        parent::__construct($context);
    }

    public function execute()
    {
        $vendorId = $this->_auth->getUser()->getVendorId();
        $vendorSku = $this->getRequest()->getPostValue('sku');
        if ($vendorId && $vendorSku) {
            $errors = $this->productRequest->validateUniqueVendorSku($vendorId, $vendorSku);
        }
        
        if (!empty($errors)) {
            return $this->getResponse()->setBody($errors);
        }
    }
}
