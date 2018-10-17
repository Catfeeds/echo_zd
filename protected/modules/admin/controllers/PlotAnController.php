<?php
/**
 * 项目案场控制器
 */
class PlotAnController extends AdminController{
	
	public $cates = [];

	public $cates1 = [];

	public $controllerName = '';

	public $modelName = 'PlotAnExt';

	public function init()
	{
		parent::init();
		$this->controllerName = '项目案场';
		// $this->cates = CHtml::listData(LeagueExt::model()->normal()->findAll(),'id','name');
		// $this->cates1 = CHtml::listData(TeamExt::model()->normal()->findAll(),'id','name');
	}
	public function actionList($type='title',$value='',$time_type='created',$time='',$cate='',$expire='',$hid='')
	{
		$modelName = $this->modelName;
		$criteria = new CDbCriteria;
		$criteria->addCondition('type<3');
		if($value = trim($value))
            if ($type=='title') {
            	$criter = new CDbCriteria;
            	$criter->addSearchCondition('title',$value);
            	$plotres = PlotExt::model()->findAll($criter);
            	$ids  = [];
            	if($plotres) {
            		foreach ($plotres as $pr) {
            			$ids[] = $pr->id;
            		}
            		$criteria->addInCondition('hid',$ids);
            	}
            } elseif ($type=='phone') {
            	$criter = new CDbCriteria;
            	$criter->addSearchCondition('phone',$value);
            	$plotres = StaffExt::model()->findAll($criter);
            	$ids  = [];
            	if($plotres) {
            		foreach ($plotres as $pr) {
            			$ids[] = $pr->id;
            		}
            		$criteria->addInCondition('uid',$ids);
            	}
            }
        //添加时间、刷新时间筛选
        if($time_type!='' && $time!='')
        {
            list($beginTime, $endTime) = explode('-', $time);
            $beginTime = (int)strtotime(trim($beginTime));
            $endTime = (int)strtotime(trim($endTime));
            $criteria->addCondition("{$time_type}>=:beginTime");
            $criteria->addCondition("{$time_type}<:endTime");
            $criteria->params[':beginTime'] = TimeTools::getDayBeginTime($beginTime);
            $criteria->params[':endTime'] = TimeTools::getDayEndTime($endTime);

        }
		if($cate) {
			$criteria->addCondition('status=:cid');
			$criteria->params[':cid'] = $cate;
		}
		if($hid) {
			$criteria->addCondition('hid=:hid');
			$criteria->params[':hid'] = $hid;
		}
		if($expire) {
			$criteria->addCondition('type=:expire');
			$criteria->params[':expire'] = $expire;
		}
		$criteria->order = 'updated desc';
		$infos = $modelName::model()->getList($criteria,20);
		$this->render('list',['cate'=>$cate,'infos'=>$infos->data,'cates'=>$this->cates,'pager'=>$infos->pagination,'type' => $type,'value' => $value,'time' => $time,'time_type' => $time_type,'expire'=>$expire,'z'=>0]);
	}
	public function actionZlist($type='title',$value='',$time_type='created',$time='',$cate='',$expire='',$hid='')
	{
		$modelName = $this->modelName;
		$criteria = new CDbCriteria;
		$criteria->addCondition('type>=3');
		if($value = trim($value))
            if ($type=='title') {
            	$criter = new CDbCriteria;
            	$criter->addSearchCondition('title',$value);
            	$plotres = PlotExt::model()->findAll($criter);
            	$ids  = [];
            	if($plotres) {
            		foreach ($plotres as $pr) {
            			$ids[] = $pr->id;
            		}
            		$criteria->addInCondition('hid',$ids);
            	}
            } elseif ($type=='phone') {
            	$criter = new CDbCriteria;
            	$criter->addSearchCondition('phone',$value);
            	$plotres = StaffExt::model()->findAll($criter);
            	$ids  = [];
            	if($plotres) {
            		foreach ($plotres as $pr) {
            			$ids[] = $pr->id;
            		}
            		$criteria->addInCondition('uid',$ids);
            	}
            }
        //添加时间、刷新时间筛选
        if($time_type!='' && $time!='')
        {
            list($beginTime, $endTime) = explode('-', $time);
            $beginTime = (int)strtotime(trim($beginTime));
            $endTime = (int)strtotime(trim($endTime));
            $criteria->addCondition("{$time_type}>=:beginTime");
            $criteria->addCondition("{$time_type}<:endTime");
            $criteria->params[':beginTime'] = TimeTools::getDayBeginTime($beginTime);
            $criteria->params[':endTime'] = TimeTools::getDayEndTime($endTime);

        }
		if($cate) {
			$criteria->addCondition('status=:cid');
			$criteria->params[':cid'] = $cate;
		}
		if($hid) {
			$criteria->addCondition('hid=:hid');
			$criteria->params[':hid'] = $hid;
		}
		if($expire) {
			$criteria->addCondition('type=:expire');
			$criteria->params[':expire'] = $expire;
		}
		$criteria->order = 'updated desc';
		$infos = $modelName::model()->getList($criteria,20);
		$this->render('list',['cate'=>$cate,'infos'=>$infos->data,'cates'=>$this->cates,'pager'=>$infos->pagination,'type' => $type,'value' => $value,'time' => $time,'time_type' => $time_type,'expire'=>$expire,'z'=>1]);
	}

	public function actionEdit($id='',$hid='',$z='')
	{
		$modelName = $this->modelName;
		$info = $id ? $modelName::model()->findByPk($id) : new $modelName;
		if(Yii::app()->request->getIsPostRequest()) {
			if($did = Yii::app()->request->getPost('did',[])) {
				$uidtmp = StaffDepartmentExt::model()->findAll("did=$did");
				if($uidtmp) {
					foreach ($uidtmp as $up) {
						$obj = new $modelName;
						$obj->attributes = Yii::app()->request->getPost($modelName,[]);
						$obj->uid = $up->uid;
						$obj->save();
					}
				}
				$this->setMessage('操作成功','success',[$z?'zlist':'list']);
			} else {
				$info->attributes = Yii::app()->request->getPost($modelName,[]);
				// $info->uid = $uid;
				// $info->time =  is_numeric($info->time)?$info->time : strtotime($info->time);
				if($info->save()) {
					$this->setMessage('操作成功','success',['list']);
				} else {
					$this->setMessage(array_values($info->errors)[0][0],'error');
				}
			}
				
		} 
		$this->render('edit',['cates'=>$this->cates,'article'=>$info,'cates1'=>$this->cates1,'userphone'=>isset($info->staff->phone)?$info->staff->phone:'','hid'=>$hid,'z'=>$z]);
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
}