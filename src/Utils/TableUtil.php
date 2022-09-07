<?php

namespace Lcg\Utils;

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
class TableUtil {
    /**
     * 成员属性
     */
    public $table;
    public $primary_key;
    public $fields = [];

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
        $fieldsData = DB::select("SHOW FULL COLUMNS FROM $table");
        foreach ($fieldsData as $fieldData){
            //构建字段模型
            $fieldModel = new TableFieldUtil($fieldData);
            //判断是否为主键
            if($fieldModel->isPk)
                $this->primary_key = $fieldModel;
            //添加到字段列表
            $this->fields[] = $fieldModel;
        }
    }
}
