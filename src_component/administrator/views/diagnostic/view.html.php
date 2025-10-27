<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\LayoutHelper;

class BookingmanagerViewDiagnostic extends HtmlView
{
    protected $mailSettings;
    protected $schemaChecks;

    public function display($tpl = null)
    {
        $this->mailSettings = $this->getModel()->getMailSettings();
        $this->schemaChecks = $this->getModel()->getSchemaHealthChecks();
        BookingmanagerHelper::addSubmenu('diagnostic');
        $this->addToolbar();

        if (LayoutHelper::getLayoutFile('sidebar')) {
            $this->sidebar = LayoutHelper::render('sidebar');
        }

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Diagnostic Tools');
    }
}
