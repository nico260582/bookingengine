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
                'main_region_id', 'sub_region_id', 'guests', 'dates'
            );
        }

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $app = Factory::getApplication();

        $query->select('p.id, p.article_id, p.max_guests, c.title, mr.name AS main_region_name, sr.name AS sub_region_name')
            ->from($db->quoteName('#__bookingmanager_properties', 'p'))
            ->join('LEFT', $db->quoteName('#__content', 'c') . ' ON ' . $db->quoteName('p.article_id') . ' = ' . $db->quoteName('c.id'))
            ->join('LEFT', $db->quoteName('#__bookingmanager_main_regions', 'mr') . ' ON ' . $db->quoteName('p.main_region_id') . ' = ' . $db->quoteName('mr.id'))
            ->join('LEFT', $db->quoteName('#__bookingmanager_sub_regions', 'sr') . ' ON ' . $db->quoteName('p.sub_region_id') . ' = ' . $db->quoteName('sr.id'));

        // Get filter values
        $main_region_id = $app->input->get('main_region_id', 0, 'int');
        $sub_region_id = $app->input->get('sub_region_id', 0, 'int');
        $guests = $app->input->get('guests', 0, 'int');
        $dates = $app->input->get('dates', '', 'string');

        if (!empty($main_region_id)) {
            $query->where($db->quoteName('p.main_region_id') . ' = ' . (int)$main_region_id);
        }

        if (!empty($sub_region_id)) {
            $query->where($db->quoteName('p.sub_region_id') . ' = ' . (int)$sub_region_id);
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
