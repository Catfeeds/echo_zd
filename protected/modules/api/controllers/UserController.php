<?php
class UserController extends ApiController{

	public function actionCheckPhone($phone='')
	{
		if(UserExt::model()->undeleted()->find("phone='$phone'")) {
			 $this->returnError('手机号已存在');
		} else {
			$this->returnSuccess('手机号可用');
		}
	}

	public function actionAddOne($phone='',$type='1')
	{
		$arr = [1=>'注册',2=>'找回密码'];
		if(!SmsExt::addOne($phone,$arr[$type])) {
			$this->returnError('操作失败');
		}
		// $this->returnSuccess('操作成功');
	}

	public function actionCheckCode($phone='',$code='')
	{
		if(!SmsExt::checkPhone($phone,$code)) {
			$this->returnError('验证码错误');
		}
	}

	public function actionCheckCompanyCode($code='')
	{
		if(!CompanyExt::model()->find("code='$code'")) {
			$this->returnError('门店码不存在');
		}
	}

	public function actionRegis()
	{
		if(Yii::app()->request->getIsPostRequest()) {
			$obj = Yii::app()->request->getPost('UserExt',[]);
			if(!$obj['phone']) {
				return $this->returnError('请绑定手机号再进行提交');
			}
			if($obj) {
				if($user = UserExt::model()->find("phone='".$obj['phone']."'")){
					;
				} else
					$user = new UserExt;
				$company = '';
				// if(Yii::app()->db->createCommand("select id from user where phone='".$obj['phone']."'")->queryScalar()) {
				// 	return $this->returnError('您已提交申请，请勿重复提交');
				// }
				if($obj['type']<3) {
					$code = $obj['companycode'];
					unset($obj['companycode']);
					if(!$code||!is_numeric($code)) {
						$this->returnError('门店码有误');
						return ;
					}
					if($obj['type'] == '1') {
						if(substr($code, 0,1)!='8') {
							$this->returnError('门店码有误');
							return ;
						}
					} elseif($obj['type'] == '2') {
						if(substr($code, 0,1)!='6') {
							$this->returnError('门店码有误');
							return ;
						}
					}
					$company = CompanyExt::getCompanyByCode($code);
					if($company) {
						$user->cid = $company->id;
					}
					$user->status = 1;
				}
				$user->attributes = $obj;
				!$user->pwd && $user->pwd = 'jjqxftv587';
				$user->pwd = md5($user->pwd);
				if(!$user->save()) {
					return $this->returnError(current(current($user->getErrors())));
				} else {
					// if($company) {
					// 	$managers = $company->managers;
					// 	if($managers) {
					// 		$uidss = '';
					// 		foreach ($managers as $key => $value) {
					// 			$value->qf_uid && $uidss .= $value->qf_uid.',';
					// 		}
					// 		$uidss = trim($uidss,',');
					// 		Yii::app()->controller->sendNotice('您好，经纪人'.$user->name.$user->phone.'通过门店码'.$code.'成功加入贵公司，请知悉。如有疑问请致电'.SiteExt::getAttr('qjpz','site_phone'),$uidss);
					// 	}
					// 	$this->frame['data'] = $company->name;
					// }
					$this->frame['data'] = $company->name;

				}
			}
		}
	}

	public function actionLogin()
	{
		$phone = $pwd = '';
		if(Yii::app()->request->getIsPostRequest()) {
			$phone = $this->cleanXss(Yii::app()->request->getPost('name'));
			$pwd = $this->cleanXss(Yii::app()->request->getPost('pwd'));
			$rememberMe = $this->cleanXss(Yii::app()->request->getPost('rememberMe',''));
			$model = new ApiLoginForm();
			$model->username = $phone;
			$model->password = $pwd;
			$model->rememberMe = $rememberMe;
			if($model->login()) {
				$this->returnSuccess('登陆成功');
			}
			else {
				$this->returnError('用户名或密码错误');
			}
		}
	}

