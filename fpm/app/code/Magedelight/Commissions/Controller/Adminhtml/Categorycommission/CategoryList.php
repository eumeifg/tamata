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
namespace Magedelight\Commissions\Controller\Adminhtml\Categorycommission;

use Magedelight\Commissions\Model\Source\Category as SourceCateogry;
use Magento\Framework\Json\EncoderInterface;

class CategoryList extends \Magento\Framework\App\Action\Action
{
    /**
     * @var SourceCategory
     */
    protected $sourceCategory;
    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Vendor\Model\Source\Status $vendorStatuses
     * @param EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        SourceCateogry $sourceCategory,
        EncoderInterface $jsonEncoder
    ) {
        $this->sourceCategory = $sourceCategory;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context);
    }

    /**
     * Get available vendors list
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('website');
        $categoryListOptions = '';
        if (!$websiteId || $websiteId == '') {
            $categoryListOptions .= "<option value=''>-- " . __('Please Select Website') . " --</option>";
        } else {
            $categories = $this->sourceCategory->toOptionArray(true, $websiteId);
            foreach ($categories as $category) {
                $disabled = (array_key_exists('disabled', $category) &&
                    $category['disabled']) ? 'disabled="disabled"' : '';
                $categoryListOptions .= "<option value='" . $category['value'] . "' " .
                    $disabled . ">" . $category['label'] . "</option>";
            }
        }

        $result['htmlcontent'] = $categoryListOptions;
        $this->getResponse()->representJson(
            $this->jsonEncoder->encode($result)
        );
    }
}
