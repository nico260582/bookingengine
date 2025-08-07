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

    public function getSchemaHealthChecks()
    {
        $checks = [];
        $requiredSchema = [
            '#__bookingmanager_rates' => [
                'property_id',
                'season_name',
                'base_rate',
                'override_admin_commission',
                'admin_commission'
            ],
            '#__bookingmanager_suppliers' => [
                'id',
                'name',
                'rules'
            ],
            // Add other tables and columns as needed
        ];

        foreach ($requiredSchema as $tableName => $columns) {
            foreach ($columns as $columnName) {
                $check = new \stdClass();
                $check->name = "Column `{$columnName}` in `{$tableName}`";
                $check->status = $this->columnExists($tableName, $columnName);
                $checks[] = $check;
            }
        }

        return $checks;
    }

    private function columnExists($tableName, $columnName)
    {
        $db = Factory::getDbo();
        $tableName = $db->replacePrefix($tableName);

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME = ' . $db->quote($tableName))
            ->where('COLUMN_NAME = ' . $db->quote($columnName));

        $db->setQuery($query);
        return (bool) $db->loadResult();
    }
}