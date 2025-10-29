<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use BookingmanagerHelper;

class BookingmanagerViewDiagnostic extends HtmlView
{
    public $sidebar;

    public function display($tpl = null)
    {
        $this->addToolbar();
        BookingmanagerHelper::addSubmenu('diagnostic');
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_BOOKINGMANAGER_DIAGNOSTIC'));
    }
}
