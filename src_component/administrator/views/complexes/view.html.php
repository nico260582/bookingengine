<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class BookingmanagerViewComplexes extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;
    public $nestedItems;
    public $form;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        // --- Prepare data for the regions tab ---
        $regionsModel = JModelLegacy::getInstance('Regions', 'BookingmanagerModel');
        $regionsModel->setState('list.limit', 0);
        $regions = $regionsModel->getItems();

        $nestedItems = [];
        $children = [];
        foreach ($regions as $item) {
            if (!isset($item->children)) {
                $item->children = [];
            }
            if ($item->parent_id > 0) {
                $children[$item->parent_id][] = $item;
            }
        }
        foreach ($regions as $item) {
            if ($item->parent_id == 0) {
                if (isset($children[$item->id])) {
                    $item->children = $children[$item->id];
                }
                $nestedItems[] = $item;
            }
        }
        $this->nestedItems = $nestedItems;
        $this->form = JModelLegacy::getInstance('Region', 'BookingmanagerModel')->getForm();


        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('complexes');
        $this->sidebar = JHtmlSidebar::render();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Region & Complexes');
        ToolbarHelper::addNew('complex.add');
        ToolbarHelper::editList('complex.edit');
        ToolbarHelper::deleteList('Are you sure?', 'complexes.delete');
    }
}
