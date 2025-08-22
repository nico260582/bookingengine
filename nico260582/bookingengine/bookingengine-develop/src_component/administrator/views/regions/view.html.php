<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

// Ensure the model path is included
JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');

class BookingmanagerViewRegions extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;
    protected $nestedItems;
    public $form;
    public $item;

    public function display($tpl = null)
    {
        $this->state = $this->get('State');

        $regionModel = JModelLegacy::getInstance('Region', 'BookingmanagerModel');
        if ($regionModel) {
            $this->form = $regionModel->getForm();
            $this->item = $regionModel->getItem();
        } else {
            JFactory::getApplication()->enqueueMessage('Error: Could not load the Region model.', 'error');
            return;
        }

        // For a new item, ensure the object has the default properties the form expects.
        if (empty($this->item->id)) {
            $this->item->id = 0;
            $this->item->name = '';
            $this->item->parent_id = 0;
            $this->item->state = 1;
        }

        // Get all items to build the nested structure for the list
        $this->state->set('list.limit', 0);
        $items = $this->get('Items');

        $nestedItems = [];
        $children = [];
        foreach ($items as $item) {
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
        BookingmanagerHelper::addSubmenu('regions');
        $this->sidebar = JHtmlSidebar::render();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Regions');
    }
}
