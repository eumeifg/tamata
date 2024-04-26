<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   MDC_Vendor
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace MDC\Vendor\Plugin\Helper;

class ValidateMicrositeUrl
{

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Vendor\Model\Microsite
     */
    protected $micrositeModel;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
    public function __construct(
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Vendor\Model\Microsite $micrositeModel,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->micrositeModel = $micrositeModel;
        $this->urlBuilder = $urlBuilder;
    }
    
    /**
     *
     * @param \Magedelight\Vendor\Helper\Microsite\Data $subject
     * @param integer $vendorId
     * @return integer|null
     */
    public function aroundGetVendorMicrositeUrl(
        \Magedelight\Vendor\Helper\Microsite\Data $subject,
        \Closure $proceed,
        $vendorId
    ) {
        if ($vendorId) {
            try {
                $vendor = $this->vendorRepository->getById($vendorId);
                if(((int)$vendor->getEnableMicrosite() == 1)){
                    $micrositeUrl = $this->micrositeModel->getVendorMicrositeUrl($vendorId);
                    if ($micrositeUrl) {
                        return $this->urlBuilder->getUrl($micrositeUrl);
                    }
                }
            } catch (\Exception $e) {
                return;
            }
        }
    }
}
