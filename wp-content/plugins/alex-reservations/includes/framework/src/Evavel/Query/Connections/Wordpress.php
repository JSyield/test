<?php

namespace Evavel\Query\Connections;

use Evavel\Database\DB;

class Wordpress
{
    public function query($sql)
    {
        global $wpdb;

        if (preg_match('#UPDATE#', $sql)){
            return $wpdb->query($sql);
        }

        if (preg_match('#INSERT#', $sql)) {

            $result = $wpdb->query($sql);

            if ($result >= 1){
                $last_id = $wpdb->insert_id;
                $list_ids = [$last_id];
                while($result > 1){
                    $last_id++;
                    $list_ids[] = $last_id;
                    $result--;
                }
                return $list_ids;
            }

            return $result;
        }

        if (preg_match('#COLUMN_NAME#', $sql)){
            return $wpdb->get_col($sql);
        }

        if (preg_match('#DELETE#', $sql)){
            return $wpdb->query($sql);
        }

        return $wpdb->get_results($sql);
    }

    public function tableName($resourceTable)
    {
        global $wpdb;

        $prefix = $wpdb->base_prefix.DB::$namespace.'_';

        if ($prefix && strpos($resourceTable, $prefix) !== 0) {
            $resourceTable = $prefix.$resourceTable;
        }

        return $resourceTable;
    }
}
