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
namespace Magedelight\Vendor\Helper;

use Magedelight\Vendor\Model\Config\Fields;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const DEFAULT_AVATAR_PATH = 'vendor_design/profile/default_avatar';
    const DEFAULT_AVATAR = 'default/profile-placeholder.png';
    const XML_PATH_VENDORVACATION_ENABLED = 'vendor/general/vendor_vacation_mode';
    const XML_PATH_DEFAULT_IS_RTL = 'general/locale/is_rtl';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Backend\Model\Url
     */
    protected $vendorUrl;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Fields
     */
    protected $registrationFields;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var File
     */
    protected $_file;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Backend\Model\Url $vendorUrl
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param Filesystem $_filesystem
     * @param File $file
     * @param Fields $registrationFields
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Backend\Model\Url $vendorUrl,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        Filesystem $_filesystem,
        File $file,
        Fields $registrationFields
    ) {
        $this->request = $context->getRequest();
        $this->_storeManager = $storeManager;
        $this->vendorUrl = $vendorUrl;
        $this->vendorFactory = $vendorFactory;
        $this->_file = $file;
        $this->_filesystem = $_filesystem;
        $this->registrationFields = $registrationFields;
        parent::__construct($context);
    }

    /**
     *
     * @param int $vendorId
     * @return string
     */
    public function getVendorNameById($vendorId = null)
    {
        $vendor = $this->getVendorDetails($vendorId, ['vendor_id'], ['business_name']);
        return $vendor->getBusinessName();
    }
    /**
     *
     * @return string
     */
    public function searchText()
    {
        $search = $this->request->getParam('q');
        if ($search) {
            return $search;
        } else {
            return '';
        }
    }

    /**
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int $storeId
     * @return boolean
     */
    public function isEnabledVendorVacationMode($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_VENDORVACATION_ENABLED, $storeId);
    }

    /**
     * Central function to get full details from both the tables
     * @param int $vendorId
     * @param array $primaryTableFields
     * @param array $secondaryTableFields
     * @return \Magedelight\Vendor\Model\ResourceModel\Vendor\Collection|\Magento\Framework\DataObject
     */
    public function getVendorDetails($vendorId, $primaryTableFields = ['*'], $secondaryTableFields = ['*'])
    {
        if ($vendorId) {
            $vendorColln = $this->vendorFactory->create()->getCollection()
                ->addFieldToFilter('vendor_id', $vendorId)->addFieldToSelect($primaryTableFields);
            $vendorColln->getSelect()->joinLeft(
                ['rvwd'=>'md_vendor_website_data'],
                'rvwd.vendor_id = main_table.vendor_id',
                $secondaryTableFields
            );
            return $vendorColln->getFirstItem();
        }
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (null === $this->_storeManager) {
            $this->_storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->_storeManager;
    }

    /**
     * Get store id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->getStoreManager()->getStore()->getId();
    }

    /**
     * Get store ids
     * @return array
     */
    public function getAllStoreIds()
    {
        $stores = $this->getStoreManager()->getStores();
        $storeList = [];
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            $storeList[] = $storeId;
        }
        return $storeList;
    }

    /**
     *
     * @param string $moduleName
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->isModuleOutputEnabled($moduleName);
    }

    /**
     * Delete File.
     * @param string $fileName
     * @param string $subDir
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFile($fileName = '', $subDir = '')
    {
        if ($fileName) {
            $file = ($subDir) ? $this->_getMediaFilePath() . $subDir . $fileName :
                $this->_getMediaFilePath() . $fileName;
            if ($this->_file->isExists($file)) {
                $this->_file->deleteFile($file);
            }
        }
    }

    /**
     * Get file path.
     *
     * @param string $type
     * @return string
     */
    protected function _getMediaFilePath($type = 'absolute')
    {
        if ($type == 'absolute') {
            return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        } else {
            return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getRelativePath();
        }
    }

    /**
     *
     * @param float|integer $bytes
     * @return integer
     */
    public function getFormattedFileSize($bytes = '')
    {
        $bytes = (float)$bytes;
        if ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 0) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes;
    }

    /**
     * @return mixed
     */
    public function getRTLFlag()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_IS_RTL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $status
     * @param $isSubuser
     * @param null $reason
     * @return bool|\Magento\Framework\Phrase
     */
    public function getStatusMsg($status = null, $isSubuser, $reason = null)
    {
        switch ($status) {
            case VendorStatus::VENDOR_STATUS_PENDING:
                return __('Your account is under review. In the meantime, you can update your profile.');
            case VendorStatus::VENDOR_STATUS_DISAPPROVED:
                return __('Your account is disapproved due to %1. Please correct and resubmit.', $reason);
            case VendorStatus::VENDOR_STATUS_VACATION_MODE:
                return __('Your account mode is on vacation. Your selling is disabled till vacation ends.');
            case VendorStatus::VENDOR_STATUS_CLOSED:
                $data['msg'] = __('Your account has been closed. You will be logged out soon.');
                return $data;
            case VendorStatus::VENDOR_STATUS_INACTIVE:
                $reason = ($isSubuser) ? "Account has been deactivated by parent vendor." : $reason;
                $data['msg'] = __(
                    'Your account has been inactivated due to "%1". You will be logged out soon.',
                    $reason
                );
                return $data;
            default:
                return false;
        }
    }

    /**
     *
     * @param string $field
     * @param string $type
     * @return boolean
     */
    public function isRemoved($field = '', $type = '')
    {
        switch ($type) {
            case 'personal':
                return (!array_key_exists($field, $this->registrationFields->getPersonalFields()));
            case 'business':
                return (!array_key_exists($field, $this->registrationFields->getBusinessFields()));
            case 'shipping':
                return (!array_key_exists($field, $this->registrationFields->getShippingFields()));
            default:
                return true;
        }
    }

    /**
     * @return bool
     */
    public function useWizard()
    {
        return false;
    }

    /**
     *
     * @param string $logo
     * @return string
     */
    public function getLogoUrl($logo = '')
    {
        if ($logo != null && $logo != '') {
            return $this->vendorUrl->getMediaUrl() . 'vendor/logo' . $logo;
        }
        $placeholder = self::DEFAULT_AVATAR;
        $configLogo = $this->getConfigValue(self::DEFAULT_AVATAR_PATH);
        if ($configLogo != null && $configLogo != '') {
            $placeholder = $configLogo;
        }
        return $this->vendorUrl->getMediaUrl() . 'vendor/logo/' . $placeholder;
    }
}
