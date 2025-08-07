<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class BookingmanagerModelDiagnostic extends BaseDatabaseModel
{
    public function getMailSettings()
    {
        $config = Factory::getConfig();
        $settings = new \stdClass();
        $settings->mailer = $config->get('mailer');
        $settings->mailfrom = $config->get('mailfrom');
        $settings->fromname = $config->get('fromname');
        return $settings;
    }
}