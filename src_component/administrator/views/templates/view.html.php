<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class BookingmanagerViewTemplates extends HtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('templates');
        JToolbarHelper::title('Email Templates');
        JToolbarHelper::editList('template.edit');
        parent::display($tpl);
    }
}