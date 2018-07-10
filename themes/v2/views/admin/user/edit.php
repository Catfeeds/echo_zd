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
<div class="form-group">
    <label class="col-md-2 control-label">名字<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'name', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'name') ?></div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label">手机号<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'phone', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'phone') ?></div>
</div>
<!-- <div class="form-group">
    <label class="col-md-2 control-label">虚拟号<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'virtual_no', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'virtual_no') ?></div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label">虚拟号分机<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'virtual_no_ext', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'virtual_no_ext') ?></div>
</div> -->
<div class="form-group">
    <label class="col-md-2 control-label">微信号<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'wx', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'wx') ?></div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label">openid<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'openid', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'openid') ?></div>
</div>
<!-- <div class="form-group">
    <label class="col-md-2 control-label">刷新数<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'refresh_num', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'refresh_num') ?></div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label">论坛id<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'qf_uid', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'qf_uid') ?></div>
</div> -->
<div class="form-group">
    <label class="col-md-2 control-label">选择门店</label>
    <div class="col-md-4">
        <?php echo $form->dropDownList($article, 'cid',  CHtml::listData(CompanyExt::model()->normal()->findAll(),'id','name'), array('class'=>'form-control select2','empty'=>'无')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'cid') ?></div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label text-nowrap">认证材料</label>
    <div class="col-md-8">
        <?php $this->widget('FileUpload',array('model'=>$article,'attribute'=>'image','inputName'=>'img','width'=>400,'height'=>300)); ?>
        <span class="help-block">建议尺寸：430*230</span> 
    </div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label text-nowrap">身份证</label>
    <div class="col-md-8">
        <?php $this->widget('FileUpload',array('model'=>$article,'attribute'=>'id_pic','inputName'=>'img','width'=>400,'height'=>300)); ?>
        <span class="help-block">建议尺寸：430*230</span> 
    </div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label">身份证号码<span class="required" aria-required="true">*</span></label>
    <div class="col-md-4">
        <?php echo $form->textField($article, 'id_no', array('class' => 'form-control')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'id_no') ?></div>
</div>

<div class="form-group">
    <label class="col-md-2 control-label">身份</label>
    <div class="col-md-4">
        <?php echo $form->radioButtonList($article, 'type', UserExt::$ids, array('separator' => '')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'type') ?></div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label">状态</label>
    <div class="col-md-4">
        <?php echo $form->radioButtonList($article, 'status', UserExt::$status, array('separator' => '')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'status') ?></div>
</div>
<div class="form-group">
    <label class="col-md-2 control-label">是否店长</label>
    <div class="col-md-4">
        <?php echo $form->radioButtonList($article, 'is_manage', ['否','是'], array('separator' => '')); ?>
    </div>
    <div class="col-md-2"><?php echo $form->error($article, 'is_manage') ?></div>
</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <button type="submit" class="btn green">保存</button>
            <?php echo CHtml::link('返回',$this->createUrl($type=='admin'?'/admin/plot/list':'list'), array('class' => 'btn default')) ?>
        </div>
    </div>
</div>

<?php $this->endWidget() ?>

<?php
$js = "

    var getHousesAjax =
     {
        url: '".$this->createUrl('/admin/plot/AjaxGetHouse')."',"."
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                kw:params
            };
        },
        results:function(data){
            var items = [];

             $.each(data.results,function(){
                var tmp = {
                    id : this.id,
                    text : this.name
                }
                items.push(tmp);
            });

            return {
                results: items
            };
        },
        processResults: function (data, page) {
            var items = [];
             $.each(data.msg,function(){
                var tmp = {
                    id : this.id,
                    text : this.title
                }
                items.push(tmp);
            });
            return {
                results: items
            };
        }
    }
        $(function(){

           $('.select2').select2({
              placeholder: '请选择',
              allowClear: true
           });

				var houses_edit = $('#plot');
				var data = {};
				if( houses_edit.length && houses_edit.data('houses') ){
					data = eval(houses_edit.data('houses'));
				}

				$('#plot').select2({
					multiple:true,
					ajax: getHousesAjax,
					language: 'zh-CN',
					initSelection: function(element, callback){
						callback(data);
					}
				});

             $('.form_datetime').datetimepicker({
                 autoclose: true,
                 isRTL: Metronic.isRTL(),
                 format: 'yyyy-mm-dd hh:ii',
                 // minView: 'm',
                 language: 'zh-CN',
                 pickerPosition: (Metronic.isRTL() ? 'bottom-right' : 'bottom-left'),
             });

             $('.form_datetime1').datetimepicker({
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
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/select2/select2.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/select2/select2_locale_zh-CN.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile('/static/global/plugins/select2/select2.css');
Yii::app()->clientScript->registerCssFile('/static/admin/pages/css/select2_custom.css');

Yii::app()->clientScript->registerScriptFile('/static/admin/pages/scripts/addCustomizeDialog.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile('/static/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js', CClientScript::POS_END, array('charset'=> 'utf-8'));
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bootbox/bootbox.min.js', CClientScript::POS_END);
?>
