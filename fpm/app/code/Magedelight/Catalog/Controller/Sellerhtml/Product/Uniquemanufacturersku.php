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

use Magedelight\Backend\App\Action\Context;
use Magedelight\Catalog\Model\ProductRequestFactory;

class Uniquemanufacturersku extends \Magedelight\Backend\App\Action
{

    /**
     * @var VendorProductRequest
     */
    protected $productRequest;

    /**
     * @param Context $context
     * @param ProductRequestFactory $productRequestFactory
     */
    public function __construct(
        Context $context,
        ProductRequestFactory $productRequestFactory
    ) {
        $this->productRequest = $productRequestFactory->create();
        parent::__construct($context);
    }

    public function execute()
    {
        $vendorId = $this->_auth->getUser()->getVendorId();
        $manufacturerSku = $this->getRequest()->getPostValue('manufacturer_sku');
        $errors = $this->productRequest->validateUniqueManufacturerSku($vendorId, $manufacturerSku);
        
        if (!empty($errors)) {
            return $this->getResponse()->setBody($errors);
        }
    }
}
