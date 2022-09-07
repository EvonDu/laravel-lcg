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
     * Table
     *
     * @var string
     */
    protected $table = '__MODEL_TABLE__';

    /**
     * Fields
     *
     * @return string[]
     */
    public static function fields() {
        return [
            /** MODEL_FIELDS */
        ];
    }

    /**
     * Labels
     *
     * @return string[]
     */
    public static function labels(){
        return [
            /** MODEL_LABELS */
        ];
    }

    /**
     * Validate
     *
     * @param Request $request
     * @return array
     */
    public static function validate(Request $request){
        return $request->validate([
            /** MODEL_RULES */
        ]);
    }

    /** MODEL_FK_RELEVANCE */
}
