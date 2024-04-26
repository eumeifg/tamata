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
namespace Magedelight\Vendor\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * @author Rocket Bazaar Core Team
 */
class Vendor extends AbstractDb
{

    /**
     * @var \Magedelight\Theme\Model\Source\Region
     */
    protected $regionSource;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    protected $vendorCategoryTable;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
     */
    protected $vendorWebsiteRepository;

    /**
     *
     * @param Context $context
     * @param ManagerInterface $eventManager
     * @param \Magedelight\Theme\Model\Source\Frontend\Region $regionSource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        \Magedelight\Theme\Model\Source\Frontend\Region $regionSource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
    ) {
        $this->eventManager = $eventManager;
        $this->regionSource = $regionSource;
        $this->storeManager = $storeManager;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        parent::__construct($context);
        $this->vendorCategoryTable = $this->getTable('md_vendor_category');
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('md_vendor', 'vendor_id');
    }

    /**
     *
     * @param AbstractModel $vendor
     * @return Vendor $object
     * @throws AlreadyExistsException
     */
    protected function _beforeSave(AbstractModel $vendor)
    {
        parent::_beforeSave($vendor);

        /* set region and pickup_region if regions source available for country*/
        if ($vendor->getData('region_id')) {
            $vendor->setData(
                'region',
                $this->regionSource->getRegionLabel(
                    $vendor->getData('region_id'),
                    $vendor->getData('country_id')
                )
            );
        }

        if ($vendor->getData('pickup_region_id')) {
            $vendor->setData(
                'pickup_region',
                $this->regionSource->getRegionLabel(
                    $vendor->getData('pickup_region_id'),
                    $vendor->getData('pickup_country_id')
                )
            );
        }

        /*
         * Please move this code in controller while save/create a vendor profile
        if (!$vendor->getEmail()) {
            throw new ValidatorException(__('Please enter a vendor email.'));
        }
        if (!$vendor->getMobile()) {
            throw new ValidatorException(__('Please enter a vendor mobile.'));
        }
        */
        $errors = '';

        if ($errors != '') {
            throw new AlreadyExistsException(__($errors));
        }

        // set confirmation key logic
        if ($vendor->getForceConfirmed() || $vendor->getPasswordHash() == '') {
            $vendor->setConfirmation(null);
        }
        return $this;
    }

    /**
     * @param $vendor
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkEmailExist($vendor)
    {
        $connection = $this->getConnection();
        //$bind = ['email' => $vendor['email_id']];
        $bind = ['email' => $vendor['email']];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['email']
        );

        $select->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = ' . $this->getMainTable() . '.vendor_id and rvwd.website_id = '
            . $this->storeManager->getStore()->getWebsiteId(),
            ['website_id']
        );

        $select->where(
            'email = :email'
        );

        if ($vendor['vendor_id']) {
            $bind['vendor_id'] = (int)$vendor['vendor_id'];
            $select->where($this->getMainTable() . '.vendor_id != :vendor_id');
        }

        $result = $connection->fetchAll($select, $bind);

        if (count($result) > 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $vendor
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkMobileExist($vendor)
    {
        $connection = $this->getConnection();

        $bind = ['mobile' => $vendor->getMobile()];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['mobile']
        );

        $select->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = ' . $this->getMainTable() . '.vendor_id and rvwd.website_id = '
            . $this->storeManager->getStore()->getWebsiteId(),
            ['website_id']
        );

        $select->where(
            'mobile = :mobile'
        );

        if ($vendor->getId()) {
            $bind['vendor_id'] = (int)$vendor->getId();
            $select->where($this->getMainTable() . '.vendor_id != :vendor_id');
        }
        $result = $connection->fetchOne($select, $bind);

        if ($result) {
            return __('A vendor with the same mobile already exists.<br/>');
        }
        return '';
    }

    /**
     * @param $vendor
     * @return \Magento\Framework\Phrase|string
     */
    protected function checkVatExist($vendor)
    {
        $connection = $this->getConnection();

        if ($vendor->getVat()) {
            $bind = ['vat' => $vendor->getVat()];
            $select = $connection->select()->from(
                'md_vendor',
                ['vat']
            )->where(
                'vat = :vat'
            );

            if ($vendor->getId()) {
                $bind['vendor_id'] = (int)$vendor->getId();
                $select->where('vendor_id != :vendor_id');
            }
            $result = $connection->fetchOne($select, $bind);

            if ($result) {
                return __('A vendor with the same Vat already exists.<br/>');
            }
        }
        return '';
    }