	public function actionEditPwd()
	{
		if(Yii::app()->request->getIsPostRequest()) {
			$phone = $this->cleanXss(Yii::app()->request->getPost('phone',''));
			$pwd = Yii::app()->request->getPost('pwd','');
			if($phone && $pwd) {
				$user = UserExt::model()->find('phone=:phone',[':phone'=>$phone]);
				$user->pwd = md5($pwd);
				if($user->save()){
					$this->returnSuccess('操作成功');
				}
				else {
					$this->returnError('操作失败');
				}
			}	
		}
	}

	public function actionAddImage()
	{
		if(Yii::app()->request->getIsPostRequest()) {
			$image = Yii::app()->request->getPost('image','');
			$this->staff->ava = $image;
			$this->staff->save();
		}
	}

	public function actionIndex($uid='')
	{
		$data = [];
		$user = UserExt::model()->normal()->findByPk($uid);
		if(!$user) {
			return $this->returnError('用户不存在或禁用');
		}
		$tagarr = [];
		if($tags = TagExt::model()->findAll("status=1 and cate='fxmy'")) {
			foreach ($tags as $key => $value) {
				$tagarr[] = ['name'=>$value->name,'image'=>ImageTools::fixImage($value->icon),'url'=>$value->url];
			}
		}
		$companyinfo = $user->companyinfo;
		$data = [
			'name'=>$user->name,
			'type'=>$user->type,
			'typename'=>$user->type==2?'分销':($user->type==3?'独立经纪人':'总代'),
			'wx_word'=>$companyinfo?$companyinfo->name:'独立经纪人',
			'company'=>$companyinfo?(Tools::u8_title_substr($companyinfo->name,24).' '.$companyinfo->code):'独立经纪人',
			'tags'=>$tagarr,
			'tel'=>SiteExt::getAttr('qjpz','site_phone'),
		];
		$this->frame['data'] = $data;
	}

