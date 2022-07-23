<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FormulaService;
use App\RawSurvey;
use Carbon\Carbon;
use DateTime;
use DB;

class Series {
    public $name = '';
    public $type = 'line';
    public $data = [];
};

class SPersonCases {
    // RAW.Distribution,
    // RAW.Sell,
    // RAW.SUM_CASE,
    // RAW.ACC_CASE,
    // ROUND(RAW.STATISFY_NUM/RAW.SUM_CASE, 2) AS STATISFY_RATE,
    // ROUND(RAW.MOVING_NUM/RAW.SUM_CASE, 2) AS MOVING_RATE,
    // (
    // ROUND(RAW.Q13Big9_NUM/RAW.SUM_CASE, 2) - ROUND(RAW.Q13_0_6_NUM/RAW.SUM_CASE, 2)
    // ) AS NPS_RATE
    public $SPerson = '';
    public $Distribution = '';
    public $Sell = '';
    public $SUM_CASE = 0;
    public $ACC_CASE = 0;
    public $STATISFY_RATE = 0;
    public $MOVING_RATE = 0;
    public $NPS_RATE = 0;
}

class SmsRow {
    public $date = '';
    public $item = '';
    public $value = 0;
};

class SummaryController extends Controller
{
    public $FormulaService;
    public function __construct(Request $req)
    {
        $this->FormulaService = new FormulaService();
    }

    ### Start 回復總類分析 ###

