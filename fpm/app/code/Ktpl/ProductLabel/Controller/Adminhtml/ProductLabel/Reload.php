<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Controller\Adminhtml\ProductLabel;

use Magento\Backend\App\Action;
use Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory;

class Reload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ktpl_ProductLabel::manage';

    /**
     * @var \Magento\Framework\View\Result\PageFactory|null
     */
    protected $resultPageFactory = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Ktpl\ProductLabel\Api\ProductLabelRepositoryInterface
     */
    protected $productLabelRepository;

    /**
     * Product Label Factory
     *
     * @var \Ktpl\ProductLabel\Api\Data\ProductLabelInterfaceFactory
     */
    protected $productLabelFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory $collectionFactory,
        \Ktpl\ProductLabel\Api\ProductLabelRepositoryInterface $productLabelRepository,
        \Ktpl\ProductLabel\Api\Data\ProductLabelInterfaceFactory $productLabelFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory      = $resultPageFactory;
        $this->coreRegistry           = $coreRegistry;
        $this->dataPersistor          = $dataPersistor;
        $this->filter                 = $filter;
        $this->collectionFactory      = $collectionFactory;
        $this->productLabelRepository = $productLabelRepository;
        $this->productLabelFactory    = $productLabelFactory;
    }

    public function execute()
    {
        if (!$this->getRequest()->getParam('set')) {
            return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        $identifier = $this->getRequest()->getParam('product_label_id');
        $model      = $this->productLabelFactory->create();

        if ($identifier) {
            $model = $this->productLabelRepository->getById($identifier);
        }

        $model->setAttributeId((int) $this->getRequest()->getParam('set'));
        $this->coreRegistry->register('current_productlabel', $model);

        $resultLayout = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT);

        $resultLayout->getLayout()->getUpdate()->removeHandle('default');
        $resultLayout->setHeader('Content-Type', 'application/json', true);

        return $resultLayout;
    }

    protected function createPage()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ktpl_ProductLabel::rule')->addBreadcrumb(__('Product Label'), __('Product Label'));
        return $resultPage;
    }
}
