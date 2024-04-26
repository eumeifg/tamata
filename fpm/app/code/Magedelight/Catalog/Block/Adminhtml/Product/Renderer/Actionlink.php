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
namespace Magedelight\Catalog\Block\Adminhtml\Product\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Actionlink extends AbstractRenderer
{
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->vendorProduct = $vendorProduct;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->_authorization = $context->getAuthorization();
    }

    public function render(DataObject $row)
    {
        $params = ['id'=> $row->getId() , 'approve' => '0'];
        $storeId = $this->getProductWebsite($row->getId());
        if ($storeId) {
            $params['store'] = $storeId;
        }
        $url=$this->getUrl('*/*/edit', $params);
        return sprintf("<a href='%s'>%s</a>", $url, 'Edit');
    }
    
    public function getProductWebsite($id)
    {
        if ($id != null) {
            $productWebModel = $this->productWebsiteRepository->getProductWebsiteData($id);
            $websiteId = $productWebModel->getWebsiteId();
            return $this->getDefaultStoreId($websiteId);
        }
    }
    
    public function getDefaultStoreId($websiteId)
    {
        $defaultStoreId = $this->vendorProduct->getDefaultStoreId($websiteId);
        return $defaultStoreId;
    }
}
