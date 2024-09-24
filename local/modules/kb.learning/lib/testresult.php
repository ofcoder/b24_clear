<?php

namespace Kb\Learning;

use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

class TestResult extends \CTestResult
{
    public function CheckFields(&$arFields, $ID = false)
    {
        global $DB, $APPLICATION;

        if ($ID===false)
        {
            if (is_set($arFields, "ATTEMPT_ID"))
            {
                $r = \CTestAttempt::GetByID($arFields["ATTEMPT_ID"]);
                if(!$r->Fetch())
                {
                    $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_ATTEMPT_ID_EX"), "ERROR_NO_ATTEMPT_ID");
                    return false;
                }
            }
            else
            {
                $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_ATTEMPT_ID"), "EMPTY_ATTEMPT_ID");
                return false;
            }

            if (is_set($arFields, "QUESTION_ID"))
            {
                $r = \CLQuestion::GetByID($arFields["QUESTION_ID"]);
                if(!$r->Fetch())
                {
                    $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_QUESTION_ID"), "EMPTY_QUESTION_ID");
                    return false;
                }
            }
            else
            {
                $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_QUESTION_ID"), "EMPTY_QUESTION_ID");
                return false;
            }
        }

        if (is_set($arFields, "RESPONSE") && is_array($arFields["RESPONSE"]))
        {
            $s = "";
            foreach($arFields["RESPONSE"] as $val)
                $s .= $val.",";
            $arFields["RESPONSE"] = mb_substr($s, 0, -1);
        }

        /*
        if (is_set($arFields, "ANSWERED") && is_set($arFields, "RESPONSE"))
        {
            if ($arFields["ANSWERED"]=="Y" && strlen($arFields["RESPONSE"]) <= 0)
            {
                $APPLICATION->ThrowException(GetMessage("LEARNING_BAD_NO_ANSWERS"), "EMPTY_ANSWERS");
                return false;
            }
        }
        */

        if (is_set($arFields, "CORRECT") && $arFields["CORRECT"] != "Y")
            $arFields["CORRECT"] = "N";

        if (is_set($arFields, "ANSWERED") && is_set($arFields, "RESPONSE") && $arFields["ANSWERED"]=="Y")
        {
            $arFields['DATE_INSERT'] = new DateTime();
        }

        return true;
    }

    public static function AddResponse($TEST_RESULT_ID, $RESPONSE)
    {
        global $DB;

        $TEST_RESULT_ID = intval($TEST_RESULT_ID);
        if ($TEST_RESULT_ID < 1) return false;

        $rsTestResult = \CTestResult::GetList(Array(), Array("ID" => $TEST_RESULT_ID, 'CHECK_PERMISSIONS' => 'N'));

        if ($arTestResult = $rsTestResult->GetNext())
        {
            if ($arTestResult["QUESTION_TYPE"] == "T")
            {
                $arFields = Array(
                    "ANSWERED" => "Y",
                    "RESPONSE" => $RESPONSE,
                    "POINT"=> 0,
                    "CORRECT"=> "N",
                );
            }
            else
            {
                if (!is_array($RESPONSE))
                    $RESPONSE = Array($RESPONSE);

                $strSql =
                    "SELECT A.ID, Q.POINT ".
                    "FROM b_learn_test_result TR ".
                    "INNER JOIN b_learn_question Q ON TR.QUESTION_ID = Q.ID ".
                    "INNER JOIN b_learn_answer A ON Q.ID = A.QUESTION_ID ".
                    "WHERE TR.ID = '".$TEST_RESULT_ID."' ".
                    ($arTestResult["QUESTION_TYPE"] != "R" ? "AND A.CORRECT = 'Y' " : "").
                    "ORDER BY A.SORT ASC, A.ID ASC";

                if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
                    return false;

                $arAnswer = Array();
                while ($arRes = $res->Fetch())
                {
                    $arAnswer[] = $arRes["ID"];
                    $str_POINT = $arRes["POINT"];
                }

                if ($arTestResult["QUESTION_TYPE"] == "R")
                {
                    if ($arAnswer != $RESPONSE)
                        $str_POINT = "0";
                }
                else
                {
                    $t1 = array_diff($arAnswer,$RESPONSE);
                    $t2 = array_diff($RESPONSE,$arAnswer);
                    if ($t1!=$t2 || $t2 != Array())
                        $str_POINT = "0";
                }

                //echo "!".$str_POINT."!";

                $arFields = Array(
                    "ANSWERED" => "Y",
                    "RESPONSE" => $RESPONSE,
                    "POINT"=> $str_POINT,
                    "CORRECT"=> ($str_POINT == "0" ? "N" : "Y"),
                );
            }

            $tr = new self;
            if (!$res = $tr->Update($TEST_RESULT_ID, $arFields))
                return false;

            return $arFields;
        }
        else
        {
            return false;
        }
    }
}
