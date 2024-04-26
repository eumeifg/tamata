<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_VendorCommissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission;

class Save extends \MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $vendorId = $data['vendorid'];

        foreach ($data['parent_commission'] as $pkey => $pvalue) {
            $isValid = $this->checkValid($pvalue, $data['parent_marketplaceCommission'][$pkey], $data['parent_cancellationCommission'][$pkey]);
            if($isValid) {
                $cIds = explode(',',$data['parent_ids'][$pkey]);
                foreach($cIds as $ckey) {
                    if(array_key_exists($ckey, $data['commission'])) {
                        if($data['commission'][$ckey] == '' && $data['marketplaceCommission'][$ckey] == '' && $data['cancellationCommission'][$ckey] == '') {
                            $data['commission'][$ckey] = $data['parent_commission'][$pkey];
                            $data['calculation_type'][$ckey] = $data['parent_calculation_type'][$pkey];
                            $data['marketplaceCommission'][$ckey] = $data['parent_marketplaceCommission'][$pkey];
                            $data['marketplace'][$ckey] = $data['parent_marketplace'][$pkey];
                            $data['cancellationCommission'][$ckey] = $data['parent_cancellationCommission'][$pkey];
                            $data['cancellation'][$ckey] = $data['parent_cancellation'][$pkey];
                            $data['status'][$ckey] = $data['parent_status'][$pkey];
                        }
                    }
                }
            }
        }

        foreach ($data['commission'] as $key => $value) {
            $isValid = $this->checkValid($value, $data['marketplaceCommission'][$key], $data['cancellationCommission'][$key]);
            $model = $this->vendorCategoryCommissionFactory->create();
            if($data['id'][$key] != '') {
                $model->load($data['id'][$key]);
                if($isValid == false) {
                    $model->delete();
                    continue;
                }
            }

            if($isValid) {
                $model->setVendorId($vendorId);
                $model->setProductCategory($key);
                $model->setCalculationType($data['calculation_type'][$key]);
                $model->setCommissionValue($value);
                $model->setMarketplaceFeeType($data['marketplace'][$key]);
                $model->setMarketplaceFee($data['marketplaceCommission'][$key]);
                $model->setCancellationFeeCommissionValue($data['cancellationCommission'][$key]);
                $model->setCancellationFeeCalculationType($data['cancellation'][$key]);
                $model->setStatus($data['status'][$key]);
                $model->save();
            }
        }

        // echo print_r($data, true);
        return false;
    }

    /**
     * Check validation for input values
     */
    private function checkValid($commission, $marketplaceCommission, $cancellationCommission) {
        if(is_numeric($commission) && is_numeric($marketplaceCommission) && is_numeric($cancellationCommission)) {
            return true;
        }

        return false;
    }
}
