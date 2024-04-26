<?php


namespace CAT\ReferralProgram\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\SalesRule\Model\CouponRepository;
use Magento\Store\Model\ScopeInterface;

class CleanUnlinkedCoupons
{
    const LIMIT = 'cat_customer_referral/coupon_clean/limit';
    const ENABLE = 'cat_customer_referral/coupon_clean/enable_coupon';

    /**
     * @var CouponRepository
     */
    private $couponRepository;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var string[]
     */
    private $columns = ['code', 'coupon_id'];

    private $scopeConfig;

    /**
     * CleanUnlinkedCoupons constructor.
     * @param CouponRepository $couponRepository
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CouponRepository $couponRepository,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->couponRepository = $couponRepository;
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function cleanUnlinkedCouponCodes(){
        try {
            $isEnabled = $this->scopeConfig->getValue(
                self::ENABLE,
                ScopeInterface::SCOPE_WEBSITE
            );
            if ($isEnabled){
                $limit = $this->scopeConfig->getValue(
                    self::LIMIT,
                    ScopeInterface::SCOPE_WEBSITE
                );
                $limit = !empty($limit) ? $limit : 100;
                $connection = $this->resourceConnection->getConnection();
                $sql = $connection->select()->from(
                    ['sales_coup' => $this->resourceConnection->getTableName('salesrule_coupon')],
                    $this->columns
                )->useStraightJoin()->where(
                    'sales_coup.code not in(select value From customer_entity_text where customer_entity_text.attribute_id=475) and sales_coup.rule_id = 1087'
                )->limit($limit);
                $result = $connection->query($sql)->fetchAll();
                foreach ($result as $item){
                    $this->logDeletedCoupon("before coupon verification");
                    $this->logDeletedCoupon("-----------coupon code-------------".$item['code']);
                    $isExist = $this->checkIsExistAtCustomer($item['code']);
                    if (empty($isExist)){
                        $this->logDeletedCoupon("coupon code not assigned to any customer");
                        $this->logDeletedCoupon("coupon code before delete");
                        $this->couponRepository->deleteById($item['coupon_id']);
                        $this->logDeletedCoupon("coupon code deleted".$item['code']);
                        $this->logDeletedCoupon("coupon id deleted".$item['coupon_id']);
                    }
                }
                $this->cleanUnlinkedCouponCodes();
            }
        } catch (\Exception $exception){
            $this->logDeletedCoupon($exception->getMessage());
        }
    }

    /**
     * @param $coupon
     * @return array
     */
    public function checkIsExistAtCustomer($coupon){
        try {
            $connection = $this->resourceConnection->getConnection();
            $verifyResult = $connection->select()->from(
                ['cet' => $this->resourceConnection->getTableName('customer_entity_text')],
                ['entity_id']
            )->useStraightJoin()->where(
                'value ='."'$coupon'"
            );
            $resultData = $connection->query($verifyResult)->fetchAll();
        }catch (\Exception $e){

        }
        return $resultData;
    }

    /**
     * @param $log
     */
    public function logDeletedCoupon($log){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Unlinked_Deleted_coupon_codes.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($log);
    }
}
