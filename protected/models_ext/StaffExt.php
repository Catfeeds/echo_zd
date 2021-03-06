<?php 
/**
 * 球员类
 * @author steven.allen <[<email address>]>
 * @date(2017.2.12)
 */
class StaffExt extends Staff{
	/**
     * 定义关系
     */
    public function relations()
    {
        return array(
            // 'url'=>array(self::BELONGS_TO, 'UserExt', 'uid'),
            'sds'=>array(self::HAS_MANY, 'StaffDepartmentExt', 'uid'),
            'companys'=>array(self::MANY_MANY, 'CompanyExt', 'cooperate(staff,cid)'),
            'departments'=>array(self::MANY_MANY, 'DepartmentExt', 'staff_department(uid,did)'),
        );
    }
    public static $is_jls = [
        0=>'暂无',
        1=>'案场助理',
        2=>'市场',
        3=>'案场销售',
        // 4=>'案场助理',
        // 5=>'案场销售',
    ];
    public static $types = [
    '员工','主管'
    ];

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
        if(!$this->name_phone) {
            $this->name_phone = $this->name.$this->phone;
        }
        if($this->getIsNewRecord()) {
            if($this->phone&&$this->password) {
                SmsExt::sendMsg('后台新增员工',$this->phone,['name'=>$this->name,'phone'=>$this->phone,'pwd'=>$this->password."。请在微信小程序内搜索“".Yii::app()->file->sitename1."”登陆！"]);
            }
            $this->created = $this->updated = time();
        }
        else
            $this->updated = time();
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
                'condition' => "{$alias}.status=1 ",
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