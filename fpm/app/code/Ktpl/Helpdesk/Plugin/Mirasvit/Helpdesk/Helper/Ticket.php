<?php

namespace Ktpl\Helpdesk\Plugin\Mirasvit\Helpdesk\Helper;

class Ticket
{
    public function afterFormatTicketLabel(
        \Mirasvit\Helpdesk\Helper\Ticket $subject,
        $result,
        $amount
    ) {
        return ($amount > 0) ? "($amount)" : "";
    }
}
