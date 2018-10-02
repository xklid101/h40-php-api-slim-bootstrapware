<?php
/**
 * @md
 * Helper database functions
 *
 */

namespace Xklid101\H40\PhpApiSlimBoostrapware;

class Helper
{
    /**
     * helper function to get sql WHERE part and parameters
     *     - all prepared for \PDO statement with named parameters
     * 
     * @param  string $dbcolname database column name to search in (with table prefixed if needed - e.g. table."columnname")
     * @param  array $values    array of values to search
     * @param array  &$wh       actual $wh array prepared for query (will be modified by refference)
     * @param array  &$params   actual $params array prepared for query (will be modified by refference)
     * @return void            nothing returned as all changes are made by refference
     */
    public static function setWhereIn(string $dbcolname, array $values, array &$wh, array &$params) {
        $ins = [];
        $idx = str_replace('"', '', str_replace('.', '', $dbcolname));
        $isNull = false;
        foreach ($values as $key => $value) {
            if($value && $value !== 'null') {
                $ins[] = ":$idx$key";
                $params["$idx$key"] = $value;
            }
            elseif(!$value || strtolower($value) === "null") {
                $isNull = true;
            }
        }
        if($isNull)  {
            $subWh = [
                $dbcolname . ' IS NULL'
            ];
            if($ins)
                $subWh[] = $dbcolname . ' IN (' . implode(',', $ins) . ')';

            $wh[] = '(' . implode(' OR ', $subWh) . ')';
        }
        else
            $wh[] = $dbcolname . ' IN (' . implode(',', $ins) . ')';
    }

    /**
     * helper function to get sql HAVING part and parameters for JSON array groupping
     *     - all prepared for \PDO statement with named parameters
     * 
     * @param  string $expression database expression with array_agg function to include in having
     *                            (columna names aliases are not allowed to use by sql standards)
     * @param string $typecast   postgresql array type cast (e.g. citext[], int[] ...)
     * @param  array $values    array of values to search 
     * @param array  &$hv       actual $hv array prepared for query (will be modified by refference)
     * @param array  &$params   actual $params array prepared for query (will be modified by refference)
     * @return void            nothing returned as all changes are made by refference
     */
    public static function setHavingArrayIn(string $expression, string $typecast, array $values, array &$hv, array &$params) {
        $ins = [];
        $idx = md5($expression);
        foreach ($values as $key => $value) {
            $ins[] = ":$idx$key";
            $params["$idx$key"] = $value;
        }
        $hv[] = $expression . '::' . $typecast . ' && ARRAY[' . implode(',', $ins) . ']::' . $typecast;
    }
}
