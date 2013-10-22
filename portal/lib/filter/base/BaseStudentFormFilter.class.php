<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * Student filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormFilterGeneratedTemplate.php 13459 2008-11-28 14:48:12Z fabien $
 */
class BaseStudentFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'fmid'                   => new sfWidgetFormFilterInput(),
      'first_name'             => new sfWidgetFormFilterInput(),
      'last_name'              => new sfWidgetFormFilterInput(),
      'email'                  => new sfWidgetFormFilterInput(),
      'reset_key'              => new sfWidgetFormFilterInput(),
      'new_email'              => new sfWidgetFormFilterInput(),
      'new_email_request_time' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'home_address'           => new sfWidgetFormFilterInput(),
      'city'                   => new sfWidgetFormFilterInput(),
      'state_prov'             => new sfWidgetFormFilterInput(),
      'country'                => new sfWidgetFormFilterInput(),
      'zip_postal'             => new sfWidgetFormFilterInput(),
      'home_phone'             => new sfWidgetFormFilterInput(),
      'cell_phone'             => new sfWidgetFormFilterInput(),
      'business_phone'         => new sfWidgetFormFilterInput(),
      'level'                  => new sfWidgetFormFilterInput(),
      'password'               => new sfWidgetFormFilterInput(),
      'salt'                   => new sfWidgetFormFilterInput(),
      'created_at'             => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'updated_at'             => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'extra1'                 => new sfWidgetFormFilterInput(),
      'extra2'                 => new sfWidgetFormFilterInput(),
      'extra3'                 => new sfWidgetFormFilterInput(),
      'extra4'                 => new sfWidgetFormFilterInput(),
      'extra5'                 => new sfWidgetFormFilterInput(),
      'extra6'                 => new sfWidgetFormFilterInput(),
      'extra7'                 => new sfWidgetFormFilterInput(),
      'extra8'                 => new sfWidgetFormFilterInput(),
      'extra9'                 => new sfWidgetFormFilterInput(),
      'extra10'                => new sfWidgetFormFilterInput(),
      'extra11'                => new sfWidgetFormFilterInput(),
      'extra12'                => new sfWidgetFormFilterInput(),
      'extra13'                => new sfWidgetFormFilterInput(),
      'extra14'                => new sfWidgetFormFilterInput(),
      'extra15'                => new sfWidgetFormFilterInput(),
      'extra16'                => new sfWidgetFormFilterInput(),
      'extra17'                => new sfWidgetFormFilterInput(),
      'extra18'                => new sfWidgetFormFilterInput(),
      'extra19'                => new sfWidgetFormFilterInput(),
      'extra20'                => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'fmid'                   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'first_name'             => new sfValidatorPass(array('required' => false)),
      'last_name'              => new sfValidatorPass(array('required' => false)),
      'email'                  => new sfValidatorPass(array('required' => false)),
      'reset_key'              => new sfValidatorPass(array('required' => false)),
      'new_email'              => new sfValidatorPass(array('required' => false)),
      'new_email_request_time' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'home_address'           => new sfValidatorPass(array('required' => false)),
      'city'                   => new sfValidatorPass(array('required' => false)),
      'state_prov'             => new sfValidatorPass(array('required' => false)),
      'country'                => new sfValidatorPass(array('required' => false)),
      'zip_postal'             => new sfValidatorPass(array('required' => false)),
      'home_phone'             => new sfValidatorPass(array('required' => false)),
      'cell_phone'             => new sfValidatorPass(array('required' => false)),
      'business_phone'         => new sfValidatorPass(array('required' => false)),
      'level'                  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'password'               => new sfValidatorPass(array('required' => false)),
      'salt'                   => new sfValidatorPass(array('required' => false)),
      'created_at'             => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'updated_at'             => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'extra1'                 => new sfValidatorPass(array('required' => false)),
      'extra2'                 => new sfValidatorPass(array('required' => false)),
      'extra3'                 => new sfValidatorPass(array('required' => false)),
      'extra4'                 => new sfValidatorPass(array('required' => false)),
      'extra5'                 => new sfValidatorPass(array('required' => false)),
      'extra6'                 => new sfValidatorPass(array('required' => false)),
      'extra7'                 => new sfValidatorPass(array('required' => false)),
      'extra8'                 => new sfValidatorPass(array('required' => false)),
      'extra9'                 => new sfValidatorPass(array('required' => false)),
      'extra10'                => new sfValidatorPass(array('required' => false)),
      'extra11'                => new sfValidatorPass(array('required' => false)),
      'extra12'                => new sfValidatorPass(array('required' => false)),
      'extra13'                => new sfValidatorPass(array('required' => false)),
      'extra14'                => new sfValidatorPass(array('required' => false)),
      'extra15'                => new sfValidatorPass(array('required' => false)),
      'extra16'                => new sfValidatorPass(array('required' => false)),
      'extra17'                => new sfValidatorPass(array('required' => false)),
      'extra18'                => new sfValidatorPass(array('required' => false)),
      'extra19'                => new sfValidatorPass(array('required' => false)),
      'extra20'                => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('student_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Student';
  }

  public function getFields()
  {
    return array(
      'id'                     => 'Number',
      'fmid'                   => 'Number',
      'first_name'             => 'Text',
      'last_name'              => 'Text',
      'email'                  => 'Text',
      'reset_key'              => 'Text',
      'new_email'              => 'Text',
      'new_email_request_time' => 'Date',
      'home_address'           => 'Text',
      'city'                   => 'Text',
      'state_prov'             => 'Text',
      'country'                => 'Text',
      'zip_postal'             => 'Text',
      'home_phone'             => 'Text',
      'cell_phone'             => 'Text',
      'business_phone'         => 'Text',
      'level'                  => 'Number',
      'password'               => 'Text',
      'salt'                   => 'Text',
      'created_at'             => 'Date',
      'updated_at'             => 'Date',
      'extra1'                 => 'Text',
      'extra2'                 => 'Text',
      'extra3'                 => 'Text',
      'extra4'                 => 'Text',
      'extra5'                 => 'Text',
      'extra6'                 => 'Text',
      'extra7'                 => 'Text',
      'extra8'                 => 'Text',
      'extra9'                 => 'Text',
      'extra10'                => 'Text',
      'extra11'                => 'Text',
      'extra12'                => 'Text',
      'extra13'                => 'Text',
      'extra14'                => 'Text',
      'extra15'                => 'Text',
      'extra16'                => 'Text',
      'extra17'                => 'Text',
      'extra18'                => 'Text',
      'extra19'                => 'Text',
      'extra20'                => 'Text',
    );
  }
}
