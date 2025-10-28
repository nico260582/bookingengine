<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

class BookingmanagerViewTest extends HtmlView
{
    public function display($tpl = null)
    {
        ToolbarHelper::title('Test View');

        $layout = new FileLayout('sidebar');
        $this->sidebar = $layout->render();

        parent::display($tpl);
    }
}
