<?php

namespace CAT\Bnpl\Model;
use Magento\Authorization\Model\UserContextInterface as UserContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Psr\Log\LoggerInterface as Logger;

class ShippingInformationManagementById extends \Magedelight\Quote\Model\ShippingInformationManagementById
{   
    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     * @deprecated 100.2.0
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated 100.2.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     * @deprecated 100.2.0
     */
    protected $totalsCollector;


    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;
    /**
     * @var \Magento\Quote\Model\ShippingFactory
     */
    private $shippingFactory;

    protected $_customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteAddressValidator $addressValidator
     * @param Logger $logger
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param CartExtensionFactory|null $cartExtensionFactory
     * @param ShippingAssignmentFactory|null $shippingAssignmentFactory
     * @param ShippingFactory|null $shippingFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        UserContext $userContext,
        Logger $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AddressInterface $addressInterface,
        BillingAddressManagementInterface $billingAddressManagement,
        CartExtensionFactory $cartExtensionFactory = null,
        ShippingAssignmentFactory $shippingAssignmentFactory = null,
        ShippingFactory $shippingFactory = null
    ) {
        Parent::__construct($paymentMethodManagement,$paymentDetailsFactory,$cartTotalsRepository,$quoteRepository,$addressValidator,$userContext,$logger,$addressRepository,$scopeConfig,$totalsCollector,$request,$customerRepository,$addressInterface,$billingAddressManagement,$cartExtensionFactory,$shippingAssignmentFactory,$shippingFactory);
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance();
        $this->shippingFactory = $shippingFactory ?: ObjectManager::getInstance()
            ->get(ShippingFactory::class);
    }

    public function saveAddressInformation(
        $cartId
    ) {
        // Get All Params i.e address_id, method
        $request = $this->_request->getParams();
        if (empty($request['addressId']) || empty($request['method']) || empty($request['carrier'])) {
            throw new InputException(
                __("Please select address & shipping method.")
            );
        }        

        /* get Address by address id */
        $selectedShippingAddress = $this->addressRepository->getById($request['addressId']);
       /* get Address by address id */
        

        try {
            $this->addressInterface->setFirstname($selectedShippingAddress->getFirstname());
            $this->addressInterface->setLastname($selectedShippingAddress->getLastname());
            $this->addressInterface->setCity($selectedShippingAddress->getCity());
            $this->addressInterface->setPostcode($selectedShippingAddress->getPostcode());
            $this->addressInterface->setTelephone($selectedShippingAddress->getTelephone());
            $this->addressInterface->setCountryId($selectedShippingAddress->getCountryId());
            $this->addressInterface->setStreet($selectedShippingAddress->getStreet());
            $region = $selectedShippingAddress->getRegion();
            if (is_object($region)) {
                $region = '';
            }
            $this->addressInterface->setRegion($region);
            $this->addressInterface->setRegionId($selectedShippingAddress->getRegionId());

            $this->addressInterface->setCustomAttributes($selectedShippingAddress->getCustomAttributes());
            //$this->billingAddressManagement->assign($cartId, $this->addressInterface, true);
            // Get Current Quote
            $quote = $this->quoteRepository->getActive($cartId);
            $quote->getBillingAddress()->addData($this->addressInterface->getData());
            $carrierCode = $request['carrier'];
            $methodCode = $request['method'];
            $quote = $this->prepareShippingAssignment(
                $quote,
                $this->addressInterface,
                $carrierCode . '_' . $methodCode
            );
            $quote->setIsMultiShipping(false);
            $this->quoteRepository->save($quote);
            /** @var \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails */
            $paymentDetails = $this->paymentDetailsFactory->create();
            foreach ($this->paymentMethodManagement->getList($cartId) as $paymentMethod) {
                if($paymentMethod->getCode() == 'bnpl'){
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $BnplHelper = $objectManager->create(\CAT\Bnpl\Helper\Data::class);
                    $BnplData = $BnplHelper->getCustomerBnplBalance($quote->getCustomer()->getEmail());
                    if(isset($BnplData['availableBalance'])){
                        $paymentMethods[] = [
                            'code' => $paymentMethod->getCode(),
                            'title' => $paymentMethod->getTitle(),
                            'availableBalance' => $BnplData['availableBalance']
                        ];
                    }
                }
                else{

                    $paymentMethods[] = [
                        'code' => $paymentMethod->getCode(),
                        'title' => $paymentMethod->getTitle()
                    ];
                }
            }
            $paymentDetails->setPaymentMethods($paymentMethods);
            $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
            return $paymentDetails;

        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The shipping information was unable to be saved. Verify the input data and try again.')
            );
        }
        
    }
    private function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method)
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }
}