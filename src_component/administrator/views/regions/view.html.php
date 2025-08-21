<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class BookingmanagerViewRegions extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;
    protected $nestedItems;
    public $form;

    public function display($tpl = null)
    {
        $this->state      = $this->get('State');
        $this->form       = $this->getModel('Region')->getForm();

        // Get all items to build the nested structure
        $this->state->set('list.limit', 0);
        $items = $this->get('Items');

        // Create a nested structure
        $nestedItems = [];
        $children = [];

        foreach ($items as $item) {
            // Ensure children is an array
            if (!isset($item->children)) {
                $item->children = [];
            }
            if ($item->parent_id > 0) {
                $children[$item->parent_id][] = $item;
            }
        }

        foreach ($items as $item) {
            if ($item->parent_id == 0) {
                if (isset($children[$item->id])) {
                    $item->children = $children[$item->id];
                }
                $nestedItems[] = $item;
            }
        }

        $this->nestedItems = $nestedItems;

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('complexes');
        $this->sidebar = JHtmlSidebar::render();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Regions & Sub-regions');
        ToolbarHelper::preferences('com_bookingmanager');
    }
}
