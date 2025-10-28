<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\LayoutHelper;

class BookingmanagerViewTest extends HtmlView
{
    public function display($tpl = null)
    {
        ToolbarHelper::title('Test View');

        if (LayoutHelper::getLayoutFile('sidebar')) {
            $this->sidebar = LayoutHelper::render('sidebar');
        }

        parent::display($tpl);
    }
}
