<?php

namespace Lcg\Exceptions\Eloquent;

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
            $value = $request->input($field, null);
            if($value === null)
                continue;
            if($field === "id")
                $find->where($field, '=', $value);
            else
                $find->where($field, 'like', "%{$value}%");
            /*switch ($type){
                case "string":
                    $find->where($field, 'like', "%{$value}%");
                    break;
                default:
                    $find->where($field, '=', $value);
            }*/
        }

        // 排序条件
        $orderBy = $request->input('orderBy', null);
        if($orderBy !== null){
            $orderType = $request->input('orderType', 'ascending');
            if($orderType === 'descending')
                $find->orderBy($orderBy, "DESC");
            else
                $find->orderBy($orderBy, "ASC");
        }

        // 分页相关
        $paginates = [
            "size" => 20,
            "page" => $request->input('page', 1),
        ];
        $find->paginate($paginates['size']);

        // 返回查询
        return $find;
    }

    /**
     * 设置分页
     * @param Builder $search
     * @param Request $request
     * @param int $size
     * @return array
     */
    public static function paginate(Builder $search, Request $request, int $size=20){
        $paginate = $search->paginate($size);
        return [
            "size" => $size,
            "page" => $paginate->currentPage(),
            "total" => $paginate->total()
        ];
    }

    /**
     * 查询主键
     *
     * @param  mixed  $id
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public static function findOne($id){
        return self::find($id);
    }

    /**
     * 查询全部
     *
     * @param array $condition
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function findAll($condition = []){
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
        return $find->get();
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
     * @return void
     */
    public function loadParams($params){
        foreach ($params as $key=>$value){
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
