<?php
namespace Rtholidays\Component\Bookingmanager\Administrator\View\Regions;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;

class RegionsView extends BaseHtmlView
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
        $this->form  = $this->getModel('Region')->getForm();
        $this->item  = $this->getModel('Region')->getItem();

        // For a new item, ensure the object has the default properties the form expects.
        if (empty($this->item->id)) {
            $this->item->id = 0;
            $this->item->name = '';
            $this->item->parent_id = 0;
            $this->item->state = 1;
        }

        // Get all items to build the nested structure for the list
        $this->state->set('list.limit', 0);
        $items = $this->get('Items'); // Note: This uses the default model for the view (Regions)

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
        if (Factory::getUser()->authorise('core.manage', 'com_bookingmanager'))
        {
            require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
            \BookingmanagerHelper::addSubmenu('regions');
            $this->sidebar = \JHtmlSidebar::render();
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Regions');
    }
}
