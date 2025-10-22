<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\SidebarHelper;
use Joomla\CMS\Uri\Uri;

class BookingmanagerViewProperty extends BaseHtmlView
{
    public $form;
    public $item;
    public $sidebar;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('properties');
        $this->sidebar = SidebarHelper::render();

        $this->addToolbar();

        // Load the custom javascript directly to bypass Web Asset Manager issues
        $this->document->addScript(Uri::root() . 'media/com_bookingmanager/js/property-edit.js?v=2.0.1');

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? 'New Property' : 'Edit Property');

        ToolbarHelper::apply('property.apply');
        ToolbarHelper::save('property.save');
        ToolbarHelper::cancel('property.cancel');
    }
}
