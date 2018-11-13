<?php

/**
 * 区域管理相关
 * @author SC
 * @date 2015-09-11
 */
class DepartmentController extends AdminController {

    /**
     * 编辑区域列表
     */
    public function actionAreaList() {
        $list = DepartmentExt::model()->findAll();
        // var_dump($list);exit;
        $tree = Tools::makeTree($list,1);
        // var_dump($tree);exit;
        $this->render('arealist', array('tree' => $tree));
    }

    /**
     * 区域排序
     */
    public function actionSortArea()
    {
        if(Yii::app()->request->isAjaxRequest && $data = Yii::app()->request->getPost('data', '')){
            $list = DepartmentExt::model()->findAll(['index'=>'id']);
            $data = CJSON::decode($data) ? CJSON::decode($data) : array();
            $list = $this->sortArea($data, $list);
            $this->setMessage('保存成功','success');
        }
        $this->setMessage('保存失败','error');
    }

    /**
     * 处理前台排序过的数据
     * @param  array $data array
     * @param  DepartmentExt[] 取出的区域列表
     * @return DepartmentExt[]
     */
    protected function sortArea($data, $list)
    {
        foreach($data as $k=>$v){
            if(isset($list[$v['id']])){
                $model = $list[$v['id']];
                $model->sort = $k;
                $model->status = $v['status'];
                $model->save();
            }
            if(isset($v['children'])){
                $list = $this->sortArea($v['children'], $list);
            }
        }
        return $list;
    }

//    public function actionAddArea() {
//        $this->render('addarea');
//    }

    /**
     * 区域管理 -- 区域添加编辑
     */
    public function actionAreaEdit($id=0) {
        if ($id > 0) {
            $area = DepartmentExt::model()->findByPk($id);
            $msg = '修改';
        } else {
            $area = new DepartmentExt();
            $msg = '添加';
        }

        $_data = Tools::menuMake(DepartmentExt::model()->normal()->findAll());
        $catelist[0] = '--根节点--';
        foreach ($_data as $v) {
            $catelist[$v['id']] = $v['name'];
        }

        if (Yii::app()->request->isPostRequest) {
            $AreaInfo = Yii::app()->request->getPost('DepartmentExt');

            $area->setAttributes($AreaInfo);

            if ($area->save()) {
                $this->setMessage($msg . '成功！','success',$this->createAbsoluteUrl('arealist'));
                //$this->redirect($this->createAbsoluteUrl('arealist'));
            } else {
                $this->setMessage($msg . '失败！','error');
                //$this->redirect(Yii::app()->request->urlReferrer);
            }
        }
        // $maps = array('zoom' => 14, 'lat' => SiteExt::getAttr('qjpz','map_lat') ? SiteExt::getAttr('qjpz','map_lat') : "31.810077", 'lng' => SiteExt::getAttr('qjpz','map_lng') ? SiteExt::getAttr('qjpz','map_lng') : "119.974454");
        $this->render('areaedit', array(
            'area' => $area,
            // 'maps' => $maps,
            'catelist' => $catelist,
        ));
    }

    /**
     * 区域管理 -- 删除区域
     */
    public function actionAreaDel() {
        $id = Yii::app()->request->getParam('id', 0);
        if ($id > 0) {
            $isTrue = FALSE;
            $count = DepartmentExt::model()->normal()->count('parent=:parent',array(':parent'=>$id));
            $area = DepartmentExt::model()->findByPk($id);
            if($count == 0&&$area->delete()){
                $isTrue = TRUE;
            }else{
                $isTrue = FALSE;
            }
            if ($isTrue) {
                echo CJSON::encode(array('code' => 100));
                exit;
            } else {
                echo CJSON::encode(array('code' => -1));
                exit;
            }
        } else {
            echo CJSON::encode(array('code' => -1));
            exit;
        }
    }

    public function actionFoo() {
        $this->render('userinfo', array());
    }

    /**
     * 区域管理 -- 处理排序
     */
    public function actionAreaSort() {
        $sort = $_GET['Area']['sort'];
        $count = 0;
        if (!empty($sort) || !empty($title)) {
            foreach ($sort as $k => $v) {
                $count = Area::model()->updateByPk($k, array('sort' => $v));
                $count++;
            }
            if ($count > 0) {
                Yii::app()->user->setFlash('success', '操作成功！');
                $this->redirect(Yii::app()->request->urlReferrer);
            } else {
                Yii::app()->user->setFlash('danger', '操作失败！');
                $this->redirect(Yii::app()->request->urlReferrer);
            }
        }
    }

    /**
     * ajax获取二级联动下拉菜单[楼盘信息编辑页用]
     * @param  integer $area 上级id
     */
    public function actionAjaxGetArea($area)
    {
        $data = DepartmentExt::model()->getByParent($area)->normal()->findAll();
        if($data)
        {
            echo CHtml::tag('option', array('value'=>0), CHtml::encode('--无子分类--'), true);
            foreach($data as $v)
            {
                echo CHtml::tag('option', array('value'=>$v->id), CHtml::encode($v->name), true);
            }
        }
        else
            echo CHtml::tag('option', array('value'=>0), CHtml::encode('--无子分类--'), true);
    }

    /**
     * 所属区域二级联动下拉菜单
     */
    public function actionShowArea() {
        $pid = Yii::app()->request->getParam('parent', 0);
        DepartmentExt::showArea($pid);
    }

    public function actionClearCache()
    {
        $ids = ['wap_all_area','all_area','all_street'];
        foreach ($ids as $key => $id) {
            CacheExt::delete($id);
        }
        $this->setMessage('操作成功');
    }

    public function actionSlist($type='title',$value='',$time_type='created',$time='',$cate='',$did='')
    {
        $modelName = 'StaffExt';
        $criteria = new CDbCriteria;
        $ids = [];
        $ress = StaffDepartmentExt::model()->findAll("did=$did");
        if($ress) {
            foreach ($ress as $res) {
                $ids[] = $res['uid'];
            }
        }
        $criteria->addInCondition('id',$ids);
        if($value = trim($value))
            if ($type=='title') {

                $criteria->addSearchCondition('name', $value);
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
        $criteria->order = 'updated desc';
        $infos = $modelName::model()->getList($criteria,20);
        $this->render('slist',['cate'=>$cate,'infos'=>$infos->data,'pager'=>$infos->pagination,'type' => $type,'value' => $value,'time' => $time,'time_type' => $time_type,'de'=>DepartmentExt::model()->findByPk($did)]);
    }

    public function actionSedit($id='',$did='')
    {
        $modelName = 'StaffDepartmentExt';
        $info = $id ? $modelName::model()->findByPk($id) : new $modelName;
        if(Yii::app()->request->getIsPostRequest()) {
            $res = Yii::app()->request->getPost($modelName,[]);
            $info->attributes = $res;
            $info->did = $did;
            // $info->time =  is_numeric($info->time)?$info->time : strtotime($info->time);
            if($info->save()) {
                $this->setMessage('操作成功','success',['slist?did='.$did]);
            } else {
                $this->setMessage(array_values($info->errors)[0][0],'error');
            }
        } 
        $this->render('sedit',['article'=>$info,'did'=>$did]);
    }

}
