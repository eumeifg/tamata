<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Adminhtml\Categorycommission;

use Magento\Ui\Component\MassAction\Filter;
use Magedelight\Commissions\Model\ResourceModel\Commission\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Description of AbstractMassAction
 *
 * @author KTPL0012
 */
abstract class AbstractMassAction extends \Magento\Backend\App\Action
{
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
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::save');
    }
}
