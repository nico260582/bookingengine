<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\LayoutHelper;

class BookingmanagerViewTemplates extends HtmlView
{
    protected $items;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        BookingmanagerHelper::addSubmenu('templates');
        $this->addToolbar();

        if (LayoutHelper::getLayoutFile('sidebar')) {
            $this->sidebar = LayoutHelper::render('sidebar');
        }

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Email Templates');
        ToolbarHelper::editList('template.edit');
    }
}
