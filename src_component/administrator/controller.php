<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerController extends AdminController
{
    protected $default_view = 'bookingrequests';

	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
	}

    public function display($cachable = false, $urlparams = array())
    {
        $this->input->set('view', $this->input->getCmd('view', $this->default_view));
        parent::display($cachable, $urlparams);
        return $this;
    }
}
