<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper; // <-- Added
use Joomla\CMS\Layout\LayoutHelper;

class BookingmanagerViewProperties extends BaseHtmlView
{
    // Class properties changed from 'protected' to 'public'
    public $items;
    public $pagination;
    public $state;
    public $sidebar;
    public $filterForm;
    public $activeFilters;

    public function display($tpl = null)
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        // Get the filter form and active filters.
        $this->filterForm    = $this->get('FilterForm'); // <-- Added
        $this->activeFilters = $this->get('ActiveFilters'); // <-- Added

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('properties');

        if (LayoutHelper::getLayoutFile('sidebar')) {
            $this->sidebar = LayoutHelper::render('sidebar');
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Properties');
        $canDo = ContentHelper::getActions('com_bookingmanager'); // <-- Added

        if ($canDo->get('core.create')) { // <-- Added
            ToolbarHelper::addNew('property.add');
        } // <-- Added

        ToolbarHelper::editList('property.edit');
        ToolbarHelper::deleteList('Are you sure?', 'properties.delete');

        if ($canDo->get('core.admin')) { // <-- Added
            ToolbarHelper::preferences('com_bookingmanager');
        } // <-- Added
    }
}
