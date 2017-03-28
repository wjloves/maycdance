<?php

namespace App\Service\Admin\AdminUser;

use App\Models\AdminUser;
use Core\Service;

class AdminUserService extends Service
{


    /**
     * @param string $uid
     * @return bool
     */
    public function getUserByUid($uid='')
    {
        if(empty($uid)){
            return false;
        }
       // $msg = AdminUser::where('admin_id',$uid)->where('dml_flag','!=',3)->get();
        $msg = AdminUser::find($uid);
        return $msg;
    }


}