	public function actionSubList($uid='',$user_type=0,$type='',$kw='',$hid='')
	{
		$data = $all =  [];
		if(!$user_type) {
			$user = UserExt::model()->normal()->findByPk($uid);
		} else {
			$user = StaffExt::model()->normal()->findByPk($uid);
		}
		if(!$user) {
			return $this->returnError('用户不存在或禁用');
		}
		$criteria = new CDbCriteria;
		$criteria->addCondition("uid=$uid");
		if($hid) {
			$criteria->addCondition("hid=$hid");
		}
		$criteria->order = 'updated desc';
		if($user_type==0) {
			// 搜项目和客户电话
			// $criteria = new CDbCriteria;
			// $criteria->addCondition("uid=$uid");
			// $criteria->order = 'updated desc';
			if(is_numeric($kw)) {
				$criteria->addSearchCondition('phone',$kw);
			} else {
				$ids = [];
				$cre = new CDbCriteria;
				$cre->addSearchCondition('title',$kw);
				$ress = PlotExt::model()->findAll($cre);
				if($ress) {
					foreach ($ress as $key => $value) {
						$ids[] = $value->id;
					}
				}
				$criteria->addInCondition('hid',$ids);
			}
			$subs = SubExt::model()->findAll($criteria);
			if($subs) {
				foreach ($subs as $key => $value) {
					$market_user = $value->market_user;
					$all[] = [
						'id'=>$value->id,
						'userName'=>$value->name,
						'userPhone'=>$value->phone,
						'isShowCode'=>1,
						'type'=>$value->status,
						'staffName'=>$market_user?$market_user->name:'暂无',
						'StaffPhone'=>$market_user?$market_user->phone:'暂无',
						'time'=>date("m-d H:i",$value->created),
						'thirdLine'=>$value->plot?$value->plot->title:'暂无',
					];
				}
				$data[] = ['num'=>count($all),'name'=>'所有客户','list'=>$all];
				if($all) {
					foreach (SubExt::$status as $key => $value) {
						$data[$key+1] = ['num'=>0,'name'=>$value,'list'=>[]];
					}
					foreach ($all as $key => $value) {
						$data[$value['type']+1]['num']++;
						$data[$value['type']+1]['list'][] = $value;
					}
				}
			}
		} elseif ($user_type==1) {
			// 搜项目和客户电话
			// $criteria = new CDbCriteria;
			// $criteria->addCondition("uid=$uid");
			// $criteria->order = 'updated desc';
			if(is_numeric($kw)) {
				$criteria->addSearchCondition('phone',$kw);
			} else {
				$ids = [];
				$cre = new CDbCriteria;
				$cre->addSearchCondition('title',$kw);
				$ress = PlotExt::model()->findAll($cre);
				if($ress) {
					foreach ($ress as $key => $value) {
						$ids[] = $value->id;
					}
				}
				$criteria->addInCondition('hid',$ids);
			}
			$subs = SubExt::model()->findAll($criteria);
			if($subs) {
				foreach ($subs as $key => $value) {
					$market_user = $value->user;
					$all[] = [
						'id'=>$value->id,
						'userName'=>$value->name,
						'userPhone'=>$value->phone,
						'isShowCode'=>1,
						'type'=>$value->status,
						'staffName'=>$market_user?$market_user->name:'暂无',
						'StaffPhone'=>$market_user?$market_user->phone:'暂无',
						'time'=>date("m-d H:i",$value->created),
						'thirdLine'=>$market_user->companyinfo?$value->companyinfo->name:'暂无',
					];
				}
				$data[] = ['num'=>count($all),'name'=>'所有客户','list'=>$all];
				if($all) {
					foreach (SubExt::$status as $key => $value) {
						$data[$key+1] = ['num'=>0,'name'=>$value,'list'=>[]];
					}
					foreach ($all as $key => $value) {
						$data[$value['type']+1]['num']++;
						$data[$value['type']+1]['list'][] = $value;
					}
				}
			} 
		} else {
			// 搜项目、分销公司
			// $criteria = new CDbCriteria;
			$criteria->addCondition("company_name like '%$kw%' or plot_title like '%$kw%'");
			// $criteria->order = 'updated desc';
			// if(is_numeric($kw)) {
			// 	$criteria->addSearchCondition('phone',$kw);
			// } else {
			// 	$ids = [];
			// 	$cre = new CDbCriteria;
			// 	$cre->addSearchCondition('title',$kw);
			// 	$ress = PlotExt::model()->findAll($cre);
			// 	if($ress) {
			// 		foreach ($ress as $key => $value) {
			// 			$ids[] = $value->id;
			// 		}
			// 	}
			// 	$criteria->addInCondition('hid',$ids);
			// }

			$subs = SubExt::model()->findAll($criteria);
			if($subs) {
				foreach ($subs as $key => $value) {
					$market_user = $value->user;
					$all[] = [
						'id'=>$value->id,
						'userName'=>$value->name,
						'userPhone'=>$value->phone,
						'isShowCode'=>1,
						'type'=>$value->status,
						'staffName'=>$market_user?$market_user->name:'暂无',
						'StaffPhone'=>$market_user?$market_user->phone:'暂无',
						'time'=>date("m-d H:i",$value->created),
						'thirdLine'=>$market_user->companyinfo?$value->companyinfo->name:'暂无',
					];
				}
				$data[] = ['num'=>count($all),'name'=>'所有客户','list'=>$all];
				if($all) {
					foreach (SubExt::$status as $key => $value) {
						$data[$key+1] = ['num'=>0,'name'=>$value,'list'=>[]];
					}
					foreach ($all as $key => $value) {
						$data[$value['type']+1]['num']++;
						$data[$value['type']+1]['list'][] = $value;
					}
				}
			}
		}
		$this->frame['data'] = $data;

	}