    /**
     * Process category data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['vendor_id = ?' => (int) $object->getId()];
        $this->getConnection()->delete($this->vendorCategoryTable, $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * @param AbstractModel $object
     * @return AbstractDb
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveCategoryRelation($object);
        //$this->saveDeliveryZoneRelation($object); // module has been disabled
        return parent::_afterSave($object);
    }

    /**
     * Load an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $object->beforeLoad($value, $field);
        if ($field === null) {
            $field = $this->getIdFieldName();
        }

        if ($object->getWebsiteId()) {
            $websiteId = $object->getWebsiteId();
        }
        if ($object->getStoreId()) {
            $storeId = $object->getStoreId();
        }
        $connection = $this->getConnection();
        if ($connection && $value !== null) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        if (!empty($websiteId)) {
            $object->setData('website_id', $websiteId);
        }
        if (!empty($storeId)) {
            $object->setData('store_id', $storeId);
        }
        $this->unserializeFields($object);
        $this->_afterLoad($object);
        $object->afterLoad();
        $object->setOrigData();
        $object->setHasDataChanges(false);

        return $this;
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $cats = $this->lookupCategoryIds($object->getId());
            if (!is_array($cats)) {
                $cats = [];
            }
            $object->setData('category', $cats);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Get category ids which are selected by vendor
     *
     * @param int $vendorId
     * @return array
     */
    public function lookupCategoryIds($vendorId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from($this->vendorCategoryTable, 'category_id')
                ->where('vendor_id = ?', (int) $vendorId);
        return $adapter->fetchCol($select);
    }

    /**
     * @param $vendor
     *
     * @return array
     */
    public function getCategoryIds($vendor)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->vendorCategoryTable,
            'category_id'
        )
        ->where(
            'vendor_id = ?',
            (int)$vendor->getVendorId()
        );
        return $adapter->fetchCol($select);
    }

    /**
     * @param $vendor
     * @return $this
     */
    private function saveCategoryRelation($vendor)
    {
        if ($vendor === null) {
            return $this;
        }
        $id = $vendor->getId();

        /* $categories = $vendor->getCategory(); */
        $categories = $vendor->getCategoriesIds(); /* newly post categories ids */

        if ($categories === null) {
            return $this;
        }
        $oldCategoryIds = $vendor->getCategoryIds(); /* old categories ids */
        $insert = array_diff($categories, $oldCategoryIds);
        $delete = array_diff($oldCategoryIds, $categories);

        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['category_id IN(?)' => $delete, 'vendor_id=?' => $id];
            $adapter->delete($this->vendorCategoryTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $categoryId) {
                $data[] = [
                    'vendor_id' => (int)$id,
                    'category_id' => (int)$categoryId
                ];
            }
            $adapter->insertMultiple($this->vendorCategoryTable, $data);
        }

        if (!empty($insert) || !empty($delete)) {
            $categoryIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'md_vendor_vendor_change_categories',
                ['vendor' => $vendor, 'category_ids' => $categoryIds]
            );
        }

        if (!empty($insert) || !empty($delete)) {
            $vendor->setIsChangedCategoryList(true);
            $categoryIds = array_keys($insert + $delete /* + $update*/);
            $vendor->setAffectedCategoryIds($categoryIds);
        }
        return $this;
    }

    /**
     * Get vendor identifier by email
     *
     * @param string $email
     * @param null $websiteId
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdByEmail($email, $websiteId = null)
    {
        $connection = $this->getConnection();

        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $select = $connection->select()->from($this->getMainTable(), 'vendor_id')->where('email = :email');
        $select->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = ' . $this->getMainTable() . '.vendor_id and rvwd.website_id = ' . $websiteId,
            ['website_id']
        );

        $bind = [':email' => (string)$email];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Get vendor identifier by mobile
     *
     * @param int $mobile
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdByMobile($mobile)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'vendor_id')->where('mobile = :mobile');
        $select->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = ' . $this->getMainTable() . '.vendor_id and rvwd.website_id = '
            . $this->storeManager->getStore()->getWebsiteId(),
            ['website_id']
        );

        $bind = [':mobile' => (string)$mobile];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Get vendor identifier by token
     *
     * @param string $token
     * @param null $websiteId
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdByEmailVerificationCode($token, $websiteId = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'vendor_id')
            ->where('email_verification_code = :email_verification_code');

        $select->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = ' . $this->getMainTable() . '.vendor_id and rvwd.website_id = '
            . $this->storeManager->getStore()->getWebsiteId(),
            ['business_name']
        );

        $bind = [':email_verification_code' => $token];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Return vendor sub-user
     *
     * @return array
     */
    public function getVendorAllChildUsers($vendorId)
    {
        return $this->getConnection()->fetchAll(
            $this->getConnection()
                ->select()
                ->from($this->getTable('md_vendor'))
                ->where('parent_vendor_id = :parent_vendor_id')
                ->order('vendor_id ' . \Magento\Framework\DB\Select::SQL_ASC),
            [':parent_vendor_id' => $vendorId]
        );
    }
}
