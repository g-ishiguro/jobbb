<?php

namespace App;

class CreateExcel extends Controller {

    public function run() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->postProcess();
        }
    }

    private function postProcess() {
        try {
            // リクエストをセット
            $this->setFromRequest();
            // 入力チェック
            $this->validate();
            // ジョブカンのページをスクレイピングし勤怠状況を配列にする
            $timeArr = $this->createTimeArr();
            // 勤怠情報をExcelに書き込みます
            $this->createExcel($timeArr);
            // Excelをダウンロードします
            $this->downloadExcel();
        } catch (\Exception $e) {
            $this->setErrors('error', $e->getMessage());
        }

        if ($this->hasError()) {
            return;
        }
        header('Location:' . SITE_URL);
    }

    /**
     * Formの値をセットする。
     */
    private function setFromRequest() {
        $this->setValues('code', $_POST['code']);
        $this->setValues('name', $_POST['name']);
        if ($_POST['pto'] !== '') {
            $ptoArr = array();
            $noFormatPtoArr = explode(',', $_POST['pto']);
            foreach($noFormatPtoArr as $val) {
            // 2020-1-1を日付だけ配列に入れる
            $ptoArr[] = explode('-', $val)[2];
            }
        $this->setValues('ptoArr', $ptoArr);
        }
    }

    /**
     * 入力チェック
     */
    private function validate() {
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            throw new \Exception('不正な処理が行われました。');
            exit;
        }
        // 必須チェック
        if ($this->getValues()->code === '' || $this->getValues()->name === '') {
            throw new \Exception('code、nameの入力は必須です。');
        }
        // スペースチェック
        if (preg_match('/( |　)+/', $this->getValues()->code) ||
            preg_match('/( |　)+/', $this->getValues()->name)) {
            throw new \Exception('入力値にスペースが含まれています。');
        }
    }

    /**
     * ジョブカンのページをスクレイピングし勤怠状況を配列にする
     */
    private function createTimeArr() {
        // ページ取得
        $contents = file_get_contents(JOBCAN_URL . $this->getValues()->code);
        $enc = mb_convert_encoding($contents, "UTF-8", "auto");

        $html = \phpQuery::newDocumentHTML($enc);
        // 年月度取得
        $ym = explode('年', $html->find(".data03")->find("th")->text());
        $this->setValues('year', $ym[0]);
        $this->setValues('moth', str_replace('月', '', $ym[1]));

        $timeArr = array();
        for ($i = 1; $i <= date('j'); $i++) {
            // 勤務開始時間
            $startTime = $html->find(".schedule4")->find("tr:eq($i)")->find("td:eq(2)")->find("a")->text();
            $startTime = $this->shaping($startTime);
            // 勤務終了時間
            $endTime = $html->find(".schedule4")->find("tr:eq($i)")->find("td:eq(3)")->find("a")->text();
            $endTime = $this->shaping($endTime);
            // 休憩時間
            $breakTime = $html->find(".schedule4")->find("tr:eq($i)")->find("td:eq(4)")->find("a")->text();
            $breakTime = $this->shaping($breakTime);

            $timeArr[] = array(
                // 'day' => $i, 
                'startTime' => $startTime,
                'endTime' => $endTime,
                'breakTime' => $breakTime
            );
        }
        return $timeArr;
    }

    /**
     * スペース削除
     * -を空欄に変換
     * H時間IをH:iに変換
     * 改行コードを削除
     * 00:00を削除
     */
    private function shaping($val) {
        // スペース削除
        $timeStr = str_replace(array(" ", "　"), "", $val); 
        // -を空欄に変換
        $timeStr = str_replace(array('-'), '', $timeStr);
        // 改行コードを削除
        $timeStr = str_replace(array("\r\n", "\r", "\n"), '', $timeStr);
        // H:sに変換
        if (strpos($timeStr, '時間') ||
            strpos($timeStr, '分')) {
            $timeStr = str_replace('時間', ':', $timeStr);
            $timeStr = str_replace('分', '', $timeStr);
        }
        //00:00を削除
        $timeStr = str_replace(array('00:00'), '', $timeStr);
        if (!preg_match("/^[0-9 :]+$/", $timeStr)) {
            //(勤務中)は削除
            return $timeStr = '';
        }

        return $timeStr;
    }

    /**
     * Excelを作成
     */
    private function createExcel($timeArr) {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        // temp.xlsxを読み込みます
        $tempXlsx = $reader->load(TEMP_XLSX_PATH);
        // 勤務報告書シート選択
        $WorkRptsheet = $tempXlsx->setActiveSheetIndex(0);
        // 名前をセット
        $WorkRptsheet->setCellValue('L3', $this->getValues()->name);
         // 年をセット
        $WorkRptsheet->setCellValue('C1', $this->getValues()->year);
         // 月をセット
        $WorkRptsheet->setCellValue('E1', $this->getValues()->moth);
        // セルの書式を指定する時に必要
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder());

        for ($i = 0; $i < date('j'); $i++) {
            $line = 6 + $i;
            // 勤務開始時刻をセット 書式をセット
            $WorkRptsheet->setCellValue('E' . $line, $timeArr[$i]['startTime']);
            $WorkRptsheet->getStyle('E' . $line)->getNumberFormat()->setFormatCode('[h]:mm');
            // 勤務終了時刻をセット 書式をセット
            $WorkRptsheet->setCellValue('F' . $line, $timeArr[$i]['endTime']);
            $WorkRptsheet->getStyle('F' . $line)->getNumberFormat()->setFormatCode('[h]:mm');
            // 休憩時刻をセット
            $WorkRptsheet->setCellValue('G' . $line, $timeArr[$i]['breakTime']);
            $WorkRptsheet->getStyle('G' . $line)->getNumberFormat()->setFormatCode('[h]:mm');

            if ($timeArr[$i]['startTime'] != '') {
                 // 勤務区分をセット
                $WorkRptsheet->setCellValue('D' . $line, '出勤');
            } elseif (in_array($i + 1, $this->getValues()->ptoArr)) {
                 // 有休セット
                $WorkRptsheet->setCellValue('D' . $line, '有休');
            } elseif ($timeArr[$i]['startTime'] == '' && $timeArr[$i]['endTime'] == '') {
                 // 公休セット
                $WorkRptsheet->setCellValue('D' . $line, '公休');
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($tempXlsx);
        // Y.n名前.xlsxで保存する
        $this->setValues('createdExcelPath',  __DIR__ . '/../xlsx/' . $this->getValues()->year . '.' . $this->getValues()->moth . $this->getValues()->name . '.xlsx');
        $writer->save($this->getValues()->createdExcelPath);
    }

    /**
     * Excelをダウンロードします
     */
    private function downloadExcel() {
        // excelファイル存在確認
        if (!file_exists($this->getValues()->createdExcelPath)) {
            throw new \Exception('excelの作成に失敗しました');
        }
        $fileName =  str_replace(array(__DIR__ . '/../xlsx/'), '', $this->getValues()->createdExcelPath);
        $fileSize = filesize($this->getValues()->createdExcelPath);

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Length: {$fileSize}");
        header("Content-Disposition: attachment; filename=$fileName");

        ob_end_clean();
        readfile($this->getValues()->createdExcelPath);
        // download終了後に$this->createdExcelPathを削除する
        unlink($this->getValues()->createdExcelPath);
    }
}