	public function actionShowCode($id='')
	{
		$data = $imgs = [];
		$sub = SubExt::model()->findByPk($id);
		if(!$sub) {
			return $this->returnError('参数错误');
		}
		if($subimgs = $sub->imgs) {
			foreach ($subimgs as $key => $value) {
				$imgs[] = ImageTools::fixImage($value->url);
			}
		}
		$data = [
			'id'=>$id,
			'name'=>$sub->name,
			'phone'=>$sub->phone,
			'time'=>date('Y-m-d H:i',$sub->time),
			'status'=>SubExt::$status[$sub->status],
			'plot'=>$sub->plot_title,
			'code'=>$sub->code,
			'note'=>SiteExt::model()->getAttr('qjpz','subnote'),
			'imgs'=>$imgs,
		];
		$this->frame['data'] = $imgs;

	}

	public function actionSubInfo($id='',$user_type='')
	{
		$data = $pros = $imgs = $firstArr = $secondArr = [];
		$sub = SubExt::model()->findByPk($id);
		$subArr  = SubExt::$status;
		if(!$sub) {
			return $this->returnError('参数错误');
		}
		if($pross = $sub->pros) {
			foreach ($pross as $key => $value) {
				$pros[] = [
					'id'=>$value->id,
					'name'=>($value->user?$value->user->name:($value->staffObj?$value->staffObj->name:'')).'添加了'.$subArr[$value->status],
					'time'=>date('m-d H:i',$value->created),
					'note'=>$value->note,
				];
			}
		}
		if($subimgs = $sub->imgs) {
			foreach ($subimgs as $key => $value) {
				$imgs[] = ImageTools::fixImage($value->url);
			}
		}
		$firstArr = [
			'name'=>$sub->name,
			'phone'=>$sub->phone,
			'tag'=>'客户姓名',
		];
		// 项目展示系统用0 案场传1 市场传2
		if(!$user_type) {
			// 市场消息
			if($u = $sub->market_user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'市场资料',
				];
			}
				
		} elseif($user_type==1) {
			if($u = $sub->user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'分销资料',
				];
			}
		} else {
			if($u = $sub->an_user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'案场资料',
				];
			}
		}

		$subArr = array_slice($subArr, 0,-1);
		// var_dump($subArr);exit;
		$data = [
			'id'=>$id,
			'plot_title'=>$sub->plot_title,
			'status'=>$sub->status,
			'status_arr'=>$subArr,
			// 'vip_name'=>$sub->name,
			// 'vip_phone'=>$sub->phone,
			// 'vip_tag'=>'客户姓名',
			'pros'=>$pros,
			'imgs'=>$imgs,
			'secondArr'=>$secondArr,
			'firstArr'=>$firstArr,
			'user_phone'=>$sub->phone,
		];
		$this->frame['data'] = $data;
	}

	public function actionAddSubPro()
	{
		if(Yii::app()->request->getIsPostRequest()) {
			$data['sid'] = $_post['sid'];
			$data['note'] = $_post['note'];
			$data['status'] = $_post['status'];
			$data['uid'] = $_post['uid'];
			$data['staff'] = $_post['staff'];
			if(!$data['sid']) {
				$obj = new SubProExt;
				$obj->attributes = $obj;
				if(!$obj->status) {
					$obj->status = 9;
				}
				if($obj->save()) {
					$sub = $obj->sub;
					if($obj->status!=9) {
						$sub->status = $obj->status;
						$sub->save();
					}
				}
			}
		}
	}

	public function actionGetSubTag()
	{
		$this->frame['data'] = SubExt::$status;
	}

	public function actionAddSubImg()
	{
		if(Yii::app()->request->getIsPostRequest()) {
			$data['sid'] = $_post['sid'];
			$data['imgs'] = $_post['imgs'];
			if(!$data['sid'] || !$data['imgs']) {
				return $this->returnError('参数错误');
			}
			foreach (explode(',', $data['imgs']) as $key => $value) {
				$obj = new SubImgExt;
				$obj->sid = $data['sid'];
				$obj->url = $value;
				$obj->save();
			}
		}
	}

	public function actionLeave($uid='')
	{
		if($user = UserExt::model()->findByPk($uid)) {
			$user->cid = 0;
			$user->save();
		}
	}
}