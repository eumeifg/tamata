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
namespace Magedelight\Vendor\Controller\Adminhtml\Request;

use Magento\Ui\Component\MassAction\Filter;
use Magedelight\Vendor\Model\ResourceModel\Request\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class AbstractMassStatus
 */
abstract class AbstractMassAction extends \Magento\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     *
     * @var Filter
     */
    protected $filter;
    /**
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $_vendorRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Vendor\Model\ResourceModel\Request\CollectionFactory $collectionFactory,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_vendorRepository = $vendorRepository;
        parent::__construct($context);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::save');
    }
}
