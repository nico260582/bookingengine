<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerController extends AdminController
{
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
	}

    public function display($cachable = false, $urlparams = array())
    {
        $view = $this->input->get('view', 'bookingrequests');
        $this->input->set('view', $view);
        parent::display($cachable, $urlparams);
        return $this;
    }
}