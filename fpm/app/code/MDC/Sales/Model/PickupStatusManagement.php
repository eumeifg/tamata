<?php
namespace MDC\Sales\Model;

use MDC\Sales\Model\Source\Order\PickupStatus;
use MDC\Sales\Api\PickupStatusManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magedelight\Catalog\Model\Product;

class PickupStatusManagement implements PickupStatusManagerInterface
{

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory
     */
    protected $customMessageInterface;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * PickupStatusManagement constructor.
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\App\RequestInterface $request
     * @param PsrLogger $logger
     * @param \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param Product $vendorProduct
     * @param \Magedelight\Catalog\Api\Data\ProductRequestInterfaceFactory $vendorProductInterface
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        PsrLogger $logger,
        \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        Product $vendorProduct,
        \Magedelight\Catalog\Api\Data\ProductRequestInterfaceFactory $vendorProductInterface
    ) {
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->customMessageInterface = $customMessageInterface;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->vendorProduct = $vendorProduct;
        $this->vendorProductInterface = $vendorProductInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus($vendorOrderId, $pickupStatus, $barcodeNumber = null)
    {
        try{
            $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
            if($vendorOrder->getVendorId() != $this->userContext->getUserId()){
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('Requested order does not exist.'));
                $customMessage->setStatus(false);
                return $customMessage;
            }
            $vendorOrder->setPickupStatus($pickupStatus);            
            $vendorOrder->setBarcodeNumber($barcodeNumber);
            $this->vendorOrderRepository->save($vendorOrder);
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('Pickup status and Barcode Screen successfully updated.'));
            $customMessage->setStatus(true);
            return $customMessage;
        }catch (\Exception $exception){
            $this->logger->critical($exception);
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('Failed to update pickup status and Barcode Screen.'));
            $customMessage->setStatus(false);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function displayVendorProductDetail($vendorSku)
    {
		$response = [];
        try{

            $vendorProductDetails = $this->vendorProduct->getVendorProductsBySku($vendorSku);
            $vendorOfferDetail = $this->vendorProductInterface->create();
            $vendorOfferDetail->setReorderLevel($vendorProductDetails->getReorderLevel());
            $vendorOfferDetail->setSpecialPrice($vendorProductDetails->getSpecialPrice());
            $vendorOfferDetail->setQty($vendorProductDetails->getQty());
            $vendorOfferDetail->setVendorProductId($vendorProductDetails->getVendorProductId());
            $vendorOfferDetail->setMarketplaceProductId($vendorProductDetails->getMarketplaceProductId());
            $vendorOfferDetail->setVendorId($vendorProductDetails->getVendorId());
            $vendorOfferDetail->setSpecialFromDate($vendorProductDetails->getSpecialFromDate());
            $vendorOfferDetail->setSpecialToDate($vendorProductDetails->getSpecialToDate());
			//~ $result = array();
            //~ foreach ($vendorOfferDetail->getData() as $key => $value) {
                //~ if($key != null)
                //~ {
                   //~ $vendorOfferDetails[$key] = $value; 
                //~ }
            //~ }
            $response[] = $vendorOfferDetail->getData();
            return $response;

        }catch (\Exception $exception){
            $this->logger->critical($exception);
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('Please check Vendor Sku.'));
            $customMessage->setStatus(false);
        }
    }
}
