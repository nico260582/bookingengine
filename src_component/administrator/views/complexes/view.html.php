<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\Model\BaseModel;

class BookingmanagerViewComplexes extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;
    protected $regionsItems;
    protected $regionsPagination;
    protected $regionsState;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Get data for the regions tab
        $regionsModel = \Joomla\CMS\MVC\Model\BaseModel::getInstance('Regions', 'BookingmanagerModel', ['ignore_request' => true]);
        $this->regionsItems      = $regionsModel->getItems();
        $this->regionsPagination = $regionsModel->getPagination();
        $this->regionsState      = $regionsModel->getState();

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('complexes');
        $this->sidebar = JHtmlSidebar::render();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Regions & Complexes');
        // Toolbar buttons will be handled in the template based on the active tab
    }
}
