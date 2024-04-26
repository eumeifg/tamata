<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Controller\Adminhtml\Ktplpushnotifications;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory;


class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;
    protected $storeManager;
    protected $dateFactory;
    protected $fireBaseFactory;
    protected $resourceConnection;
    protected $connection;
    protected $deviceCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory,
        CollectionFactory $deviceCollectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->dateFactory = $dateFactory;
        $this->fireBaseFactory = $fireBaseFactory;
        $this->deviceCollectionFactory = $deviceCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        //echo "<pre>"; print_r($data); echo "</pre>"; die();

        if(isset($data['send_to_customer_group']) && $data['send_to_customer_group'] > 1 ) {
            $data['send_to_customer_group'] = implode(',', $data['send_to_customer_group']);
            /*$select = $this->connection->select()->from('customer_entity', 'entity_id')
                ->where('group_id IN ('. $data['send_to_customer_group'].')');
            $result = $this->connection->fetchCol($select);
            $customerIds = array_values(array_unique($result));
            $deviceCollection = $this->deviceCollectionFactory->create();
            $deviceCollection->addFieldToFilter('main_table.status', '1');
            $deviceCollection->getSelect()->group('device_token');
            $deviceCollection->addFieldToFilter('customer_id', ['in' => $customerIds]);
            if ($deviceCollection->getSize() > 0) {
                $data['device_per_batch'] = round(((int)$deviceCollection->getSize() * (int)$data['device_percentage'])/100);
            }*/
        }

        if(isset($data['send_to_customer']) &&  is_array($data['send_to_customer'])) {
            $data['send_to_customer'] = implode(',', $data['send_to_customer']);
        }

        if(isset($data['send_to_customer']) &&  !empty($data['send_to_customer'])) {
            $data['send_to_customer'] = trim(preg_replace('/\s\s+/', ' ', $data['send_to_customer']));
            /*$select = $this->connection->select()->from('customer_entity', 'entity_id')
                    ->where("email IN ('". str_replace(',', "','", $data['send_to_customer'])."')");
            $result = $this->connection->fetchCol($select);
            $customerIds = array_values(array_unique($result));
            $deviceCollection = $this->deviceCollectionFactory->create();
            $deviceCollection->addFieldToFilter('main_table.status', '1');
            $deviceCollection->getSelect()->group('device_token');
            $deviceCollection->addFieldToFilter('customer_id', ['in' => $customerIds]);
            if ($deviceCollection->getSize() > 0) {
                $data['device_per_batch'] = round(((int)$deviceCollection->getSize() * (int)$data['device_percentage'])/100);
            }*/
        }

        if (isset($data['send_to']) && !empty($data['send_to']) & $data['send_to'] == 'all') {
            $deviceCollection = $this->deviceCollectionFactory->create();
            $deviceCollection->addFieldToFilter('main_table.status', '1');
            $deviceCollection->getSelect()->group('device_token');
            if ($deviceCollection->getSize() > 0) {
                $data['device_per_batch'] = round(((int)$deviceCollection->getSize() * (int)$data['device_percentage'])/100);
                $data['total_count'] = round($deviceCollection->getSize()/$data['device_per_batch']);
            }
        }

        //echo "<pre>"; print_r($data); echo "</pre>"; die();

        if(isset($data['image_url'][0]['url'])){
            $data['image_url']  = $data['image_url'][0]['url'];
        }
        $date = $this->dateFactory->create()->gmtDate();
        if ($data) {

            $id = $this->getRequest()->getParam('id');

            $model = $this->_objectManager->create(\Ktpl\Pushnotification\Model\KtplPushnotifications::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Pushnotifications no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            //echo $model->getData('created_at');die;
            if(!$model->getCreatedAt()) {
                $data['created_at'] = $date;
            } else {
                $data['updated_at'] = $date;
            }

            $data['is_sent'] = 1;  // Newly created notification, to be sent

            $model->setData($data);
            try {

                $model->save();

                // $firebase = $this->fireBaseFactory->create();
                // $firebase->setTitle($model->getTitle())
                // ->setMessage($model->getDescription())
                // ->setType($model->getTypePromotion())
                // ->setTypeId($model->getPromotionId())
                // ->setImage($model->getImageUrl());

                // if($model->getSendTo() == 'customer_group') {

                //     $firebase->setCustomerGroups($model->getSendToCustomerGroup());

                // } elseif ($model->getSendTo() == 'customer') {

                //     $firebase->setCustomers($model->getSendToCustomer());
                // }

                // $firebase->send();

                $this->messageManager->addSuccessMessage(__('You saved the Pushnotifications.'));
                $this->dataPersistor->clear('ktpl_pushnotification');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                 $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Pushnotifications.'));
            }

            $this->dataPersistor->set('ktpl_pushnotification', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

