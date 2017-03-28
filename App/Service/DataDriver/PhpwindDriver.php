<?php
namespace App\Service\DataDriver;
use App\Exception\LoginException;
use App\Exception\UserException;
use App\Models\PwMembercredit;
use App\Models\PwMemberdata;
use App\Models\PwMembers;
use App\Models\PwUsergroups;
use App\Service\Base\ResourceOwner;
use App\Service\Helper\Helper;

/**
 * @description 老论坛的数据获取层
 * */
class PhpwindDriver extends DataDriver
{
    /**
     * @description 获取用户基本信息的的统一接口
     * @param array $fields 传入的fields必须是pw_members的字段 ，因为和phpwind和discuz的表的结构不一样，因此需要在查询的时候可能产生连表查询
     * @return array 将返回的对象强转为数组返回到AppResource类中的resources属性，目前返回的数据中的数组中包含的字段为 :
     * array(
     * username , gender,email
     * )
     * */
    public function getUserInfo(array $fields)
    {
        return  PwMembers::getUserByUid($this->resourceOwner->getUid() , $fields )->toArray();
    }

    public function getUidByUsername($username)
    {
        return PwMembers::getUidByUsername($username);
    }

    public function getUserByUid($uid , $fields)
    {
        return PwMembers::getUserByUid($uid , $fields);
    }

    public function getMemberDataByUid($uid , $fields){
        return PwMemberdata::getCreditByUid($uid , $fields);
    }

    public function getUserByUsername($username)
    {
        return PwMembers::getUserByUsername($username);
    }
    public function validateUserPassword($user , $password)
    {
        PwMembers::validateUserPassword($user,$password);
    }
    public function passwordCompare($user , $password_md5){
        if($user['password'] != $password_md5){
            throw new LoginException("the password is wrong");
        }
    }
    public function existField(array $attr)
    {
        return PwMembers::existField($attr);

    }
    /**
     * @description 根据uid获取对应的fields的值 ，可以动态获取
     * username 为用户名
     * honor为个人签名
     * groupId为用户组的id
     * icon 为个人头像的地址
     * groupName 为用户组名
     * credit 为用户的积分值
     * money 为积分的值
     * gender为性别
     *
     * */
    public function getFieldsByUid($uid , $fields)
    {
        //获取用户的基本信息
        $user = PwMembers::getUserByUid($uid);
        $username = $user->username;
        $honor = $user->honor;
        $groupId = $user->groupid;
        $icon = $user->icon;
        //根据用户组ID获取用户组名

        if($groupId == -1){
            $groupId = $user->memberid;
            $groupInfo = PwUsergroups::getGroupByGid($user->memberid ,array('grouptitle'));
        }else{
            $groupInfo = PwUsergroups::getGroupByGid($groupId ,array('grouptitle'));
        }
		if(count($groupInfo)>=1)
			$groupName = $groupInfo->grouptitle;
        //获取用户的详细积分信息
        $userCredit = PwMemberdata::getCreditByUid($uid);

		$userCreditInfo = array(
			'postnum'	=> $userCredit['postnum'],
			'digests'	=> $userCredit['digests'],
			'rvrc'		=> $userCredit['rvrc'],
			'money'		=> $userCredit['money'],
			'credit'	=> $userCredit['credit'],
			'currency'	=> $userCredit['currency'],
			'onlinetime'=> $userCredit['onlinetime']
		);

        $userCustomCredit = PwMembercredit::getCreditByUid($uid);

		if(count($userCustomCredit)>=1){
			foreach($userCustomCredit as $custom_key => $custom_value)      //按照pw的积分逻辑将所有的积分字段拼接成数组
			{
				$userCreditInfo[$custom_key] = $custom_value;
			}
		}
        $credit = $this -> CalculateCredit($userCreditInfo);
        $money = $userCredit->money;
        $gender = $user->gender;
        return compact($fields);
    }

    /**
     * @description 注册用户进入系统
     * */
    public function registerUser($data)
    {
        try
        {
            //插入用户信息
            $membersInfo = array(
                'username'	=> $data['username'],
                'password'	=> md5($data['password']),
                'safecv'	=> '',
                'email'		=> $data['email'],
                'groupid'	=> -1,
                'memberid'	=> 9,
                'regdate'	=> time(),
                'yz'		=> $data['yz'],
                'userstatus'=> 1028,
                'newpm'		=> 0,
                'gender'    => intval($data['gender']),//(0为保密1为男2为女)
                'site'      => $data['site']
            );
            $user = PwMembers::insertData($membersInfo);
            //插入用户信息
            $memberDataInfo = array(
                'uid' => $user['uid'],
                'postnum'	=> 0,
                'lastvisit'	=> time(),
                'thisvisit'	=> time(),
                'onlineip'	=> '8.8.8.8',
            );
            PwMemberdata::insertData($memberDataInfo);
            return $user['uid'];
        }
        catch (\Exception $e )
        {
            Helper::logException($e);
            throw new UserException("register error");
        }
    }

    public function updateData($condition , $attr)
    {

        PwMembers::updateOneData($condition ,$attr);
    }
    /**
     * @description 根据pw论坛的积分进行计算积分
     * @param array
     * @return int
     * */
    private function CalculateCredit($creditdb)
    {
        $upgradeset = unserialize('a:11:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"1";s:4:"rvrc";s:5:"0.002";s:5:"money";s:4:"0.02";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";s:10:"onlinetime";s:1:"0";i:1;s:1:"3";i:2;s:3:"0.1";i:3;s:1:"2";i:4;s:5:"0.002";}');
        $credit = 0;

        foreach ($upgradeset as $key => $val) {
            if (isset($creditdb[$key]) && $val) {
                if ($key == 'rvrc') {
                    $creditdb[$key] = round($creditdb[$key]/10,1);
                } elseif ($key == 'onlinetime') {
                    $creditdb[$key] = (int)($creditdb[$key]/3600);
                }
                $credit += (int)$creditdb[$key]*$val;
            }
        }
        return (int)$credit;
    }

    //检测邮件
    public function checkEmailExist($email){
        return PwMembers::checkUserEmail( $email );
    }

}
