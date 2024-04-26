<?php
namespace MDC\MobileBanner\Model\Cms\HomePage;

use MDC\MobileBanner\Model\ResourceModel\Banner\CollectionFactory;
use \Magento\Framework\Serialize\Serializer\Json;

class CategoryBanner extends \Magento\Framework\DataObject
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Json
     */
    protected $serialize;

    /**
     * CategoryBanner constructor.
     * @param CollectionFactory $collectionFactory
     * @param Json $serialize
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Json $serialize
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->serialize = $serialize;
    }

    public function getStaticSliderCategories($index)
    {
        $i = 0; $sectionDetails ='';
        $mobileCollection = $this->collectionFactory->create();
        $mobileCollection->addFieldToFilter('section_enable', 1);
        $mobileCollection->addFieldToFilter('is_after_top_category', [
            ['null' => true],['eq' => 0]
        ]);

        $categories =[];

        foreach ($mobileCollection->getItems() as $value) {
            $arrayImage= $bannerImageArray = [];
            if (!empty($value->getSectionDetails())) {
                $serializeData = $value->getSectionDetails();
                $sectionDetails = $this->serialize->unserialize($serializeData);
                $pageType = '';
                foreach ($sectionDetails as $sectionvalues){
                    if ($sectionvalues['banner_enable']=='1') {
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
                                $arrayImage[] = [
                                    'image_path' => $image['url'],
                                    'category_id' => $sectionvalues['category_id'],
                                    'height' => $image['height'],
                                    'width' => $image['width'],
                                    'page_type' => $pageType,
                                    'layout' => $value->getLayout()
                                ];
                            }
                        }
                    }
                }
                $categories[$i]=$arrayImage;
            }
            $i++;
        }
        return (array_key_exists($index, $categories)) ? $categories[$index] : [];
    }
}
