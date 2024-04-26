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
namespace Magedelight\Commissions\Controller\Adminhtml\Payment;

use Magento\Ui\Component\MassAction\Filter;
use Magedelight\Commissions\Model\Commission\InvoiceFactory as CommissionInvoiceFactory;
use Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Description of AbstractMassAction
 *
 * @author KTPL0012
 */
abstract class AbstractMassAction extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var CommissionInvoiceFactory
     */
    protected $_commissionInvoiceFactory;

    /**
     *
     * @var Filter
     */
    protected $_filter;
    /**
     *
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CommissionInvoiceFactory $commissionInvoiceFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_commissionInvoiceFactory = $commissionInvoiceFactory;
        $this->_date = $date;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::payment');
    }
}
