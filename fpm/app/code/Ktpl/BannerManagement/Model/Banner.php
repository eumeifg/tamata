<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Model;

use Ktpl\BannerManagement\Api\Data\BannerInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Ktpl\BannerManagement\Model\Config\Source\Image as configImage;
use Ktpl\BannerManagement\Model\ResourceModel\Slider\CollectionFactory;
use Magento\Framework\Api\Data\ImageContentInterface;

class Banner extends AbstractModel implements BannerInterface, IdentityInterface
{
    /**
     * Banner cache tag
     */
    const CACHE_TAG = 'Ktpl_BanneresCustomization';
    /**
     * Banner Block Identifier
     */
    const IDENTIFIER = 'block_Banner';

    /**
     * Banner constructor.
     *
     * @param sliderCollectionFactory $sliderCollectionFactory
     * @param Context                 $context
     * @param Registry                $registry
     * @param configImage             $configImage
     * @param AbstractResource|null   $resources
     * @param AbstractDb|null         $resourceCollection
     * @param array                   $data
     */
    /**
     * Slider Collection
     *
     * @var \Ktpl\BannerManagement\Model\ResourceModel\Slider\Collection
     */
    protected $sliderCollection;

    /**
     * Slider Collection Factory
     *
     * @var \Ktpl\BannerManagement\Model\ResourceModel\Slider\CollectionFactory
     */
    protected $sliderCollectionFactory;

    /**
     * @var configImage
     */
    protected $imageModel;

    public function __construct(
        CollectionFactory $sliderCollectionFactory,
        Context $context,
        Registry $registry,
        configImage $configImage,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->sliderCollectionFactory = $sliderCollectionFactory;
        $this->imageModel              = $configImage;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Ktpl\BannerManagement\Model\ResourceModel\Banner');
    }

    /**
     * Prevent Banner recursion
     *
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getBannerId()];
    }

    /**
     * Retrieve block identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return (string) $this->getData(self::IDENTIFIER);
    }

    /**
     * Retrieve Banner id
     *
     * @return int
     */
    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }

    /**
     * Retrieve banner status
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Retrieve Banner name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Retrieve Banner type
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Retrieve Banner content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Retrieve Banner Image
     *
     * @return ImageContentInterface
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Retrieve Banner Url
     *
     * @return string
     */
    public function getUrlBanner()
    {
        return $this->getData(self::URL_BANNER);
    }

    /**
     * Retrieve Banner title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Retrieve Banner title
     *
     * @return string
     */
    public function getBannerText()
    {
        return $this->getData(self::BANNER_TEXT);
    }

    /**
     * Retrieve Banner newtab
     *
     * @return string
     */
    public function getNewtab()
    {
        return $this->getData(self::NEWTAB);
    }

    /**
     * Retrieve Banner Created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Retrieve Banner newtab
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set ID
     *
     * @param  int $id
     * @return BannerInterface
     */
    public function setBannerId($id)
    {
        return $this->setData(self::BANNER_ID, $id);
    }

    /**
     * Set Product ID
     *
     * @param  bool $status
     * @return BannerInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set banner name
     *
     * @param  string $name
     * @return BannerInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set banner type
     *
     * @param  string $type
     * @return BannerInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Set content
     *
     * @param  string $content
     * @return BannerInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Set image
     *
     * @param  ImageContentInterface $image
     * @return BannerInterface
     */
    public function setImage($image = null)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Set banner url
     *
     * @param  string $url
     * @return BannerInterface
     */
    public function setUrlBanner($url)
    {
        return $this->setData(self::URL_BANNER, $url);
    }

     /**
      * Set banner title
      *
      * @param  string $title
      * @return BannerInterface
      */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set banner title
     *
     * @param  string $title
     * @return BannerInterface
     */
    public function setBannerText($banner_text)
    {
        return $this->setData(self::BANNER_TEXT, $banner_text);
    }

     /**
      * Set banner newtab
      *
      * @param  string $newTab
      * @return BannerInterface
      */
    public function setNewtab($newTab)
    {
        return $this->setData(self::NEWTAB, $newTab);
    }

     /**
      * Set banner createdAt
      *
      * @param  string $createdAt
      * @return BannerInterface
      */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

      /**
       * Set banner updatedAt
       *
       * @param  string $updatedAt
       * @return BannerInterface
       */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

        /**
         * get entity default values
         *
         * @return array
         */
    public function getDefaultValues()
    {
        return ['status => 1', 'type' => '0'];
    }

    /**
     * @return ResourceModel\Slider\Collection
     */
    public function getSelectedSlidersCollection()
    {
        if ($this->sliderCollection==="null") {
            $collection = $this->sliderCollectionFactory->create();
            $collection->getSelect()->join(
                ['banner_slider' => $this->getResource()->getTable('ktpl_bannerslider_banner_slider')],
                'main_table.slider_id=banner_slider.slider_id AND banner_slider.banner_id=' . $this->getId(),
                ['position']
            );
            $collection->addFieldToFilter('status', 1);

            $this->sliderCollection = $collection;
        }

        return $this->sliderCollection;
    }

    /**
     * get full image url
     *
     * @return setImage
     */
    public function getImageUrl()
    {
        return $this->imageModel->getBaseUrl() . $this->getImage();
    }

    /**
     * @return array
     */
    public function getSliderIds()
    {
        if (!$this->hasData('slider_ids')) {
            $ids = $this->getResource()->getSliderIds($this);

            $this->setData('slider_ids', $ids);
        }
        return (array)$this->getData('slider_ids');
    }

    /**
     * Retrieve Page type
     * @return string
     */
    public function getPageType()
    {
        return $this->getData(self::PAGE_TYPE);
    }

    /**
     * Set page type
     *
     * @param  string $pageType
     * @return BannerInterface
     */
    public function setPageType($pageType)
    {
        return $this->setData(self::PAGE_TYPE, $pageType);
    }

     /**
     * Retrieve Data Id
     * @return int
     */
    public function getDataId()
    {
         return $this->getData(self::DATA_ID);
    }

    /**
     * @param  int $dataId
     * @return $this
     */
    public function setDataId($dataId)
    {
        return $this->setData(self::DATA_ID, $dataId);
    }
}
