<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Api\Data;

/**
 * Banner interface.
 *
 * @api
 * @since 100.0.2
 */
interface SliderInterface
{
    /**
     * #@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SLIDER_ID = 'slider_id';
    const NAME = 'name';
    const STATUS = 'status';
    const LOCATION = 'location';
    const STOREIDS = 'store_ids';
    const CUSTOMERGROUPIDS = 'customer_group_ids';
    const PRIORITY = 'priority';
    const EFFECT = 'effect';
    const AUTOWIDTH = 'autowidth';
    const AUTOHEIGHT = 'autoheight';
    const DESIGN = 'design';
    const LOOP = 'loop';
    const LAZYLOAD = 'lazyload';
    const AUTOPLAY = 'autoplay';
    const AUTOPLAYTIMEOUT = 'autoplaytimeout';
    const NAV = 'nav';
    const DOTS = 'dots';
    const ISRESPONSIVE = 'is_responsive';
    const RESPONSIVEITEMS = 'responsive_items';
    const FROMDATE = 'from_date';
    const TODATE = 'to_date';
    const CREATEDAT = 'created_at';
    const UPDATEDAT = 'updated_at';
    const VISIBLEDEVICES = 'visible_devices';
    const AUTOPLAYHOVERPAUSE = 'autoplayhoverpause';
    const BANNERS = 'banners';
    const PATH = 'banner_path';

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getSliderId();
    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $id
     * @return $this
     */
    public function setSliderId($id);

    /**
     * @return $this
     */
    public function getBannerPath();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $path
     * @return $this
     */
    public function setBannerPath($path);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getStatus();
    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getName();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getLocation();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $location
     * @return $this
     */
    public function setLocation($location);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getStoreids();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $storeids
     * @return $this
     */
    public function setStoreids($storeids);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getCustomerGroupIds();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $customerGroupIds
     * @return $this
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getPriority();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getEffect();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $effect
     * @return $this
     */
    public function setEffect($title);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getAutowidth();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $autoWidth
     * @return $this
     */
    public function setAutowidth($autoWidth);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getAutoheight();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $autoHeight
     * @return $this
     */
    public function setAutoheight($autoHeight);

     /**
      * @return Ktpl\BannerManagement\Api\Data\SliderInterface
      */
    public function getDesign();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $design
     * @return $this
     */
    public function setDesign($design);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getLoop();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $loop
     * @return $this
     */
    public function setLoop($loop);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getLazyload();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $lazyload
     * @return $this
     */
    public function setLazyload($lazyload);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getAutoplay();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $autoplay
     * @return $this
     */
    public function setAutoplay($autoplay);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getAutoplaytimeout();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $autoplay
     * @return $this
     */
    public function setAutoplaytimeout($autoplaytimeout);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getNav();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $nav
     * @return $this
     */
    public function setNav($nav);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getDots();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $dots
     * @return $this
     */
    public function setDots($dots);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getIsResponsive();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $isResponsive
     * @return $this
     */
    public function setIsResponsive($isResponsive);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getResponsiveItems();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $responsiveItems
     * @return $this
     */
    public function setResponsiveItems($responsiveItems);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getFromDate();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $formDate
     * @return $this
     */
    public function setFromDate($formDate);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getToDate();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $toDate
     * @return $this
     */
    public function setToDate($toDate);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getCreatedAt();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $toDate
     * @return $this
     */
    public function setCreatedAt($createdAt);

     /**
      * @return Ktpl\BannerManagement\Api\Data\SliderInterface
      */
    public function getUpdatedAt();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getVisibleDevices();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $visibleDevices
     * @return $this
     */
    public function setVisibleDevices($visibleDevices);

    /**
     * @return Ktpl\BannerManagement\Api\Data\SliderInterface
     */
    public function getAutoplayhoverpause();

    /**
     * @param  Ktpl\BannerManagement\Api\Data\SliderInterface $autoplayhoverpause
     * @return $this
     */
    public function setAutoplayhoverpause($autoplayhoverpause);

    /**
     * Get Slider Banner
     * @return \Ktpl\BannerManagement\Api\Data\BannerSearchResultsInterface|null
     */
    public function getBanners();

    /**
     * Set Slider Banner
     * @param \Ktpl\BannerManagement\Api\Data\BannerSearchResultsInterface $banners
     * @return $this
     */
    public function setBanners($banners);
}
