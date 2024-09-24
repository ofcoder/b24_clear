<?php
namespace Kb\Learning\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class TestResultTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ATTEMPT_ID int mandatory
 * <li> QUESTION_ID int mandatory
 * <li> RESPONSE text optional
 * <li> POINT int optional default 0
 * <li> CORRECT bool ('N', 'Y') optional default 'N'
 * <li> ANSWERED bool ('N', 'Y') optional default 'N'
 * <li> DATE_INSERT datetime optional
 * </ul>
 *
 * @package Bitrix\Learn
 **/

class TestResultTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_learn_test_result';
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
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'ATTEMPT_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_ATTEMPT_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'QUESTION_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_QUESTION_ID_FIELD'),
                ]
            ),
            new TextField(
                'RESPONSE',
                [
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_RESPONSE_FIELD'),
                ]
            ),
            new IntegerField(
                'POINT',
                [
                    'default' => 0,
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_POINT_FIELD'),
                ]
            ),
            new BooleanField(
                'CORRECT',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_CORRECT_FIELD'),
                ]
            ),
            new BooleanField(
                'ANSWERED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_ANSWERED_FIELD'),
                ]
            ),
            new DatetimeField(
                'DATE_INSERT',
                [
                    'title' => Loc::getMessage('TEST_RESULT_ENTITY_DATE_INSERT_FIELD'),
                    'default_value' => new DateTime
                ]
            ),
            new ReferenceField(
                'ATTEMPT',
                'Kb\Learning\Entity\Attempt',
                ['=this.ATTEMPT_ID' => 'ref.ID'],
                ['join_type' => 'LEFT']
            ),
            new ReferenceField(
                'QUESTION',
                'Kb\Learning\Entity\Question',
                ['=this.QUESTION_ID' => 'ref.ID'],
                ['join_type' => 'LEFT']
            ),
        ];
    }
}