<?php

namespace CAT\OfferPage\Controller\Adminhtml\Image;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ImageUploader;

class Uploader extends \Magento\Backend\App\Action
{
    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @param Action\Context $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Action\Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    public function execute() {
        $docs = [];
        $dynamicRows = isset($_FILES['dynamic_rows']) ? $_FILES['dynamic_rows'] : [];

        foreach ($dynamicRows as $key => $dynamicRow){
            foreach ($dynamicRow as $doc){
                if($key == 'name'){
                    $docs['name'] = $doc['image'];
                }
                if($key == 'type'){
                    $docs['type'] = $doc['image'];
                }
                if($key == 'tmp_name'){
                    $docs['tmp_name'] = $doc['image'];
                }
                if($key == 'error'){
                    $docs['error'] = $doc['image'];
                }
                if($key == 'size'){
                    $docs['size'] = $doc['image'];
                }
            }
        }
        if(!empty($docs)){
            try {
                $result = $this->imageUploader->saveFileToTmpDir($docs);
                $result['cookie'] = [
                    'name' => $this->_getSession()->getName(),
                    'value' => $this->_getSession()->getSessionId(),
                    'lifetime' => $this->_getSession()->getCookieLifetime(),
                    'path' => $this->_getSession()->getCookiePath(),
                    'domain' => $this->_getSession()->getCookieDomain(),
                ];
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
        }
    }
}
