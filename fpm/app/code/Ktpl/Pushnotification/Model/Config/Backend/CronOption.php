<?php

namespace Ktpl\Pushnotification\Model\Config\Backend;

class CronOption extends \Magento\Framework\App\Config\Value
{

    const CRON_STRING_PATH = 'crontab/default/jobs/recentlyviewcron/schedule/cron_expr';
 
    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/recentlyviewcron/run/model';
 
    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;
 
    /**
     * @var string
     */
    protected $_runModelPath = '';
 
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,      
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,        
        $runModelPath = '',
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;        
    }
 
    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $timeConfig = $this->getData('groups/recentlyviewcron/fields/time/value');
        $frequencyConfig= $this->getData('groups/recentlyviewcron/fields/cron_frequency/value');
 
        $cronExprArray = [
            intval($timeConfig[1]), //Minute
            intval($timeConfig[0]), //Hour
            $frequencyConfig == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequencyConfig == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*', //Day of the Week
        ];
 
        $cronExprString = join(' ', $cronExprArray);
 
        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }
        return parent::afterSave();
    }
}
