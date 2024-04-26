<?php
namespace MDC\Vendor\Plugin;

use MDC\Vendor\Api\Data\MicrositeInterfaceFactory as CustomMicrositeInterfaceFactory;
use Magedelight\Vendor\Helper\Microsite\Data;

class AddMicrositeCustomData
{
    /**
     * @var CustomMicrositeInterfaceFactory
     */
    protected $customMicrositeInterfaceFactory;

    /**
     * @var Data
     */
    protected $micrositeHelper;

    /**
     * AddMicrositeCustomData constructor.
     * @param CustomMicrositeInterfaceFactory $customMicrositeInterfaceFactory
     * @param Data $micrositeHelper
     */
    public function __construct(
        CustomMicrositeInterfaceFactory $customMicrositeInterfaceFactory,
        Data $micrositeHelper
    ) {
        $this->customMicrositeInterfaceFactory = $customMicrositeInterfaceFactory;
        $this->micrositeHelper = $micrositeHelper;
    }

    public function afterGetByVendorId(
        \Magedelight\Vendor\Api\MicrositeRepositoryInterface $subject,
        \Magedelight\Vendor\Api\Data\MicrositeInterface $entity,
        $vendorId,
        $storeId,
        $columns = ['*'],
        $forceReload = false
    ) {
        (!empty($entity->getPromoBanner1()))?$entity->setPromoBanner1($this->micrositeHelper->getMicrositeFileUrl($entity->getPromoBanner1(), 'microsite/promo_banners')):'';
        (!empty($entity->getPromoBanner2()))?$entity->setPromoBanner2($this->micrositeHelper->getMicrositeFileUrl($entity->getPromoBanner2(), 'microsite/promo_banners')):'';
        (!empty($entity->getPromoBanner3()))?$entity->setPromoBanner3($this->micrositeHelper->getMicrositeFileUrl($entity->getPromoBanner3(), 'microsite/promo_banners')):'';
         
        (!empty($entity->getMobilePromoBanner1()))?$entity->setMobilePromoBanner1($this->micrositeHelper->getMicrositeFileUrl($entity->getMobilePromoBanner1(), 'microsite/promo_banners')):'';
        (!empty($entity->getMobilePromoBanner2()))?$entity->setMobilePromoBanner2($this->micrositeHelper->getMicrositeFileUrl($entity->getMobilePromoBanner2(), 'microsite/promo_banners')):'';
        (!empty($entity->getMobilePromoBanner3()))?$entity->setMobilePromoBanner3($this->micrositeHelper->getMicrositeFileUrl($entity->getMobilePromoBanner3(), 'microsite/promo_banners')):'';
          
        $customData = $this->customMicrositeInterfaceFactory->create()->setData($entity->getData());
        $extensionAttributes = $entity->getExtensionAttributes();
        $extensionAttributes->setMicrositeCustomData($customData);
        $entity->setExtensionAttributes($extensionAttributes);
        return $entity;
    }
}
