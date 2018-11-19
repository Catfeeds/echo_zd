<?php 
/**
 * 球员类
 * @author steven.allen <[<email address>]>
 * @date(2017.2.12)
 */
class SubExt extends Sub{
    public static $status = [
        0=>'报备',
        1=>'到访',
        2=>'认筹',
        3=>'成交',
        4=>'签约',
        5=>'结佣',
        9=>'跟进',
    ];
	/**
     * 定义关系
     */
    public function relations()
    {
        return array(
            'user'=>array(self::BELONGS_TO, 'UserExt', 'uid'),
            'sale_user'=>array(self::BELONGS_TO, 'StaffExt', 'sale_uid'),
            'market_user'=>array(self::BELONGS_TO, 'StaffExt', 'market_uid'),
            'an_user'=>array(self::BELONGS_TO, 'StaffExt', 'an_uid'),
            'help_user'=>array(self::BELONGS_TO, 'StaffExt', 'help_uid'),
            'plot'=>array(self::BELONGS_TO, 'PlotExt', 'hid'),
            'pros'=>array(self::HAS_MANY, 'SubProExt', 'sid','order'=>'pros.created desc'),
            'imgs'=>array(self::HAS_MANY, 'SubImgExt', 'sid','order'=>'imgs.created asc'),
            'company'=>array(self::BELONGS_TO, 'CompanyExt', 'cid'),
        );
    }

    /**
     * @return array 验证规则
     */
    public function rules() {
        $rules = parent::rules();
        return array_merge($rules, array(
            array('uid,hid,time,name,phone', 'required'),
        ));
    }

    /**
     * 返回指定AR类的静态模型
     * @param string $className AR类的类名
     * @return CActiveRecord Admin静态模型
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function afterFind() {
        parent::afterFind();
        // if(!$this->image){
        //     $this->image = SiteExt::getAttr('qjpz','productNoPic');
        // }
    }

    public function beforeValidate() {
        if($this->hid && !$this->plot_title) {
            $this->plot_title = $this->plot->title;
        }
        if($this->an_uid && !$this->an_phone) {
            $this->an_phone = $this->an_user->phone;
        }
        if($this->sale_uid && !$this->sale_phone) {
            $this->sale_phone = $this->sale_user->phone;
        }
        if($this->market_uid && !$this->market_phone) {
            $this->market_phone = $this->market_user->phone;
        }
        if($this->uid && !$this->fx_phone) {
            $this->fx_phone = $this->user->phone;
        }
        if($this->uid && !$this->cid) {
            $this->cid = $this->user->cid;
        }
        if(!$this->code) {
            // 新增6位客户码 不重复
            $code = 700000+rand(0,99999);
            // var_dump($code);exit;
            while (SubExt::model()->find('code='.$code)) {
                $code = 700000+rand(0,99999);
            }
            $this->code = $code;
        }
        if($this->cid&&!$this->company_name) {
            $this->company_name = $this->company->name;
        }
        if(strstr($this->qy_time,'-')) {
            $this->qy_time = strtotime($this->qy_time);
        }
        if($this->getIsNewRecord()) {
            // 如果是员工的话 就is_zf=1
            if($this->fx_phone && $staff = StaffExt::model()->find("phone='".$this->fx_phone."'")) {
                $this->is_zf = 1;
            }
            // $res = Yii::app()->controller->sendNotice(($this->plot?$this->plot->title:'').'有新的报备，请登陆后台审核','',1);
            $this->created = $this->updated = time();
        }
        else {
            // if($this->status!=Yii::app()->db->createCommand("select status from sub where id=".$this->id)->queryScalar()) {
            //     $user = $this->user;
            //     $user->qf_uid && Yii::app()->controller->sendNotice('经纪人'.$user->name.'您好，尾号为：'.substr($this->phone,-4, 4).'的客户，已被'.($this->plot?$this->plot->title:'').'案场助理确认'.SubExt::$status[$this->status].'。',$user->qf_uid);
            //     SmsExt::sendMsg('报备状态变更',$user->phone,['phone'=>substr($this->phone,-4, 4),'pro'=>$this->plot->title,'sta'=>SubExt::$status[$this->status]]);
            // }
            // if($this->status==2) {
            //     $company = CompanyExt::model()->findByPk($this->plot->company_id);
            //     $managers = $company->managers;
            //     if($managers) {
            //         $uidss = '';
            //         foreach ($managers as $key => $value) {
            //             $value->qf_uid && $uidss .= $value->qf_uid.',';
            //         }
            //         $uidss = trim($uidss,',');
            //         Yii::app()->controller->sendNotice('恭喜您，您的项目'.($this->plot?$this->plot->title:'').'有新的认筹。',$uidss);
            //     }
            //     Yii::app()->controller->sendNotice(($this->plot?$this->plot->title:'').'有新的认筹，请登陆后台查看','',1);
            // }
            // if($this->status==3) {
            //     $company = CompanyExt::model()->findByPk($this->plot->company_id);
            //     $managers = $company->managers;
            //     if($managers) {
            //         $uidss = '';
            //         foreach ($managers as $key => $value) {
            //             $value->qf_uid && $uidss .= $value->qf_uid.',';
            //         }
            //         $uidss = trim($uidss,',');
            //         Yii::app()->controller->sendNotice('恭喜您，您的项目'.($this->plot?$this->plot->title:'').'有新的认购。',$uidss);
            //     }
            //     Yii::app()->controller->sendNotice(($this->plot?$this->plot->title:'').'有新的认购，请登陆后台查看','',1);
            // }
            $this->updated = time();
        }
        
            
        return parent::beforeValidate();
    }

    public function afterSave()
    {
        if($this->getIsNewRecord()) {
            // 如果有help_uid 说明辅助报备 则发短信给分销
            if($user = $this->user)
                SmsExt::sendMsg('发送客户码链接',$this->fx_phone,['name'=>$user->name,'khm'=>$this->getShort($this->id).",",'kh'=>$this->name.$this->phone]);
        }
        parent::afterSave();
    }

    /**
     * 命名范围
     * @return array
     */
    public function scopes()
    {
        $alias = $this->getTableAlias();
        return array(
            'sorted' => array(
                'order' => "{$alias}.sort desc,{$alias}.updated desc",
            ),
            'normal' => array(
                'condition' => "{$alias}.status=1 and {$alias}.deleted=0",
                'order'=>"{$alias}.sort desc,{$alias}.updated desc",
            ),
            'undeleted' => array(
                'condition' => "{$alias}.deleted=0",
                // 'order'=>"{$alias}.sort desc,{$alias}.updated desc",
            ),
        );
    }

    /**
     * 绑定行为类
     */
    public function behaviors() {
        return array(
            'CacheBehavior' => array(
                'class' => 'application.behaviors.CacheBehavior',
                'cacheExp' => 0, //This is optional and the default is 0 (0 means never expire)
                'modelName' => __CLASS__, //This is optional as it will assume current model
            ),
            'BaseBehavior'=>'application.behaviors.BaseBehavior',
        );
    }

    public function getShort($value='id')
    {
        if(isset(Yii::app()->file->url) && $url = Yii::app()->file->url) {
            $url = $url."subwap/index.html?id=$value";
            $res = HttpHelper::get("http://suo.im/api.php?url=$url");
            return $res['content'];
        }
    }

}