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

class Index extends AbstractAction
{
    public function execute()
    {
        $breadMain = __('Manage Product labels');

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Ktpl_ProductLabel::manage');
        $resultPage->addBreadcrumb(__('Ktpl Product Label'), __('Ktpl Product Label'));
        $resultPage->getConfig()->getTitle()->prepend($breadMain);

        return $resultPage;
    }
}
