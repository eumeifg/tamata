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
namespace Magedelight\Catalog\Controller\Sellerhtml\Listing;

abstract class Exports extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     */
    protected $vendorProductFactory;
    
    /**
     * @var Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    protected $resultLayoutFactory;
    
    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }
    
    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
