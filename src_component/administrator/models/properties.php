<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class BookingmanagerModelProperties extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'article_title', 'article.title',
                'max_guests', 'a.max_guests',
                'complex_name', 'complex.name',
                'supplier_name', 'supplier.name',
                'published', 'a.published'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'article.title', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.*, article.title AS article_title, complex.name AS complex_name, supplier.name AS supplier_name'
            )
        )
            ->from($db->quoteName('#__bookingmanager_properties', 'a'))
            ->join('LEFT', $db->quoteName('#__content', 'article') . ' ON a.article_id = article.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_complex_property_map', 'map') . ' ON a.id = map.property_id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_complexes', 'complex') . ' ON map.complex_id = complex.id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_property_map', 'supplier_map') . ' ON a.article_id = supplier_map.property_id')
            ->join('LEFT', $db->quoteName('#__bookingmanager_suppliers', 'supplier') . ' ON supplier_map.supplier_id = supplier.id');

        // Filter by search in title or supplier name
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(article.title LIKE ' . $like . ' OR supplier.name LIKE ' . $like . ')');
        }

        // Add sorting
        $orderCol = $this->state->get('list.ordering', 'article.title');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    public function getItems()
    {
        // Get the raw list from the parent, which will have duplicates
        $items = parent::getItems();

        if (empty($items)) {
            return [];
        }

        $processedItems = [];
        $complexNamesByProperty = [];

        // First pass: group complex names by property ID
        foreach ($items as $item) {
            if (!isset($complexNamesByProperty[$item->id])) {
                $complexNamesByProperty[$item->id] = [];
            }
            if (!empty($item->complex_name)) {
                // Avoid adding the same complex name twice if there are other joins causing duplicates
                if (!in_array($item->complex_name, $complexNamesByProperty[$item->id])) {
                    $complexNamesByProperty[$item->id][] = $item->complex_name;
                }
            }
        }

        // Second pass: create the final, de-duplicated list
        foreach ($items as $item) {
            if (!isset($processedItems[$item->id])) {
                // If we haven't added this property yet, add it now

                // Set the concatenated complex names
                if (isset($complexNamesByProperty[$item->id])) {
                    $item->complex_name = implode(', ', $complexNamesByProperty[$item->id]);
                }

                $processedItems[$item->id] = $item;
            }
        }

        // Return the de-duplicated list, re-indexed from 0
        return array_values($processedItems);
    }
}
