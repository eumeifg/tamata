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
namespace Magedelight\Vendor\Block\Sellerhtml\Account;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;

/**
 * @author Rocket Bazaar Core Team
 */
class Profile extends \Magedelight\Vendor\Block\Sellerhtml\Form\Register
{
    /**
     *
     * @param string $section
     * @return string
     */
    public function getPostActionUrl($section = 'vendorinfo')
    {
        return $this->_vendorUrl->getEditPostUrl() . 'section/' . $section . '/rbhash/'
            . $this->getVendor()->getEmailVerificationCode();
    }

    /**
     *
     * @return \Magento\User\Model\User|null
     */
    public function getVendor()
    {
        return $this->authSession->getUser();
    }

    /**
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     *
     * @param string $logo
     * @return string
     */
    public function getLogoUrl($logo = '')
    {
        return $this->vendorHelper->getLogoUrl($logo);
    }

    /**
     *
     * @return string
     */
    public function getVatDocUrl()
    {
        return $this->_vendorUrl->getMediaUrl() . 'vendor/vat_doc' . $this->getVendor()->getVatDoc();
    }

    /**
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_vendorUrl->getLogoutUrl();
    }

    /**
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_vendorUrl->getAccountUrl();
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_vendorUrl->getUrl($route, $params);
    }

    /**
     *
     * @return boolean
     */
    public function isVendorOnVacation()
    {
        return ((int)$this->getVendor()->getStatus() === VendorStatus::VENDOR_STATUS_VACATION_MODE);
    }

    /**
     *
     * @return boolean
     */
    public function isVendorActive()
    {
        return ((int)$this->getVendor()->getStatus() === VendorStatus::VENDOR_STATUS_ACTIVE);
    }

    /**
     *
     * @return boolean
     */
    public function isVendorRejected()
    {
        return ((int)$this->getVendor()->getStatus() === VendorStatus::VENDOR_STATUS_DISAPPROVED);
    }

    /**
     * @return boolean
     */
    public function hasPendingVendorVacationRequest()
    {
        if ($this->getVendor()->getData('vacation_request_status') != null) {
            return (!$this->isVendorOnVacation() &&
                !$this->isVendorRejected() &&
                ((int)$this->getVendor()->getData('vacation_request_status')) === 0);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasApprovedVendorVacationRequest()
    {
        if ($this->getVendor()->getData('vacation_request_status') != null) {
            return (!$this->isVendorOnVacation() &&
                !$this->isVendorRejected() &&
                ((int)$this->getVendor()->getData('vacation_request_status')) === 1);
        }
        return false;
    }

    /**
     *
     * @param null $status
     * @return mix
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStatusMessage($status = null)
    {
        if ($status === null) {
            $status = $this->getVendor()->getStatus();
        }

        $isSubuser = false;

        if (!empty($this->authSession->getUser()->getData('vendor_id')) &&
            $this->authSession->getUser()->getData('vendor_id') != $this->getVendor()->getVendorId()) {
            /* Check if its a sub-user or main vendor. Sub - User Case.*/
            $isSubuser = true;
            $status = $this->vendorRepository->getById(
                $this->authSession->getUser()->getData('vendor_id')
            )->getStatus();
        }
        $reason = $this->getVendor()->getData('status_description');
        $message = $this->vendorHelper->getStatusMsg($status, $isSubuser, $reason);
        return $message;
    }

    /**
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     *
     * @return string
     */
    public function getVendorVacationList()
    {
        $collection = $this->vendorStatusRequest->getCollection()
            ->addFieldToFilter('vendor_id', ['eq' => $this->authSession->getUser()->getVendorId()]);
        $sortOrder = $this->getRequest()->getParam('sort_order', 'requested_at');
        $direction = $this->getRequest()->getParam('dir', 'DESC');

        if ($sortOrder != '') {
            $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        }
        return $collection;
    }

