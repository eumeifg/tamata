<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Review\Index;

use Magento\Framework\Controller\ResultFactory;

class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $vendorRating;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    
    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magedelight\Vendor\Model\Vendorrating $vendorRating
     * @param \Magedelight\Vendor\Model\Vendorfrontratingtype $vendorFrontRatingType
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Vendor\Model\Vendorrating $vendorRating,
        \Magedelight\Vendor\Model\Vendorfrontratingtype $vendorFrontRatingType
    ) {
        $this->_date = $date;
        $this->storeManager = $storeManager;
        $this->vendorRating = $vendorRating;
        $this->vendorFrontRatingType = $vendorFrontRatingType;
        parent::__construct($context);
    }
    
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $post = $this->getRequest()->getPostValue();
        $storeId = $this->storeManager->getStore()->getId();
        try {
            $error = false;
            if (!\Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
                $error = true;
            }
            if ($error) {
                throw new \Exception();
            }
            $rating = [];
            if (isset($post['ratings']) && is_array($post['ratings'])) {
                $rating = $post['ratings'];
            } else {
                $rating = $this->getRequest()->getParam('ratings', []);
            }
            $model = $this->vendorRating;
            $sharedBy = $post['shared_by'] = 1;
            $model->setData('customer_id', $post['customer_id']);
            $model->setData('comment', $post['comment']);
            $model->setData('created_at', $this->_date->gmtDate());
            $model->setData('vendor_id', $post['vendor_id']);
            $model->setData('shared_by', $sharedBy);
            $model->setData('vendor_order_id', $post['incremen_id']);
            $model->setData('store_id', $storeId);
            $model->save();
            $vRatingId = $model->getVendorRatingId();
            $allRatCount = count($rating);
            foreach ($rating as $ratingId => $optionId) {
                $optionData = explode('_', $optionId);
                $ratmodel = $this->vendorFrontRatingType;
                $ratmodel->setData('vendor_rating_id', $vRatingId);
                $ratmodel->setData('option_id', $optionData[0]);
                $rvalueFinal = (($optionData[1] / 5) * 100) / $allRatCount;
                $ratmodel->setData('rating_value', $optionData[1]);
                $ratmodel->setData('rating_avg', $rvalueFinal);
                $ratmodel->setData('store_id', $storeId);
                $this->vendorFrontRatingType->unsetData('entity_id');
                $ratmodel->save();
            }
            $this->messageManager->addSuccessMessage(__('Vendor Rating has been submitted for moderation.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }
        
        $resultRedirect->setPath('sales/order/view/order_id/' . $post['order_id']);
        return $resultRedirect;
    }
}
