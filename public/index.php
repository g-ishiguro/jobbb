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
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="script.js"></script>
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>jobbb create excel tool</h1>
        <div class="how-to w50p-mg0a">
            <?php if ($app->hasError()) : ?>
                <div class="error-area">
                    <p class="error-message"><i class="fas fa-exclamation-triangle"></i>入力内容に不備またはエラーが発生しました。</p>
                    <p>・<?= h($app->getErrors('error')); ?></p>
                </div>
            <?php endif; ?>
            <h2>使い方</h2>
            <ol>
                <li>
                    <p>1. 名前をスペースなしで入力してください。</p>
                </li>
                <li>
                    <p>2. 打刻時に使用するurlのcodeの値をフォームに入力してください</p>
                </li>
                <li>
                    <p>3. 有休を取得した場合は、下記カレンダーから選択してください</p>
                </li>
                <li>
                    <p>4. FORM入力後に[スタート]を押下してください</p>
                </li>
                <li>
                    <p>5. Excelファイルがダウンロードされます</p>
                </li>
                <li>
                    <p>6. 有休以外で出勤時刻、退勤時刻が打刻されていない日は公休になります。<br>&nbsp;&nbsp;&nbsp;&nbsp;必要に応じて修正してください。</p>
                </li>
            </ol>
            <p>※ 初回実行時、テンプレートになるexcelファイルをxlsx/temp.xlsxに配置してください</p>
        </div>
        <div class="form-area w50p-mg0a">
            <form method="post">
                <ul class="w50p-mg0a">
                    <li>
                        <label id="name">name</label>
                        <input id="name" type="text" name="name">
                    </li>
                    <li>
                        <label id="code">code</label>
                        <input id="code" type="text" name="code">
                    </li>
                </ul>
                <input type="hidden" id="pto" name="pto">
                <div id="pto-area" class="w50p-mg0a"></div>
                <input id="button" type="submit" value="スタート" name="start" class="start">
                <input type="hidden" name="token" value="<?= h($_SESSION['token']); ?>">
            </form>
        </div>
        <script>
            $(function() {
                $('#pto-area').cehckcalendar();
            });
        </script>
    </div>
</body>

</html>