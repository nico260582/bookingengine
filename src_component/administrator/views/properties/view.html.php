<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use BookingmanagerHelper;

class BookingmanagerViewProperties extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    public $sidebar;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->filterForm = $this->get('FilterForm');

        $this->addToolbar();
        BookingmanagerHelper::addSubmenu('properties');
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_BOOKINGMANAGER_PROPERTIES'));
        ToolbarHelper::addNew('property.add');
        ToolbarHelper::editList('property.edit');
        ToolbarHelper::deleteList('', 'properties.delete');
    }
}
