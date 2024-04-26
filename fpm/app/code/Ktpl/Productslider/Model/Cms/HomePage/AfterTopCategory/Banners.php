<?php

namespace Ktpl\Productslider\Model\Cms\HomePage\AfterTopCategory;

use MDC\MobileBanner\Api\Data\Homepage\AfterTopCategory\BannerInterfaceFactory;
use MDC\MobileBanner\Model\ResourceModel\Banner\CollectionFactory;
use \Magento\Framework\Serialize\Serializer\Json;

class Banners extends \Magento\Framework\DataObject
{
    protected $bannerFactory;

    protected $collectionFactory;

    protected $serialize;

    public function __construct(
        BannerInterfaceFactory $bannerFactory,
        CollectionFactory $collectionFactory,
        Json $serialize,
        array $data = []
    ) {
        $this->bannerFactory = $bannerFactory;
        $this->collectionFactory = $collectionFactory;
        $this->serialize = $serialize;
        parent::__construct($data);
    }

    public function build() {
        $mobileCollection = $this->collectionFactory->create();
        $mobileCollection->addFieldToFilter('section_enable', 1);
        $mobileCollection->addFieldToFilter('is_after_top_category', ['eq' => 1]);
        //echo $mobileCollection->getSelect(); die();
        if ($mobileCollection->getSize()) {
            $bannerData = [];
            foreach ($mobileCollection->getItems() as $value) {
                $arrayImage= $bannerImageArray = [];
                if (!empty($value->getSectionDetails())) {
                    $serializeData = $value->getSectionDetails();
                    $sectionDetails = $this->serialize->unserialize($serializeData);
                    foreach ($sectionDetails as $sectionvalues) {
                        if ($sectionvalues['banner_enable'] == '1') {
                            if(isset($sectionvalues['mobile_banner_image']))
                            {
                                $bannerImageArray = $sectionvalues['mobile_banner_image'];
                            }

                            if(isset($sectionvalues['page_type']))
                            {
                                $pageType = $sectionvalues['page_type'];
                            }

                            if(!empty($bannerImageArray))
                            {
                                foreach ($bannerImageArray as $image) {
                                    $arrayImage[] =$this->bannerFactory->create()->setData([
                                        'image_path' => $image['url'],
                                        'data_id' => $sectionvalues['category_id'],
                                        'page_type' => $pageType
                                    ]);
                                }
                            }
                        }
                    }
                }
                return $arrayImage;
            }
            return [];
        }
        return [];
    }
}
