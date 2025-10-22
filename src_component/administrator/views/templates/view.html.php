<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class BookingmanagerViewTemplates extends HtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('templates');
        ToolbarHelper::title('Email Templates');
        ToolbarHelper::editList('template.edit');
        parent::display($tpl);
    }
}