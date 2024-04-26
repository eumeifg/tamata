<?php
namespace MDC\Vendor\Api\Data;

/**
 * Microsite interface.
 */
interface MicrositeInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const PROMO_BANNER_1 = 'promo_banner_1';
    const PROMO_BANNER_2 = 'promo_banner_2';
    const PROMO_BANNER_3 = 'promo_banner_3';
    const MOBILE_PROMO_BANNER_1 = 'mobile_promo_banner_1';
    const MOBILE_PROMO_BANNER_2 = 'mobile_promo_banner_2';
    const MOBILE_PROMO_BANNER_3 = 'mobile_promo_banner_3';
    
    /**
     * Get promo banner 1
     *
     * @api
     * @return string|null
     */
    public function getPromoBanner1();

    /**
     * Set promo banner 1
     *
     * @api
     * @param string $banner
     * @return $this
     */
    public function setPromoBanner1($banner);

    /**
     * Get promo banner 2
     *
     * @api
     * @return string|null
     */
    public function getPromoBanner2();

    /**
     * Set promo banner 2
     *
     * @api
     * @param string $banner
     * @return $this
     */
    public function setPromoBanner2($banner);

    /**
     * Get promo banner 3
     *
     * @api
     * @return string|null
     */
    public function getPromoBanner3();

    /**
     * Set promo banner 3
     *
     * @api
     * @param string $banner
     * @return $this
     */
    public function setPromoBanner3($banner);

    /**
     * Get mobile promo banner 1
     *
     * @api
     * @return string|null
     */
    public function getMobilePromoBanner1();

    /**
     * Set mobile promo banner 1
     *
     * @api
     * @param string $banner
     * @return $this
     */
    public function setMobilePromoBanner1($banner);

    /**
     * Get mobile promo banner 2
     *
     * @api
     * @return string|null
     */
    public function getMobilePromoBanner2();

    /**
     * Set mobile promo banner 2
     *
     * @api
     * @param string $banner
     * @return $this
     */
    public function setMobilePromoBanner2($banner);

    /**
     * Get mobile promo banner 3
     *
     * @api
     * @return string|null
     */
    public function getMobilePromoBanner3();

    /**
     * Set mobile promo banner 3
     *
     * @api
     * @param string $banner
     * @return $this
     */
    public function setMobilePromoBanner3($banner);
}
