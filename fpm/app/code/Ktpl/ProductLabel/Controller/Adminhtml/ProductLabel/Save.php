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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterfaceFactory as ProductLabelFactory;
use Ktpl\ProductLabel\Api\ProductLabelRepositoryInterface as ProductLabelRepository;
use Ktpl\ProductLabel\Model\ProductLabel;

class Save extends AbstractAction
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ProductLabelFactory $modelFactory,
        ProductLabelRepository $modelRepository,
        DataPersistorInterface $dataPersistor,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $modelFactory, $modelRepository, $filter, $collectionFactory);

        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        $request = $this->getRequest();
        $data = $request->getPostValue();
        if (empty($data)) {
            return $resultRedirect;
        }

        // Get the product label id (if edit).
        $productlabelId = null;
        if (!empty($data['product_label_id'])) {
            $productlabelId = (int) $data['product_label_id'];
        }

        // Load the product label.
        $model = $this->initModel($productlabelId);

        // By default, redirect to the edit page of the product label.
        $resultRedirect->setPath('*/*/edit', ['product_label_id' => $productlabelId]);

        /**
         * @var ProductLabel $model
        */
        $model->populateFromArray($data);

        // Try to save it.
        try {
            $this->modelRepository->save($model);
            if ($productlabelId === null) {
                $resultRedirect->setPath('*/*/edit', ['product_label_id' => $model->getProductLabelId()]);
            }

            // Display success message.
            $this->messageManager->addSuccessMessage(__('The product label has been saved.'));
            $this->dataPersistor->clear('ktpl_productlabel');

            // If requested => redirect to the list.
            if (!$this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('*/*/');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('ktpl_productlabel', $data);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving the product label. "%1"', $e->getMessage())
            );
            $this->dataPersistor->set('ktpl_productlabel', $data);
        }

        return $resultRedirect;
    }
}
