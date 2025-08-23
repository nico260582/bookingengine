<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;

class BookingmanagerViewTemplates extends HtmlView
{
    protected $items;
    protected $sidebar;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('templates');
        ToolbarHelper::title('Email Templates');
        ToolbarHelper::editList('template.edit');
        $this->sidebar = Sidebar::render();
        parent::display($tpl);
    }
}