<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ktpl\BannerManagement\Helper\Data as bannerHelper;

/**
 * Resource model for catalog product batch
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Slider extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
     /**
      * Date model
      *
      * @var \Magento\Framework\Stdlib\DateTime\DateTime
      */
    protected $date;

    /**
     * Banner relation model
     *
     * @var string
     */
    protected $sliderBannerTable;

    /**
     * Event Manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var bannerHelper
     */
    protected $bannerHelper;

    /**
     * Slider constructor.
     *
     * @param DateTime         $date
     * @param ManagerInterface $eventManager
     * @param Context          $context
     * @param bannerHelper     $helperData
     */
    public function __construct(
        DateTime $date,
        ManagerInterface $eventManager,
        Context $context,
        bannerHelper $helperData
    ) {
        $this->date         = $date;
        $this->eventManager = $eventManager;
        $this->bannerHelper = $helperData;

        parent::__construct($context);
        $this->sliderBannerTable = $this->getTable('ktpl_bannerslider_banner_slider');
    }
     /**
      * Resource initialization
      *
      * @return void
      */
    protected function _construct()
    {
        $this->_init('ktpl_bannerslider_slider', 'slider_id');
    }
     /**
      * Retrieves Slider Name from DB by passed id.
      *
      * @param $id
      *
      * @return string
      * @throws \Magento\Framework\Exception\LocalizedException
      */
    public function getSliderNameById($id)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('slider_id = :slider_id');
        $binds   = ['slider_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * before save callback
     *
     * @param AbstractModel $object
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Zend_Serializer_Exception
     */
    protected function _beforeSave(AbstractModel $object)
    {
        //set default Update At and Create At time post
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        $location = $object->getLocation();
        if (is_array($location)) {
            $object->setLocation(implode(',', $location));
        }

        $visibleDevices = $object->getVisibleDevices();
        if (is_array($visibleDevices)) {
            $object->setVisibleDevices(implode(',', $visibleDevices));
        }

        $storeIds = $object->getStoreIds();
        if (is_array($storeIds)) {
            $object->setStoreIds(implode(',', $storeIds));
        }

        $groupIds = $object->getCustomerGroupIds();
        if (is_array($groupIds)) {
            $object->setCustomerGroupIds(implode(',', $groupIds));
        }

        $responsiveItems = $object->getResponsiveItems();
        if ($responsiveItems && is_array($responsiveItems)) {
            $object->setResponsiveItems($this->bannerHelper->serialize($responsiveItems));
        } else {
            $object->setResponsiveItems($this->bannerHelper->serialize([]));
        }

        return parent::_beforeSave($object);
    }

    /**
     * after save callback
     *
     * @param AbstractModel|\Ktpl\BannerManagement\Model\Slider $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveBannerRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Zend_Serializer_Exception
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        if ($object->getResponsiveItems()!=="null") {
            $object->setResponsiveItems($this->bannerHelper->unserialize($object->getResponsiveItems()));
        } else {
            $object->setResponsiveItems(null);
        }

        return $this;
    }

    /**
     * @param \Ktpl\BannerManagement\Model\Slider $slider
     *
     * @return array
     */
    public function getBannersPosition(\Ktpl\BannerManagement\Model\Slider $slider)
    {
        $select = $this->getConnection()->select()->from(
            $this->sliderBannerTable,
            ['banner_id', 'position']
        )
            ->where(
                'slider_id = :slider_id'
            );
        $bind   = ['slider_id' => (int)$slider->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param \Ktpl\BannerManagement\Model\Slider $slider
     *
     * @return array
     */
    public function getBannersSliderID(\Ktpl\BannerManagement\Model\Slider $slider)
    {
        $resultData=$result=[];
        $select = $this->getConnection()->select()->from(
            $this->sliderBannerTable,
            ['banner_id']
        )
            ->where(
                'slider_id = :slider_id'
            );
        $bind   = ['slider_id' => (int)$slider->getId()];

         $resultData=$this->getConnection()->fetchAll($select, $bind);

        foreach ($resultData as $key => $value) {
            $result[$key]=$value['banner_id'];
        }
        return $result;
    }

    /**
     * @param \Ktpl\BannerManagement\Model\Slider $slider
     *
     * @return $this
     */
    protected function saveBannerRelation(\Ktpl\BannerManagement\Model\Slider $slider)
    {
        $slider->setIsChangedBannerList(false);
        $oldBanners=[];
        $banners=[];

        $id      = $slider->getId();
        $banners = $slider->getBannersData();
        if ($banners === null) {
            return $this;
        }
      
        $oldBanners = $this->getBannersSliderID($slider);

        $insert     = array_diff($banners, $oldBanners);

        $delete     = array_diff($oldBanners, $banners);
        
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['banner_id IN(?)' => $delete, 'slider_id=?' => $id];
            $adapter->delete($this->sliderBannerTable, $condition);
        }

        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $bannerId) {
                $data[] = [
                    'slider_id' => (int)$id,
                    'banner_id' => (int)$bannerId
                ];
            }
            $adapter->insertMultiple($this->sliderBannerTable, $data);
        }


        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $slider->setIsChangedBannerList(true);
            $bannerIds = array_keys($insert + $delete);
            $slider->setAffectedBannerIds($bannerIds);
        }

        return $this;
    }
}
