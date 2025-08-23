<?php
namespace RTHolidays\Component\BookingManager\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class TemplateTable extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_templates', 'id', $db);
    }

    public function check()
    {
        if (trim($this->subject) === '')
        {
            $this->setError(Text::_('COM_BOOKINGMANAGER_ERROR_TEMPLATE_SUBJECT_REQUIRED'));
            return false;
        }
        return true;
    }
}