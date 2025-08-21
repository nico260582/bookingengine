<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class BookingmanagerViewRegion extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Load the sidebar
        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/bookingmanager.php';
        BookingmanagerHelper::addSubmenu('regions');
        $this->sidebar = JHtmlSidebar::render();

        HTMLHelper::_('behavior.formvalidator');
        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? Text::_('New Region') : Text::_('Edit Region'));
        ToolbarHelper::save('region.save');
        ToolbarHelper::cancel('region.cancel', 'JTOOLBAR_CANCEL');
    }
}
