<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

class BookingmanagerViewProperty extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('properties');
        $this->sidebar = JHtmlSidebar::render();

        $this->addToolbar();

        // Load the custom javascript
        $wa = $this->document->getWebAssetManager();
        $wa->useScript('com_bookingmanager.property-edit');

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