    /**
     * 總覽(折線圖)
     * endpoint: /api/summary/receive-category/getChart1
     */
    public function getChart1(Request $req)
    {
        $Calender = array();
        $LowScoreVec = array();
        $CommentsVec = array();
        $NoCommentsVec = array();
        $HighScoreVec = array();

        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $i_start = $NowMonth;
        for($i=$i_start; $i<=$EndMonth; $i++)
        {
            $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            $D2 = date('Y-m-t', strtotime($NowYear.'-'.($i).'-01 00:00:00'));
            $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

            $LowScore = $this->FormulaService->getLScoreSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $NotLowScoreSet = $this->FormulaService->getNotLowSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $CommentsNum = 0;   //1000 ~ 9999
            $NoCommentsNum = 0;    //is 9999
            $HighScoreNum = 0;     //less then 999

            foreach($NotLowScoreSet as $item)
            {
                $rq14Set = $item->rq14;
                try {
                    $rq14Set = explode(',', $rq14Set);
                } catch (\Throwable $th) {}
                
                $rq14IntSet = array();
                foreach ($rq14Set as $rq14) 
                {
                    array_push($rq14IntSet, intval($rq14));
                }

                $theMaxVal = max($rq14IntSet);
                if ($theMaxVal < 9999 && $theMaxVal >= 1000) {
                    $CommentsNum++;
                }
                if ($theMaxVal == 9999) {
                    $NoCommentsNum++;
                }
                if ($theMaxVal <= 999) {
                    $HighScoreNum++;
                }
            }

            array_push($LowScoreVec, count($LowScore) );
            array_push($CommentsVec, $CommentsNum );
            array_push($NoCommentsVec, $NoCommentsNum );
            array_push($HighScoreVec, $HighScoreNum );
            array_push($Calender, date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00')));
            $NowMonth++;
        }
        
        $LowSeries = new Series();
        $LowSeries->name = '客訴分析';
        $LowSeries->data = $LowScoreVec;

        $HighSeries = new Series();
        $HighSeries->name = '讚美分析';
        $HighSeries->data = $HighScoreVec;

        $CommentSeries = new Series();
        $CommentSeries->name = '建議事項';
        $CommentSeries->data = $CommentsVec;

        $NoCommentSeries = new Series();
        $NoCommentSeries->name = '無意見';
        $NoCommentSeries->data = $NoCommentsVec;

        $Data = [
            'label' => $Calender,
            'datasets' => [
                'type' => 'line',
                'label' => '總覽分析',
                'data' => [$LowSeries,$HighSeries,$CommentSeries,$NoCommentSeries]
            ]
        ];
        return json_encode($Data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 總覽(堆疊柱狀圖)
     * endpoint: /api/summary/receive-category/getChart2
     */
    public function getChart2(Request $req)
    {
        $Calender = array();
        $LowScoreVec = array();
        $CommentsVec = array();
        $NoCommentsVec = array();
        $HighScoreVec = array();

        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $i_start = $NowMonth;
        for($i=$i_start; $i<=$EndMonth; $i++)
        {
            $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            $D2 = date('Y-m-t', strtotime($NowYear.'-'.($i).'-01 00:00:00'));
            $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

            $LowScore = $this->FormulaService->getLScoreSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $NotLowScoreSet = $this->FormulaService->getNotLowSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $CommentsNum = 0;   //1000 ~ 9999
            $NoCommentsNum = 0;    //is 9999
            $HighScoreNum = 0;     //less then 999

            foreach($NotLowScoreSet as $item)
            {
                $rq14Set = $item->rq14;
                try {
                    $rq14Set = explode(',', $rq14Set);
                } catch (\Throwable $th) {}
                
                $rq14IntSet = array();
                foreach ($rq14Set as $rq14) 
                {
                    array_push($rq14IntSet, intval($rq14));
                }

                $theMaxVal = max($rq14IntSet);
                if ($theMaxVal < 9999 && $theMaxVal >= 1000) {
                    $CommentsNum++;
                }
                if ($theMaxVal == 9999) {
                    $NoCommentsNum++;
                }
                if ($theMaxVal <= 999) {
                    $HighScoreNum++;
                }
            }

            array_push($LowScoreVec, count($LowScore) );
            array_push($CommentsVec, $CommentsNum );
            array_push($NoCommentsVec, $NoCommentsNum );
            array_push($HighScoreVec, $HighScoreNum );
            array_push($Calender, date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00')));
            $NowMonth++;
        }

        $Data = [
            'label' => $Calender,
            'datasets' => [
                'type' => 'bar',
                'label' => '總攬',
                'data' => [
                    'lowscore' => $LowScoreVec,
                    'comments' => $CommentsVec,
                    'nocomments' => $NoCommentsVec,
                    'highscore' => $HighScoreVec,
                ]
            ]
        ];            
        return json_encode($Data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 客訴分析(折線圖)
     * endpoint: /api/summary/receive-category/getChart3
     */
    public function getChart3(Request $req)
    {
        $Calender = array();
        $LowScoreVec = array();

        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $i_start = $NowMonth;
        for($i=$i_start; $i<=$EndMonth; $i++)
        {
            $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            $D2 = date('Y-m-t', strtotime($NowYear.'-'.($i).'-01 00:00:00'));
            $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

            $LowScore = $this->FormulaService->getLScoreSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            array_push($LowScoreVec, count($LowScore) );
            array_push($Calender, date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00')));
            $NowMonth++;
        }

        $Data = [
            'label' => $Calender,
            'datasets' => [
                'type' => 'line',
                'label' => '客訴分析',
                'data' => $LowScoreVec
            ]
        ];
        return json_encode($Data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 客訴分析(彙總分析)
     * endpoint: /api/summary/receive-category/getTable1
     */
    public function getTable1(Request $req)
    {
        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
        $D2 = date('Y-m-t', strtotime($NowYear.'-'.($EndMonth).'-01 00:00:00'));
        $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

        $Table = $this->FormulaService->getSummaryTable01(
            $D1, 
            $D2, 
            $req->Region,
            $req->Category,
            $req->Person
        );
                        
        return json_encode($Table, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 客訴分析(排名分析)
     * endpoint: /api/summary/receive-category/getTable2
     */
    public function getTable2(Request $req)
    {
        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
        $D2 = date('Y-m-t', strtotime($NowYear.'-'.($EndMonth).'-01 00:00:00'));
        $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

        $Table = $this->FormulaService->getSummaryTable02(
            $D1, 
            $D2, 
            $req->Region,
            $req->Category,
            $req->Person
        );
                        
        return json_encode($Table, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 讚美分析(折線圖)
     * endpoint: /api/summary/receive-category/getChart4
     */
    public function getChart4(Request $req)
    {
        $Calender = array();
        $HighScoreVec = array();

        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $i_start = $NowMonth;
        for($i=$i_start; $i<=$EndMonth; $i++)
        {
            $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            $D2 = date('Y-m-t', strtotime($NowYear.'-'.($i).'-01 00:00:00'));
            $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

            $NotLowScoreSet = $this->FormulaService->getNotLowSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $HighScoreNum = 0;     //less then 999
            foreach($NotLowScoreSet as $item)
            {
                $rq14Set = $item->rq14;
                try {
                    $rq14Set = explode(',', $rq14Set);
                } catch (\Throwable $th) {}
                
                $rq14IntSet = array();
                foreach ($rq14Set as $rq14) 
                {
                    array_push($rq14IntSet, intval($rq14));
                }

                $theMaxVal = max($rq14IntSet);
                if ($theMaxVal <= 999) {
                    $HighScoreNum++;
                }
            }

            array_push($HighScoreVec, $HighScoreNum );
            array_push($Calender, date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00')));
            $NowMonth++;
        }

        $Data = [
            'label' => $Calender,
            'datasets' => [
                'type' => 'line',
                'label' => '讚美分析',
                'data' => $HighScoreVec
            ]
        ];
        return json_encode($Data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 讚美分析(彙總查詢)
     * endpoint: /api/summary/receive-category/getTable3
     */
    public function getTable3(Request $req)
    {
        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
        $D2 = date('Y-m-t', strtotime($NowYear.'-'.($EndMonth).'-01 00:00:00'));
        $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

        $DistinctSPersonTable = $this->FormulaService->getDistinctSPerson(
            $D1, 
            $D2, 
            $req->Region,
            $req->Category,
            $req->Person
        );

        $S_PersonQueue = array();

        foreach($DistinctSPersonTable as $RawData)
        {
            $HighScoreNum = 0;
            $RawData = RawSurvey::where('s_person', $RawData->s_person)
            ->where('start_time','>=',$D1)
            ->where('end_time','<',$D2)
            ->get();

            $needNew = true;
            foreach ($S_PersonQueue as $_SPersonCase) {
                if ($_SPersonCase->SPerson == $RawData->s_person) {
                    $needNew = false;
                }
            }

            foreach($RawData as $item)
            {
                $rq14Set = $item->rq14;
                try {
                    $rq14Set = explode(',', $rq14Set);
                } catch (\Throwable $th) {}
                
                $rq14IntSet = array();
                foreach ($rq14Set as $rq14) 
                {
                    array_push($rq14IntSet, intval($rq14));
                }

                $theMaxVal = max($rq14IntSet);
                if ($theMaxVal <= 999) {
                    $HighScoreNum++;
                }
            }

            if ($needNew) {
                $SPersonCasesObj = new SPersonCases();
                $SPersonCasesObj->SPerson = $RawData->s_person;
                $SPersonCasesObj->SUM_CASE = $HighScoreNum;
                array_push($S_PersonQueue, $SPersonCasesObj);
            }
        }

        dd($S_PersonQueue);

        // $Table = $this->FormulaService->getSummaryTable03(
        //     $D1, 
        //     $D2, 
        //     $req->Region,
        //     $req->Category,
        //     $req->Person
        // );
                        
        return json_encode($Table, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 讚美分析(排名分析)
     * endpoint: /api/summary/receive-category/getTable4
     */
    public function getTable4(Request $req)
    {
        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
        $D2 = date('Y-m-t', strtotime($NowYear.'-'.($EndMonth).'-01 00:00:00'));
        $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

        $Table = $this->FormulaService->getSummaryTable04(
            $D1, 
            $D2, 
            $req->Region,
            $req->Category,
            $req->Person
        );
                        
        return json_encode($Table, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 建議事項分析(折線圖)
     * endpoint: /api/summary/receive-category/getChart5
     */
    public function getChart5(Request $req)
    {
        $Calender = array();
        $CommentsVec = array();

        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $i_start = $NowMonth;
        for($i=$i_start; $i<=$EndMonth; $i++)
        {
            $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            $D2 = date('Y-m-t', strtotime($NowYear.'-'.($i).'-01 00:00:00'));
            $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

            $NotLowScoreSet = $this->FormulaService->getNotLowSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $CommentsNum = 0;   //1000 ~ 9999
            foreach($NotLowScoreSet as $item)
            {
                $rq14Set = $item->rq14;
                try {
                    $rq14Set = explode(',', $rq14Set);
                } catch (\Throwable $th) {}
                
                $rq14IntSet = array();
                foreach ($rq14Set as $rq14) 
                {
                    array_push($rq14IntSet, intval($rq14));
                }

                $theMaxVal = max($rq14IntSet);
                if ($theMaxVal < 9999 && $theMaxVal >= 1000) {
                    $CommentsNum++;
                }
            }

            array_push($CommentsVec, $CommentsNum );
            array_push($Calender, date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00')));
            $NowMonth++;
        }

        $Data = [
            'label' => $Calender,
            'datasets' => [
                'type' => 'line',
                'label' => '建議事項分析',
                'data' => $CommentsVec
            ]
        ];            
        return json_encode($Data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 建議事項分析(彙總分析)
     * endpoint: /api/summary/receive-category/getTable5
     */
    public function getTable5(Request $req)
    {
        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
        $D2 = date('Y-m-t', strtotime($NowYear.'-'.($EndMonth).'-01 00:00:00'));
        $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

        $Table = $this->FormulaService->getSummaryTable05(
            $D1, 
            $D2, 
            $req->Region,
            $req->Category,
            $req->Person
        );
                        
        return json_encode($Table, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 建議事項分析(建議事項)
     * endpoint: /api/summary/receive-category/getTable6
     */
    public function getTable6(Request $req)
    {
        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
        $D2 = date('Y-m-t', strtotime($NowYear.'-'.($EndMonth).'-01 00:00:00'));
        $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

        $Table = $this->FormulaService->getSummaryTable06(
            $D1, 
            $D2, 
            $req->Region,
            $req->Category,
            $req->Person
        );
                        
        return json_encode($Table, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 無意見(折線圖)
     * endpoint: /api/summary/receive-category/getChart6
     */
    public function getChart6(Request $req)
    {
        $Calender = array();
        $NoCommentsVec = array();

        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $i_start = $NowMonth;
        for($i=$i_start; $i<=$EndMonth; $i++)
        {
            $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            $D2 = date('Y-m-t', strtotime($NowYear.'-'.($i).'-01 00:00:00'));
            $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

            $NotLowScoreSet = $this->FormulaService->getNotLowSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $NoCommentsNum = 0;    //is 9999
            foreach($NotLowScoreSet as $item)
            {
                $rq14Set = $item->rq14;
                try {
                    $rq14Set = explode(',', $rq14Set);
                } catch (\Throwable $th) {}
                
                $rq14IntSet = array();
                foreach ($rq14Set as $rq14) 
                {
                    array_push($rq14IntSet, intval($rq14));
                }

                $theMaxVal = max($rq14IntSet);
                if ($theMaxVal == 9999) {
                    $NoCommentsNum++;
                }
            }


            array_push($NoCommentsVec, $NoCommentsNum );
            array_push($Calender, date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00')));
            $NowMonth++;
        }

        $Data = [
            'label' => $Calender,
            'datasets' => [
                'type' => 'line',
                'label' => '無意見',
                'data' => $NoCommentsVec
            ]
        ];            
        return json_encode($Data, JSON_UNESCAPED_UNICODE);
    }

    ### End 回復總類分析 ###

    ### Start 簡訊SMS市調統計 ###

    /**
     * 簡訊滿意度分析表
     * endpoint: /api/summary/receive-category/getChart7
     */
    public function getTable7(Request $req)
    {
        $ResultVec = array();

        $NowYear = date('Y', strtotime($req->StartTime));
        $NowMonth = date('m', strtotime($req->StartTime));
        $EndMonth = date('m', strtotime($req->EndTime));

        $i_start = $NowMonth;
        for($i=$i_start; $i<=$EndMonth; $i++)
        {
            $D1 = date('Y-m-d', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            $D2 = date('Y-m-t', strtotime($NowYear.'-'.($i).'-01 00:00:00'));
            $D2 = date('Y-m-d', strtotime($D2. ' + 1 days'));

            $Qs = $this->FormulaService->getAllQSet(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C1_value = $this->FormulaService->getQ1big4Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C2_value = $this->FormulaService->getQ1is5Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C3_value = $this->FormulaService->getQ1is1Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C4_value = $this->FormulaService->getQ1is2Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C5_value = $this->FormulaService->getQ1is3Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C6_value = $this->FormulaService->getQ1is4Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C7_value = $this->FormulaService->getQ1is5Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            ### NPS ###
            $C8_01_value = $this->FormulaService->getQ13_0_6Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );
            $C8_02_value = $this->FormulaService->getQ13big9Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C9_value = $this->FormulaService->getQ13_0_6Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C10_value = $this->FormulaService->getQ13_7_8Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C11_value = $this->FormulaService->getQ13big9Set(
                $D1, 
                $D2, 
                $req->Region,
                $req->Category,
                $req->Person
            );

            $C12_value = 0; //維修總件數
            $C13_value = 0; //簡訊發送件數

            $C14_value = $Qs;

            $C15_value = 0; //訪問占比
            $C16_value = 0; //回覆率

            $RawSurveyNum = count($Qs);

            $C1Rate = 0; //滿意度%
            $C2Rate = 0; //感動率%

            try {
                $C1Rate = round(count($C1_value)/$RawSurveyNum, 2)*100;
            } catch (\Throwable $th) {}

            try {
                $C2Rate = round(count($C2_value)/$RawSurveyNum, 2)*100;
            } catch (\Throwable $th) {}

            ### Start 滿意度 ###
            $C1 = new SmsRow;
            $C1->item = '滿意度';
            $C1->value = $C1Rate;
            $C1->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 滿意度 ###

            ### Start 感動率 ###
            $C2 = new SmsRow;
            $C2->item = '感動率';
            $C2->value = $C2Rate;
            $C2->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 感動率 ###

            ### Start 1分 ###
            $C3 = new SmsRow;
            $C3->item = '1分';
            $C3->value = count($C3_value);
            $C3->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 1分 ###

            ### Start 2分 ###
            $C4 = new SmsRow;
            $C4->item = '2分';
            $C4->value = count($C4_value);
            $C4->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 2分 ###

            ### Start 3分 ###
            $C5 = new SmsRow;
            $C5->item = '3分';
            $C5->value = count($C5_value);
            $C5->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 3分 ###

            ### Start 4分 ###
            $C6 = new SmsRow;
            $C6->item = '4分';
            $C6->value = count($C6_value);
            $C6->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 4分 ###

            ### Start 5分 ###
            $C7 = new SmsRow;
            $C7->item = '5分';
            $C7->value = count($C7_value);
            $C7->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 5分 ###

            ### Start NPS ###
            $C8 = new SmsRow;
            $C8->item = 'NPS淨推薦值%';
            $p0_6Rate = round(count($C8_01_value)/$RawSurveyNum, 2)*100;
            $p9_10Rate = round(count($C8_02_value)/$RawSurveyNum, 2)*100;
            $C8->value = abs($p0_6Rate - $p9_10Rate);
            // $C8->value = count($C8_01_value) - count($C8_02_value);
            $C8->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End NPS ###

            ### Start 0-6 ###
            $C9 = new SmsRow;
            $C9->item = '0-6';
            $C9->value = count($C9_value);
            $C9->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 0-6 ###

            ### Start 7-8 ###
            $C10 = new SmsRow;
            $C10->item = '7-8';
            $C10->value = count($C10_value);
            $C10->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 7-8 ###

            ### Start 9-10 ###
            $C11 = new SmsRow;
            $C11->item = '9-10';
            $C11->value = count($C11_value);
            $C11->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 9-10 ###

            ### Start 維修總件數 ###
            $C12 = new SmsRow;
            $C12->item = '維修總件數';
            $C12->value = $C12_value;
            $C12->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 維修總件數 ###

            ### Start 簡訊發送件數 ###
            $C13 = new SmsRow;
            $C13->item = '簡訊發送件數';
            $C13->value = $C13_value;
            $C13->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 簡訊發送件數 ###

            ### Start 回覆件數 ###
            $C14 = new SmsRow;
            $C14->item = '回覆件數';
            $C14->value = count($C14_value);
            $C14->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 回覆件數 ###

            ### Start 訪問占比 ###
            $C15 = new SmsRow;
            $C15->item = '訪問占比';
            $C15->value = $C15_value;
            $C15->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 訪問占比 ###

            ### Start 回覆率 ###
            $C16 = new SmsRow;
            $C16->item = '回覆率';
            $C16->value = $C16_value;
            $C16->date = date('Y/m', strtotime($NowYear.'-'.($NowMonth).'-01 00:00:00'));
            ### End 回覆率 ###
            
            array_push($ResultVec, $C1);
            array_push($ResultVec, $C2);
            array_push($ResultVec, $C3);
            array_push($ResultVec, $C4);
            array_push($ResultVec, $C5);
            array_push($ResultVec, $C6);
            array_push($ResultVec, $C7);
            array_push($ResultVec, $C8);
            array_push($ResultVec, $C9);
            array_push($ResultVec, $C10);
            array_push($ResultVec, $C11);
            array_push($ResultVec, $C12);
            array_push($ResultVec, $C13);
            array_push($ResultVec, $C14);
            array_push($ResultVec, $C15);
            array_push($ResultVec, $C16);

            $NowMonth++;
        }

        $Data = [
            'datasets' => [
                'type' => 'table',
                'label' => '簡訊滿意度分析表',
                'data' => $ResultVec
            ]
        ];            
        return json_encode($Data, JSON_UNESCAPED_UNICODE);
    }

    ### End 簡訊SMS市調統計 ###
}
