<?php
/**
 * 员工控制器
 */
class StaffController extends AdminController{
	
	public $cates = [];

	public $cates1 = [];

	public $controllerName = '';

	public $modelName = 'StaffExt';

	public function init()
	{
		parent::init();
		$this->controllerName = '员工';
		// $this->cates = CHtml::listData(LeagueExt::model()->normal()->findAll(),'id','name');
		// $this->cates1 = CHtml::listData(TeamExt::model()->normal()->findAll(),'id','name');
	}
	public function actionList($type='title',$value='',$time_type='created',$time='',$cate='',$did='')
	{
		$modelName = $this->modelName;
		$criteria = new CDbCriteria;
        if($did) {
            $ids = [];
            $ress = StaffDepartmentExt::model()->findAll("did=$did");
            if($ress) {
                foreach ($ress as $res) {
                    $ids[] = $res->uid;
                }
            }
            $criteria->addInCondition('t.id',$ids);
        }
		if($value = trim($value))
            if ($type=='title') {
                $criteria->addSearchCondition('t.name', $value);
            } elseif ($type=='phone') {
                $criteria->addSearchCondition('t.phone', $value);
            }
        //添加时间、刷新时间筛选
        if($time_type!='' && $time!='')
        {
            list($beginTime, $endTime) = explode('-', $time);
            $beginTime = (int)strtotime(trim($beginTime));
            $endTime = (int)strtotime(trim($endTime));
            $criteria->addCondition("t.{$time_type}>=:beginTime");
            $criteria->addCondition("t.{$time_type}<:endTime");
            $criteria->params[':beginTime'] = TimeTools::getDayBeginTime($beginTime);
            $criteria->params[':endTime'] = TimeTools::getDayEndTime($endTime);

        }
		if(is_numeric($cate)) {
			$criteria->addCondition('t.is_jl=:cid');
			$criteria->params[':cid'] = $cate;
		}
        $criteria->order = 'updated desc';
        // 权限管理 主管看到当前和子部门 员工看到当前部门
        if(Yii::app()->user->id>1) {
            $uids = [];
            $uids[] = Yii::app()->user->id;
            $sds = StaffDepartmentExt::model()->findAll("uid=".Yii::app()->user->id);
            if($sds) {
                foreach ($sds as $sd) {
                    $dids = [];
                    $dids[] = $sd->did;
                    if($sd->is_major) {
                        // 找到所有子部门
                        $dids = array_merge($dids,$this->getChild($sd->did));
                    }
                    $cres = new CDbCriteria;
                    $cres->addInCondition('did',$dids);
                    $stus = StaffDepartmentExt::model()->findAll($cres);
                    if($stus) {
                        foreach ($stus as $stu) {
                            $uids[] = $stu['uid'];
                        }
                    }
                }
            }
            $criteria->addInCondition('t.id',$uids);
        }
		$infos = $modelName::model()->with('departments')->getList($criteria,20);
        $scjls = [];
        $acjls = [];
        if($infos->data) {
            foreach ($infos->data as $d) {
                if($d->is_jl==1) {
                    $scjls[] = ['name'=>$d->name,'id'=>$d->id];
                }elseif($d->is_jl==2) {
                    $acjls[] = ['name'=>$d->name,'id'=>$d->id];
                }
            }
        }

        array_unshift($scjls, ['id'=>'0','name'=>'暂无']);
        array_unshift($acjls, ['id'=>'0','name'=>'暂无']);
		$this->render('list',['cate'=>$cate,'infos'=>$infos->data,'cates'=>$this->cates,'pager'=>$infos->pagination,'type' => $type,'value' => $value,'time' => $time,'time_type' => $time_type,'scjls'=>$scjls,'acjls'=>$acjls]);
	}

	public function actionEdit($id='')
	{
		$modelName = $this->modelName;
		$info = $id ? $modelName::model()->findByPk($id) : new $modelName;
		if(Yii::app()->request->getIsPostRequest()) {
			$res = Yii::app()->request->getPost($modelName,[]);
			$res['arr'] = json_encode($res['arr']);
			$info->attributes = $res;
			// $info->time =  is_numeric($info->time)?$info->time : strtotime($info->time);
			if($info->save()) {
				$this->setMessage('操作成功','success',['list']);
			} else {
				$this->setMessage(array_values($info->errors)[0][0],'error');
			}
		} 
		if($info->arr) {
			$info->arr = json_decode($info->arr,true);
		}
		$this->render('edit',['cates'=>$this->cates,'article'=>$info,'cates1'=>$this->cates1,]);
	}

