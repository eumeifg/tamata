<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
namespace Magedelight\Vendor\Block\Adminhtml\Renderer;

/**
 * Description of Store
 *
 * @author Rocket Bazaar Core Team
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $_storeRepository;

    /**
     * @param \Magento\Store\Model\System\Store $systemStore
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->_storeRepository = $storeRepository;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getStoreId() != '') {
            return  $this->_storeRepository->getById($row->getStoreId())->getName();
        }
    }
}
