<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

class BookingmanagerViewComplexes extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;
    public $regionsViewContent; // Property to hold the rendered regions view

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // --- Correctly load and render the Regions view ---
        $regionsView = $this->getView('Regions', 'html');
        if ($regionsView) {
            $this->regionsViewContent = $regionsView->display();
        }
        // --- End of new code ---

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('complexes');
        $this->sidebar = JHtmlSidebar::render();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Complexes & Regions'); // Updated title
        ToolbarHelper::addNew('complex.add');
        ToolbarHelper::editList('complex.edit');
        ToolbarHelper::deleteList('Are you sure?', 'complexes.delete');
    }
}
