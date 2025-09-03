<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class BookingmanagerModelTerms extends BaseDatabaseModel
{
    public function getTerms()
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $bookingId = $input->getInt('id', 0);

        if (!$bookingId) {
            return null;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('log.terms_content', 'req.booking_ref', 'req.property_name')))
            ->from($db->quoteName('#__bookingmanager_terms_log', 'log'))
            ->join('LEFT', $db->quoteName('#__booking_requests', 'req') . ' ON ' . $db->quoteName('log.booking_id') . ' = ' . $db->quoteName('req.id'))
            ->where($db->quoteName('log.booking_id') . ' = ' . (int)$bookingId);

        $db->setQuery($query);

        return $db->loadObject();
    }
}
