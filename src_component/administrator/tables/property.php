<?php
defined('_JEXEC') or die;

class BookingmanagerTableProperty extends JTable
{
    public $id = null;
    public $article_id = null;
    public $max_guests = null;
    public $number_of_units = null;

    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_properties', 'id', $db);
    }

    public function check()
    {
        if (empty($this->article_id)) {
            $this->setError(JText::_('COM_BOOKINGMANAGER_ERR_PROPERTY_ARTICLE_REQUIRED'));
            return false;
        }

        if (empty($this->max_guests) || (int)$this->max_guests <= 0) {
            $this->setError(JText::_('COM_BOOKINGMANAGER_ERR_PROPERTY_MAX_GUESTS_INVALID'));
            return false;
        }

        if (empty($this->number_of_units) || (int)$this->number_of_units <= 0) {
            $this->setError('Number of available units must be a positive number.');
            return false;
        }

        return true;
    }
}
