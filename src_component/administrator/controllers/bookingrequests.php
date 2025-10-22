<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerControllerBookingrequests extends AdminController
{
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
	}

    public function getModel($name = 'Bookingrequests', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
