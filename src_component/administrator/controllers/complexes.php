<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class BookingmanagerControllerComplexes extends AdminController
{
    public function getModel($name = 'Complexes', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function display($cachable = false, $urlparams = array())
    {
        // Get the view
        $view = $this->getView('complexes', 'html', 'BookingmanagerView');

        // Get the regions view and assign it to the complexes view
        $view->regionsView = $this->getView('regions', 'html', 'BookingmanagerView');

        // Display the view
        $view->display();
    }
}
