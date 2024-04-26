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

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class ImportPost extends \Magedelight\Backend\App\Action
{
    protected $resultPageFactory;
    protected $csvProcessor;
    protected $rateBlock = null;
    protected $_headersArray = [];
    protected $_catalogHelper;
    protected $shippingMatrix;
    protected $layoutFactory;

    /**
     * ImportPost constructor.
     * @param Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magedelight\Shippingmatrix\Model\Shippingmatrix $shippingMatrix
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        PageFactory $resultPageFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magedelight\Shippingmatrix\Model\Shippingmatrix $shippingMatrix
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->csvProcessor = $csvProcessor;
        $this->_catalogHelper = $catalogHelper;
        parent::__construct($context);
        $this->shippingMatrix = $shippingMatrix;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|null
     */
    public function getRateBlock()
    {
        if ($this->rateBlock == null) {
            $this->rateBlock = $this->layoutFactory->create()->createBlock('Magedelight\Shippingmatrix\Block\Sellerhtml\Rates\Rate');
        }
        return $this->rateBlock;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->_headersArray = array_values($this->getHeaders($this->getRateBlock()));
        $resultRedirect = $this->resultRedirectFactory->create();
        $ext = pathinfo($_FILES['import_rates_file']['name'], PATHINFO_EXTENSION);

        if ($_FILES['import_rates_file']['name'] == 0 && $_FILES['import_rates_file']['error'] == 4) {
            $this->messageManager->addError(__('No file has been selected. Please select file.'));
        } else {
            if ($ext == 'csv') {
                $shippingRates = $this->csvProcessor->getData($_FILES['import_rates_file']['tmp_name']);
                try {
                    $isDuplicateRowFound = $this->_catalogHelper->checkCSVForDuplicateRows($shippingRates, [0, 1, 2, 3, 4, 5, 6, 8]);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('The uploaded file is not valid.'));
                    return $resultRedirect->setPath('*/*/');
                }

                if (!empty($isDuplicateRowFound)) {
                    $this->messageManager->addError(__(
                        'Duplicate Row(s) #(%1)',
                        implode(", ", $isDuplicateRowFound)
                    ));

                    return $resultRedirect->setPath('rbshippingmatrix/*/');
                }

                if (!empty($shippingRates)) {
                    $i = 1;
                    foreach ($shippingRates as $rowIndex => $dataRow) {
                        if ($rowIndex == 0) {
                            foreach ($dataRow as $column) {
                                if (!in_array($column, $this->getHeaders($this->getRateBlock()))) {
                                    $this->messageManager->addError(__('Csv Field not match.'));
                                    return $resultRedirect->setPath('*/*/');
                                }
                            }
                            continue;
                        }
                        if (!$this->_importShippingRate($dataRow, $i)) {
                            break;
                        } else {
                            $i++;
                        }
                    }
                    if ($i > 1) {
                        $this->messageManager->addSuccess(__('You saved this matrix rates.'));
                    }
                } else {
                    $this->messageManager->addError("You can't upload empty file.");
                }
            } else {
                $this->messageManager->addError(__('Invalid file formate. Upload only csv file.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $rateData
     * @param $count
     * @return bool
     */
    protected function _importShippingRate(array $rateData, $count)
    {

        $flag = false;
        $model = $this->shippingMatrix;
        $rateData[0] = isset($rateData[0]) ? $rateData[0] : '';
        $rateData[1] = isset($rateData[1]) ? $rateData[1] : '';
        $block = $this->getRateBlock();
        $regionId = $block->getRegionId($rateData[0], $rateData[1]);
        $formBlock = $this->layoutFactory->create()->createBlock('Magedelight\Shippingmatrix\Block\Sellerhtml\Rates\Edit\Form');
        $conditionName = $formBlock->getShippingMatrixCondition();
        $vendorId = $this->_auth->getUser()->getVendorId();

        $inValidRow = $this->isValidRow($rateData);
        if (true !== $inValidRow) {
            $this->messageManager->addError(__("%1 value cannot be blank on row #%2", $inValidRow, $count));
            return false;
        }

        $modelData = [
            'vendor_id' => $vendorId,
            'website_id' => '1',
            'dest_country_id' => $rateData[0],
            'dest_region_id' => $regionId,
            'dest_city' => isset($rateData[2]) ? $rateData[2] : '',
            'dest_zip' => isset($rateData[3]) ? $rateData[3] : '',
            'dest_zip_to' => isset($rateData[4]) ? $rateData[4] : '',
            'condition_name' => $conditionName,
            'condition_from_value' => isset($rateData[5]) ? $rateData[5] : '',
            'condition_to_value' => isset($rateData[6]) ? $rateData[6] : '',
            'price' => isset($rateData[7]) ? $rateData[7] : '',
            'shipping_method' => isset($rateData[8]) ? $rateData[8] : 'Freight',
        ];

        $country = trim($modelData['dest_country_id']);
        $city = trim($modelData['dest_city']);
        $price = trim($modelData['price']);
        $shippingMethod = trim($modelData['shipping_method']);
        try {
            $model->setData($modelData);
            if ((empty($country) || $country=='') &&
                    (empty($city) || $city=='') &&
                    (empty($price) || $price=='') &&
                    (empty($shippingMethod) || $shippingMethod=='')
                ) {
                $this->messageManager->addErrorMessage('Invalid Data Row ' . $count);
            } else {
                $model->save();
                $flag = true;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Zend_Db_Statement_Exception $e) {
            $this->messageManager->addException($e, __(
                'Duplicate Row #%1 (Country "%2" and City "%3")',
                $count,
                $modelData['dest_country_id'],
                $modelData['dest_city']
            ));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the matrix rates.'));
        }

        if ($flag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::shippingmethod');
    }

    /**
     *
     * @return array
     */
    protected function getHeaders($block)
    {
        $conditions = $block->getShippingMatrixConditionLabel();
        return [
                'dest_country_id' => __('Country Code'),
                'dest_region' => __('State/Region Code'),
                'dest_city' => __('City'),
                'dest_zip' => __('Zip/Postal Code From'),
                'dest_zip_to' => __('Zip/Postal Code To'),
                'condition_from_value' => __($conditions['condition_from']),
                'condition_to_value' => __($conditions['condition_to']),
                'price' => __('Shipping Price'),
                'shipping_method' => __('Shipping Method ID'),
                'shipping_method_label' => __('Shipping Method Name'),
            ];
    }

    /**
     * @param $rateData
     * @return bool|mixed
     */
    public function isValidRow($rateData)
    {
        foreach ($rateData as $key => $data) {
            if (trim($data) == '') {
                return $this->_headersArray[$key];
            }
        }
        return true;
    }
}
