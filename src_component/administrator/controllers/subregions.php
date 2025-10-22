<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerControllerSubregions extends AdminController
{
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
	}

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
