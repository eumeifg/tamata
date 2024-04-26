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

class MassStatus extends AbstractAction
{
    public function execute()
    {
        $collection     = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $status         = (int) $this->getRequest()->getParam('is_active');
        // @codingStandardsIgnoreStart
        $message        = ($status === 0) ? 'A total of %1 product label(s) have been disabled.' : 'A total of %1 product label(s) have been enabled.';
        // @codingStandardsIgnoreEnd

        /**
         * @var \Ktpl\ProductLabel\Api\Data\ProductLabelInterface $plabel
        */
        foreach ($collection as $plabel) {
            $plabel->setIsActive($status);
            $this->modelRepository->save($plabel);
        }

        $this->messageManager->addSuccessMessage(__($message, $collectionSize));

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
