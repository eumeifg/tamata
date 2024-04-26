<?php

namespace MDC\Catalog\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayUtils;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 *
 */
class OpenInApp extends AbstractView
{

    const FIREBASE_WEB_API_KEY = 'open_in_app/general/firebase_web_api_key';
    const FIREBASE_DEEP_LINK_URL = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var LoggerInterface
     */
    private $psrLogger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Context                    $context,
        ArrayUtils                 $arrayUtils,
        Registry                   $coreRegistry,
        ScopeConfigInterface       $scopeConfig,
        LoggerInterface            $psrLogger,
        ProductRepositoryInterface $productRepository,
        array                      $data = []
    )
    {
        $this->_registry = $coreRegistry;
        $this->_scopeConfig = $scopeConfig;
        $this->psrLogger = $psrLogger;
        $this->productRepository = $productRepository;
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * @return false
     * @throws NoSuchEntityException
     */
    public function getCurrentProduct()
    {
        $currentProduct = $this->_registry->registry('current_product');
        $productUrl = $currentProduct->getProductUrl();
        $productId = $currentProduct->getId();
        $productUrlWithId = $productUrl . "?productId=" . $productId;
        $appLinkValue = $currentProduct->getResource()->getAttribute("app_link")->getFrontend()->getValue($currentProduct);

        $productShortLink = false;
        if (!empty($appLinkValue)) {
            $productShortLink = $appLinkValue;
        } else {
            $generatedDeepLink = $this->createDeepLinkForCurrentPorduct($productUrlWithId);
            if ($generatedDeepLink) {
                $repositoryProduct = $this->productRepository->getById($productId);
                $repositoryProduct->setAppLink($generatedDeepLink);
                $repositoryProduct->getResource()->saveAttribute($repositoryProduct, 'app_link');
                $productShortLink = $generatedDeepLink;
            }
        }
        return $productShortLink;
    }

    public function createDeepLinkForCurrentPorduct($longUrl)
    {
        $webAPIKey = $this->_scopeConfig->getValue(self::FIREBASE_WEB_API_KEY);
        $url = self::FIREBASE_DEEP_LINK_URL . $webAPIKey;
        $data = array(
            "dynamicLinkInfo" => array(
                "domainUriPrefix" => "https://tamatago.page.link",
                "link" => $longUrl,
                "androidInfo" => array(
                    "androidPackageName" => "com.tamata.retail.app"
                ),
                "iosInfo" => array(
                    "iosBundleId" => "com.tamata.app.retail"
                )
            )
        );
        $headers = array('Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        if (isset($response['error'])) {
            $this->psrLogger->error("dynamic link create web:");
            $this->psrLogger->error($response['error']['message']);
            return false;
        } else {
            return $response['shortLink'];
        }
    }
}
