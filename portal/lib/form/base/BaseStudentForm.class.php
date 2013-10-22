<?php

/**
 * Student form base class.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 12815 2008-11-09 10:43:58Z fabien $
 */
class BaseStudentForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'fmid'                   => new sfWidgetFormInput(),
      'first_name'             => new sfWidgetFormInput(),
      'last_name'              => new sfWidgetFormInput(),
      'email'                  => new sfWidgetFormInput(),
      'reset_key'              => new sfWidgetFormInput(),
      'new_email'              => new sfWidgetFormInput(),
      'new_email_request_time' => new sfWidgetFormDateTime(),
      'home_address'           => new sfWidgetFormTextarea(),
      'city'                   => new sfWidgetFormTextarea(),
      'state_prov'             => new sfWidgetFormTextarea(),
      'country'                => new sfWidgetFormTextarea(),
      'zip_postal'             => new sfWidgetFormTextarea(),
      'home_phone'             => new sfWidgetFormInput(),
      'cell_phone'             => new sfWidgetFormInput(),
      'business_phone'         => new sfWidgetFormInput(),
      'level'                  => new sfWidgetFormInput(),
      'password'               => new sfWidgetFormInput(),
      'salt'                   => new sfWidgetFormInput(),
      'created_at'             => new sfWidgetFormDateTime(),
      'updated_at'             => new sfWidgetFormDateTime(),
      'extra1'                 => new sfWidgetFormTextarea(),
      'extra2'                 => new sfWidgetFormTextarea(),
      'extra3'                 => new sfWidgetFormTextarea(),
      'extra4'                 => new sfWidgetFormTextarea(),
      'extra5'                 => new sfWidgetFormTextarea(),
      'extra6'                 => new sfWidgetFormTextarea(),
      'extra7'                 => new sfWidgetFormTextarea(),
      'extra8'                 => new sfWidgetFormTextarea(),
      'extra9'                 => new sfWidgetFormTextarea(),
      'extra10'                => new sfWidgetFormTextarea(),
      'extra11'                => new sfWidgetFormTextarea(),
      'extra12'                => new sfWidgetFormTextarea(),
      'extra13'                => new sfWidgetFormTextarea(),
      'extra14'                => new sfWidgetFormTextarea(),
      'extra15'                => new sfWidgetFormTextarea(),
      'extra16'                => new sfWidgetFormTextarea(),
      'extra17'                => new sfWidgetFormTextarea(),
      'extra18'                => new sfWidgetFormTextarea(),
      'extra19'                => new sfWidgetFormTextarea(),
      'extra20'                => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorPropelChoice(array('model' => 'Student', 'column' => 'id', 'required' => false)),
      'fmid'                   => new sfValidatorInteger(array('required' => false)),
      'first_name'             => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'last_name'              => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'email'                  => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'reset_key'              => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'new_email'              => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'new_email_request_time' => new sfValidatorDateTime(array('required' => false)),
      'home_address'           => new sfValidatorString(array('required' => false)),
      'city'                   => new sfValidatorString(array('required' => false)),
      'state_prov'             => new sfValidatorString(array('required' => false)),
      'country'                => new sfValidatorString(array('required' => false)),
      'zip_postal'             => new sfValidatorString(array('required' => false)),
      'home_phone'             => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'cell_phone'             => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'business_phone'         => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'level'                  => new sfValidatorInteger(array('required' => false)),
      'password'               => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'salt'                   => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'created_at'             => new sfValidatorDateTime(array('required' => false)),
      'updated_at'             => new sfValidatorDateTime(array('required' => false)),
      'extra1'                 => new sfValidatorString(array('required' => false)),
      'extra2'                 => new sfValidatorString(array('required' => false)),
      'extra3'                 => new sfValidatorString(array('required' => false)),
      'extra4'                 => new sfValidatorString(array('required' => false)),
      'extra5'                 => new sfValidatorString(array('required' => false)),
      'extra6'                 => new sfValidatorString(array('required' => false)),
      'extra7'                 => new sfValidatorString(array('required' => false)),
      'extra8'                 => new sfValidatorString(array('required' => false)),
      'extra9'                 => new sfValidatorString(array('required' => false)),
      'extra10'                => new sfValidatorString(array('required' => false)),
      'extra11'                => new sfValidatorString(array('required' => false)),
      'extra12'                => new sfValidatorString(array('required' => false)),
      'extra13'                => new sfValidatorString(array('required' => false)),
      'extra14'                => new sfValidatorString(array('required' => false)),
      'extra15'                => new sfValidatorString(array('required' => false)),
      'extra16'                => new sfValidatorString(array('required' => false)),
      'extra17'                => new sfValidatorString(array('required' => false)),
      'extra18'                => new sfValidatorString(array('required' => false)),
      'extra19'                => new sfValidatorString(array('required' => false)),
      'extra20'                => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('student[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Student';
  }


}
