<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class BookingmanagerTableProperty extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_properties', 'id', $db);
    }

    public function check()
    {
        if (empty($this->article_id)) {
            $this->setError(Text::_('COM_BOOKINGMANAGER_ERR_PROPERTY_ARTICLE_REQUIRED'));
            return false;
        }

        if (empty($this->max_guests) || (int)$this->max_guests <= 0) {
            $this->setError(Text::_('COM_BOOKINGMANAGER_ERR_PROPERTY_MAX_GUESTS_INVALID'));
            return false;
        }

        // If number_of_units is not set or is not a positive number, default it to 1
        if (empty($this->number_of_units) || (int)$this->number_of_units <= 0) {
            $this->number_of_units = 1;
        }

        return true;
    }
}
