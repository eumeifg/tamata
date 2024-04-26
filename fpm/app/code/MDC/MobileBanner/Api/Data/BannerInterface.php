<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Api\Data;

interface BannerInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const SECTION_DETAILS = 'section_details';
    const ENTITY_ID = 'entity_id';
    const SECTION_ENABLE = 'section_enable';
    const SECTION_TITLE ='section_title';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setEntityId($entityId);

    /**
     * Get section_title
     * @return string|null
     */
    public function getSectionTitle();

    /**
     * Set section_title
     * @param string $sectionTitle
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setSectionTitle($sectionTitle);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \MDC\MobileBanner\Api\Data\BannerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \MDC\MobileBanner\Api\Data\BannerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MDC\MobileBanner\Api\Data\BannerExtensionInterface $extensionAttributes
    );

    /**
     * Get section_details
     * @return string|null
     */
    public function getSectionDetails();

    /**
     * Set section_details
     * @param string $sectionDetails
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setSectionDetails($sectionDetails);

    /**
     * Get section_enable
     * @return string|null
     */
    public function getSectionEnable();

    /**
     * Set section_enable
     * @param string $sectionEnable
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setSectionEnable($sectionEnable);

    /**
     * Get created at
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     * @param string $createdAt
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */

    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     * @param string $updatedAt
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     */
    public function setUpdatedAt($updatedAt);
}
