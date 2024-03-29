<?php

namespace App\Expand\Lcg\Eloquent;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait ModelExpand{
    /**
     * 字段列表
     * @return string[]
     */
    public static function fields() {
        return [
            "id" => "int"
        ];
    }

    /**
     * 字段标签
     * @return string[]
     */
    public static function labels() {
        return [
            "id" => "ID"
        ];
    }

    /**
     * 输入验证
     * @param Request $request
     * @return array
     */
    public static function validate(Request $request){
        return $request->validate([
            'id' => 'int',
        ], [

        ]);
    }

    /**
     * 设置查询
     * @param Request $request
     * @return Builder
     */
    public static function search(Request $request) {
        // 构建查询
        $find = self::query();

        // 查询条件
        foreach (self::fields() as $field => $type){
            // 获取数据
            $value = $request->input($field, null);
            // 判断数据
            if($value === null)
                continue;
            // 特殊处理
            if($field === "id"){
                $find->where($field, '=', $value);
                continue;
            }
            // 类型处理
            switch ($type){
                case "string":
                    $find->where($field, 'like', "%{$value}%");
                    break;
                case "array":
                    $find->whereJsonContains($field, $value);
                    break;
                default:
                    $find->where($field, '=', $value);
            }
        }

        // 排序条件
        $sort = $request->input('_sort', null);
        if($sort !== null){
            $order = $request->input('_order', 'asc');
            if(in_array($order, ["asc", "desc"]))
                $find->orderBy($sort, $order);
        }

        // 返回查询
        return $find;
    }

    /**
     * 设置分页
     * @param Builder $search
     * @param Request $request
     * @return array
     */
    public static function paginate(Builder $search, Request $request){
        $size = $request->input('_size', 20);
        $paginate = $search->paginate($size, ['*'], "_page");
        return [
            "size" => $size,
            "page" => $paginate->currentPage(),
            "total" => $paginate->total()
        ];
    }

    /**
     * 查询方法
     *
     * @param $condition
     * @return Builder
     * @throws \Exception
     */
    public static function getFindQuery($condition = []){
        $find = self::query();
        foreach ($condition as $key => $value){
            if(is_array($value)){
                if(count($value) >= 3)
                    $find->where($value[0], $value[1], $value[2]);
                else if(count($value) >= 2)
                    $find->where($value[0], '=', $value[1]);
                else
                    throw new \Exception("findALL \$condition error.");
            } else {
                $find->where($key, '=', $value);
            }
        }
        return $find;
    }

    /**
     * 查询主键
     *
     * @param  mixed  $id
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public static function findByPK($id){
        return self::find($id);
    }

    /**
     * 查询单个
     *
     * @param $condition
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|void
     * @throws \Exception
     */
    public static function findOne($condition){
        if(is_array($condition)){
            return self::getFindQuery($condition)->first();
        } else {
            return self::findByPK($condition);
        }
    }

    /**
     * 查询全部
     *
     * @param array $condition
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function findAll($condition = []){
        return self::getFindQuery($condition)->get();
    }

    /**
     * 获取字段标签
     * @param $field
     * @return mixed|string
     */
    public static function getLabel($field){
        $list = self::labels();
        if(isset($list[$field]))
            return $list[$field];
        else
            return $field;
    }

    /**
     * 加载属性值
     * @param $params
     * @param $check
     * @return void
     */
    public function loadParams($params, $check=true){
        $fields = self::fields();
        foreach ($params as $key=>$value){
            if($check && !array_key_exists($key, $fields))
                continue;
            $this->$key = $value;
        }
    }

    /**
     * 时间格式转换
     * @param DateTimeInterface $date
     * @return string
     */
    public function serializeDate(DateTimeInterface $date){
        return $date->format("Y-m-d H:i:s");
    }
}
