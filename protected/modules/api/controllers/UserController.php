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
				'wx_word'=>$companyinfo?$companyinfo->name:'独立经纪人',
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
		// 都是搜索手机+客户姓名+楼盘名
		// 案场助理看关联的所有项目的报备
		// 案场销售看分配给自己的报备
		if($kw)
			if(is_numeric($kw)) {
				$criteria->addSearchCondition('phone',$kw);
			} else {
				$criteria->addCondition("plot_title like '%$kw%' or name like '%$kw%'");
			}
		if($hid) {
			$criteria->addCondition("hid=$hid");
		}
		$criteria->order = 'updated desc';
		if($user_type==0) {
			$criteria->addCondition("uid=$uid");
			// 搜项目和客户电话
			// $criteria = new CDbCriteria;
			// $criteria->addCondition("uid=$uid");
			// $criteria->order = 'updated desc';
			
			$subs = SubExt::model()->findAll($criteria);
			if($subs) {
				foreach ($subs as $key => $value) {
					$market_user = $value->market_user;
					$an_user = $value->sale_user;
					$all[] = [
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
						'thirdL'=>'分销',
						'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
						'fouthL'=>'公司',
						'fouthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
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
			$mkids = [];

			$idrr = Yii::app()->db->createCommand("select distinct(hid) from plot_makert_user where uid=".$uid)->queryAll();
			if($idrr) {
				foreach ($idrr as $mkid) {
					$mkids[] = $mkid['hid'];
				}
			}
			$criteria->addInCondition("hid",$mkids);
			// 搜项目、分销公司
			// $criteria = new CDbCriteria;
			$kw && $criteria->addCondition("company_name like '%$kw%' or plot_title like '%$kw%'");
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
						'thirdL'=>'分销',
						'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
						'fouthL'=>'公司',
						'fouthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
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
			$criteria->addCondition("an_uid=$uid");
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
						'thirdL'=>'分销',
						'thirdR'=>$dj_user?($dj_user->name.' '.$dj_user->phone):'暂无',
						'fouthL'=>'公司',
						'fouthR'=>isset($dj_user->companyinfo->name)?($dj_user->companyinfo->name):'暂无',
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
			QRcode::png($id, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
			$image = $sub->qr = $filename;
			$sub->save();
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
			'image'=>Yii::app()->request->getHostInfo().'/'.$image,
		];
		$this->frame['data'] = $data;

	}

	public function actionSubInfo($id='',$user_type='')
	{
		$data = $pros = $imgs = $firstArr = $secondArr = $thirdArr = $imgpros = [];
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
					'note'=>$value->status?$value->note:('预计到访时间：'.date('Y-m-d H:i',$sub->time).'<br>到访人数：'.$sub->visit_num.'<br>备注：'.$sub->note),
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
		if(!$user_type) {
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
				
		} elseif($user_type==1) {
			if($u = $sub->user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'分销资料',
					'company'=>$sub->company?$sub->company->name:'',
				];
			}
		} else {
			if($u = $sub->an_user) {
				$secondArr = [
					'name'=>$u->name,
					'phone'=>$u->phone,
					'tag'=>'案场资料',
					'company'=>'',
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
			$sale_price = Yii::app()->request->getPost('price',0);
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
						$sub->sale_price = $sale_price;
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
		$kwsql = '';
		$kw && $kwsql = " and c.name like '%$kw%'";
		if($companys = Yii::app()->db->createCommand("select c.id,c.name,c.address,c.image,c.area,c.street from company c left join cooperate o on c.id=o.cid where o.staff=$uid".$kwsql)->queryAll()) {
			foreach ($companys as $key => $value) {
				$areaInfo = AreaExt::model()->findByPk($value['area']);
				$streetInfo =  AreaExt::model()->findByPk($value['street']);
				$data[] = [
					'id'=>$value['id'],
					'name'=>$value['name'],
					'area'=>$areaInfo?$areaInfo->name:'',
					'street'=>$streetInfo?$streetInfo->name:'',
					'address'=>$value['address'],
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

	public function actionSetCome($sid='',$uid='')
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
    	$sub->an_uid = $uid;
    	$sub->save();
    	

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
	}

	public function actionAnIndex($uid='',$user_type='')
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
        $tobe = TimeTools::getDayBeginTime(time());
        // var_dump($tobe);
        $cres->addCondition("updated>$tobe");
        if($user_type==1) {
        	$ans = [];
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
        	} else {
        		// $cres = new CDbCriteria;
        		$cres->addCondition("an_uid=$uid");
        	}
        	// $cres = new CDbCriteria;
        	// $cres->addInCondition("hid",$hids);
        	
        } elseif ($user_type==2) {
        	# code...
        } else {

        }
        $subs = SubExt::model()->findAll($cres);
    	if($subs) {
    		foreach ($subs as $s) {
    			// var_dump($s->id);
    			if($s->status==0) {
    				$todarr['报备'] += 1;
    			} elseif ($s->status==1) {
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
			'topArr'=>['name'=>$user->name,'tag'=>StaffExt::$is_jls[$user_type],'company'=>Yii::app()->file->sitename],
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
}