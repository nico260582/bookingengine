<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class BookingmanagerModelSearchresults extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'main_region', 'guests', 'dates'
            );
        }

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $app = Factory::getApplication();

        $query->select('p.id, p.article_id, p.max_guests, p.main_region, p.sub_region, c.title')
            ->from($db->quoteName('#__bookingmanager_properties', 'p'))
            ->join('LEFT', $db->quoteName('#__content', 'c') . ' ON ' . $db->quoteName('p.article_id') . ' = ' . $db->quoteName('c.id'));

        // Get filter values
        $mainRegion = $app->input->get('main_region', '', 'string');
        $guests = $app->input->get('guests', 0, 'int');
        $dates = $app->input->get('dates', '', 'string');

        if (!empty($mainRegion)) {
            $query->where($db->quoteName('p.main_region') . ' = ' . $db->quote($mainRegion));
        }

        if ($guests > 0) {
            $query->where($db->quoteName('p.max_guests') . ' >= ' . (int)$guests);
        }

        if (!empty($dates)) {
            $dateParts = explode(' - ', $dates);
            if (count($dateParts) == 2) {
                $startDate = $db->quote($dateParts[0]);
                $endDate = $db->quote($dateParts[1]);

                $subQuery = $db->getQuery(true);
                $subQuery->select('1')
                    ->from($db->quoteName('#__booking_requests', 'br'))
                    ->where($db->quoteName('br.article_id') . ' = ' . $db->quoteName('p.article_id'))
                    ->where($db->quoteName('br.status') . ' = ' . $db->quote('confirmed'))
                    ->where(
                        '(' . $db->quoteName('br.start_date') . ' < ' . $endDate . ' AND ' .
                        $db->quoteName('br.end_date') . ' > ' . $startDate . ')'
                    );

                $query->where('NOT EXISTS (' . $subQuery . ')');
            }
        }

        return $query;
    }
}
