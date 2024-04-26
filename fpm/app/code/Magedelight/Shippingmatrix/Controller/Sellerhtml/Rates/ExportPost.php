<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Sellerhtml\Rates;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of exportPost
 *
 * @author Rocket Bazaar Core Team
 */
class ExportPost extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }


    /**
     * Export action from import/export vendor order transaction
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $filename = "shippingmatrix.csv";
        $block =  $this->layoutFactory->create()->createBlock('Magedelight\Shippingmatrix\Block\Sellerhtml\Rates\Rate');
        $collection = $block->getMatrixRateCollection(false);
        $collection->getSelect()->joinLeft(
            ['tbl_shipping_method'=>$collection->getTable('md_shipping_methods')],
            'main_table.shipping_method = tbl_shipping_method.shipping_method_id',
            ['shipping_method_label'=>'tbl_shipping_method.shipping_method']
        );

        $conditions = $block->getShippingMatrixConditionLabel();

        /** start csv content and set template */
        $headers = new \Magento\Framework\DataObject(
            [
                'dest_country_id' => __('Country Code'),
                'dest_region' => __('State/Region Code'),
                'dest_city' => __('City'),
                'dest_zip' => __('Zip/Postal Code From'),
                'dest_zip_to' => __('Zip/Postal Code To'),
                'condition_from_value' => __($conditions['condition_from']),
                'condition_to_value' => __($conditions['condition_to']),
                'price' => __('Shipping Price'),
                'shipping_method' => __('Shipping Method ID'),
                // 'shipping_method_label' => __('Shipping Method Name')
            ]
        );

        $template = '"{{dest_country_id}}","{{dest_region}}","{{dest_city}}","{{dest_zip}}","{{dest_zip_to}}","{{condition_from_value}}","{{condition_to_value}}","{{price}}","{{shipping_method}}","{{shipping_method_label}}"';
        $content = $headers->toString($template);
        unset($store);
        $content .= "\n";
      
        while ($transaction = $collection->fetchItem()) {
            $getData = explode(",", $transaction->toString($template));
            $destCountry = $getData[0];
            $destRegion = $getData[1];
            $destCity = $getData[2];
            $destZipFrom = $getData[3];
            $destZipTo = $getData[4];
            $conditionFromValue = $getData[5];
            $conditionToValue = $getData[6];
            $price = $getData[7];
            $shippingMethod = $getData[8];
            $shippingMethodName = $getData[9];
           
            $newString = $destCountry.",".$destRegion.",".$destCity.",".$destZipFrom.",".$destZipTo.",".$conditionFromValue.",".$conditionToValue.",".$price.",".$shippingMethod.",".$shippingMethodName;
            $content .= $newString . "\n";
        }
        return $this->fileFactory->create($filename, $content, DirectoryList::VAR_DIR);
    }
   
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::shippingmethod');
    }
}
