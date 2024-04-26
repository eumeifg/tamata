<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\GoogleMapAddress\Model;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;

class AccountManagement extends \Magedelight\Vendor\Model\AccountManagement
{
     protected function processShippingInfo($vendorWebsite)
    {
    	 $vendorFields = [
            'pickup_address1',
            'pickup_address2',
            'pickup_country_id',
            'pickup_region',
            'pickup_region_id',
            'pickup_city',
            'pickup_pincode',
            'pickup_latitude',
            'pickup_longitiude'
        ];
        $this->_activeSection = "shipping-info";
        $this->processVendorFields($vendorFields, $vendorWebsite);
    }
     public function processVendorInfo($vendorWebsite)
    {
        $vendorFields = ['name', 'address1', 'address2', 'country_id', 'region', 'region_id', 'city', 'pincode','latitude','longitude'];
        $this->_activeSection = "vendor-info";
        $this->processVendorFieldsForEdit($vendorFields, $vendorWebsite);
    }

    /**
     * Whole business logic of Edit Vendor Account As per service contract.
     **/
    public function editVendor()
    {
        $this->_initPostCheck();

        $postData = $this->request->getParams();

        $section = $postData['section'];
        $this->vendor = $this->authSession->getUser();

        try {
            $vendorWebsiteDataValidate = true;
            $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($this->vendor->getVendorId());
            $error = $this->_processSection($section, $vendorWebsite, $this->vendor);
            if ($error) {
                return $error;
            }

            $vendorWebsiteDataValidate = $this->validateEdit($section, $vendorWebsite, $this->vendor);
            if (is_array($vendorWebsiteDataValidate) && count($vendorWebsiteDataValidate) > 0) {
                $result['redirect_url'] = $this->url->getUrl('rbvendor/account');
                $result['message']['type'] = 'error';
                $result['message']['data'] = implode(' ', $vendorWebsiteDataValidate);
                return $this->jsonHelper->jsonEncode($result);
            }

            if ($this->vendor->getStatus() === VendorStatus::VENDOR_STATUS_DISAPPROVED) {
                $vendorWebsite->setStatus(VendorStatus::VENDOR_STATUS_PENDING);
            }

            /*....To save pickup latitude and longitude in md_vendor_website_data....*/
            $vendorWebsite->setPickupLatitude($postData['pickup_latitude']);
            $vendorWebsite->setPickupLongitude($postData['pickup_longitude']);

            $eventParams = ['account_controller' => null, 'vendor' => $this->vendor,'request' => $postData];
            $this->eventManager->dispatch('vendor_update_before', $eventParams);
            $this->vendorRepository->save($this->vendor);
            $vendorWebsite->save();
            $eventParams['vendor'] = $vendorWebsite;
            $this->eventManager->dispatch('vendor_update_success', $eventParams);
            $result['message']['type'] = 'success';
            $result['message']['data'] =__('Information has been updated successfully.');
            $url = $this->url->getUrl('*/*/index', ['section' => $this->_activeSection, '_secure' => true]);
            $result['redirect_url'] = $url;
            //return $this->jsonHelper->jsonEncode($result);
        } catch (\Magento\Framework\Model\Exception $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
        } catch (\RuntimeException $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
        } catch (LocalizedException $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
        } catch (\Exception $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Something went wrong while updating the profile.');
        }
        if ($this->_activeSection == "login-info") {
            $url = $this->url->getUrl('rbvendor/account/logout');
        } else {
            $url = $this->url->getUrl('*/*/index', ['section' => $this->_activeSection, '_secure' => true]);
        }
        $result['redirect_url'] = $url;
        return $this->jsonHelper->jsonEncode($result);
    }

    /**
     * @return boolean
     */
    private function _initPostCheck()
    {
        if (!$this->authSession->isLoggedIn()) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Session has been timeout, Please login');
            return $this->jsonHelper->jsonEncode($result);
        }
        if (!$this->request->getParams()) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor/account');
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Please fill up all data');
            return $this->jsonHelper->jsonEncode($result);
        }
        return false;
    }
}