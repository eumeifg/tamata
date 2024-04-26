<?php

namespace CAT\Patches\Model\ResourceModel\DesktopNotification;

class Collection extends \Mirasvit\Helpdesk\Model\ResourceModel\DesktopNotification\Collection
{

    public function getMessagesCollection($user)
    {

        /** @var $this $collection */
        $collection = $this->joinTickets();

        $select = $collection->getSelect();
        if (!$permission = $this->helpdeskPermission->getPermission()) {
            $select->where('ticket.department_id', -1);
        }
        if($permission){

            $departmentIds = $permission->getDepartmentIds();
            if (!in_array(0, $departmentIds)) {
                $select->where('ticket.department_id IN ('.implode(',', $departmentIds).')');
            }
        }

        // add 1 hour to current gmt date
        $date = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');

        $select->where('main_table.created_at < ?', $date);

        $select->where(
            'read_by_user_ids NOT LIKE "%,' . $user->getId() . ',%" ' . //user has not read this notification before
            //notification is about new message of ticket of this user
            'AND (
                (
                    notification_type = "' . \Mirasvit\Helpdesk\Model\Config::NOTIFICATION_TYPE_NEW_MESSAGE . '"
                    AND (ticket.user_id = ' . $user->getId() . ' ) ' .
                ') ' .
            //or notification about something else
                'OR notification_type <> "' . \Mirasvit\Helpdesk\Model\Config::NOTIFICATION_TYPE_NEW_MESSAGE . '"' .
            ')'
        );

        return $collection;
    }
}
