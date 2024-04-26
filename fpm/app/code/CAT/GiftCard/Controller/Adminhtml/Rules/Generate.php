<?php

namespace CAT\GiftCard\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Dates;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Model\Service\CouponManagementService;
use CAT\GiftCard\Model\GiftCardRuleFactory;
use CAT\GiftCard\Model\CouponFactory;
use CAT\GiftCard\Model\ResourceModel\Coupon;
use Magento\SalesRule\Helper\Coupon as SalesRuleCouponHelper;

class Generate extends \CAT\GiftCard\Controller\Adminhtml\GiftCardRule implements HttpPostActionInterface
{
    /**
     * @var Date
     */
    protected $_dateFilter;

    /**
     * @var CouponGenerator
     */
    private $couponGenerator;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var CouponGenerationSpecInterfaceFactory
     */
    private $generationSpecFactory;

    /**
     * @var CouponManagementService
     */
    protected $couponManagementService;

    /**
     * @var GiftCardRuleFactory
     */
    protected $giftCardRuleFactory;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var int
     */
    protected $generatedCount = 0;

    protected $generatedCodes = [];

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Dates
     */
    protected $date;

    /**
     * @var Coupon
     */
    protected $resourceCoupon;

    /**
     * @var SalesRuleCouponHelper
     */
    protected $salesRuleCoupon;

    /**
     * Generate constructor.
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param CouponGenerator|null $couponGenerator
     * @param PublisherInterface|null $publisher
     * @param CouponGenerationSpecInterfaceFactory|null $generationSpecFactory
     * @param CouponManagementService $couponManagementService
     * @param GiftCardRuleFactory $giftCardRuleFactory
     * @param CouponFactory $couponFactory
     * @param DateTime $dateTime
     * @param Dates $date
     * @param Coupon $resourceCoupon
     * @param SalesRuleCouponHelper $salesRuleCoupon
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        Date $dateFilter,
        CouponGenerator $couponGenerator = null,
        PublisherInterface $publisher = null,
        CouponGenerationSpecInterfaceFactory $generationSpecFactory = null,
        CouponManagementService $couponManagementService,
        GiftCardRuleFactory $giftCardRuleFactory,
        CouponFactory $couponFactory,
        DateTime $dateTime,
        Dates $date,
        Coupon $resourceCoupon,
        SalesRuleCouponHelper $salesRuleCoupon
    ) {
        parent::__construct($context, $coreRegistry);
        $this->_dateFilter = $dateFilter;
        $this->couponGenerator = $couponGenerator ?:
            $this->_objectManager->get(CouponGenerator::class);
        $this->messagePublisher = $publisher ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PublisherInterface::class);
        $this->generationSpecFactory = $generationSpecFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                CouponGenerationSpecInterfaceFactory::class
            );
        $this->couponManagementService = $couponManagementService;
        $this->giftCardRuleFactory = $giftCardRuleFactory;
        $this->couponFactory = $couponFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->resourceCoupon = $resourceCoupon;
        $this->salesRuleCoupon = $salesRuleCoupon;
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }
        $result = [];
        $this->_initRule();

        $rule = $this->_coreRegistry->registry(\CAT\GiftCard\Model\RegistryConstants::CURRENT_GIFT_CARD_RULE);

        if (!$rule->getId()) {
            $result['error'] = __('Rule is not defined');
        } else {
            try {
                $data = $this->getRequest()->getParams();
                if (!empty($data['to_date'])) {
                    $inputFilter = new \Zend_Filter_Input(['to_date' => $this->_dateFilter], [], $data);
                    $data = $inputFilter->getUnescaped();
                }

                $data['quantity'] = isset($data['qty']) ? $data['qty'] : null;
                $couponSpec = $this->generationSpecFactory->create(['data' => $data]);
                $this->generate($couponSpec);

                //$this->messagePublisher->publish('sales_rule.codegenerator', $couponSpec);
                $this->messageManager->addSuccessMessage(
                    __('Message is added to queue, wait to get your coupons soon')
                );
                $this->_view->getLayout()->initMessages();
                $result['messages'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
            } catch (\Magento\Framework\Exception\InputException $inputException) {
                $result['error'] = __('Invalid data provided');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['error'] = $e->getMessage();
            } catch (\Exception $e) {
                $result['error'] = __(
                    'Something went wrong while generating coupons. Please review the log and try again.'
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }

    public function generate(\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec)
    {
        $data = $this->convertCouponSpec($couponSpec);
        if (!$this->validateData($data)) {
            throw new \Magento\Framework\Exception\InputException();
        }
        try {
            $rule = $this->giftCardRuleFactory->create()->load($couponSpec->getRuleId());
            if (!$rule->getRuleId()) {
                throw NoSuchEntityException::singleField(
                    \Magento\SalesRule\Model\Coupon::KEY_RULE_ID,
                    $couponSpec->getRuleId()
                );
            }
            if (!$rule->getUseAutoGeneration()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Specified rule does not allow automatic coupon generation')
                );
            }

            $this->generatePool($rule, $data);
            return $this->getGeneratedCodes();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Error occurred when generating coupons: %1', $e->getMessage())
            );
        }

    }

    public function convertCouponSpec(\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec) {
        $data = [];
        $data['rule_id'] = $couponSpec->getRuleId();
        $data['qty'] = $couponSpec->getQuantity();
        $data['format'] = $couponSpec->getFormat();
        $data['length'] = $couponSpec->getLength();
        $data['prefix'] = $couponSpec->getPrefix();
        $data['suffix'] = $couponSpec->getSuffix();
        $data['dash'] = $couponSpec->getDelimiterAtEvery();

        //ensure we have a format
        if (empty($data['format'])) {
            $data['format'] = $couponSpec::COUPON_FORMAT_ALPHANUMERIC;
        }

        //if specified, use the supplied delimiter
        if ($couponSpec->getDelimiter()) {
            $data['delimiter'] = $couponSpec->getDelimiter();
        }
        return $data;
    }

    public function validateData($data)
    {
        return !empty($data)
            && !empty($data['qty'])
            && !empty($data['rule_id'])
            && !empty($data['length'])
            && !empty($data['format'])
            && (int)$data['qty'] > 0
            && (int)$data['rule_id'] > 0
            && (int)$data['length'] > 0;
    }

    /**
     * @param $rule
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generatePool($rule, $data) {
        $this->generatedCount = 0;
        $this->generatedCodes = [];
        $size = $data['qty'];
        $maxAttempts = 10;
        //$this->increaseLength($rule, $data);
        /** @var $coupon \CAT\GiftCard\Model\Coupon */
        $coupon = $this->couponFactory->create();
        $nowTimestamp = $this->dateTime->formatDate($this->date->gmtTimestamp());

