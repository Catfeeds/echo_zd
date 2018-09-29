 <?php
/**
 * 后台验证登录类
 * @author tivon
 * @date 2015-04-22
 */
class AdminIdentity extends CUserIdentity
{
	/**
	 * 验证身份
	 * @return bool
	 */
	public function authenticate()
	{
		$pwdnew = SiteExt::getAttr('qjpz','sitePwd');
		!$pwdnew && $pwdnew = Yii::app()->file->password; 
		// var_dump($this->username);exit;
		//内置帐号
		if($this->username=='admin' && $pwdnew==$this->password)
		{
			$this->errorCode = self::ERROR_NONE;
			$this->setState('id',1);
			$this->setState('username','管理员');
			$this->setState('user_type',1);
			$this->setState('avatar','');
			$this->setState('is_m',1);
			return $this->errorCode;
		} else{
			if(is_numeric($this->username)) {
				$user = StaffExt::model()->normal()->find("phone='".$this->username."'");
			} else {
				$user = StaffExt::model()->normal()->find("name='".$this->username."'");
			}
			if($user){
				// var_dump($user);exit;
				if($user->password == $this->password) {
					$this->errorCode = self::ERROR_NONE;
					$this->setState('id',$user->id);
					$this->setState('cid','');
					$this->setState('username',$user->name);
					$this->setState('user_type',$user->is_jl);
					$this->setState('avatar','');
					$this->setState('is_m',0);
					return $this->errorCode;
				}
			}
		}

		$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
		return $this->errorCode;
	}

	public function getId()
	{
		return $this->getState('id');
	}

	public function getName()
	{
		return $this->getState('username');
	}

	public function getIp()
    {
        $ip = '127.0.0.1';
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