    /**
     *
     * @return string
     */
    public function getVendorVacationStatus($status)
    {
        if ($status == 0) {
            return 'Pending';
        } elseif ($status == 1) {
            return 'Approved';
        } elseif ($status == 2) {
            return 'Rejected';
        }
    }

    /**
     * @return \Magedelight\Vendor\Model\Source\available
     */
    public function getRequestList()
    {
        return $this->requestTypes->toOptionArray();
    }

    /**
     * @param $requestType
     * @return string
     */
    public function getRequestType($requestType)
    {
        if ($requestType == 1) {
            return 'Vacation';
        } elseif ($requestType == 2) {
            return 'Close';
        }
    }

    /**
     *
     * @return string
     */
    public function getVendorLogoWidth()
    {
        return $this->vendorHelper->getConfigValue('vendor/general/company_logo_width');
    }

    /**
     *
     * @return string
     */
    public function getVendorLogoHeight()
    {
        return $this->vendorHelper->getConfigValue('vendor/general/company_logo_height');
    }

    /**
     *
     * @param type $collection
     * @param integer $sortOrder
     * @param string $direction
     */
    protected function _addSortOrderToCollection($collection, $sortOrder, $direction)
    {
        $collection->getSelect()->order($sortOrder . ' ' . $direction);
    }

    /**
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('rbvendor/account/index');
    }

    /**
     *
     * @param type $parent
     * @param type $isChild
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getTreeCategories($parent, $isChild)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $vendor = $this->getVendor();
        $vendorCats = $vendor->getCategory();
        ($vendorCats === null) ? $vendorCats = [] : $vendorCats;
        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', ['eq' => $parent->getId()])
            ->addAttributeToFilter('entity_id', ['neq' => $parent->getId()])
            ->addAttributeToSort('position', 'asc')
            ->load();
        $currentlevel = $parent->getLevel() + 1;

        $ulClasses = ($currentlevel > 2) ? " submenu" : "";
        $html = '<ul class="category-ul level-' . $currentlevel . ' ' . $ulClasses . '">';
        foreach ($collection as $category) {
            $addSelectbtn = false;
            $class = 'level-' . $category->getLevel();
            if ($category->getLevel() > $currentlevel) {
                $html .= '<ul class="' . $class . '">';
            } elseif ($category->getLevel() < $currentlevel) {
                $html .= '</ul><ul class="' . $class . '">';
            }

            $childClass = '';
            if ($category->hasChildren()) {
                $childClass = ' expand';
                if ($category->getLevel() == 3) {
                    $childClass .= ' has-children sub-cat-parent';
                } else {
                    $childClass .= ($category->getLevel() == 2) ? ' base has-children' : ' has-children';
                }
                $addSelectbtn = true;
            }

            $html .= '<li class="item level-' . $category->getLevel() . $childClass . '">';
            if (!$category->hasChildren() && !($vendorCats === null)) {
                $checked = in_array($category->getId(), $vendorCats, true) ? ' checked = "checked"' : '';
                $html .= '<input disabled type="checkbox" name="category[]" id="category-' . $category->getId() . '" ';
                $html .= 'value="' . $category->getId() . '" title="' . $category->getName() . '" ';
                $html .= 'class="checkbox"' . $checked . '/>';
            }
            $html .= '<label class="label cat-collapse" for="category-' . $category->getId() . '">';
            $html .= '<span>' . $category->getName() . '</span></label>';
            if ($addSelectbtn) {
                $html .= '<input disabled type="checkbox" name="selectall" id="slt-' . $category->getId() . '" value="';
                $html .= $category->getId() . '" title="' . $category->getName() . '" class="checkbox slt-chk" />';
                $html .= '<label class="label selectall-subcat" for="slt-' . $category->getId() . '"><span>';
                $html .= __('Select All ') . '<strong>' . $category->getName() . '</strong>' . '</span></label>';
            }

            $currentlevel = $category->getLevel();
            if ($category->hasChildren()) {
                $html .= $this->_getTreeCategories($category, true);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