        for ($i = 0; $i < $size; $i++) {
            $attempt = 0;
            do {
                if ($attempt >= $maxAttempts) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('We cannot create the requested Coupon Qty. Please check your settings and try again.')
                    );
                }
                $code = $this->generateCode($data);
                ++$attempt;
            } while ($this->resourceCoupon->exists($code));

            $coupon->setId(null)
                ->setRuleId($rule->getRuleId())
                ->setUsageLimit($rule->getUsesPerCoupon())
                ->setUsagePerCustomer($rule->getUsesPerCustomer())
                ->setCreatedAt($nowTimestamp)
                ->setType(1)
                ->setCode($code)
                ->save();

            $this->generatedCount += 1;
            $this->generatedCodes[] = $code;
        }

        return $this;
    }

    protected function increaseLength($rule, $data)
    {
        $maxProbability = 0.25;
        //$chars = count($this->salesRuleCoupon->getCharset($rule->getFormat()));//alphanum
        $chars = count($this->salesRuleCoupon->getCharset('alphanum'));
        $size = $data['qty'];
        $length = (int)$data['length'];
        $maxCodes = pow($chars, $length);
        $probability = $size / $maxCodes;

        if ($probability > $maxProbability) {
            do {
                $length++;
                $maxCodes = pow($chars, $length);
                $probability = $size / $maxCodes;
            } while ($probability > $maxProbability);
            $this->setLength($length);
        }
    }

    public function generateCode($data)
    {
        $format = $data['format'];
        if (empty($format)) {
            $format = \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC;
        }

        $splitChar = $this->salesRuleCoupon->getCodeSeparator();
        $charset = $this->salesRuleCoupon->getCharset($format);

        $code = '';
        $charsetSize = count($charset);
        $split = max(0, (int)$data['dash']);
        $length = max(1, (int)$data['length']);
        for ($i = 0; $i < $length; ++$i) {
            $char = $charset[\Magento\Framework\Math\Random::getRandomNumber(0, $charsetSize - 1)];
            if (($split > 0) && (($i % $split) === 0) && ($i !== 0)) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        //return $data['prefix'] . $code . $data['suffix'];
        return $code;
    }

    public function getGeneratedCodes()
    {
        return $this->generatedCodes;
    }
}
