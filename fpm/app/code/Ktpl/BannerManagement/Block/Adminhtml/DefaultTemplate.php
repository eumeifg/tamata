<?php
namespace Ktpl\BannerManagement\Block\Adminhtml;

use Magento\Backend\Block\Template;

class DefaultTemplate extends Template
{

    const DEMO_1="demo1.png";
    const DEMO_2="demo2.png";
    const DEMO_3="demo3.png";
    const DEMO_4="demo4.jpg";
    const DEMO_5="demo5.jpg";

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->assetRepo = $assetRepo;
        parent::__construct($context);
    }
    public function getImages()
    {
        $image1=$this->assetRepo->getUrl('Ktpl_BannerManagement::images/' . self::DEMO_1);
        $image2=$this->assetRepo->getUrl('Ktpl_BannerManagement::images/' . self::DEMO_2);
        $image3=$this->assetRepo->getUrl('Ktpl_BannerManagement::images/' . self::DEMO_3);
        $image4=$this->assetRepo->getUrl('Ktpl_BannerManagement::images/' . self::DEMO_4);
        $image5=$this->assetRepo->getUrl('Ktpl_BannerManagement::images/' . self::DEMO_5);

        $imageArray=[$image1,$image2,$image3,$image4,$image5];

        return $imageArray;
    }

    public function getTemplateHtml($template)
    {
        if ($template==1) {
            $url="ktpl/bannermanagement/banner/demo/".self::DEMO_1;
        } elseif ($template==2) {
            $url="ktpl/bannermanagement/banner/demo/".self::DEMO_2;
        } elseif ($template==3) {
            $url="ktpl/bannermanagement/banner/demo/".self::DEMO_3;
        } elseif ($template==4) {
            $url="ktpl/bannermanagement/banner/demo/".self::DEMO_4;
        } elseif ($template==5) {
            $url="ktpl/bannermanagement/banner/demo/".self::DEMO_5;
        }
        
        $imgTmp = '<div class="item" style="background:url({{media url=&quot;'.$url.'&quot;}}) center center no-repeat;background-size:cover;"><div class="container" style="position:relative"><img src="{{media url=&quot;'.$url.'&quot;}}" alt="Demo Template"></div></div>';
            return $imgTmp;
    }
}
