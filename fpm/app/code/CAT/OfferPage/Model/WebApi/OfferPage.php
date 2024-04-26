<?php

namespace CAT\OfferPage\Model\WebApi;

use CAT\OfferPage\Api\Data\CustomMessageInterface;
use CAT\OfferPage\Model\OfferPageFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Webapi\Rest\Response;
use CAT\OfferPage\Api\Data\CustomMessageInterfaceFactory;
use CAT\OfferPage\Api\Data\BannerDetailsInterfaceFactory;

class OfferPage implements \CAT\OfferPage\Api\OfferPageInterface
{
    /** Format for expiration timestamp headers */
    const EXPIRATION_TIMESTAMP_FORMAT = 'D, d M Y H:i:s T';

    /**
     * @var OfferPageFactory
     */
    protected $offerPageFactory;

    /**
     * @var Response
     */
    protected $httpResponse;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CustomMessageInterfaceFactory
     */
    protected $customMessageInterface;

    /**
     * @var BannerDetailsInterfaceFactory
     */
    protected $bannerDetailsInterface;

    /**
     * @param CustomMessageInterfaceFactory $customMessageInterface
     * @param BannerDetailsInterfaceFactory $bannerDetailsInterface
     * @param OfferPageFactory $offerPageFactory
     * @param Response $httpResponse
     * @param DateTime $dateTime
     */
    public function __construct(
        CustomMessageInterfaceFactory $customMessageInterface,
        BannerDetailsInterfaceFactory $bannerDetailsInterface,
        OfferPageFactory $offerPageFactory,
        Response $httpResponse,
        DateTime $dateTime
    ) {
        $this->customMessageInterface = $customMessageInterface;
        $this->bannerDetailsInterface = $bannerDetailsInterface;
        $this->offerPageFactory = $offerPageFactory;
        $this->httpResponse = $httpResponse;
        $this->dateTime = $dateTime;
    }

    /**
     * @param int $offerId
     * @return CustomMessageInterface
     */
    public function getOfferPage(int $offerId): CustomMessageInterface
    {
        $this->httpResponse->setHeader('pragma', 'cache', true);
        $this->httpResponse->setHeader('cache-control', 'public, max-age=3600, s-maxage=3600', true);
        $ttl = '21600';
        $this->httpResponse->setHeader('expires', $this->getExpirationHeader('+' . $ttl . ' seconds'), true);
        $customMessage = $this->customMessageInterface->create();

        $collection = $this->getOfferPageCollection($offerId);
        if ($collection->getSize() > 0) {
            $offerPage = $collection->getFirstItem();
            $customMessage->setMessage(__('Offer details found.'));
            $customMessage->setStatus(true);
            $customMessage->setOfferpageId($offerPage->getOfferpageId());
            $customMessage->setTitle($offerPage->getTitle());
            $customMessage->setBannerDetails([0 => ['page_type' => 'category']]);

            if (!empty($offerPage->getAdditionalInfo())) {
                $additionalInfo = json_decode($offerPage->getAdditionalInfo());
                $bannerDetails = [];
                foreach ($additionalInfo as $key => $info) {
                    $bannerDetails[$key] = [
                        'page_type' => $info->page_type,
                        'data_id' => $info->data_id,
                        'image_url' => $info->image[0]->url,
                        'layout' => $info->banner_layout,
                        'images' => array_column($info->image, 'url'),
                    ];
                }
                $customMessage->setBannerDetails($bannerDetails);
            }
        } else {
            $customMessage->setMessage(__('No offer details found.'));
            $customMessage->setStatus(false);
        }
        return $customMessage;
    }

    /**
     * @param $offerId
     * @return mixed
     */
    public function getOfferPageCollection($offerId) {
        $offerPage = $this->offerPageFactory->create();
        $collection = $offerPage->getCollection();
        $collection->addFieldToSelect(['offerpage_id', 'title', 'additional_info']);
        $collection->addFieldToFilter('offerpage_id', ['eq' => $offerId]);
        $collection->addFieldToFilter('status', ['eq' => 1]);
        $collection->addFieldToFilter('device', ['eq' => 1]);
        return $collection;
    }

    public function getExpirationHeader($time) {
        return $this->dateTime->gmDate(self::EXPIRATION_TIMESTAMP_FORMAT, $this->dateTime->strToTime($time));
    }
}
