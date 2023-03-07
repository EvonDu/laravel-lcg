<?php

namespace Lcg\Models;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * TableUtil
 *
 * 关于单复数约定说明：
 *  模型名：单数
 *  控制器名：单数
 *  路由名：复数
 *  数据库表：复数
 */
class Table {
    /**
     * 成员属性
     */
    public $table;
    public $fields;
    public $primary_key;
    public $foreign_keys;

    /**
     * 构造函数
     *
     * @param $table
     * @throws Exception
     */
    public function __construct($table){
        //保存表名
        $this->table = $table;

        //判断存在
        $exist = DB::select("show tables like '$table'");
        if(empty($exist)){
            throw new Exception("表[$table]不存在与数据库中");
        }

        //获取字段
        $fields = [];
        $fieldsData = DB::select("SHOW FULL COLUMNS FROM $table");
        foreach ($fieldsData as $fieldData){
            //构建字段模型
            $fieldModel = new TableField($fieldData);
            //判断是否为主键
            if($fieldModel->isPk)
                $this->primary_key = $fieldModel;
            //添加到字段列表
            $fields[] = $fieldModel;
        }
        $this->fields = $fields;

        //获取外键
        $foreignKeys = [];
        $foreignKeysData = DB::select("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = SCHEMA() AND (REFERENCED_TABLE_NAME IS NOT NULL) AND (TABLE_NAME = '$table' OR REFERENCED_TABLE_NAME = '$table')");
        foreach ($foreignKeysData as $foreignKeyData){
            $foreignKeys[] = [
                "type" => ($foreignKeyData->TABLE_NAME === $table) ? "one" : "many",
                "table" => $foreignKeyData->TABLE_NAME,
                "column" => $foreignKeyData->COLUMN_NAME,
                "referenced_table" => $foreignKeyData->REFERENCED_TABLE_NAME,
                "referenced_column" => $foreignKeyData->REFERENCED_COLUMN_NAME,
            ];
        }
        $this->foreign_keys = $foreignKeys;
    }
}
