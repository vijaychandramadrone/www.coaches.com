<?php

/**
 * Enrollment form base class.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 12815 2008-11-09 10:43:58Z fabien $
 */
class BaseEnrollmentForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'student_id' => new sfWidgetFormPropelChoice(array('model' => 'Student', 'add_empty' => true)),
      'event_id'   => new sfWidgetFormPropelChoice(array('model' => 'Event', 'add_empty' => true)),
      'date'       => new sfWidgetFormDateTime(),
      'start_date' => new sfWidgetFormInput(),
      'end_date'   => new sfWidgetFormInput(),
      'type'       => new sfWidgetFormInput(),
      'updated_at' => new sfWidgetFormDateTime(),
      'created_at' => new sfWidgetFormDateTime(),
      'extra1'     => new sfWidgetFormTextarea(),
      'extra2'     => new sfWidgetFormTextarea(),
      'extra3'     => new sfWidgetFormTextarea(),
      'extra4'     => new sfWidgetFormTextarea(),
      'extra5'     => new sfWidgetFormTextarea(),
      'extra6'     => new sfWidgetFormTextarea(),
      'extra7'     => new sfWidgetFormTextarea(),
      'extra8'     => new sfWidgetFormTextarea(),
      'extra9'     => new sfWidgetFormTextarea(),
      'extra10'    => new sfWidgetFormTextarea(),
      'extra11'    => new sfWidgetFormTextarea(),
      'extra12'    => new sfWidgetFormTextarea(),
      'extra13'    => new sfWidgetFormTextarea(),
      'extra14'    => new sfWidgetFormTextarea(),
      'extra15'    => new sfWidgetFormTextarea(),
      'extra16'    => new sfWidgetFormTextarea(),
      'extra17'    => new sfWidgetFormTextarea(),
      'extra18'    => new sfWidgetFormTextarea(),
      'extra19'    => new sfWidgetFormTextarea(),
      'extra20'    => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorPropelChoice(array('model' => 'Enrollment', 'column' => 'id', 'required' => false)),
      'student_id' => new sfValidatorPropelChoice(array('model' => 'Student', 'column' => 'id', 'required' => false)),
      'event_id'   => new sfValidatorPropelChoice(array('model' => 'Event', 'column' => 'id', 'required' => false)),
      'date'       => new sfValidatorDateTime(array('required' => false)),
      'start_date' => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'end_date'   => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'type'       => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'updated_at' => new sfValidatorDateTime(array('required' => false)),
      'created_at' => new sfValidatorDateTime(array('required' => false)),
      'extra1'     => new sfValidatorString(array('required' => false)),
      'extra2'     => new sfValidatorString(array('required' => false)),
      'extra3'     => new sfValidatorString(array('required' => false)),
      'extra4'     => new sfValidatorString(array('required' => false)),
      'extra5'     => new sfValidatorString(array('required' => false)),
      'extra6'     => new sfValidatorString(array('required' => false)),
      'extra7'     => new sfValidatorString(array('required' => false)),
      'extra8'     => new sfValidatorString(array('required' => false)),
      'extra9'     => new sfValidatorString(array('required' => false)),
      'extra10'    => new sfValidatorString(array('required' => false)),
      'extra11'    => new sfValidatorString(array('required' => false)),
      'extra12'    => new sfValidatorString(array('required' => false)),
      'extra13'    => new sfValidatorString(array('required' => false)),
      'extra14'    => new sfValidatorString(array('required' => false)),
      'extra15'    => new sfValidatorString(array('required' => false)),
      'extra16'    => new sfValidatorString(array('required' => false)),
      'extra17'    => new sfValidatorString(array('required' => false)),
      'extra18'    => new sfValidatorString(array('required' => false)),
      'extra19'    => new sfValidatorString(array('required' => false)),
      'extra20'    => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('enrollment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Enrollment';
  }


}
