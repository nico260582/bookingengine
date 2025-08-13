<?php
defined('_JEXEC') or die;

class BookingmanagerTableComplex extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__bookingmanager_complexes', 'id', $db);
    }

    public function check()
    {
        if (trim($this->name) == '') {
            $this->setError(JText::_('COM_BOOKINGMANAGER_ERR_COMPLEX_NAME_REQUIRED'));
            return false;
        }

        return true;
    }
}
