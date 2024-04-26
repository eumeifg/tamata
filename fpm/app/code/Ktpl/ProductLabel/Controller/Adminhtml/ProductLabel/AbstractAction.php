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
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterface as ProductLabel;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterfaceFactory as ProductLabelFactory;
use Ktpl\ProductLabel\Api\ProductLabelRepositoryInterface as ProductLabelRepository;

abstract class AbstractAction extends Action
{
    /**
     * Authorization level.
     */
    const ADMIN_RESOURCE = 'Ktpl_ProductLabel::manage';

    /**
     * @var ProductLabelFactory
     */
    protected $modelFactory;

    /**
     * @var ProductLabelRepository
     */
    protected $modelRepository;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ProductLabelFactory $modelFactory,
        ProductLabelRepository $modelRepository,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->modelRepository = $modelRepository;
        $this->coreRegistry = $coreRegistry;
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    protected function initModel($labelId)
    {
        /**
         * @var \Ktpl\ProductLabel\Model\ProductLabel $model
        */
        $model = $this->modelFactory->create();

        // Initial checking.
        if ($labelId) {
            try {
                $model = $this->modelRepository->getById($labelId);
            } catch (NoSuchEntityException $e) {
                throw new NotFoundException(__('This product label does not exist.'));
            }
        }

        // Register model to use later in blocks.
        $this->coreRegistry->register('current_productlabel', $model);

        return $model;
    }
}
