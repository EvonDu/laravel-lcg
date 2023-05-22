<?php

namespace App\Expand\Lcg\Rbac\Models;

use App\Expand\Lcg\Eloquent\ModelExpand;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
/* Role
 * @property integer $id ID
 * @property string $name 角色
 * @property array $permissions 权限
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间

 * @OA\Schema(schema="Role", description="")
 * @OA\Property(property="id", type="integer", description="ID")
 * @OA\Property(property="name", type="string", description="角色")
 * @OA\Property(property="permissions", type="json", description="权限")
 * @OA\Property(property="created_at", type="timestamp", description="创建时间")
 * @OA\Property(property="updated_at", type="timestamp", description="更新时间")
 */
class Role extends Model
{
    use HasFactory;
    use ModelExpand;

    /**
     * Table
     *
     * @var string
     */
    protected $table = 'rbac_role';

    /**
     * Casts
     *
     * @var string[]
     */
    protected $casts = ['permissions' => 'array'];

    /**
     * Default
     *
     * @var array[]
     */
    protected $attributes = [
        'permissions' => "[]",
    ];

    /**
     * Fields
     *
     * @return string[]
     */
    public static function fields() {
        return [
            'id' => 'integer',
            'name' => 'string',
            'permissions' => 'array',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    /**
     * Labels
     *
     * @return string[]
     */
    public static function labels(){
        return [
            'id' => 'ID',
            'name' => '角色',
            'permissions' => '权限',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
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
            'name' => 'string|required|between:0,128',
            'permissions' => 'array',
        ]);
    }

    /**
     * Property - roleUsers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function roleUsers(){
        return $this->hasManyThrough(
            User::class,
            RoleUser::class,
            "role_id",
            "id",
            "id",
            "user_id",
        );
    }
}
