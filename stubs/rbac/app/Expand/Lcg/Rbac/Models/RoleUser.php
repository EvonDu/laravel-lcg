<?php

namespace App\Expand\Lcg\Rbac\Models;

use App\Expand\Lcg\Eloquent\ModelExpand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
/* RoleUser
 * @property integer $id ID
 * @property integer $role_id 角色ID
 * @property integer $user_id 用户ID

 * @OA\Schema(schema="RoleUser", description="")
 * @OA\Property(property="id", type="integer", description="ID")
 * @OA\Property(property="role_id", type="integer", description="角色ID")
 * @OA\Property(property="user_id", type="integer", description="用户ID")
 */
class RoleUser extends Model
{
    use HasFactory;
    use ModelExpand;

    /**
     * Table
     *
     * @var string
     */
    protected $table = 'rbac_role_user';

    /**
     * Casts
     *
     * @var string[]
     */
    protected $casts = [];

    /**
     * Timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fields
     *
     * @return string[]
     */
    public static function fields() {
        return [
            'user_id' => 'integer',
            'role_id' => 'integer',
        ];
    }

    /**
     * Labels
     *
     * @return string[]
     */
    public static function labels(){
        return [
            'user_id' => '用户ID',
            'role_id' => '角色ID',
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
            'user_id' => 'integer|required',
            'role_id' => 'integer|required',
        ]);
    }

    /**
     * Property - Role
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(){
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
