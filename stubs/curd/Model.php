<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Lcg\Exceptions\Eloquent\ModelExpand;

/** MODEL_ANNOTATE */
class __MODEL_NAME__ extends Model
{
    use HasFactory;
    use ModelExpand;

    /**
     * 字段列表
     *
     * @return string[]
     */
    public static function fields() {
        return [
            /** MODEL_FIELDS */
        ];
    }

    /**
     * 字段标签
     *
     * @return string[]
     */
    public static function labels(){
        return [
            /** MODEL_LABELS */
        ];
    }

    /**
     * 表单验证
     *
     * @param Request $request
     * @return array
     */
    public static function validate(Request $request){
        return $request->validate([
            /** MODEL_RULES */
        ]);
    }
}
