<?php
require_once('../config/config.php');

$app = new App\CreateExcel();
$app->run();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>jobbb create excel tool</title>
</head>
<style>
    .container {
        width: 1280px;
    }

    #pto-area {
        width: 45%;
    }

    input[id^="pto"] {
        margin-left: 0;
        margin-right: 0;
    }

    label[id^="pto"] {
        display: inline-block;
        width: 13%;
    }
</style>
<body>
    <div class="container">
        <div class="how-to">
            <h1>jobbb create excel tool</h1>
            <p>
                <?= h($app->getErrors('error')); ?>
            </p>
            <h2>使い方</h2>
            <ol>
                <li>
                    <p>名前をスペースなしで入力してください。</p>
                </li>
                <li>
                    <p>打刻時に使用するurlのcodeの値をフォームに入力してください</p>
                </li>
                <li>
                    <p>有休を取得した場合は、取得した日付をチェックしてください</p>
                </li>
                <li>
                    <p>FORM入力後に[スタート]を押下してください</p>
                </li>
                <li>
                    <p>Excelファイルがダウンロードされます</p>
                </li>
                <li>
                    <p>有休以外で出勤時刻、退勤時刻が打刻されていない日は公休になります。<br>必要に応じて修正してください。</p>
                </li>
            </ol>
            <p>※ 初回実行時、テンプレートになるexcelファイルをxlsx/temp.xlsxに配置してください</p>
        </div>
        <form method="post">
            <label id="name">name</label>
            <input id="name" type="text" name="name">
            <label id="code">code</label>
            <input id="code" type="text" name="code">
            <input type="button" id="pto-btn" value="有休選択" onclick="showPto()">
            <div id="pto-area">
                <?php for ($i = 1; $i <= 31; $i++) : ?>
                    <label id="<?= 'pto'. $i ?>"><?= $i . '日' ?>
                        <input type="checkbox" class="check-boxes" id="pto<?= $i ?>" name="pto[]" value="<?= $i ?>">
                    </label>
                <?php endfor ?>
            </div>
            <input id="input" type="submit" value="スタート" name="start" class="start">
        </form>
        <script>
            document.getElementById("pto-area").style.display = "none";

            function showPto() {
                const ptoArea = document.getElementById("pto-area");

                if (ptoArea.style.display == "block") {
                    ptoArea.style.display = "none";
                } else {
                    ptoArea.style.display = "block";
                    // チェックボックスをすべて非選択にする
                    var checkboxes = document.getElementsByClassName('check-boxes');
                    for (i in checkboxes) {
                        checkboxes[i].checked = false;
                    }
                }
            }
        </script>
    </div>
</body>
</html>
