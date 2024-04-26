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

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page as ResultPage;

class Edit extends AbstractAction
{
    public function execute()
    {
        $modelId = (int) $this->getRequest()->getParam('product_label_id');
        $model = $this->initModel($modelId);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $breadcrumbTitle = $model->getProductLabelId() ? __('Edit Product Label') : __('New Product Label');
        $resultPage
            ->setActiveMenu('Ktpl_ProductLabel::manage')
            ->addBreadcrumb(__('Ktpl Product Label'), __('Ktpl Product Label'))
            ->addBreadcrumb($breadcrumbTitle, $breadcrumbTitle);

        $title = $model->getProductLabelId() ? __("Edit product label #%1", $model->getProductLabelId()) : __('New product label');

        $resultPage->getConfig()->getTitle()->prepend(__('Manage Ktpl_ProductLabel'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
