<?php 
/**
 * 球员类
 * @author steven.allen <[<email address>]>
 * @date(2017.2.12)
 */
class SubProExt extends SubPro{
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
            'sub'=>array(self::BELONGS_TO, 'SubExt', 'sid'),
            'staffObj'=>array(self::BELONGS_TO, 'StaffExt', 'staff'),
        );
    }

    /**
     * @return array 验证规则
     */
    public function rules() {
        $rules = parent::rules();
        return array_merge($rules, array(
            // array('name', 'unique', 'message'=>'{attribute}已存在')
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
        if($this->getIsNewRecord()) {
            $sub = $this->sub;
            $user = $sub->user;
            $scuser = $sub->market_user;

            $plotname = $sub->plot_title;
            // $salename = $user->name.$user->phone;
            // $cumsname = $sub->name.$sub->phone;
            if($this->status==9) {
                SmsExt::sendMsg('跟进通知用户',$user->phone,['comname'=>($user->companyinfo?$user->companyinfo->name:'').$user->name,'pro'=>$plotname,'name'=>$sub->name]);
            } else {
                $staffObj = $this->staffObj;
                if($staffObj && $staffObj->is_jl)
                    SmsExt::sendMsg('客户状态变更通知',$user->phone,['comname'=>($user->companyinfo?$user->companyinfo->name:'').$user->name,'pro'=>$plotname,'name'=>$sub->name,'typename'=>StaffExt::$is_jls[$staffObj->is_jl].$staffObj->name,'usertype'=>SubProExt::$status[$this->status]]);
                if($this->status==2||$this->status==3) {
                    if($scuser) {

                        SmsExt::sendMsg('客户成交认筹通知市场',$scuser->phone,['scname'=>$scuser->name,'com'=>$sub->company_name,'fxname'=>$user->name.$user->phone,'pro'=>$plotname,'name'=>$sub->name,'anname'=>StaffExt::$is_jls[$staffObj->is_jl].$staffObj->name,'usertype'=>SubProExt::$status[$this->status]]);
                    }
                }
            }
            $this->created = $this->updated = time();
        }
        else {

            $this->updated = time();
        }
        return parent::beforeValidate();
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

}