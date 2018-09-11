<?php 
/**
 * 球员类
 * @author steven.allen <[<email address>]>
 * @date(2017.2.12)
 */
class CooperateExt extends Cooperate{
	/**
     * 定义关系
     */
    public function relations()
    {
         return array(
            'user'=>array(self::BELONGS_TO, 'UserExt', 'uid'),
            'staffObj'=>array(self::BELONGS_TO, 'StaffExt', 'staff'),
            'plot'=>array(self::BELONGS_TO, 'PlotExt', 'hid'),
            'company'=>array(self::BELONGS_TO, 'CompanyExt', 'cid'),
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
        if($this->uid && !$this->user_name && $user = $this->user) {
            $this->user_name = $user->name;
            $this->user_phone = $user->phone;
            $this->user_company = $user->companyinfo?$user->companyinfo->name:'';
        }
        $obj = CooperateExt::model()->find("hid=".$this->hid." and cid=".$this->cid);
        if($obj && $obj->staff>0 && $obj->staff!=$this->staff) {
            return $this->addError('staff','已存在其他员工绑定');
        }

        if($this->getIsNewRecord()){
            if($staffObj = $this->staffObj) {
                if($company = $this->company)
                    SmsExt::sendMsg('渠道绑定公司项目成功',$staffObj->phone,['scname'=>$staffObj->name,'com'=>$company->name,'pro'=>$this->plot->title,'code'=>$company->code]);
            }
            $this->created = $this->updated = time();
        }
        else {
            $this->updated = time();
        }
        // 分销签约禁止重复添加
        if(CooperateExt::model()->find("hid=".$this->hid." and cid=".$this->cid." and staff=".$this->staff)) {
            $this->addError('staff','请勿重复绑定');
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