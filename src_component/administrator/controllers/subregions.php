<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerControllerSubregions extends AdminController
{
    /**
     * The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_BOOKINGMANAGER_SUB_REGIONS';

    /**
     * Method to get the model for list view tasks like delete.
     */
    public function getModel($name = 'Subregion', $prefix = 'BookingmanagerModel', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
