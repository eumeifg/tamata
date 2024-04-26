<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Model\Data;

use MDC\MobileBanner\Api\Data\BannerInterface;

class Banner extends \Magento\Framework\Api\AbstractExtensibleObject implements BannerInterface
{

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get section_title
     * @return string|null
     */
    public function getSectionTitle()
    {
        return $this->_get(self::SECTION_TITLE);
    }

    /**
     * Set section_title
     * @param string $sectionTitle
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setSectionTitle($sectionTitle)
    {
        return $this->setData(self::SECTION_TITLE, $sectionTitle);
    }
    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \MDC\MobileBanner\Api\Data\BannerExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \MDC\MobileBanner\Api\Data\BannerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MDC\MobileBanner\Api\Data\BannerExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get section_details
     * @return string|null
     */
    public function getSectionDetails()
    {
        return $this->_get(self::SECTION_DETAILS);
    }

    /**
     * Set section_details
     * @param string $sectionDetails
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setSectionDetails($sectionDetails)
    {
        return $this->setData(self::SECTION_DETAILS, $sectionDetails);
    }

    /**
     * Get section_enable
     * @return string|null
     */
    public function getSectionEnable()
    {
        return $this->_get(self::SECTION_ENABLE);
    }

    /**
     * Set section_enable
     * @param string $sectionEnable
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setSectionEnable($sectionEnable)
    {
        return $this->setData(self::SECTION_ENABLE, $sectionEnable);
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
}
