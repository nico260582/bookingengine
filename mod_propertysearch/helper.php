<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModPropertysearchHelper
{
    public static function getMainRegions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select(array($db->quoteName('id'), $db->quoteName('name')))
            ->from($db->quoteName('#__bookingmanager_main_regions'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    public static function getAvailablePropertiesByRegion($startDate, $endDate, $guests)
    {
        $db = Factory::getDbo();

        // 1. Find the article IDs of all unavailable properties for the given date range.
        $subQuery = $db->getQuery(true);
        $subQuery->select('c.id')
            ->from($db->quoteName('#__booking_requests', 'br'))
            ->join('INNER', $db->quoteName('#__content', 'c') . ' ON br.property_name = c.title')
            ->where('br.status = ' . $db->quote('Confirmed'))
            ->where('br.start_date < ' . $db->quote($endDate))
            ->where('br.end_date > ' . $db->quote($startDate));

        $unavailableArticleIds = $db->setQuery($subQuery)->loadColumn();
        // Ensure we have a value to use in the NOT IN clause, even if it's empty.
        if (empty($unavailableArticleIds)) {
            $unavailableArticleIds = [0];
        }

        // 2. Get all properties that meet the guest count and are NOT in the unavailable list.
        $query = $db->getQuery(true);
        $query->select([
                'p.main_region_id',
                'mr.name as main_region_name',
                'p.sub_region_id',
                'sr.name as sub_region_name',
                'COUNT(p.id) as property_count'
            ])
            ->from($db->quoteName('#__bookingmanager_properties', 'p'))
            ->join('INNER', $db->quoteName('#__bookingmanager_main_regions', 'mr') . ' ON p.main_region_id = mr.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_sub_regions', 'sr') . ' ON p.sub_region_id = sr.id')
            ->where('p.published = 1')
            ->where('p.max_guests >= ' . (int) $guests)
            ->where('p.article_id NOT IN (' . implode(',', $unavailableArticleIds) . ')')
            ->group('p.main_region_id, mr.name, p.sub_region_id, sr.name')
            ->order('mr.name, sr.name');

        $results = $db->setQuery($query)->loadObjectList();

        // 3. Structure the data for the frontend.
        $structuredData = [
            'main_regions' => [],
            'sub_regions' => []
        ];
        $mainRegionCounts = [];

        foreach ($results as $row) {
            // Aggregate counts for main regions
            if (!isset($mainRegionCounts[$row->main_region_id])) {
                $mainRegionCounts[$row->main_region_id] = [
                    'id' => $row->main_region_id,
                    'name' => $row->main_region_name,
                    'count' => 0
                ];
            }
            $mainRegionCounts[$row->main_region_id]['count'] += (int)$row->property_count;

            // List sub-regions with their individual counts
            if ($row->sub_region_id) {
                 $structuredData['sub_regions'][] = [
                    'id' => $row->sub_region_id,
                    'name' => $row->sub_region_name,
                    'main_region_id' => $row->main_region_id,
                    'count' => (int)$row->property_count
                ];
            }
        }

        $structuredData['main_regions'] = array_values($mainRegionCounts);

        return $structuredData;
    }
}
