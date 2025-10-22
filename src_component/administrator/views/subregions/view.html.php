<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\SidebarHelper;

class BookingmanagerViewSubregions extends BaseHtmlView
{
    public $items;
    public $pagination;
    public $state;
    public $filterForm;
    public $sidebar;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->filterForm = $this->get('FilterForm');

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('subregions');
        $this->sidebar = SidebarHelper::render();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_BOOKINGMANAGER_SUB_REGIONS'));

        ToolbarHelper::addNew('subregion.add');
        ToolbarHelper::editList('subregion.edit');
        ToolbarHelper::deleteList('', 'subregions.delete');
        ToolbarHelper::publish('subregions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('subregions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
    }
}
