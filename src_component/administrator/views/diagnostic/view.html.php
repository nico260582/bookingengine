<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\SidebarHelper;

class BookingmanagerViewDiagnostic extends HtmlView
{
    protected $mailSettings;
    protected $schemaChecks;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->mailSettings = $this->getModel()->getMailSettings();
        $this->schemaChecks = $this->getModel()->getSchemaHealthChecks();
        BookingmanagerHelper::addSubmenu('diagnostic');
        $this->sidebar = SidebarHelper::render();
        ToolbarHelper::title('Diagnostic Tools');
        parent::display($tpl);
    }
}
