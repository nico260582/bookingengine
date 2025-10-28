<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Layout\FileLayout;

class BookingmanagerViewMainregions extends BaseHtmlView
{
    public function display($tpl = null)
    {
        ToolbarHelper::title('Main Regions');

        $layout = new FileLayout('sidebar');
        $this->sidebar = $layout->render();

        parent::display($tpl);
    }
}
