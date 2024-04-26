<?php
namespace MDC\Vendor\Model;

use MDC\Vendor\Api\Data\MicrositeInterface as CustomMicrositeInterface;

/**
 * Description of Microsite
 *
 * @author Rocket Bazaar Core Team
 */
class Microsite extends \Magento\Framework\DataObject implements CustomMicrositeInterface
{

    /**
     * {@inheritdoc}
     */
    public function getPromoBanner1()
    {
        return $this->getData(self::PROMO_BANNER_1);
    }

    /**
     * {@inheritdoc}
     */
    public function setPromoBanner1($banner)
    {
        return $this->setData(self::PROMO_BANNER_1, $banner);
    }

    /**
     * {@inheritdoc}
     */
    public function getPromoBanner2()
    {
        return $this->getData(self::PROMO_BANNER_2);
    }

    /**
     * {@inheritdoc}
     */
    public function setPromoBanner2($banner)
    {
        return $this->setData(self::PROMO_BANNER_2, $banner);
    }

    /**
     * {@inheritdoc}
     */
    public function getPromoBanner3()
    {
        return $this->getData(self::PROMO_BANNER_3);
    }

    /**
     * {@inheritdoc}
     */
    public function setPromoBanner3($banner)
    {
        return $this->setData(self::PROMO_BANNER_3, $banner);
    }

    /**
     * {@inheritdoc}
     */
    public function getMobilePromoBanner1()
    {
        return $this->getData(self::MOBILE_PROMO_BANNER_1);
    }

    /**
     * {@inheritdoc}
     */
    public function setMobilePromoBanner1($banner)
    {
        return $this->setData(self::MOBILE_PROMO_BANNER_1, $banner);
    }

    /**
     * {@inheritdoc}
     */
    public function getMobilePromoBanner2()
    {
        return $this->getData(self::MOBILE_PROMO_BANNER_2);
    }

    /**
     * {@inheritdoc}
     */
    public function setMobilePromoBanner2($banner)
    {
        return $this->setData(self::MOBILE_PROMO_BANNER_2, $banner);
    }

    /**
     * {@inheritdoc}
     */
    public function getMobilePromoBanner3()
    {
        return $this->getData(self::MOBILE_PROMO_BANNER_3);
    }

    /**
     * {@inheritdoc}
     */
    public function setMobilePromoBanner3($banner)
    {
        return $this->setData(self::MOBILE_PROMO_BANNER_3, $banner);
    }
}
