<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

class BookingmanagerViewDiagnostic extends HtmlView
{
    protected $mailSettings;
    protected $schemaChecks;

    public function display($tpl = null)
    {
        $this->mailSettings = $this->getModel()->getMailSettings();
        $this->schemaChecks = $this->getModel()->getSchemaHealthChecks();
        $this->addToolbar();

        $layout = new FileLayout('sidebar');
        $this->sidebar = $layout->render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Diagnostic Tools');
    }
}
