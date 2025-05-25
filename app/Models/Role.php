<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;

class Role extends Model
{
    use HasFactory;
    public function user(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions(){
        return $this->belongsToMany(permission::class)->select('permission_id','title');
    }
    public function check($param){
        $permission = permission::query()->where('title' , '=' , $param)->first();

        return PermissionRole::query()
            ->where('permission_id' , '=' , $permission->id)
            ->where('role_id' , '=' , $this->id)
            ->exists();
    }

    public function permissionRoles(): HasMany
    {
        return $this->hasMany(PermissionRole::class, 'role_id');
    }

}
