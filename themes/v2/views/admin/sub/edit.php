<?php
$this->pageTitle = $this->controllerName.'新建/编辑';
$this->breadcrumbs = array($this->controllerName.'管理', $this->pageTitle);
?>
<?php $this->widget('ext.ueditor.UeditorWidget',array('id'=>'UserExt_content','options'=>"toolbars:[['fullscreen','source','undo','redo','|','customstyle','paragraph','fontfamily','fontsize'],
        ['bold','italic','underline','fontborder','strikethrough','superscript','subscript','removeformat',
        'formatmatch', 'autotypeset', 'blockquote', 'pasteplain','|',
        'forecolor','backcolor','insertorderedlist','insertunorderedlist','|',
        'rowspacingtop','rowspacingbottom', 'lineheight','|',
        'directionalityltr','directionalityrtl','indent','|'],
        ['justifyleft','justifycenter','justifyright','justifyjustify','|','link','unlink','|',
        'insertimage','emotion','scrawl','insertvideo','music','attachment','map',
        'insertcode','|',
        'horizontal','inserttable','|',
        'print','preview','searchreplace']]")); ?>
<?php $form = $this->beginWidget('HouseForm', array('htmlOptions' => array('class' => 'form-horizontal'))) ?>
<div class="tabbale">
    <ul class="nav nav-tabs nav-tabs-lg">
        <li class="active">
            <a href="#tab_1" data-toggle="tab"> 客户信息 </a>
        </li>
        <li>
            <a href="#tab_2" data-toggle="tab"> 房源信息 </a>
        </li>
        <li>
            <a href="#tab_3" data-toggle="tab"> 人员信息 </a>
        </li>
    </ul>
    <div class="tab-content col-md-12" style="padding-top:20px;">
    <div class="tab-pane col-md-12 active" id="tab_1">
      <div class="form-group">
          <label class="col-md-2 control-label">客户姓名</label>
          <div class="col-md-4">
              <?php echo $form->textField($article, 'name', array('class' => 'form-control')); ?>
          </div>
          <div class="col-md-2"><?php echo $form->error($article, 'name') ?></div>
      </div>
      <div class="form-group">
          <label class="col-md-2 control-label">客户性别</label>
          <div class="col-md-4">
              <?php echo $form->radioButtonList($article, 'sex', UserExt::$sex, array('separator' => '')); ?>
          </div>
          <div class="col-md-2"><?php echo $form->error($article, 'sex') ?></div>
      </div>
      <div class="form-group">
          <label class="col-md-2 control-label">客户联系方式</label>
          <div class="col-md-4">
              <?php echo $form->textField($article, 'phone', array('class' => 'form-control')); ?>
          </div>
          <div class="col-md-2"><?php echo $form->error($article, 'phone') ?></div>
      </div>
      <div class="form-group">
          <label class="col-md-2 control-label">带看时间<span class="required" aria-required="true">*</span></label>
          <div class="col-md-4">
              <div class="input-group date form_datetime">
                        <?php echo $form->textField($article,'time',array('class'=>'form-control','value'=>($article->time?date('Y-m-d',$article->time):''))); ?>
                        <span class="input-group-btn">
                          <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                       </span>
                    </div>
          </div>
      </div>
      <div class="form-group">
          <label class="col-md-2 control-label">客户备注</label>
          <div class="col-md-4">
              <?php echo $form->textarea($article, 'note', array('class' => 'form-control')); ?>
          </div>
          <div class="col-md-2"><?php echo $form->error($article, 'note') ?></div>
      </div>
      <div class="form-group">
          <label class="col-md-2 control-label">到访方式</label>
          <div class="col-md-4">
              <?php echo $form->radioButtonList($article, 'visit_way', [0=>'自驾',1=>'班车'], array('separator' => '')); ?>
          </div>
          <div class="col-md-2"><?php echo $form->error($article, 'visit_way') ?></div>
      </div>
      <div class="form-group">
          <label class="col-md-2 control-label">来访人数</label>
          <div class="col-md-4">
              <?php echo $form->textField($article, 'visit_num', array('class' => 'form-control')); ?>
          </div>
          <div class="col-md-2"><?php echo $form->error($article, 'visit_num') ?></div>
      </div>
      
    </div>
    <div class="tab-pane col-md-12" id="tab_2">

    <div class="form-group">
        <label class="col-md-2 control-label">楼盘</label>
        <div class="col-md-4">
            <?php echo $form->dropDownList($article, 'hid', CHtml::listData(PlotExt::model()->normal()->findAll(),'id','title'), array('class' => 'form-control', 'encode' => false,'disabled'=>'disabled')); ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($article, 'hid') ?></div>
    </div>
    <div class="form-group">
          <label class="col-md-2 control-label">状态</label>
          <div class="col-md-4">
              <?php echo $form->radioButtonList($article, 'status', SubExt::$status, array('separator' => '')); ?>
          </div>
          <div class="col-md-2"><?php echo $form->error($article, 'status') ?></div>
      </div>
    
    <?php foreach (['认筹金'=>'rcj','房号'=>'house_no','面积'=>'size','合同总价'=>'sale_price','定金'=>'ding_price','折佣金额'=>'zy_price','渠道佣金'=>'yj_price','回款金额'=>'hk_price',] as $key => $value) {?>
      <div class="form-group">
        <label class="col-md-2 control-label"><?=$key?></label>
        <div class="col-md-4">
            <?php echo $form->textField($article, $value, array('class' => 'form-control')); ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($article, $value) ?></div>
    </div>
    <?php } ?>
    </div>
    <div class="tab-pane col-md-12" id="tab_3">
      <div class="form-group">
        <label class="col-md-2 control-label">经纪人</label>
        <div class="col-md-4">
            <?php echo $form->dropDownList($article, 'uid', CHtml::listData(UserExt::model()->normal()->findAll('type>1'),'id','name'), array('class' => 'form-control', 'encode' => false)); ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($article, 'uid') ?></div>
    </div>
      <div class="form-group">
        <label class="col-md-2 control-label">市场对接人</label>
        <div class="col-md-4">
            <?php echo $form->dropDownList($article, 'market_uid', CHtml::listData(StaffExt::model()->normal()->findAll(),'id','name'), array('class' => 'form-control select2', 'empty'=>'请选择','encode' => false)); ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($article, 'market_uid') ?></div>
    </div>
    <div class="form-group">
        <label class="col-md-2 control-label">案场助理</label>
        <div class="col-md-4">
            <?php echo $form->dropDownList($article, 'an_uid', CHtml::listData(StaffExt::model()->normal()->findAll(),'id','name'), array('class' => 'form-control select2', 'empty'=>'请选择','encode' => false)); ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($article, 'an_uid') ?></div>
    </div>
    <div class="form-group">
        <label class="col-md-2 control-label">案场销售</label>
        <div class="col-md-4">
            <?php echo $form->dropDownList($article, 'sale_uid', CHtml::listData(StaffExt::model()->normal()->findAll(),'id','name'), array('class' => 'form-control select2', 'empty'=>'请选择','encode' => false)); ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($article, 'sale_uid') ?></div>
    </div>
    </div>
    </div>
    
    
    <div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <button type="submit" class="btn green">保存</button>
            <?php echo CHtml::link('返回',$this->createUrl('list'), array('class' => 'btn default')) ?>
        </div>
    </div>
</div>
    
</div>


<?php
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bootbox/bootbox.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bmap.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/static/admin/pages/scripts/map.js', CClientScript::POS_END);
?>

<?php $this->endWidget(); ?>

<?php
//Select2
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/select2/select2.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile('/static/global/plugins/select2/select2.css');
Yii::app()->clientScript->registerCssFile('/static/admin/pages/css/select2_custom.css');

//boostrap datetimepicker
Yii::app()->clientScript->registerCssFile('/static/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js', CClientScript::POS_END, array('charset'=> 'utf-8'));

// Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bootbox/bootbox.min.js', CClientScript::POS_END);

$js = "
            $(function(){
               $('.select2').select2({
                  placeholder: '请选择',
                  allowClear: true
               });

                 $('.form_datetime').datetimepicker({
                     autoclose: true,
                     isRTL: Metronic.isRTL(),
                     format: 'yyyy-mm-dd',
                     minView: 'month',
                     language: 'zh-CN',
                     pickerPosition: (Metronic.isRTL() ? 'bottom-right' : 'bottom-left'),
                 });

            });


            ";

Yii::app()->clientScript->registerScript('add',$js,CClientScript::POS_END);
?>