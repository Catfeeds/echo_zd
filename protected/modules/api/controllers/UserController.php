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
			$code = '';
			if(!$obj['phone']) {
				return $this->returnError('请绑定手机号再进行提交');
			}
			if(mb_strlen($obj['name'])>4) {
				return $this->returnError('名字长度不能超过四位');
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
				} else {
					$user->cid = 0;
				}
				$user->attributes = $obj;
				!$user->pwd && $user->pwd = 'jjqxftv587';
				
				$user->pwd = md5($user->pwd);
				if(!$user->save()) {
					return $this->returnError(current(current($user->getErrors())));
				} else {
					$this->frame['data'] = $code&&$company?('绑定公司成功，欢迎加入'.$company->name):SiteExt::getAttr('qjpz','confirmNote');
					if($code&&$company&&$company->phone) {
						SmsExt::sendMsg('绑定门店码成功通知店长',$company->phone,['comname'=>$company->manager,'com'=>$company->name,'code'=>$code,'name'=>$user->name]);
					}
					if($code&&$company) {
						SmsExt::sendMsg('绑定门店码成功通知用户',$user->phone,['com'=>$company->name,'name'=>$user->name]);
					} else {
						SmsExt::sendMsg('独立经纪人注册',$user->phone,['name'=>$user->name,'tel'=>SiteExt::getAttr('qjpz','site_phone')]);
					}
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
					// $this->frame['data'] = $company->name;

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

	public function actionIndex($uid='',$user_type=0)
	{
		$data = [];
		$is_true = 0;
		if(!$user_type) {
			$user = UserExt::model()->findByPk($uid);
			if(!$user) {
				return $this->returnError('用户不存在或禁用');
			}
			$companyinfo = $user->companyinfo;
			if($user->status==0) {
				$data = [
					'name'=>$user->name,
					'id'=>$user->id,
					'type'=>$user->type,
					'typename'=>$user->type==2?'分销':($user->type==3?'独立经纪人':'总代'),
					'wx_word'=>'用户正在审核中',
					'company'=>$companyinfo?(Tools::u8_title_substr($companyinfo->name,24).' '.$companyinfo->code):'独立经纪人',
					'tags'=>[],
					'tel'=>SiteExt::getAttr('qjpz','site_phone'),
					'is_true'=>$is_true,
				];
				$this->frame['data'] = $data;
				return $this->returnError('用户正在审核中');
			}
			$tagarr = [];
			if($tags = TagExt::model()->findAll("status=1 and cate='fxmy'")) {
				foreach ($tags as $key => $value) {
					$tagarr[] = ['name'=>$value->name,'image'=>ImageTools::fixImage($value->icon),'url'=>$value->url];
				}
			}
			
			if($user->type==2&&$user->cid&&$user->status||$user->type==3&&$user->status)
				$is_true = 1;
			$data = [
				'name'=>$user->name,
				'id'=>$user->id,
				'type'=>$user->type,
				'typename'=>$user->type==2?'分销':($user->type==3?'独立经纪人':'总代'),
				'wx_word'=>$user->type==2&&!$companyinfo?'未加入任何公司':($companyinfo?$companyinfo->name:'独立经纪人'),
				'company'=>$companyinfo?(Tools::u8_title_substr($companyinfo->name,24).' '.$companyinfo->code):'独立经纪人',
				'tags'=>$tagarr,
				'tel'=>SiteExt::getAttr('qjpz','site_phone'),
				'is_true'=>$is_true,
			];
		} elseif ($user_type==1) {
			$user = StaffExt::model()->normal()->findByPk($uid);
			if(!$user) {
				return $this->returnError('用户不存在或禁用');
			}
			$tagarr = [];
			if($tags = TagExt::model()->findAll("status=1 and cate='anmy'")) {
				foreach ($tags as $key => $value) {
					$tagarr[] = ['name'=>$value->name,'image'=>ImageTools::fixImage($value->icon),'url'=>$value->url];
				}
			}
			// $companyinfo = $user->companyinfo;
			$data = [
				'name'=>$user->name,
				'id'=>$user->id,
				'type'=>$user_type,
				'typename'=>'案场助理',
				'wx_word'=>Yii::app()->file->sitename,
				// 'company'=>$companyinfo?(Tools::u8_title_substr($companyinfo->name,24).' '.$companyinfo->code):'独立经纪人',
				'tags'=>$tagarr,
				'tel'=>SiteExt::getAttr('qjpz','site_phone'),
			];
		} elseif($user_type==2) {
			$user = StaffExt::model()->normal()->findByPk($uid);
			if(!$user) {
				return $this->returnError('用户不存在或禁用');
			}
			$tagarr = [];
			if($tags = TagExt::model()->findAll("status=1 and cate='scmy'")) {
				foreach ($tags as $key => $value) {
					$tagarr[] = ['name'=>$value->name,'image'=>ImageTools::fixImage($value->icon),'url'=>$value->url];
				}
			}
			// $companyinfo = $user->companyinfo;
			$data = [
				'name'=>$user->name,
				'id'=>$user->id,
				'type'=>$user_type,
				'typename'=>'市场专员',
				'wx_word'=>Yii::app()->file->sitename,
				// 'company'=>$companyinfo?(Tools::u8_title_substr($companyinfo->name,24).' '.$companyinfo->code):'独立经纪人',
				'tags'=>$tagarr,
				'tel'=>SiteExt::getAttr('qjpz','site_phone'),
			];
		} else {
			$user = StaffExt::model()->normal()->findByPk($uid);
			if(!$user) {
				return $this->returnError('用户不存在或禁用');
			}
			$tagarr = [];
			if($tags = TagExt::model()->findAll("status=1 and cate='anxs'")) {
				foreach ($tags as $key => $value) {
					$tagarr[] = ['name'=>$value->name,'image'=>ImageTools::fixImage($value->icon),'url'=>$value->url];
				}
			}
			// $companyinfo = $user->companyinfo;
			$data = [
				'name'=>$user->name,
				'id'=>$user->id,
				'type'=>$user_type,
				'typename'=>'案场销售',
				'wx_word'=>Yii::app()->file->sitename,
				// 'company'=>$companyinfo?(Tools::u8_title_substr($companyinfo->name,24).' '.$companyinfo->code):'独立经纪人',
				'tags'=>$tagarr,
				'tel'=>SiteExt::getAttr('qjpz','site_phone'),
			];
		}
		$this->frame['data'] = $data;
		
	}

	public function actionSubList($uid='',$user_type=0,$type='',$kw='',$hid='',$day='')
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
		// 都是搜索手机+客户姓名+楼盘名
		// 案场助理看关联的所有项目的报备
		// 案场销售看分配给自己的报备
		
		if($hid) {
			$criteria->addCondition("hid=$hid");
		}
		$criteria->order = 'updated desc';
		if($day) {
			switch ($day) {
				// 今天
				case '1':
					$criteria->addCondition("updated>".TimeTools::getDayBeginTime());
					break;
				// 昨天
				case '2':
					$criteria->addCondition("updated>".TimeTools::getDayBeginTime(time()-86400).' and updated<'.TimeTools::getDayEndTime(time()-86400));
					break;
					// 本周
				case '3':
					$criteria->addCondition("updated>".TimeTools::getWeekBeginTime().' and updated<'.TimeTools::getWeekEndTime());
					break;
					// 本月
				case '4':
					$criteria->addCondition("updated>".TimeTools::getMonthBeginTime().' and updated<'.TimeTools::getMonthEndTime());
					break;
				default:
					# code...
					break;
			}
		}
		// 老板看所有
		if(isset($user->is_boss)&&$user->is_boss) {
			if($kw)
				if(is_numeric($kw)) {
					$criteria->addSearchCondition('phone',$kw);
				} else {
					$criteria->addCondition("plot_title like '%$kw%' or name like '%$kw%' or company_name like '%$kw%'");
				}
				$subs = SubExt::model()->findAll($criteria);
				// var_dump(count($subs));exit;
				if($subs) {
					foreach ($subs as $key => $value) {
						$market_user = $value->market_user;
						$dj_user = $value->user;
						// if()
						$all[] = [
							'id'=>$value->id,
							'plot_title'=>$value->plot_title,
							'firstL'=>'客户',
							'firstR'=>$value->name.' '.$value->phone,
							'secondL'=>'市场',
							'secondR'=>$market_user?($market_user->name.' '.$market_user->phone):'暂无',
							'thirdL'=>$value->is_zf?'自访':'分销',
							'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
							'forthL'=>'公司',
							'forthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
							'isShowCode'=>1,
							'type'=>$value->status,
							'typeWords'=>SubExt::$status[$value->status],
							'time'=>date("Y-m-d H:i",$value->created),

							// 'id'=>$value->id,
							// 'userName'=>$value->name,
							// 'userPhone'=>$value->phone,
							// 'isShowCode'=>1,
							// 'type'=>$value->status,
							// 'staffName'=>$market_user?$market_user->name:'暂无',
							// 'staffPhone'=>$market_user?$market_user->phone:'暂无',
							// 'time'=>date("m-d H:i",$value->created),
							// 'thirdLine'=>$market_user&&$market_user->companyinfo?$market_user->companyinfo->name:'暂无',
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
		// 项目总看项目数据
		elseif($xmzs = PlotAnExt::model()->findAll("uid=$uid and type>2")) {
			// 搜索条件
				if($kw)
				if(is_numeric($kw)) {
					$criteria->addSearchCondition('phone',$kw);
				} else {
					$criteria->addCondition("plot_title like '%$kw%' or name like '%$kw%' or company_name like '%$kw%'");
				}
				$hids = [];
				foreach ($xmzs as $kxm) {
					$hids[] = $kxm->hid;
				}
				$criteria->addInCondition('hid',$hids);
				// $mkids = [];

				// $idrr = Yii::app()->db->createCommand("select distinct(hid) from plot_an where type=1 and uid=".$uid)->queryAll();
				// if($idrr) {
				// 	foreach ($idrr as $mkid) {
				// 		$mkids[] = $mkid['hid'];
				// 	}
				// }
				// $criteria->addInCondition("hid",$mkids);
				// 搜项目和客户电话
				// $criteria = new CDbCriteria;
				// $criteria->addCondition("uid=$uid");
				// $criteria->order = 'updated desc';
				// if(is_numeric($kw)) {
				// 	$criteria->addSearchCondition('phone',$kw);
				// } elseif($kw) {
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
				// var_dump($criteria);exit;
				$subs = SubExt::model()->findAll($criteria);
				// var_dump(count($subs));exit;
				if($subs) {
					foreach ($subs as $key => $value) {
						$market_user = $value->market_user;
						$dj_user = $value->user;
						// if()
						$all[] = [
							'id'=>$value->id,
							'plot_title'=>$value->plot_title,
							'firstL'=>'客户',
							'firstR'=>$value->name.' '.$value->phone,
							'secondL'=>'市场',
							'secondR'=>$market_user?($market_user->name.' '.$market_user->phone):'暂无',
							'thirdL'=>$value->is_zf?'自访':'分销',
							'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
							'forthL'=>'公司',
							'forthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
							'isShowCode'=>1,
							'type'=>$value->status,
							'typeWords'=>SubExt::$status[$value->status],
							'time'=>date("Y-m-d H:i",$value->created),

							// 'id'=>$value->id,
							// 'userName'=>$value->name,
							// 'userPhone'=>$value->phone,
							// 'isShowCode'=>1,
							// 'type'=>$value->status,
							// 'staffName'=>$market_user?$market_user->name:'暂无',
							// 'staffPhone'=>$market_user?$market_user->phone:'暂无',
							// 'time'=>date("m-d H:i",$value->created),
							// 'thirdLine'=>$market_user&&$market_user->companyinfo?$market_user->companyinfo->name:'暂无',
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
			if($user_type==0) {
				// 搜索条件
				if($kw)
				if(is_numeric($kw)) {
					$criteria->addSearchCondition('phone',$kw);
				} else {
					$criteria->addCondition("plot_title like '%$kw%' or name like '%$kw%'");
				}

				$tmp = [];
				$is_major = 0;
				$company = $user->companyinfo;
				if($company && $user->is_manage) {
					$is_major = 1;
					$uidss = $company->users;
					foreach ($uidss as $us) {
						$tmp[] = $us['id'];
					}
					$criteria->addInCondition("uid",$tmp);
				} else {
					$criteria->addCondition("uid=$uid");
				}
				
				// 搜项目和客户电话
				// $criteria = new CDbCriteria;
				// $criteria->addCondition("uid=$uid");
				// $criteria->order = 'updated desc';
				
				$subs = SubExt::model()->findAll($criteria);
				if($subs) {
					foreach ($subs as $key => $value) {
						$market_user = $value->market_user;
						$an_user = $value->sale_user;
						$tmpp = [
							'id'=>$value->id,
							'plot_title'=>$value->plot_title,
							'firstL'=>'客户',
							'firstR'=>$value->name.' '.$value->phone,
							'secondL'=>'市场',
							'secondR'=>$market_user?($market_user->name.' '.$market_user->phone):'暂无',
							'thirdL'=>'案场',
							'thirdR'=>$an_user?($an_user->name.' '.$an_user->phone):'暂无',
							'isShowCode'=>1,
							'type'=>$value->status,
							'typeWords'=>SubExt::$status[$value->status],
							'time'=>date("Y-m-d H:i",$value->created),
						];
						if($is_major) {
							$user = $value->user;
							$tmpp['forthL'] = '分销';
							$tmpp['forthR'] = $user?($user->name.' '.$user->phone):'暂无';
							// array_merge($all,[
							// 	'forthL'=>'分销',
							// 	'forthR'=>$user?($user->name.' '.$user->phone):'暂无',
							// ]);
						}
						$all[] = $tmpp;
					}
					// var_dump($all);exit;
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
				// 搜索条件
				if($kw)
				if(is_numeric($kw)) {
					$criteria->addSearchCondition('phone',$kw);
				} else {
					$criteria->addCondition("plot_title like '%$kw%' or name like '%$kw%' or company_name like '%$kw%'");
				}
				$mkids = [];

				$idrr = Yii::app()->db->createCommand("select distinct(hid) from plot_an where type=1 and uid=".$uid)->queryAll();
				if($idrr) {
					foreach ($idrr as $mkid) {
						$mkids[] = $mkid['hid'];
					}
				}
				$criteria->addInCondition("hid",$mkids);
				// 搜项目和客户电话
				// $criteria = new CDbCriteria;
				// $criteria->addCondition("uid=$uid");
				// $criteria->order = 'updated desc';
				// if(is_numeric($kw)) {
				// 	$criteria->addSearchCondition('phone',$kw);
				// } elseif($kw) {
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
				// var_dump($criteria);exit;
				$subs = SubExt::model()->findAll($criteria);
				// var_dump(count($subs));exit;
				if($subs) {
					foreach ($subs as $key => $value) {
						$market_user = $value->market_user;
						$dj_user = $value->user;
						// if()
						$all[] = [
							'id'=>$value->id,
							'plot_title'=>$value->plot_title,
							'firstL'=>'客户',
							'firstR'=>$value->name.' '.$value->phone,
							'secondL'=>'市场',
							'secondR'=>$market_user?($market_user->name.' '.$market_user->phone):'暂无',
							'thirdL'=>$value->is_zf?'自访':'分销',
							'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
							'forthL'=>'公司',
							'forthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
							'isShowCode'=>1,
							'type'=>$value->status,
							'typeWords'=>SubExt::$status[$value->status],
							'time'=>date("Y-m-d H:i",$value->created),

							// 'id'=>$value->id,
							// 'userName'=>$value->name,
							// 'userPhone'=>$value->phone,
							// 'isShowCode'=>1,
							// 'type'=>$value->status,
							// 'staffName'=>$market_user?$market_user->name:'暂无',
							// 'staffPhone'=>$market_user?$market_user->phone:'暂无',
							// 'time'=>date("m-d H:i",$value->created),
							// 'thirdLine'=>$market_user&&$market_user->companyinfo?$market_user->companyinfo->name:'暂无',
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
			} elseif($user_type==2) {
				// 搜索条件
				if($kw)
				if(is_numeric($kw)) {
					$criteria->addSearchCondition('phone',$kw);
				} else {
					$criteria->addCondition("plot_title like '%$kw%' or name like '%$kw%' or company_name like '%$kw%'");
				}
				$mkids = [];

				// $idrr = Yii::app()->db->createCommand("select distinct(hid) from plot_makert_user where uid=".$uid)->queryAll();
				// if($idrr) {
				// 	foreach ($idrr as $mkid) {
				// 		$mkids[] = $mkid['hid'];
				// 	}
				// }
				$criteria->addCondition("market_uid=$uid");
				// 搜项目、分销公司
				// $criteria = new CDbCriteria;
				// $kw && $criteria->addCondition("company_name like '%$kw%'",'OR');
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
						$dj_user = $value->user;
						$an_user = $value->an_user;
						$all[] = [
							'id'=>$value->id,
							'plot_title'=>$value->plot_title,
							'firstL'=>'客户',
							'firstR'=>$value->name.' '.$value->phone,
							'secondL'=>'案场',
							'secondR'=>$an_user?($an_user->name.' '.$an_user->phone):'暂无',
							'thirdL'=>$value->is_zf?'自访':'分销',
							'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
							'forthL'=>'公司',
							'forthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
							'isShowCode'=>1,
							'type'=>$value->status,
							'typeWords'=>SubExt::$status[$value->status],
							'time'=>date("Y-m-d H:i",$value->created),

							// 'id'=>$value->id,
							// 'userName'=>$value->name,
							// 'userPhone'=>$value->phone,
							// 'isShowCode'=>1,
							// 'type'=>$value->status,
							// 'staffName'=>$market_user?$market_user->name:'暂无',
							// 'staffPhone'=>$market_user?$market_user->phone:'暂无',
							// 'time'=>date("m-d H:i",$value->created),
							// 'thirdLine'=>$market_user->companyinfo?$market_user->companyinfo->name:'暂无',
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
				$criteria->addCondition("sale_uid=$uid");
				// 搜项目和客户电话
				// $criteria = new CDbCriteria;
				// $criteria->addCondition("uid=$uid");
				// $criteria->order = 'updated desc';
				if(is_numeric($kw)) {
					$criteria->addSearchCondition('phone',$kw);
				} elseif($kw) {
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
				// var_dump($criteria);exit;
				$subs = SubExt::model()->findAll($criteria);
				// var_dump(count($subs));exit;
				if($subs) {
					foreach ($subs as $key => $value) {
						$market_user = $value->market_user;
						$dj_user = $value->user;
						// if()
						$all[] = [
							'id'=>$value->id,
							'plot_title'=>$value->plot_title,
							'firstL'=>'客户',
							'firstR'=>$value->name.' '.$value->phone,
							'secondL'=>'市场',
							'secondR'=>$market_user?($market_user->name.' '.$market_user->phone):'暂无',
							'thirdL'=>$value->is_zf?'自访':'分销',
							'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
							'forthL'=>'公司',
							'forthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
							'isShowCode'=>1,
							'type'=>$value->status,
							'typeWords'=>SubExt::$status[$value->status],
							'time'=>date("Y-m-d H:i",$value->created),

							// 'id'=>$value->id,
							// 'userName'=>$value->name,
							// 'userPhone'=>$value->phone,
							// 'isShowCode'=>1,
							// 'type'=>$value->status,
							// 'staffName'=>$market_user?$market_user->name:'暂无',
							// 'staffPhone'=>$market_user?$market_user->phone:'暂无',
							// 'time'=>date("m-d H:i",$value->created),
							// 'thirdLine'=>$market_user&&$market_user->companyinfo?$market_user->companyinfo->name:'暂无',
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
				$imgs[] = ['key'=>$value->url,'imageURL'=>ImageTools::fixImage($value->url)];
			}
		}
		$errorCorrectionLevel = 'L';//容错级别 

		$matrixPointSize = 6;//生成图片大小 

		//生成二维码图片 
		$filename = 'qrcode/'.microtime().'.png';
		if($sub->qr) {
			$image = $sub->qr;
		} else {
			QRcode::png(json_encode(['id'=>$id,'name'=>$sub->name]), $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
			$image = $sub->qr = $filename;
			$sub->save();
		}
		
		$data = [
			'id'=>$id,
			'name'=>$sub->name,
			'phone'=>$sub->phone,
			'time'=>date('Y-m-d H:i',$sub->created),
			'status'=>SubExt::$status[$sub->status],
			'plot'=>$sub->plot_title,
			'code'=>$sub->code,
			'note'=>SiteExt::model()->getAttr('qjpz','subnote'),
			'imgs'=>$imgs,
			'image'=>Yii::app()->request->getHostInfo().'/'.$image,
		];
		$this->frame['data'] = $data;

	}

	public function actionSubInfo($id='',$type='')
	{
		$data = $pros = $imgs = $firstArr = $secondArr = $thirdArr = $imgpros = [];
		$sub = SubExt::model()->findByPk($id);
		$subArr  = SubExt::$status;
		if(!$sub) {
			return $this->returnError('参数错误');
		}
		if($pross = $sub->pros) {
			foreach ($pross as $key => $value) {
				if($value->status==3) {
					$note = "房号：".($sub->house_no?$sub->house_no:'暂无')."<br>面积：".($sub->size?$sub->size:'暂无')."<br>合同金额：".($sub->sale_price?$sub->sale_price:'暂无');
				} else {
					$note = $value->status?$value->note:(($sub->id_no?('身份证号码：'.$sub->id_no.'<br>'):'').'预计到访时间：'.($sub->time?date('Y-m-d H:i',$sub->time):'暂无').'<br>到访人数：'.$sub->visit_num.'<br>备注：'.$sub->note);
				}
				$pros[] = [
					'id'=>$value->id,
					'name'=>($value->user?$value->user->name:($value->staffObj?$value->staffObj->name:'')).'添加了'.$subArr[$value->status],
					'time'=>date('m-d H:i',$value->created),
					'note'=>$note,
				];
			}
		}
		if($subimgs = $sub->imgs) {
			foreach ($subimgs as $key => $value) {
				$imguser = $value->getUser();
				$imgs[] = ['key'=>$value->url,'imageURL'=>ImageTools::fixImage($value->url)];
				$imgpros[] = ['name'=>$imguser?$imguser->name:'用户','time'=>date('m-d H:i',$value->created)];
			}
		}
		$firstArr = [
			'name'=>$sub->name,
			'phone'=>$sub->phone,
			'tag'=>'客户',
			'company'=>'',
		];
		// 项目展示系统用0 案场传1 市场传2
		if(!$type) {
			// 市场消息
			if($u = $sub->market_user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'市场对接',
					'company'=>'',
				];
			}
			if($u = $sub->sale_user) {
				$thirdArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'案场销售',
					'company'=>'',
				];
			}
				
		} elseif($type==1) {
			if($u = $sub->market_user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'市场对接',
					'company'=>'',
				];
			}
			if($u = $sub->user) {
				$thirdArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>$sub->is_zf?'自访客':'分销信息',
					'company'=>$sub->company?$sub->company->name:'',
				];
			}
		} else {
			if($u = $sub->sale_user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'案场销售',
					'company'=>'',
				];
			}
			if($u = $sub->user) {
				$thirdArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>$sub->is_zf?'自访客':'分销信息',
					'company'=>$sub->company?$sub->company->name:'',
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
			'imgpros'=>$imgpros,
			'imgs'=>$imgs,
			'secondArr'=>$secondArr,
			'firstArr'=>$firstArr,
			'thirdArr'=>$thirdArr,
			'user_phone'=>$sub->phone,
		];
		$this->frame['data'] = $data;
	}

	public function actionAddSubPro()
	{
		if(Yii::app()->request->getIsPostRequest()) {
			$data['sid'] = $_POST['sid'];
			$data['note'] = $_POST['note'];
			$data['status'] = Yii::app()->request->getPost('status',9);
			$data['uid'] = Yii::app()->request->getPost('uid',0);
			$data['staff'] = Yii::app()->request->getPost('staff',0);
			$tmp['rcj'] = Yii::app()->request->getPost('rcj','');
			$tmp['house_no'] = Yii::app()->request->getPost('house_no','');
			$tmp['size'] = Yii::app()->request->getPost('size','');
			$tmp['sale_price'] = Yii::app()->request->getPost('sale_price','');
			$tmp['ding_price'] = Yii::app()->request->getPost('ding_price','');
			$tmp['zy_price'] = Yii::app()->request->getPost('zy_price','');
			$tmp['yj_price'] = Yii::app()->request->getPost('yj_price','');
			$tmp['hk_price'] = Yii::app()->request->getPost('hk_price','');
			$tmp['qy_time'] = Yii::app()->request->getPost('qy_time','');
			$tmp['fk_type'] = Yii::app()->request->getPost('fk_type','');
			$areaarr = Yii::app()->request->getPost('area','');
			if($areaarr){
				if(strstr($areaarr, ',')) {
					$areaarr = explode(',', $areaarr);
					$tmp['area'] = $areaarr[2];
					$tmp['city'] = $areaarr[1];
					$tmp['province'] = $areaarr[0];
				}
			}
			// 成交时 房号面积合同金额定金必填
			if($data['status']==3) {
				if(!$tmp['house_no']) {
					return $this->returnError('请填写房号');
				}
				if(!$tmp['size']) {
					return $this->returnError('请填写面积');
				}
				if(!$tmp['sale_price']) {
					return $this->returnError('请填写合同总价');
				}
				if(!$tmp['ding_price']) {
					return $this->returnError('请填写定金');
				}

			}
			if($data['sid']) {
				$obj = new SubProExt;
				$obj->attributes = $data;
				if(!$obj->status) {
					$obj->status = 9;
				}
				if($obj->save()) {
					$sub = $obj->sub;
					if($obj->status!=9) {
						$sub->status = $obj->status;
						foreach ($tmp as $key => $value) {
							$sub->$key = $value;
						}
						// $sub->sale_price = $sale_price;
						$sub->save();
					}
				}
			}
		}
	}

	public function actionGetSubTag($sid='')
	{
		$sub = SubExt::model()->findByPk($sid);
		if(!$sub) {
			return $this->returnError('参数错误');
		}
		$this->frame['data'] = 
		['tagArr'=>SubExt::$status,
		'textArr'=>['2'=>[['text'=>'认筹金','value'=>$sub->rcj,'require'=>false,'placeholder'=>'请输入认筹金','param'=>'rcj','type'=>1,'typeArr'=>[]]],'3'=>[['text'=>'房号','value'=>$sub->house_no,'require'=>true,'placeholder'=>'请输入房号','param'=>'house_no','type'=>1,'typeArr'=>[]],['text'=>'面积','value'=>$sub->size,'require'=>true,'placeholder'=>'请输入面积','param'=>'size','type'=>1,'typeArr'=>[]],['text'=>'合同总价','value'=>$sub->sale_price,'require'=>true,'placeholder'=>'请输入合同总价','param'=>'sale_price','type'=>1,'typeArr'=>[]],['text'=>'定金','value'=>$sub->ding_price,'require'=>true,'placeholder'=>'请输入定金','param'=>'ding_price','type'=>1,'typeArr'=>[]],['text'=>'折佣金额','value'=>$sub->zy_price,'require'=>false,'placeholder'=>'请输入折佣金额','param'=>'zy_price','type'=>1,'typeArr'=>[]],['text'=>'渠道佣金','value'=>$sub->yj_price,'require'=>false,'placeholder'=>'请输入渠道佣金','param'=>'yj_price','type'=>1,'typeArr'=>[]],['text'=>'回款金额','value'=>$sub->hk_price,'require'=>false,'placeholder'=>'请输入回款金额','param'=>'hk_price','type'=>1,'typeArr'=>[]],['text'=>'签约时间','value'=>date('Y-m-d H:i',$sub->qy_time?$sub->qy_time:time()),'require'=>false,'placeholder'=>'请输入签约时间','param'=>'qy_time','type'=>2,'typeArr'=>[]]],'4'=>[['text'=>'付款方式','value'=>$sub->fk_type,'require'=>false,'placeholder'=>'请选择付款方式','param'=>'fk_type','type'=>3,'typeArr'=>CHtml::listData(TagExt::model()->findAll("cate='pricetype'"),'id','name')]]]];
	}

	public function actionGetSubPrice($sid='')
	{
		$data = [];
    	$sub = SubExt::model()->findByPk($sid);

    	if(!$sub) {
    		return $this->returnError('参数错误');
    	}
    	$this->frame['data'] = $sub->sale_price;
	}

	public function actionAddSubImg()
	{
		if(Yii::app()->request->getIsPostRequest()) {
			$imgs = $imgpros = [];
			$data['sid'] = Yii::app()->request->getPost('sid','');
			$data['uid'] = Yii::app()->request->getPost('uid','');
			$data['user_type'] = Yii::app()->request->getPost('user_type',0);
			$imgs = Yii::app()->request->getPost('imgs',[]);
			if(!$data['sid']) {
				return $this->returnError('参数错误');
			}
			$sub = SubExt::model()->findByPk($data['sid']);
			// SubImgExt::model()->deleteAllByAttributes(['sid'=>$data['sid']]);
			if($imgs)
				foreach (explode(',', $imgs) as $key => $value) {
					$obj = new SubImgExt;
					$obj->attributes = $data;
					$obj->url = $value;
					$obj->save();
				}
			// if($subimgs = $sub->imgs) {
			// 	foreach ($subimgs as $key => $value) {
			// 		$imguser = $value->getUser();
			// 		$imgs[] = ['key'=>$value->url,'imageURL'=>ImageTools::fixImage($value->url)];
			// 		$imgpros[] = ['name'=>$imguser?$imguser->name:'用户','time'=>date('m-d H:i',$value->created)];
			// 	}
			// }
			// $this->frame['data'] = ['imgs'=>$imgs,'imgpros'=>$imgpros];
		}

	}

	public function actionLeave($uid='')
	{
		if($user = UserExt::model()->findByPk($uid)) {
			$user->cid = 0;
			$user->save();
		}
	}

	public function actionCheckPwd($kw='',$pwd='',$openid='')
	{
		if(!$kw||!$pwd)
			return $this->returnError('参数错误');
		if(is_numeric($kw)) {
			$user = StaffExt::model()->normal()->find("phone='$kw'");
		} else {
			$user = StaffExt::model()->normal()->find("name='$kw'");
		}
		if($user) {
			if($user->password==$pwd) {
				if(!$user->openid) {
					$user->openid = $openid;
					$user->save();
				}
				$this->frame['data'] = ['uid'=>$user->id,'type'=>$user->is_jl?$user->is_jl:1];
			} else {
				$this->returnError('用户名或密码错误');
			}
		} else{
			return $this->returnError('用户不存在或禁用');
		}
	}

	public function actionBindOpenid($openid='',$phone='')
	{
		$staff = StaffExt::model()->normal()->find("phone='$phone'");
		if(!$staff) {
			return $this->returnError('用户不存在或禁用');
		}
		$staff->openid = $openid;
		$staff->save();
		$this->frame['data'] = $staff->id;
	}

	public function actionGetCompanyList($uid='',$kw='')
	{
		if(!($user = StaffExt::model()->findByPk($uid))) {
			return $this->returnError('用户不存在或禁用');
		}
		$data = [];
		$cids = [];
		// 签约的cid
		$cidsres = CooperateExt::model()->findAll("staff=$uid");
		if($cidsres) {
			foreach ($cidsres as $key => $value) {
				!in_array($value->cid, $cids) && $cids[] = $value['cid'];
			}
		}
		// 自己添加的cid
		$cidsres = CompanyExt::model()->findAll("staff=$uid");
		if($cidsres) {
			foreach ($cidsres as $key => $value) {
				!in_array($value->cid, $cids) && $cids[] = $value['cid'];
			}
		}
		$criteria = new CDbCriteria;
		$criteria->addInCondition('id',$cids);
		$kw && $criteria->addSearchCondition('name',$kw);

		// $kwsql = '';
		// $kw && $kwsql = " and c.name like '%$kw%'";
		if($companys = CompanyExt::model()->findAll($criteria)) {
			foreach ($companys as $key => $value) {
				$areaInfo = AreaExt::model()->findByPk($value['area']);
				$streetInfo =  AreaExt::model()->findByPk($value['street']);
				$data[] = [
					'id'=>$value['id'],
					'name'=>$value['name'],
					'area'=>$areaInfo?$areaInfo->name:'',
					'street'=>$streetInfo?$streetInfo->name:'',
					'address'=>$value['address'],
					'code'=>$value['code'],
					'manager'=>$value['manager'],
					'phone'=>$value['phone'],
					'is_add'=>$value['staff']==$uid?true:false,
					'image'=>ImageTools::fixImage($value['image']?$value['image']:SiteExt::getAttr('qjpz','companynopic')),
				];
			}
		}
		$this->frame['data'] = $data;
	}

	public function actionGetCompanyInfo($id='')
	{
		if(!($company = CompanyExt::model()->findByPk($id))) {
			return $this->returnError('公司不存在或禁用');
		}
		$data = [];
		$data = [
			'id'=>$company->id,
			'name'=>$company->name,
			'address'=>$company->address,
			'map_lat'=>$company->map_lat,
			'map_lng'=>$company->map_lng,
			'area'=>$company->areainfo?$company->areainfo->name:'',
			'street'=>$company->streetinfo?$company->streetinfo->name:'',
			'manager'=>$company->manager,
			'phone'=>$company->phone,
			'image'=>ImageTools::fixImage($company->image),
		];
		$this->frame['data'] = $data;
	}

	public function actionBindMarket($uid='',$hid='',$company='')
	{
		$cid = Yii::app()->db->createCommand("select id from company where name='$company'")->queryScalar();
		if(!$cid)
			return $this->returnError('该公司不存在');
		$coo = CooperateExt::model()->find("hid=$hid and cid=$cid");
		if($coo) {
			if($coo->uid||$coo->staff) {
				return $this->returnError('该公司已绑定该项目');
			} else {
				$coo->staff = $uid;
				$coo->save();
			}
		} else {
			$coo = new CooperateExt;
			$coo->staff = $uid;
			$coo->hid = $hid;
			$coo->status = 1;
			$coo->cid = $cid;
			$coo->save();
		}
	}

	public function actionSetCome($sid='',$uid='',$user_type='')
	{
		$data = 0;
    	$sub = SubExt::model()->findByPk($sid);

    	if(!$sub) {
    		return $this->returnError('参数错误');
    	}
    	// 项目不对应，操作失败
    	if(!Yii::app()->db->createCommand("select id from plot_an where uid=$uid and hid=".$sub->hid)->queryScalar()) {
    		$this->frame['data'] = $data;
    		return $this->returnError('您不是该项目的案场，请确认');
    	}
    	if($sub->status) {
    		$data = 1;
    		$this->frame['data'] = $data;
    		return $this->returnError('客户已到访，请勿重复确认');
    	}
    	$sub->status = 1;
    	if($user_type==1)
    		$sub->an_uid = $uid;
    	elseif ($user_type==3) {
    		$sub->sale_uid = $uid;
    	}
    	if($sub->save()) {
    		$obj = new SubProExt;
    		$obj->sid = $sid;
    		$obj->staff = $uid;
    		$obj->status = $sub->status;
    		$obj->save();
    	}
    	

	}

	public function actionSetAnSale($uid='',$sid='')
	{
		$data = [];
    	$sub = SubExt::model()->findByPk($sid);

    	if(!$sub) {
    		return $this->returnError('参数错误');
    	}
    	$sub->sale_uid = $uid;
    	$sub->save();
    	if($staff = StaffExt::model()->findByPk($uid)) {
    		SmsExt::sendMsg('客户分配案场销售',$staff->phone,['anname'=>'案场助理','user'=>$sub->name.$sub->phone,'pro'=>$sub->plot_title]);
    	}
    	
	}

	public function actionAnIndex($uid='',$user_type='',$day='1')
	{
		$user = StaffExt::model()->findByPk($uid);
		$data = $tags = [];
		$tacs = $user_type==1?'anindex':($user_type==2?'scindex':'anxsindex');
		if($ress = TagExt::model()->findAll("status=1 and cate='$tacs'")) {
            foreach ($ress as $key => $value) {
                $tags[] = [
                    'name'=>$value->name,
                    'image'=>ImageTools::fixImage($value->icon),
                    'url'=>$value->url,
                ];
            }
        }
        $todarr = [
        	'报备'=>0,
        	'到访'=>0,
        	'大定'=>0,
        	'签约'=>0,
        ];
        $cres = new CDbCriteria;
        $cret = new CDbCriteria;
        $tobe = TimeTools::getDayBeginTime(time());
        if($day) {
			switch ($day) {
				// 今天
				case '1':
					$cres->addCondition("updated>".TimeTools::getDayBeginTime());
					$cret->addCondition("created>".TimeTools::getDayBeginTime());
					break;
				// 昨天
				case '2':
					$cres->addCondition("updated>".TimeTools::getDayBeginTime(time()-86400).' and updated<'.TimeTools::getDayEndTime(time()-86400));
					$cret->addCondition("created>".TimeTools::getDayBeginTime(time()-86400).' and created<'.TimeTools::getDayEndTime(time()-86400));
					break;
					// 本周
				case '3':
					$cres->addCondition("updated>".TimeTools::getWeekBeginTime().' and updated<'.TimeTools::getWeekEndTime());
					$cret->addCondition("created>".TimeTools::getWeekBeginTime().' and created<'.TimeTools::getWeekEndTime());
					break;
					// 本月
				case '4':
					$cres->addCondition("updated>".TimeTools::getMonthBeginTime().' and updated<'.TimeTools::getMonthEndTime());
					$cret->addCondition("created>".TimeTools::getMonthBeginTime().' and created<'.TimeTools::getMonthEndTime());
					break;
				default:
					# code...
					break;
			}
		}
        // var_dump($tobe);
        // $cres->addCondition("updated>$tobe");
        // 项目总看项目数据
		if($user->is_boss) {
			
		}
        elseif($xmzs = PlotAnExt::model()->findAll("uid=$uid and type>2")) {
        	$tmparr = [];
        	foreach ($xmzs as $key => $value) {
        		$tmparr[] = $value->hid;
        	}
        	$cres->addInCondition('hid',$tmparr);
        	$cret->addInCondition('hid',$tmparr);
        } else {
        	if($user_type==1) {
	        	$ans = [$uid];
	        	// 案场助理
	        	$zgbms = StaffDepartmentExt::model()->findAll("is_major=1 and uid=$uid");
	        	// 如果是主管 递归找出所有子部门的所有成员的案场绑定项目
	        	if($zgbms) {
	        		$des = [];
	        		foreach ($zgbms as $ms) {
	        			// var_dump($ms->did);
	        			$dids = $this->getChild($ms->did);
	        			// var_dump($dids);exit;
	        			$criteria = new CDbCriteria;
	        			$criteria->addInCondition('did',$dids);
	        			$mems = StaffDepartmentExt::model()->findAll($criteria);
	        			if($mems) {
	        				foreach ($mems as $mem) {
		        				if(!in_array($mem->uid, $des)) {
		        					$ans[] = $mem->uid;
		        				}
	        				}
	        				// if($des) {
	        				// 	$cre = new CDbCriteria;
	        				// 	$cre->addInCondition('uid',$des);
	        				// 	$cre->addCondition("type=1");
	        				// 	$plotans = PlotAnExt::model()->findAll($cre);
	        				// 	if($plotans) {
	        				// 		foreach ($plotans as $pa) {
	        				// 			$hids[] = $pa->hid;
	        				// 		}
	        				// 	}
	        				// }
	        			}
	        			// var_dump($ans);exit;
	        		}
	        		
	        		$cres->addInCondition("an_uid",$ans);
	        		$cret->addInCondition("an_uid",$ans);
	        	} else {
	        		// $cres = new CDbCriteria;
	        		$cres->addCondition("an_uid=$uid");
	        		$cret->addCondition("an_uid=$uid");
	        	}
	        	// $cres = new CDbCriteria;
	        	// $cres->addInCondition("hid",$hids);
	        	
	        } elseif ($user_type==2) {
	        	// 市场
	        	$ans = [$uid];
	        	// 案场助理
	        	$zgbms = StaffDepartmentExt::model()->findAll("is_major=1 and uid=$uid");
	        	// var_dump($zgbms);exit;
	        	// 如果是主管 递归找出所有子部门的所有成员的案场绑定项目
	        	if($zgbms) {
	        		$des = [];
	        		foreach ($zgbms as $ms) {
	        			// var_dump($ms->did);
	        			$dids = $this->getChild($ms->did);
	        			// var_dump($dids);exit;
	        			$criteria = new CDbCriteria;
	        			$criteria->addInCondition('did',$dids);
	        			$mems = StaffDepartmentExt::model()->findAll($criteria);
	        			if($mems) {
	        				foreach ($mems as $mem) {
		        				if(!in_array($mem->uid, $des)) {
		        					$ans[] = $mem->uid;
		        				}
	        				}
	        				// if($des) {
	        				// 	$cre = new CDbCriteria;
	        				// 	$cre->addInCondition('uid',$des);
	        				// 	$cre->addCondition("type=1");
	        				// 	$plotans = PlotAnExt::model()->findAll($cre);
	        				// 	if($plotans) {
	        				// 		foreach ($plotans as $pa) {
	        				// 			$hids[] = $pa->hid;
	        				// 		}
	        				// 	}
	        				// }
	        			}
	        			// var_dump($ans);exit;
	        		}
	        		
	        		$cres->addInCondition("market_uid",$ans);
	        		$cret->addInCondition("market_uid",$ans);
	        	} else {
	        		// $cres = new CDbCriteria;
	        		$cres->addCondition("market_uid=$uid");
	        		$cret->addCondition("market_uid=$uid");
	        	}
	        } else {
	        	$ans = [$uid];
	        	// 案场销售
	        	$zgbms = StaffDepartmentExt::model()->findAll("is_major=1 and uid=$uid");
	        	// 如果是主管 递归找出所有子部门的所有成员的案场绑定项目
	        	if($zgbms) {
	        		$des = [];
	        		foreach ($zgbms as $ms) {
	        			// var_dump($ms->did);
	        			$dids = $this->getChild($ms->did);
	        			// var_dump($dids);exit;
	        			$criteria = new CDbCriteria;
	        			$criteria->addInCondition('did',$dids);
	        			$mems = StaffDepartmentExt::model()->findAll($criteria);
	        			if($mems) {
	        				foreach ($mems as $mem) {
		        				if(!in_array($mem->uid, $des)) {
		        					$ans[] = $mem->uid;
		        				}
	        				}
	        				// if($des) {
	        				// 	$cre = new CDbCriteria;
	        				// 	$cre->addInCondition('uid',$des);
	        				// 	$cre->addCondition("type=1");
	        				// 	$plotans = PlotAnExt::model()->findAll($cre);
	        				// 	if($plotans) {
	        				// 		foreach ($plotans as $pa) {
	        				// 			$hids[] = $pa->hid;
	        				// 		}
	        				// 	}
	        				// }
	        			}
	        			// var_dump($ans);exit;
	        		}
	        		
	        		$cres->addInCondition("sale_uid",$ans);
	        		$cret->addInCondition("sale_uid",$ans);
	        	} else {
	        		// $cres = new CDbCriteria;
	        		$cres->addCondition("sale_uid=$uid");
	        		$cret->addCondition("sale_uid=$uid");
	        	}
	        }
        }
        $subs = SubExt::model()->findAll($cres);
        $allsubs = SubExt::model()->count($cret);
        $todarr['报备'] = $allsubs;
        // var_dump(count($subs));exit;
    	if($subs) {
    		foreach ($subs as $s) {
    			// switch ($day) {
    			// 	case '1':
    			// 		if($s->created>$tobe) {
		    	// 			$todarr['报备'] += 1;
		    	// 		}
    			// 		break;
    			// 	case '2':
    			// 		if($s->created>TimeTools::getDayBeginTime(time()-86400) && $s->created<TimeTools::getDayEndime(time()-86400)) {
		    	// 			$todarr['报备'] += 1;
		    	// 		}
    			// 		break;
    			// 	case '3':
    			// 		if($s->created>TimeTools::getWeekBeginTime() && $s->created<TimeTools::getWeekEndime()) {
		    	// 			$todarr['报备'] += 1;
		    	// 		}
    			// 		break;
    			// 	case '4':
    			// 		if($s->created>TimeTools::getMonthBeginTime() && $s->created<TimeTools::getMonthEndime()) {
		    	// 			$todarr['报备'] += 1;
		    	// 		}
    			// 		break;
    			// 	default:
    			// 		# code...
    			// 		break;
    			// }
    			// if($s->created>$tobe) {
    			// 	$todarr['报备'] += 1;
    			// }
    			if ($s->status==1) {
    				$todarr['到访'] += 1;
    			} elseif ($s->status==3) {
    				$todarr['大定'] += 1;
    			} elseif ($s->status==4) {
    				$todarr['签约'] += 1;
    			}
    		}
    	}

        $todayList = [];
        foreach ($todarr as $key => $value) {
        	$todayList[] = ['num'=>$value,'text'=>$key];
        }
		$data = [
			'topArr'=>['name'=>$user->name,'tag'=>$user->zw?$user->zw:StaffExt::$is_jls[$user_type],'company'=>SiteExt::getAttr('qjpz','companyname')?SiteExt::getAttr('qjpz','companyname'):Yii::app()->file->sitename1],
			'topNewsList'=>explode(' ', SiteExt::getAttr('qjpz','indexmarquee')),
			'todayList'=>$todayList,
			'tags'=>$tags,
		];
		$this->frame['data'] = $data;
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

	public function actionEditName($uid='',$name='',$type='')
	{
		$model = $type?'StaffExt':'UserExt';
		$user = $model::model()->findByPk($uid);
		if(!$user) {
			return $this->returnError('用户不存在');
		}
		if(!$name || $name>4) {
			return $this->returnError('请输入正确的姓名');
		}
		$user->name = $name;

		$user->save();
	}

	public function actionMultiSub()
	{
		$subs = Yii::app()->request->getPost('note','');
		$uid = Yii::app()->request->getPost('uid','');
		$staff = Yii::app()->request->getPost('staff','');

	}

	public function actionGetMultiTitle($type='')
	{
		if($type==1)
			$this->frame['data'] = Yii::app()->file->sitename.'快速报备';
		else
			$this->frame['data'] = Yii::app()->file->sitename1.'快速报备';
	}
}