	public function actionAjaxStatus($kw='',$ids='')
	{
		if(!is_array($ids))
			if(strstr($ids,',')) {
				$ids = explode(',', $ids);
			} else {
				$ids = [$ids];
			}
		foreach ($ids as $key => $id) {
			$model = SubExt::model()->findByPk($id);
			$model->status = $kw;
			if(!$model->save())
				$this->setMessage(current(current($model->getErrors())),'error');
		}
		$this->setMessage('操作成功','success');	
	}

	public function actionRecall($msg='',$id='')
    {
        if($id) {
            $info = ReportExt::model()->findByPk($id);
            if($msg && $info && $user = $info->user) {
                $user->qf_uid && Yii::app()->controller->sendNotice($msg,$user->qf_uid);
                $info->status = 1;
                $info->save();
                $this->setMessage('操作成功');
            } else {
                $this->setMessage('操作失败');
            }
            $this->redirect('list');
            
        }
    }

    public function actionSetJl($id='')
    {
        if($id) {
            $info = StaffExt::model()->findByPk($id);
            if($info->is_jl) {
                $this->setMessage('该用户已设定为经理，请勿重复操作','error');
            }elseif($info->is_manage) {
                $this->setMessage('该用户为店长','error');
            }else{
                $info->is_jl = 1;
                $info->save();
                $this->setMessage('操作成功');
            }
        }
    }
    public function actionSetGroup($id='',$parent='')
    {
        if($id&&is_numeric($parent)) {
            $info = StaffExt::model()->findByPk($id);
            $info->parent = $parent;
            $info->save();
            $this->setMessage('操作成功');
        }
    }
    public function actionSetType($id='',$type='')
    {
        if($id&&is_numeric($type)) {
            $info = StaffExt::model()->findByPk($id);
            $info->is_jl = $type;
            $info->save();
            $this->setMessage('操作成功');
        }
    }
    public function actioneditPwd($id='')
    {
        if($id) {
            $info = StaffExt::model()->findByPk($id);
            if(Yii::app()->request->getIsPostRequest()) {
                $info->attributes = Yii::app()->request->getPost('StaffExt',[]);
                $info->password && $info->password = $info->password;
                // $info->cid = Yii::app()->user->cid;
                // $info->getIsNewRecord() && $info->status = 1;
                // $info->pwd = md5($info->pwd);
                if($info->save()) {
                    $this->setMessage('操作成功','success');
                    Yii::app()->user->logout();
                    $this->redirect(array('/admin'));
                } else {
                    $this->setMessage(array_values($info->errors)[0][0],'error');
                }
            } 
            $this->render('editpwd',['article'=>$info]);
        }
    }

    public function actionLeave($id='')
    {
        if($id) {
            $info = StaffExt::model()->findByPk($id);
            if($info->cid==$this->company->id) {
                StaffExt::model()->updateAll(['parent'=>0],'parent=:pa',[':pa'=>$id]);
                $info->cid = 0;
                $info->is_jl = 0;
                $info->is_manage = 0;
                $info->parent = 0;
                if($info->save()) {
                    $log = new UserLogExt;
                    $log->from = $this->company->id;
                    $log->uid = $id;
                    $log->to = 0;
                    $log->save();
                    $this->setMessage('操作成功','success');
                }
            }
        }
    }

    public function actionDlist($id='')
    {
        $info = StaffExt::model()->findByPk($id);
        $this->render('dlist',['staff'=>$info,'infos'=>$info->departments]);
    }

    public function actionDedit($id='',$uid='')
    {
        $modelName = 'StaffDepartmentExt';
        $info = $id ? $modelName::model()->findByPk($id) : new $modelName;
        if(Yii::app()->request->getIsPostRequest()) {
            $res = Yii::app()->request->getPost($modelName,[]);
            $info->attributes = $res;
            $info->uid = $uid;
            // $info->time =  is_numeric($info->time)?$info->time : strtotime($info->time);
            if($info->save()) {
                $this->setMessage('操作成功','success',['dlist?id='.$uid]);
            } else {
                $this->setMessage(array_values($info->errors)[0][0],'error');
            }
        } 
        $this->render('dedit',['article'=>$info,'uid'=>$uid]);
    }

    public function actionChangeType($id='')
    {
        $dep = StaffDepartmentExt::model()->findByPk($id);
        $dep->is_major = $dep->is_major?0:1;
        $dep->save();
        $this->setMessage('操作成功','success');
    }

    public function getChild($obj)
    {
        $ids = [];
        if($dds = DepartmentExt::model()->findAll("parent=".$obj)) {
            foreach ($dds as $key => $value) {
                $ids[] = $value->id;
                if($res = $this->getChild($value->id)) {
                    $ids = array_merge($res,$ids);
                }
            }
        }
        return $ids;
    }
}