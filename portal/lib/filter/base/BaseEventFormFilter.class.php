<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * Event filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormFilterGeneratedTemplate.php 13459 2008-11-28 14:48:12Z fabien $
 */
class BaseEventFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'fmid'                         => new sfWidgetFormFilterInput(),
      'name'                         => new sfWidgetFormFilterInput(),
      'location'                     => new sfWidgetFormFilterInput(),
      'course_type_id'               => new sfWidgetFormFilterInput(),
      'start_date'                   => new sfWidgetFormFilterInput(),
      'end_date'                     => new sfWidgetFormFilterInput(),
      'max_enrollment'               => new sfWidgetFormFilterInput(),
      'current_enrollment'           => new sfWidgetFormFilterInput(),
      'max_waitlist'                 => new sfWidgetFormFilterInput(),
      'current_waitlist'             => new sfWidgetFormFilterInput(),
      'max_assisting_enrollment'     => new sfWidgetFormFilterInput(),
      'current_assisting_enrollment' => new sfWidgetFormFilterInput(),
      'max_assisting_waitlist'       => new sfWidgetFormFilterInput(),
      'current_assisting_waitlist'   => new sfWidgetFormFilterInput(),
      'booking_link'                 => new sfWidgetFormFilterInput(),
      'leader_name'                  => new sfWidgetFormFilterInput(),
      'created_at'                   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'updated_at'                   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => true)),
      'extra1'                       => new sfWidgetFormFilterInput(),
      'extra2'                       => new sfWidgetFormFilterInput(),
      'extra3'                       => new sfWidgetFormFilterInput(),
      'extra4'                       => new sfWidgetFormFilterInput(),
      'extra5'                       => new sfWidgetFormFilterInput(),
      'extra6'                       => new sfWidgetFormFilterInput(),
      'extra7'                       => new sfWidgetFormFilterInput(),
      'extra8'                       => new sfWidgetFormFilterInput(),
      'extra9'                       => new sfWidgetFormFilterInput(),
      'extra10'                      => new sfWidgetFormFilterInput(),
      'extra11'                      => new sfWidgetFormFilterInput(),
      'extra12'                      => new sfWidgetFormFilterInput(),
      'extra13'                      => new sfWidgetFormFilterInput(),
      'extra14'                      => new sfWidgetFormFilterInput(),
      'extra15'                      => new sfWidgetFormFilterInput(),
      'extra16'                      => new sfWidgetFormFilterInput(),
      'extra17'                      => new sfWidgetFormFilterInput(),
      'extra18'                      => new sfWidgetFormFilterInput(),
      'extra19'                      => new sfWidgetFormFilterInput(),
      'extra20'                      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'fmid'                         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'name'                         => new sfValidatorPass(array('required' => false)),
      'location'                     => new sfValidatorPass(array('required' => false)),
      'course_type_id'               => new sfValidatorPass(array('required' => false)),
      'start_date'                   => new sfValidatorPass(array('required' => false)),
      'end_date'                     => new sfValidatorPass(array('required' => false)),
      'max_enrollment'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'current_enrollment'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'max_waitlist'                 => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'current_waitlist'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'max_assisting_enrollment'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'current_assisting_enrollment' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'max_assisting_waitlist'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'current_assisting_waitlist'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'booking_link'                 => new sfValidatorPass(array('required' => false)),
      'leader_name'                  => new sfValidatorPass(array('required' => false)),
      'created_at'                   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'updated_at'                   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'extra1'                       => new sfValidatorPass(array('required' => false)),
      'extra2'                       => new sfValidatorPass(array('required' => false)),
      'extra3'                       => new sfValidatorPass(array('required' => false)),
      'extra4'                       => new sfValidatorPass(array('required' => false)),
      'extra5'                       => new sfValidatorPass(array('required' => false)),
      'extra6'                       => new sfValidatorPass(array('required' => false)),
      'extra7'                       => new sfValidatorPass(array('required' => false)),
      'extra8'                       => new sfValidatorPass(array('required' => false)),
      'extra9'                       => new sfValidatorPass(array('required' => false)),
      'extra10'                      => new sfValidatorPass(array('required' => false)),
      'extra11'                      => new sfValidatorPass(array('required' => false)),
      'extra12'                      => new sfValidatorPass(array('required' => false)),
      'extra13'                      => new sfValidatorPass(array('required' => false)),
      'extra14'                      => new sfValidatorPass(array('required' => false)),
      'extra15'                      => new sfValidatorPass(array('required' => false)),
      'extra16'                      => new sfValidatorPass(array('required' => false)),
      'extra17'                      => new sfValidatorPass(array('required' => false)),
      'extra18'                      => new sfValidatorPass(array('required' => false)),
      'extra19'                      => new sfValidatorPass(array('required' => false)),
      'extra20'                      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('event_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Event';
  }

  public function getFields()
  {
    return array(
      'id'                           => 'Number',
      'fmid'                         => 'Number',
      'name'                         => 'Text',
      'location'                     => 'Text',
      'course_type_id'               => 'Text',
      'start_date'                   => 'Text',
      'end_date'                     => 'Text',
      'max_enrollment'               => 'Number',
      'current_enrollment'           => 'Number',
      'max_waitlist'                 => 'Number',
      'current_waitlist'             => 'Number',
      'max_assisting_enrollment'     => 'Number',
      'current_assisting_enrollment' => 'Number',
      'max_assisting_waitlist'       => 'Number',
      'current_assisting_waitlist'   => 'Number',
      'booking_link'                 => 'Text',
      'leader_name'                  => 'Text',
      'created_at'                   => 'Date',
      'updated_at'                   => 'Date',
      'extra1'                       => 'Text',
      'extra2'                       => 'Text',
      'extra3'                       => 'Text',
      'extra4'                       => 'Text',
      'extra5'                       => 'Text',
      'extra6'                       => 'Text',
      'extra7'                       => 'Text',
      'extra8'                       => 'Text',
      'extra9'                       => 'Text',
      'extra10'                      => 'Text',
      'extra11'                      => 'Text',
      'extra12'                      => 'Text',
      'extra13'                      => 'Text',
      'extra14'                      => 'Text',
      'extra15'                      => 'Text',
      'extra16'                      => 'Text',
      'extra17'                      => 'Text',
      'extra18'                      => 'Text',
      'extra19'                      => 'Text',
      'extra20'                      => 'Text',
    );
  }
}
