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

use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends AbstractAction
{

    public function execute()
    {
        /**
         * @var ResultRedirect $resultRedirect
        */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');

        try {
            $productLabelId = (int) $this->getRequest()->getParam('product_label_id');
            $this->modelRepository->deleteById($productLabelId);

            $this->messageManager->addSuccessMessage(__('The product label "%1" has been deleted.', $productLabelId));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The product label to delete does not exist.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
