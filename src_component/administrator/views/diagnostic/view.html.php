<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewDiagnostic extends HtmlView
{
    protected $mailSettings;
    protected $schemaChecks;

    public function display($tpl = null)
    {
        $this->mailSettings = $this->getModel()->getMailSettings();
        $this->schemaChecks = $this->getModel()->getSchemaHealthChecks();
        BookingmanagerHelper::addSubmenu('diagnostic');
        JToolbarHelper::title('Diagnostic Tools');
        parent::display($tpl);
    }
}