<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewDiagnostic extends HtmlView
{
    protected $mailSettings;

    public function display($tpl = null)
    {
        $this->mailSettings = $this->getModel()->getMailSettings();
        BookingmanagerHelper::addSubmenu('diagnostic');
        JToolbarHelper::title('Diagnostic Tools');
        parent::display($tpl);
    }
}