<?php
namespace Ktpl\BannerManagement\Model\ResourceModel\Banner;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\RequestInterface;

class CollectionBanner extends AbstractCollection
{

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init('Ktpl\BannerManagement\Model\Banner', 'Ktpl\BannerManagement\Model\ResourceModel\Banner');
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->storeManager = $storeManager;
    }
    protected function _initSelect()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $request = $objectManager->create('Magento\Framework\App\Response\RedirectInterface');
        $url =$request->getRefererUrl();
        $parts=strstr($url, "key", true);
        $parts1=strstr($parts, "/id/");
        $ids=explode("/", $parts1);
        $id= !empty($ids[2]) ? $ids[2] : null;

        parent::_initSelect();

        if ($id) {
            $this->getSelect()->joinLeft(
                ['secondTable' => $this->getTable('ktpl_bannerslider_banner_slider')],
                'main_table.banner_id = secondTable.banner_id',
                []
            )->where("secondTable.slider_id=" . $id);
        } else {
            $this->getSelect()->joinLeft(
                ['secondTable' => $this->getTable('ktpl_bannerslider_banner_slider')],
                'main_table.banner_id = secondTable.banner_id',
                []
            )->where("secondTable.slider_id=0");
        }
    }
}
