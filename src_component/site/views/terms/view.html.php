<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class BookingmanagerViewTerms extends BaseHtmlView
{
    protected $terms;

    public function display($tpl = null)
    {
        $this->terms = $this->get('Terms');
        parent::display($tpl);
    }
}
