<?php 
namespace Ktpl\Pushnotification\Controller\Adminhtml\Ktplpushnotifications;

use Magento\Framework\Exception\LocalizedException;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

class SendByOrderView extends \Magento\Backend\App\Action
{

    protected $dataPersistor;
    protected $storeManager;
    protected $dateFactory;
    protected $fireBaseFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->dateFactory = $dateFactory;
        $this->fireBaseFactory = $fireBaseFactory;
        $this->resultJsonFactory = $resultJsonFactory;
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
        $resultJson = $this->resultJsonFactory->create();

        if(isset($data['send_to_order']) &&  is_array($data['send_to_order'])) {
            $data['send_to_order'] = implode(',', $data['send_to_order']);
        }

        if(isset($data['send_to_order']) &&  !empty($data['send_to_order'])) {
            $data['send_to_order'] = trim(preg_replace('/\s\s+/', ' ', $data['send_to_order']));
        }
        
        if(isset($data['image_url'])){
            $data['image_url']  = $data['image_url'];
        }

        $date = $this->dateFactory->create()->gmtDate();
        if ($data) {

            $customer_id = $data['order_customer_id'];
            $order_id = $data['order_id'];
            $id = $this->getRequest()->getParam('id');

            $model = $this->_objectManager->create(\Ktpl\Pushnotification\Model\KtplPushnotifications::class)->load($id);
            if (!$model->getId() && $id) {
             
                $result = array("status"=> false, "message" => 'This Pushnotifications no longer exists.');
                
                return $resultJson->setData($result);
                
            }
           
            if(!$model->getCreatedAt()) {
                $data['created_at'] = $date;
            } else {
                $data['updated_at'] = $date;
            }
            
            $data['is_sent'] = 3;  // Notification sent
            $data['send_to'] = "customer";
            $model->setData($data);

            try {

                $model->save();
                
                $firebase = $this->fireBaseFactory->create();
                $firebase->setTitle($model->getTitle())
                ->setMessage($model->getDescription())
                ->setType($model->getTypePromotion())
                ->setTypeId($model->getPromotionId())
                ->setImage($model->getImageUrl())
                ->setOrderId($model->getOrderId());

                if ($model->getSendTo() == 'customer') {  

                    $firebase->setCustomers($model->getSendToCustomer());
                }
                
                $firebase->send();

                $this->messageManager->addSuccessMessage(__('Notification has been sent Successfully.'));
                $this->dataPersistor->clear('ktpl_pushnotification');

                $result = array("status"=> true, "message" => "Success");
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $result = array("status"=> false, "message" => $e->getMessage());
            } catch (\Exception $e) {
                 $this->messageManager->addExceptionMessage(__("Something went wrong while saving the Pushnotifications."));
                $result = array("status"=> false, "message" => "Something went wrong while saving the Pushnotifications.");
            }
            return $resultJson->setData($result);            
        }
         
    }
}

