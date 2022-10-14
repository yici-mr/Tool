<?php

namespace yctool\tool;



class Structure
{
    /**方便处理
     * @param string $classname  app/TableStructure下的类名。
     * @param array $data       操作表的数据
     * */
    public static function Get_Structure(string $classname,array $data): array
    {
        $arr = [];
        $new_obj = '\\app\\TableStructure\\'.$classname;
        $obj_arr = (array)new $new_obj;
        foreach ($data as $k => $v){
            if(in_array($k,$obj_arr)){
                $arr[array_search($k,$obj_arr)] = $v;
            }
        }
        return  $arr;
    }

}