<?php
namespace Ktpl\Pushnotification\Cron;

use Ktpl\Pushnotification\Model\AutoSendPushnotificationModel;

class AutoSendPromotionalNotification
{

	/**
     * @var AutoSendPushnotificationModel
     */
    protected $autoSendPushnotificationModel;

    public function __construct(
        AutoSendPushnotificationModel $autoSendPushnotificationModel
    ) {
        $this->autoSendPushnotificationModel = $autoSendPushnotificationModel;
    }

	public function sendPushNotifications(){

		$this->autoSendPushnotificationModel->sendFirebaseNotifications();

	}

}