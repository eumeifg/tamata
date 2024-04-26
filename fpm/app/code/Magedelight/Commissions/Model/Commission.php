<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model;

use Magedelight\Commissions\Api\Data\CommissionInterface;
use Magedelight\Commissions\Model\Source\CalculationType;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\AbstractModel;

class Commission extends AbstractModel implements CommissionInterface
{
    /**
     * @var CalculationType
     */
    private $calculationType;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlBuilder;

    /**
     * @param CalculationType $calculationType
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        CalculationType $calculationType,
        ScopeConfigInterface $scopeConfigInterface,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->calculationType = $calculationType;
        $this->scopeConfig = $scopeConfigInterface;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Commissions\Model\ResourceModel\Commission::class);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getCalculationType()
     */
    public function getCalculationType()
    {
        return $this->getData(self::CALCULATION_TYPE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setCalculationType()
     */
    public function setCalculationType($calculation_type)
    {
        return $this->setData(self::CALCULATION_TYPE, $calculation_type);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getCommissionValue()
     */
    public function getCommissionValue()
    {
        return $this->getData(self::COMMISSION_VALUE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setCommissionValue()
     */
    public function setCommissionValue($value)
    {
        return $this->setData(self::COMMISSION_VALUE, $value);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getMarketplaceFee()
     */
    public function getMarketplaceFee()
    {
        return $this->getData(self::MARKETPLACE_FEE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getMarketplaceFeeType()
     */
    public function getMarketplaceFeeType()
    {
        return $this->getData(self::MARKETPLACE_FEE_TYPE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setMarketplaceFee()
     */
    public function setMarketplaceFee($fee)
    {
        return $this->setData(self::MARKETPLACE_FEE, $fee);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setMarketplaceFeeType()
     */
    public function setMarketplaceFeeType($marketplace_fee_type)
    {
        return $this->setData(self::MARKETPLACE_FEE_TYPE, $fee);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getProductCategory()
     */
    public function getProductCategory()
    {
        return $this->getData(self::PRODUCT_CATEGORY);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setProductCategory()
     */
    public function setProductCategory($category_id)
    {
        return $this->setData(self::PRODUCT_CATEGORY, $category_id);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::getStatus()
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionInterface::setStatus()
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     *
     * @param array $categoryIds An array of categories ids.
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryCommissionCharges(array $categoryIds)
    {
        $categoryCommissionArray = [];
        foreach ($categoryIds as $catid) {
            $categoryCommission = $this->load($catid, 'product_category');
            $catgory = $this->categoryRepository->get($catid);
            if ($categoryCommission->getCommissionValue() && $categoryCommission->getStatus()) {
                $categoryCommissionArray[$catgory->getName()]['commissionRate']      =
                    $categoryCommission->getCommissionValue();
                $categoryCommissionArray[$catgory->getName()]['commissionType']      =
                    $this->calculationType->toArray()[$categoryCommission->getCalculationType()];
                $categoryCommissionArray[$catgory->getName()]['marketplaceRate']     =
                    $categoryCommission->getMarketplaceFee();
                $categoryCommissionArray[$catgory->getName()]['marketplaceType']     =
                    $this->calculationType->toArray()[$categoryCommission->getMarketplaceFeeType()];
                $categoryCommissionArray[$catgory->getName()]['cancellationRate']    =
                    $categoryCommission->getCancellationFeeCommissionValue();
                $categoryCommissionArray[$catgory->getName()]['cancellationType']    =
                    $this->calculationType->toArray()[$categoryCommission->getCancellationFeeCalculationType()];
            } else {
                $categoryCommissionArray[$catgory->getName()]['error'] = __("Not Found");
            }
            $categoryCommission->unsetData();
        }
        return $categoryCommissionArray;
    }

    /**
     *
     * @param array $categoryIds An array of categories ids.
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlForCategoryCommissionRates(array $categoryIds)
    {
        $url = [];
        foreach ($categoryIds as $catid) {
            $categoryCommission = $this->load($catid, 'product_category');
            $catgory = $this->categoryRepository->get($catid);
            if (!empty($categoryCommission->getCommissionValue())) {
                $params = ['id' => $categoryCommission->getId()];
                $tempArray[0] = 'Edit';
                $tempArray[1] = $this->_urlBuilder->getUrl('commissionsadmin/categorycommission/edit', $params);
            } else {
                $tempArray[0] = 'Add';
                $tempArray[1] = $this->_urlBuilder->getUrl('commissionsadmin/categorycommission/index');
            }
            $url[$catgory->getName()] = $tempArray;
            $categoryCommission->unsetData();
        }
        return $url;
    }
}
