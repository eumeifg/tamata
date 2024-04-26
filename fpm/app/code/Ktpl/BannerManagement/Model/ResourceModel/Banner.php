<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Resource model for catalog product batch
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class Banner extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
        /**
         * Date model
         *
         * @var \Magento\Framework\Stdlib\DateTime\DateTime
         */
    protected $date;

    /**
     * Slider relation model
     *
     * @var string
     */
    protected $bannerSliderTable;

    /**
     * Event Manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime       $date
     * @param \Magento\Framework\Event\ManagerInterface         $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        DateTime $date,
        ManagerInterface $eventManager,
        Context $context
    ) {
        $this->date         = $date;
        $this->eventManager = $eventManager;

        parent::__construct($context);
        $this->bannerSliderTable = $this->getTable('ktpl_bannerslider_banner_slider');
    }
     /**
      * Resource initialization
      *
      * @return void
      */
    protected function _construct()
    {
        $this->_init('ktpl_bannerslider_banner', 'banner_id');
    }
    /**
     * @return \Ktpl\BannerManagement\Model\ResourceModel\Banner
     */
    public function getSelectedBannersCollection()
    {
        if ($this->bannerCollection === "null") {
            $collection = $this->bannerCollectionFactory->create();
            $collection->getSelect()->join(
                ['banner_slider' => $this->getResource()->getTable('ktpl_bannerslider_banner_slider')],
                'main_table.banner_id=banner_slider.banner_id AND banner_slider.slider_id=' . $this->getId(),
                ['position']
            );
            $this->bannerCollection = $collection;
        }

        return $this->bannerCollection;
    }

    /**
     * @param $id
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBannerNameById($id)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('banner_id = :banner_id');
        $binds   = ['banner_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this|AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        //set default Update At and Create At time post
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveSliderRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param \Ktpl\BannerManagement\Model\Banner $banner
     *
     * @return $this
     */
    protected function saveSliderRelation(\Ktpl\BannerManagement\Model\Banner $banner)
    {
        $banner->setIsChangedSliderList(false);
        $id      = $banner->getId();
        $sliders = $banner->getSlidersIds();
        if ($sliders === null) {
            $sliders = [];
        }
        $oldSliders = $banner->getSliderIds();

        $insert  = array_diff($sliders, $oldSliders);
        $delete  = array_diff($oldSliders, $sliders);
        $adapter = $this->getConnection();

        if (!empty($delete)) {
            $condition = ['slider_id IN(?)' => $delete, 'banner_id=?' => $id];
            $adapter->delete($this->bannerSliderTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $tagId) {
                $data[] = [
                    'banner_id' => (int)$id,
                    'slider_id' => (int)$tagId,
                    'position'  => 1
                ];
            }
            $adapter->insertMultiple($this->bannerSliderTable, $data);
        }
        if (!empty($insert) || !empty($delete)) {
            $sliderIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'ktpl_bannerslider_banner_after_save_sliders',
                ['banner' => $banner, 'slider_ids' => $sliderIds]
            );

            $banner->setIsChangedSliderList(true);
            $sliderIds = array_keys($insert + $delete);
            $banner->setAffectedSliderIds($sliderIds);
        }

        return $this;
    }

    /**
     * @param \Ktpl\BannerManagement\Model\Banner $banner
     *
     * @return array
     */
    public function getSliderIds(\Ktpl\BannerManagement\Model\Banner $banner)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->bannerSliderTable, 'slider_id')
            ->where('banner_id = ?', (int)$banner->getId());

        return $adapter->fetchCol($select);
    }
}
