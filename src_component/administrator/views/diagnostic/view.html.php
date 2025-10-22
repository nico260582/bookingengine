<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class BookingmanagerViewDiagnostic extends HtmlView
{
    protected $mailSettings;
    protected $schemaChecks;

    public function display($tpl = null)
    {
        $this->mailSettings = $this->getModel()->getMailSettings();
        $this->schemaChecks = $this->getModel()->getSchemaHealthChecks();
        BookingmanagerHelper::addSubmenu('diagnostic');
        ToolbarHelper::title('Diagnostic Tools');
        parent::display($tpl);
    }
}