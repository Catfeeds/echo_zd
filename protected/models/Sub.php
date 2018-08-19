<?php

/**
 * This is the model class for table "sub".
 *
 * The followings are the available columns in table 'sub':
 * @property integer $id
 * @property string $hid
 * @property integer $uid
 * @property integer $cid
 * @property string $qr
 * @property string $true_phone
 * @property string $fx_phone
 * @property string $plot_title
 * @property integer $an_uid
 * @property string $an_phone
 * @property integer $market_uid
 * @property string $market_phone
 * @property integer $time
 * @property string $market_staff
 * @property string $sale_price
 * @property string $name
 * @property string $phone
 * @property string $notice
 * @property string $code
 * @property integer $fk_type
 * @property string $hk_price
 * @property string $zy_price
 * @property string $yj_price
 * @property string $ding_price
 * @property string $size
 * @property string $house_no
 * @property string $rcj
 * @property string $company_name
 * @property integer $visit_num
 * @property integer $visit_way
 * @property string $sale_phone
 * @property integer $sale_uid
 * @property integer $sex
 * @property integer $is_check
 * @property integer $is_only_sub
 * @property string $note
 * @property integer $status
 * @property integer $deleted
 * @property integer $sort
 * @property integer $created
 * @property integer $updated
 */
class Sub extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'sub';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('uid, status, created, updated', 'required'),
			array('uid, cid, an_uid, market_uid, time, fk_type, visit_num, visit_way, sale_uid, sex, is_check, is_only_sub, status, deleted, sort, created, updated', 'numerical', 'integerOnly'=>true),
			array('hid, plot_title, market_staff, sale_price, name, hk_price, zy_price, yj_price, ding_price, size, house_no, rcj', 'length', 'max'=>100),
			array('qr, company_name, note', 'length', 'max'=>255),
			array('true_phone, fx_phone, an_phone, market_phone, phone, sale_phone', 'length', 'max'=>20),
			array('notice', 'length', 'max'=>12),
			array('code', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, hid, uid, cid, qr, true_phone, fx_phone, plot_title, an_uid, an_phone, market_uid, market_phone, time, market_staff, sale_price, name, phone, notice, code, fk_type, hk_price, zy_price, yj_price, ding_price, size, house_no, rcj, company_name, visit_num, visit_way, sale_phone, sale_uid, sex, is_check, is_only_sub, note, status, deleted, sort, created, updated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'hid' => 'Hid',
			'uid' => 'Uid',
			'cid' => 'Cid',
			'qr' => 'Qr',
			'true_phone' => 'True Phone',
			'fx_phone' => 'Fx Phone',
			'plot_title' => 'Plot Title',
			'an_uid' => 'An Uid',
			'an_phone' => 'An Phone',
			'market_uid' => 'Market Uid',
			'market_phone' => 'Market Phone',
			'time' => 'Time',
			'market_staff' => 'Market Staff',
			'sale_price' => 'Sale Price',
			'name' => 'Name',
			'phone' => 'Phone',
			'notice' => 'Notice',
			'code' => 'Code',
			'fk_type' => 'Fk Type',
			'hk_price' => 'Hk Price',
			'zy_price' => 'Zy Price',
			'yj_price' => 'Yj Price',
			'ding_price' => 'Ding Price',
			'size' => 'Size',
			'house_no' => 'House No',
			'rcj' => 'Rcj',
			'company_name' => 'Company Name',
			'visit_num' => 'Visit Num',
			'visit_way' => 'Visit Way',
			'sale_phone' => 'Sale Phone',
			'sale_uid' => 'Sale Uid',
			'sex' => 'Sex',
			'is_check' => 'Is Check',
			'is_only_sub' => 'Is Only Sub',
			'note' => 'Note',
			'status' => 'Status',
			'deleted' => 'Deleted',
			'sort' => 'Sort',
			'created' => 'Created',
			'updated' => 'Updated',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('hid',$this->hid,true);
		$criteria->compare('uid',$this->uid);
		$criteria->compare('cid',$this->cid);
		$criteria->compare('qr',$this->qr,true);
		$criteria->compare('true_phone',$this->true_phone,true);
		$criteria->compare('fx_phone',$this->fx_phone,true);
		$criteria->compare('plot_title',$this->plot_title,true);
		$criteria->compare('an_uid',$this->an_uid);
		$criteria->compare('an_phone',$this->an_phone,true);
		$criteria->compare('market_uid',$this->market_uid);
		$criteria->compare('market_phone',$this->market_phone,true);
		$criteria->compare('time',$this->time);
		$criteria->compare('market_staff',$this->market_staff,true);
		$criteria->compare('sale_price',$this->sale_price,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('notice',$this->notice,true);
		$criteria->compare('code',$this->code,true);
		$criteria->compare('fk_type',$this->fk_type);
		$criteria->compare('hk_price',$this->hk_price,true);
		$criteria->compare('zy_price',$this->zy_price,true);
		$criteria->compare('yj_price',$this->yj_price,true);
		$criteria->compare('ding_price',$this->ding_price,true);
		$criteria->compare('size',$this->size,true);
		$criteria->compare('house_no',$this->house_no,true);
		$criteria->compare('rcj',$this->rcj,true);
		$criteria->compare('company_name',$this->company_name,true);
		$criteria->compare('visit_num',$this->visit_num);
		$criteria->compare('visit_way',$this->visit_way);
		$criteria->compare('sale_phone',$this->sale_phone,true);
		$criteria->compare('sale_uid',$this->sale_uid);
		$criteria->compare('sex',$this->sex);
		$criteria->compare('is_check',$this->is_check);
		$criteria->compare('is_only_sub',$this->is_only_sub);
		$criteria->compare('note',$this->note,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('deleted',$this->deleted);
		$criteria->compare('sort',$this->sort);
		$criteria->compare('created',$this->created);
		$criteria->compare('updated',$this->updated);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Sub the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
