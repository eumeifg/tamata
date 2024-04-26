<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_Commissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\Commissions\Controller\Adminhtml\VendorGroupCommission;

use Magento\Ui\Component\MassAction\Filter;
use MDC\Commissions\Model\ResourceModel\VendorGroupCommission\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Description of AbstractMassAction
 *
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
}
