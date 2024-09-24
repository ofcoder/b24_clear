<?php
namespace Kb\Learning\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class TestTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> COURSE_ID int mandatory
 * <li> TIMESTAMP_X datetime optional default current datetime
 * <li> SORT int optional default 500
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> NAME string(255) mandatory
 * <li> DESCRIPTION text optional
 * <li> DESCRIPTION_TYPE enum ('text', 'html') optional default 'text'
 * <li> ATTEMPT_LIMIT int optional default 0
 * <li> TIME_LIMIT int optional default 0
 * <li> COMPLETED_SCORE int optional
 * <li> QUESTIONS_FROM string(1) optional default 'A'
 * <li> QUESTIONS_FROM_ID int optional default 0
 * <li> QUESTIONS_AMOUNT int optional default 0
 * <li> RANDOM_QUESTIONS bool ('N', 'Y') optional default 'Y'
 * <li> RANDOM_ANSWERS bool ('N', 'Y') optional default 'Y'
 * <li> APPROVED bool ('N', 'Y') optional default 'Y'
 * <li> INCLUDE_SELF_TEST bool ('N', 'Y') optional default 'N'
 * <li> PASSAGE_TYPE string(1) optional default '0'
 * <li> PREVIOUS_TEST_ID int optional
 * <li> PREVIOUS_TEST_SCORE int optional default 0
 * <li> INCORRECT_CONTROL bool ('N', 'Y') optional default 'N'
 * <li> CURRENT_INDICATION int optional default 0
 * <li> FINAL_INDICATION int optional default 0
 * <li> MIN_TIME_BETWEEN_ATTEMPTS int optional default 0
 * <li> SHOW_ERRORS bool ('N', 'Y') optional default 'N'
 * <li> NEXT_QUESTION_ON_ERROR bool ('N', 'Y') optional default 'Y'
 * </ul>
 *
 * @package Bitrix\Learn
 **/

class TestTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_test';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('TEST_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'COURSE_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('TEST_ENTITY_COURSE_ID_FIELD'),
                ]
            ),
            new DatetimeField(
                'TIMESTAMP_X',
                [
                    'default' => function()
                    {
                        return new DateTime();
                    },
                    'title' => Loc::getMessage('TEST_ENTITY_TIMESTAMP_X_FIELD'),
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 500,
                    'title' => Loc::getMessage('TEST_ENTITY_SORT_FIELD'),
                ]
            ),
            new BooleanField(
                'ACTIVE',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                    'title' => Loc::getMessage('TEST_ENTITY_ACTIVE_FIELD'),
                ]
            ),
            new StringField(
                'NAME',
                [
                    'required' => true,
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 255),
                        ];
                    },
                    'title' => Loc::getMessage('TEST_ENTITY_NAME_FIELD'),
                ]
            ),
            new TextField(
                'DESCRIPTION',
                [
                    'title' => Loc::getMessage('TEST_ENTITY_DESCRIPTION_FIELD'),
                ]
            ),
            new StringField(
                'DESCRIPTION_TYPE',
                [
                    'values' => array('text', 'html'),
                    'default' => 'text',
                    'title' => Loc::getMessage('TEST_ENTITY_DESCRIPTION_TYPE_FIELD'),
                ]
            ),
            new IntegerField(
                'ATTEMPT_LIMIT',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_ATTEMPT_LIMIT_FIELD'),
                ]
            ),
            new IntegerField(
                'TIME_LIMIT',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_TIME_LIMIT_FIELD'),
                ]
            ),
            new IntegerField(
                'COMPLETED_SCORE',
                [
                    'title' => Loc::getMessage('TEST_ENTITY_COMPLETED_SCORE_FIELD'),
                ]
            ),
            new StringField(
                'QUESTIONS_FROM',
                [
                    'default' => 'A',
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 1),
                        ];
                    },
                    'title' => Loc::getMessage('TEST_ENTITY_QUESTIONS_FROM_FIELD'),
                ]
            ),
            new IntegerField(
                'QUESTIONS_FROM_ID',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_QUESTIONS_FROM_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'QUESTIONS_AMOUNT',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_QUESTIONS_AMOUNT_FIELD'),
                ]
            ),
            new BooleanField(
                'RANDOM_QUESTIONS',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                    'title' => Loc::getMessage('TEST_ENTITY_RANDOM_QUESTIONS_FIELD'),
                ]
            ),
            new BooleanField(
                'RANDOM_ANSWERS',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                    'title' => Loc::getMessage('TEST_ENTITY_RANDOM_ANSWERS_FIELD'),
                ]
            ),
            new BooleanField(
                'APPROVED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                    'title' => Loc::getMessage('TEST_ENTITY_APPROVED_FIELD'),
                ]
            ),
            new BooleanField(
                'INCLUDE_SELF_TEST',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('TEST_ENTITY_INCLUDE_SELF_TEST_FIELD'),
                ]
            ),
            new StringField(
                'PASSAGE_TYPE',
                [
                    'default' => '0',
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 1),
                        ];
                    },
                    'title' => Loc::getMessage('TEST_ENTITY_PASSAGE_TYPE_FIELD'),
                ]
            ),
            new IntegerField(
                'PREVIOUS_TEST_ID',
                [
                    'title' => Loc::getMessage('TEST_ENTITY_PREVIOUS_TEST_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'PREVIOUS_TEST_SCORE',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_PREVIOUS_TEST_SCORE_FIELD'),
                ]
            ),
            new BooleanField(
                'INCORRECT_CONTROL',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('TEST_ENTITY_INCORRECT_CONTROL_FIELD'),
                ]
            ),
            new IntegerField(
                'CURRENT_INDICATION',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_CURRENT_INDICATION_FIELD'),
                ]
            ),
            new IntegerField(
                'FINAL_INDICATION',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_FINAL_INDICATION_FIELD'),
                ]
            ),
            new IntegerField(
                'MIN_TIME_BETWEEN_ATTEMPTS',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_ENTITY_MIN_TIME_BETWEEN_ATTEMPTS_FIELD'),
                ]
            ),
            new BooleanField(
                'SHOW_ERRORS',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('TEST_ENTITY_SHOW_ERRORS_FIELD'),
                ]
            ),
            new BooleanField(
                'NEXT_QUESTION_ON_ERROR',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                    'title' => Loc::getMessage('TEST_ENTITY_NEXT_QUESTION_ON_ERROR_FIELD'),
                ]
            ),
            new ReferenceField(
                'COURSE',
                'Kb\Learning\Entity\Course',
                ['=this.COURSE_ID' => 'ref.ID'],
                ['join_type' => 'LEFT']
            ),
        ];
    }
}