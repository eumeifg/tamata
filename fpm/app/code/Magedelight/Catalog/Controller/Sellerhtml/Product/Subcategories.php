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
use Magento\Framework\Controller\ResultFactory;
use Magedelight\Catalog\Api\SubCategoryInterface;

class Subcategories extends \Magedelight\Backend\App\Action
{

    /**
     * @var SubCategoryInterface
     */
    protected $subCategoryInterface;

    /**
     * @param Context $context
     * @param SubCategoryInterface $subCategoryInterface
     */
    public function __construct(
        Context $context,
        SubCategoryInterface $subCategoryInterface
    ) {
        $this->subCategoryInterface = $subCategoryInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->getParam('id', false)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }
        
        $id = $this->getRequest()->getParam('id', false);
        $level = $this->getRequest()->getParam('level', false);
        $autocompleteData = $this->subCategoryInterface->getChildCategories($id, $level);
        $responseData[] = $autocompleteData;
        
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
