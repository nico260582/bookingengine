<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class BookingmanagerViewRegions extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('regions');
        $this->sidebar = JHtmlSidebar::render();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('Regions'));
        ToolbarHelper::addNew('region.add');
        ToolbarHelper::editList('region.edit');
        ToolbarHelper::deleteList('Are you sure?', 'regions.delete');
    }
}
