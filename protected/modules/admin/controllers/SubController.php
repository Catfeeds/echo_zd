<?php
/**
 * 快速报备控制器
 */
class SubController extends AdminController{
	
	public $cates = [];

	public $cates1 = [];

	public $controllerName = '';

	public $modelName = 'SubExt';

	public function init()
	{
		parent::init();
		$this->controllerName = '快速报备';
		// $this->cates = CHtml::listData(LeagueExt::model()->normal()->findAll(),'id','name');
		// $this->cates1 = CHtml::listData(TeamExt::model()->normal()->findAll(),'id','name');
	}
	public function actionList($type='title',$value='',$time_type='created',$time='',$cate='')
	{
		$modelName = $this->modelName;
		$criteria = new CDbCriteria;
		if($value = trim($value))
            if ($type=='name') {
                $criteria->addSearchCondition('name', $value);
            } elseif ($type=='phone') {
            	$criteria->addSearchCondition('phone', $value);
            } elseif ($type=='fx_phone') {
            	$criteria->addSearchCondition('fx_phone', $value);
            } elseif ($type=='sc_phone') {
            	$criteria->addSearchCondition('market_phone', $value);
            } elseif ($type=='an_phone') {
            	$criteria->addSearchCondition('an_phone', $value);
            } elseif ($type=='sale_phone') {
            	$criteria->addSearchCondition('sale_phone', $value);
            } elseif ($type=='title') {
            	$criteria->addSearchCondition('plot_title', $value);
            } elseif ($type=='company') {
                $criteria->addSearchCondition('company_name', $value);
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
        $criteria->order = 'sort desc,updated desc';
		$infos = $modelName::model()->undeleted()->getList($criteria,20);
		$this->render('list',['cate'=>$cate,'infos'=>$infos->data,'cates'=>$this->cates,'pager'=>$infos->pagination,'type' => $type,'value' => $value,'time' => $time,'time_type' => $time_type,]);
	}

	public function actionEdit($id='')
	{
		$modelName = $this->modelName;
		$info = $id ? $modelName::model()->findByPk($id) : new $modelName;
		if(Yii::app()->request->getIsPostRequest()) {
			$info->attributes = Yii::app()->request->getPost($modelName,[]);
			$info->time =  is_numeric($info->time)?$info->time : strtotime($info->time);
			// 新增操作流水
			if(!$info->getIsNewRecord()) {
				if(Yii::app()->db->createCommand("select status from sub where id=".$info->id)->queryScalar()!=$info->status) {
					// 记录操作
					$ii = new SubProExt;
					$ii->sid = $info->id;
					$ii->staff = Yii::app()->user->id;
					$ii->status = $info->status;
					$ii->save();
				}
			}
			if($info->save()) {
				$this->setMessage('操作成功','success',['list']);
			} else {
				$this->setMessage(array_values($info->errors)[0][0],'error');
			}
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
			if($model->status!=$kw) {
				$model->status = $kw;
				if(!$model->save())
					$this->setMessage(current(current($model->getErrors())),'error');
				else {
					// 记录操作
					$ii = new SubProExt;
					$ii->sid = $id;
					$ii->staff = Yii::app()->user->id;
					$ii->status = $kw;
					$ii->save();
				}
			}
				
		}
		$this->setMessage('操作成功','success');	
	}

	public function actionImagelist($sid='')
	{
		$sub = SubExt::model()->findByPk($sid);
		$this->render('imagelist',['sub'=>$sub,'infos'=>$sub->imgs,'sid'=>$sid]);
	}
	public function actionImageedit($sid='',$id='')
	{
		$modelName = 'SubImgExt';
		$info = $id ? $modelName::model()->findByPk($id) : new $modelName;
		if(Yii::app()->request->getIsPostRequest()) {
			$info->attributes = Yii::app()->request->getPost($modelName,[]);
			$info->sid = $sid;
            if($info->getIsNewRecord()) {
                $info->uid = Yii::app()->user->id;
                $info->user_type = Yii::app()->user->user_type;
            }
			// $info->time =  is_numeric($info->time)?$info->time : strtotime($info->time);
			if($info->save()) {
				$this->setMessage('操作成功','success',['imagelist?sid='.$sid]);
			} else {
				$this->setMessage(array_values($info->errors)[0][0],'error');
			}
		} 
		$this->render('imageedit',['cates'=>$this->cates,'article'=>$info,'sid'=>$sid,]);
	}
	public function actionProlist($sid='')
	{
		$sub = SubExt::model()->findByPk($sid);
		$this->render('prolist',['sub'=>$sub,'infos'=>$sub->pros,'sid'=>$sid]);
	}
	public function actionProedit($sid='',$id='')
	{
		$modelName = 'SubProExt';
		$info = $id ? $modelName::model()->findByPk($id) : new $modelName;
		if(Yii::app()->request->getIsPostRequest()) {
			$info->attributes = Yii::app()->request->getPost($modelName,[]);
			$info->sid = $sid;
			// $info->time =  is_numeric($info->time)?$info->time : strtotime($info->time);
			if($info->save()) {
				$this->setMessage('操作成功','success',['prolist?sid='.$sid]);
			} else {
				$this->setMessage(array_values($info->errors)[0][0],'error');
			}
		} 
		$this->render('proedit',['cates'=>$this->cates,'article'=>$info,'sid'=>$sid,]);
	}

	public function actionAncount($aid='',$hid='',$time_type='created',$time='',$is_all='')
	{
		$criteria = new CDbCriteria;
		if($hid) {
			$criteria->addCondition("hid=$hid");
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
        if($aid) {
        	if($is_all) {
        		$dids = [];
        		$dids[] = $aid;
        		$childs = $this->getChild($aid);
        		$dids = array_merge($dids,$childs);
        		$cre = new CDbCriteria;
        		$cre->addInCondition("did",$dids);
        		$users = StaffDepartmentExt::model()->findAll($cre);
        		$uids = [];
        		if($users) {
        			foreach ($users as $user) {
        				!in_array($user->id,$uids) && $uids[] = $user->uid;
        			}
        		}
        		$criteria->addInCondition('sale_uid',$uids);
        	} else {
        		$users = StaffDepartmentExt::model()->findAll("did=$aid");
        		// var_dump($users);exit;
        		$uids = [];
        		if($users) {
        			foreach ($users as $user) {
        				!in_array($user->id,$uids) && $uids[] = $user->uid;
        			}
        		}
        		// var_dump($uids);exit;
        		$criteria->addInCondition('sale_uid',$uids);
        	}
        }
        $subs = SubExt::model()->findAll($criteria);
        $allws = $alldd = $allqy = $alldf = 0;
        $plotarr = [];
        if($subs) {
        	foreach ($subs as $sub) {

        		if($sub->status>=4 && $sub->status<9) {
        			$allqy++;
        			if(!isset($plotarr[$sub->plot_title]['qy'])) {
        				$plotarr[$sub->plot_title]['qy'] = 0;
        			}
        			$plotarr[$sub->plot_title]['qy']++; 
        		} elseif ($sub->status==3) {
        			$alldd++;
        			if(!isset($plotarr[$sub->plot_title]['dd'])) {
        				$plotarr[$sub->plot_title]['dd'] = 0;
        			}
        			$plotarr[$sub->plot_title]['dd']++; 
        		} elseif ($sub->status==1) {
                    $alldf++;
                    if(!isset($plotarr[$sub->plot_title]['df'])) {
                        $plotarr[$sub->plot_title]['df'] = 0;
                    }
                    $plotarr[$sub->plot_title]['df']++; 
                } else {
        			$allws++;
        			if(!isset($plotarr[$sub->plot_title]['ws'])) {
        				$plotarr[$sub->plot_title]['ws'] = 0;
        			}
        			$plotarr[$sub->plot_title]['ws']++; 
        		}
        		// $plotarr[] = 
        	}
        }
        // var_dump($plotarr);exit;
        $this->render("ancount",['time' => $time,'time_type' => $time_type,'hid'=>$hid,'aid'=>$aid,'plotarr'=>$plotarr,'allws'=>$allws,'alldd'=>$alldd,'allqy'=>$allqy,'alldf'=>$alldf,'is_all'=>$is_all,'pt'=>'案场数据统计']);
	}
	public function actionSccount($aid='',$hid='',$time_type='created',$time='',$is_all='')
	{
		$criteria = new CDbCriteria;
		if($hid) {
			$criteria->addCondition("hid=$hid");
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
        if($aid) {
        	if($is_all) {
        		$dids = [];
        		$dids[] = $aid;
        		$childs = $this->getChild($aid);
        		$dids = array_merge($dids,$childs);
        		$cre = new CDbCriteria;
        		$cre->addInCondition("did",$dids);
        		$users = StaffDepartmentExt::model()->findAll($cre);
        		$uids = [];
        		if($users) {
        			foreach ($users as $user) {
        				!in_array($user->id,$uids) && $uids[] = $user->uid;
        			}
        		}
        		$criteria->addInCondition('market_uid',$uids);
        	} else {
        		$users = StaffDepartmentExt::model()->findAll("did=$aid");
        		// var_dump($users);exit;
        		$uids = [];
        		if($users) {
        			foreach ($users as $user) {
        				!in_array($user->id,$uids) && $uids[] = $user->uid;
        			}
        		}
        		// var_dump($uids);exit;
        		$criteria->addInCondition('market_uid',$uids);
        	}
        }
        $subs = SubExt::model()->findAll($criteria);
        $allws = $alldd = $allqy = $alldf = 0;
        $plotarr = [];
        if($subs) {
        	foreach ($subs as $sub) {

        		if($sub->status>=4 && $sub->status<9) {
        			$allqy++;
        			if(!isset($plotarr[$sub->plot_title]['qy'])) {
        				$plotarr[$sub->plot_title]['qy'] = 0;
        			}
        			$plotarr[$sub->plot_title]['qy']++; 
        		} elseif ($sub->status==3) {
        			$alldd++;
        			if(!isset($plotarr[$sub->plot_title]['dd'])) {
        				$plotarr[$sub->plot_title]['dd'] = 0;
        			}
        			$plotarr[$sub->plot_title]['dd']++; 
        		} elseif ($sub->status==1) {
                    $alldf++;
                    if(!isset($plotarr[$sub->plot_title]['df'])) {
                        $plotarr[$sub->plot_title]['df'] = 0;
                    }
                    $plotarr[$sub->plot_title]['df']++; 
                } else {
        			$allws++;
        			if(!isset($plotarr[$sub->plot_title]['ws'])) {
        				$plotarr[$sub->plot_title]['ws'] = 0;
        			}
        			$plotarr[$sub->plot_title]['ws']++; 
        		}
        		// $plotarr[] = 
        	}
        }
        // var_dump($plotarr);exit;
        $this->render("ancount",['time' => $time,'time_type' => $time_type,'hid'=>$hid,'aid'=>$aid,'plotarr'=>$plotarr,'allws'=>$allws,'alldd'=>$alldd,'allqy'=>$allqy,'alldf'=>$alldf,'is_all'=>$is_all,'pt'=>'市场数据统计']);
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