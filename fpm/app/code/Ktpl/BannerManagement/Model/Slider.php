<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Model;

use Ktpl\BannerManagement\Api\Data\SliderInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Slider extends AbstractModel implements SliderInterface, IdentityInterface
{
    /**
     * Slider cache tag
     */
    const CACHE_TAG = 'Ktpl_Slideres_Slideres';
    /**
     * Slider Block Identifier
     */
    const IDENTIFIER = 'block_slider';

    protected function _construct()
    {
        $this->_init('Ktpl\BannerManagement\Model\ResourceModel\Slider');
    }

    /**
     * Prevent Slider recursion
     *
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getSliderId()];
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
     * Retrieve Slider Identifier
     *
     * @return string
     */
    public function getSliderId()
    {
        return $this->getData(self::SLIDER_ID);
    }

     /**
      * Set Slider ID
      *
      * @param  int $id
      * @return SliderInterface
      */
    public function setSliderId($id)
    {
        return $this->setData(self::SLIDER_ID, $id);
    }

    /**
     * @return $this
     */
    public function getBannerPath()
    {
      return $this->getData(self::PATH);
    }

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $path
     * @return $this
     */
    public function setBannerPath($path)
    {
      return $this->setData(self::PATH, $path);
    }

    /**
     * Retrieve Slider status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Slider status
     *
     * @param  bool $status
     * @return SliderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Retrieve Slider name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

     /**
      * Set Slider name
      *
      * @param  string $name
      * @return SliderInterface
      */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Retrieve Slider location
     *
     * @return array
     */
    public function getLocation()
    {
        return $this->getData(self::LOCATION);
    }

     /**
      * Set Slider location
      *
      * @param  string $location
      * @return SliderInterface
      */
    public function setLocation($location)
    {
        return $this->setData(self::LOCATION, $location);
    }

    /**
     * Retrieve Slider storeids
     *
     * @return string
     */
    public function getStoreids()
    {
        return $this->getData(self::STOREIDS);
    }

      /**
       * Set Slider storeids
       *
       * @param  sring $storeids
       * @return SliderInterface
       */
    public function setStoreids($storeids)
    {
        return $this->setData(self::STOREIDS, $storeids);
    }

    /**
     * Retrieve Slider customGroupIds
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        return $this->getData(self::CUSTOMERGROUPIDS);
    }

      /**
       * Set Slider customGroupIds
       *
       * @param  string $customerGroupIds
       * @return SliderInterface
       */
    public function setCustomerGroupIds($customerGroupIds)
    {
        return $this->setData(self::CUSTOMERGROUPIDS, $customerGroupIds);
    }

    /**
     * Retrieve Slider priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

      /**
       * Set Slider priority
       *
       * @param  int $priority
       * @return SliderInterface
       */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Retrieve Slider effect
     *
     * @return string
     */
    public function getEffect()
    {
        return $this->getData(self::EFFECT);
    }

     /**
      * Set Slider Effect
      *
      * @param  string $effect
      * @return SliderInterface
      */
    public function setEffect($effect)
    {
        return $this->setData(self::EFFECT, $effect);
    }

    /**
     * Retrieve Slider autowidth
     *
     * @return int
     */
    public function getAutowidth()
    {
        return $this->getData(self::AUTOWIDTH);
    }

     /**
      * Set Slider autowidth
      *
      * @param  int $autoWidth
      * @return SliderInterface
      */
    public function setAutowidth($autoWidth)
    {
        return $this->setData(self::AUTOWIDTH, $autoWidth);
    }

    /**
     * Retrieve Slider autoheight
     *
     * @return int
     */
    public function getAutoheight()
    {
        return $this->getData(self::AUTOHEIGHT);
    }

      /**
       * Set Slider autoHeight
       *
       * @param  int $autoHeight
       * @return SliderInterface
       */
    public function setAutoheight($autoHeight)
    {
        return $this->setData(self::AUTOHEIGHT, $autoHeight);
    }

    /**
     * Retrieve Slider design
     *
     * @return string
     */
    public function getDesign()
    {
        return $this->getData(self::DESIGN);
    }

      /**
       * Set Slider design
       *
       * @param  int $design
       * @return SliderInterface
       */
    public function setDesign($design)
    {
        return $this->setData(self::DESIGN, $design);
    }


    /**
     * Retrieve Slider loop
     *
     * @return string
     */
    public function getLoop()
    {
        return $this->getData(self::LOOP);
    }
  /**
   * Set Slider loop
   *
   * @param  int $loop
   * @return SliderInterface
   */
    public function setLoop($loop)
    {
        return $this->setData(self::LOOP, $loop);
    }

    /**
     * Retrieve Slider lazyload
     *
     * @return string
     */
    public function getLazyload()
    {
        return $this->getData(self::LAZYLOAD);
    }

      /**
       * Set Slider lazyload
       *
       * @param  int $lazyload
       * @return SliderInterface
       */
    public function setLazyload($lazyload)
    {
        return $this->setData(self::LAZYLOAD, $lazyload);
    }

    /**
     * Retrieve Slider autoplay
     *
     * @return int
     */
    public function getAutoplay()
    {
        return $this->getData(self::AUTOPLAY);
    }

      /**
       * Set Slider autoplay
       *
       * @param  int $autoplay
       * @return SliderInterface
       */
    public function setAutoplay($autoplay)
    {
        return $this->setData(self::AUTOPLAY, $autoplay);
    }

    /**
     * Retrieve Slider autoplaytimeout
     *
     * @return string
     */
    public function getAutoplaytimeout()
    {
        return $this->getData(self::AUTOPLAYTIMEOUT);
    }

      /**
       * Set Slider autoplaytimeout
       *
       * @param  string $autoplaytimeout
       * @return SliderInterface
       */
    public function setAutoplaytimeout($autoplaytimeout)
    {
        return $this->setData(self::AUTOPLAYTIMEOUT, $autoplaytimeout);
    }

    /**
     * Retrieve Slider Navigation
     *
     * @return string
     */
    public function getNav()
    {
        return $this->getData(self::NAV);
    }

      /**
       * Set Slider Navigation
       *
       * @param  int $nav
       * @return SliderInterface
       */
    public function setNav($nav)
    {
        return $this->setData(self::NAV, $nav);
    }

    /**
     * Retrieve Slider dots
     *
     * @return int
     */
    public function getDots()
    {
        return $this->getData(self::DOTS);
    }

     /**
      * Set Slider dots
      *
      * @param  int $dots
      * @return SliderInterface
      */
    public function setDots($dots)
    {
        return $this->setData(self::DOTS, $dots);
    }

   /**
    * Retrieve Slider isResponsive
    *
    * @return int
    */
    public function getIsResponsive()
    {
        return $this->getData(self::ISRESPONSIVE);
    }

      /**
       * Set Slider IsResponsive
       *
       * @param  int $isResponsive
       * @return SliderInterface
       */
    public function setIsResponsive($isResponsive)
    {
        return $this->setData(self::ISRESPONSIVE, $isResponsive);
    }

    /**
     * Retrieve Slider responsiveItems
     *
     * @return string
     */
    public function getResponsiveItems()
    {
        return $this->getData(self::RESPONSIVEITEMS);
    }

      /**
       * Set Slider responsiveItems
       *
       * @param  string $responsiveItems
       * @return SliderInterface
       */
    public function setResponsiveItems($responsiveItems)
    {
        return $this->setData(self::RESPONSIVEITEMS, $responsiveItems);
    }

    /**
     * Retrieve Slider formDate
     *
     * @return date
     */
    public function getFromDate()
    {
        return $this->getData(self::FROMDATE);
    }

      /**
       * Set Slider formdate
       *
       * @param  bool $status
       * @return SliderInterface
       */
    public function setFromDate($formDate)
    {
        return $this->setData(self::FROMDATE, $formDate);
    }

    /**
     * Retrieve Slider toDate
     *
     * @return string
     */
    public function getToDate()
    {
        return $this->getData(self::TODATE);
    }

      /**
       * Set Slider toDate
       *
       * @param  date $toDate
       * @return SliderInterface
       */
    public function setToDate($toDate)
    {
        return $this->setData(self::TODATE, $toDate);
    }

   /**
    * Retrieve Slider createdat
    *
    * @return date
    */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATEDAT);
    }

      /**
       * Set Slider createdat
       *
       * @param  date $createdAt
       * @return SliderInterface
       */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATEDAT, $createdAt);
    }

     /**
      * Retrieve Slider updatedat
      *
      * @return timestamp
      */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATEDAT);
    }

      /**
       * Set Slider updatedat
       *
       * @param  timestamp $updatedAt
       * @return SliderInterface
       */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATEDAT, $updatedAt);
    }

    /**
     * Retrieve Slider VisibleDevices
     *
     * @return array
     */
    public function getVisibleDevices()
    {
        return $this->getData(self::VISIBLEDEVICES);
    }

      /**
       * Set Slider VisibleDevices
       *
       * @param  string $visibleDevices
       * @return SliderInterface
       */
    public function setVisibleDevices($visibleDevices)
    {
        return $this->setData(self::VISIBLEDEVICES, $visibleDevices);
    }

    /**
     * Retrieve Slider autoplayhoverpause
     *
     * @return string
     */
    public function getAutoplayhoverpause()
    {
        return $this->getData(self::AUTOPLAYHOVERPAUSE);
    }

      /**
       * Set Slider autoplayhoverpause
       *
       * @param  int $autoplayhoverpause
       * @return SliderInterface
       */
    public function setAutoplayhoverpause($autoplayhoverpause)
    {
        return $this->setData(self::AUTOPLAYHOVERPAUSE, $autoplayhoverpause);
    }

    /**
     * Get Slider Banner
     * @return \Ktpl\BannerManagement\Api\Data\BannerSearchResultsInterface|null
     */
    public function getBanners()
    {
        return $this->getData(self::BANNERS);
    }

    /**
     * Set Slider Banner
     * @param \Ktpl\BannerManagement\Api\Data\BannerSearchResultsInterface $banners
     * @return $this
     */
    public function setBanners($banners)
    {
        return $this->setData(self::BANNERS, $banners);
    }
}
