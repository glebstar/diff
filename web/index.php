<?php

require_once dirname(__FILE__) . '/../lib/Diff.php';

$result = false;
$error = false;

if($_POST) {
    $text1 = '';
    if(isset($_POST['text1']) && $_POST['text1']) {
        $text1 = $_POST['text1'];
    }
    
    $text2 = '';
    if(isset($_POST['text2']) && $_POST['text2']) {
        $text2 = $_POST['text2'];
    }
    
    if($text1 == '' && $text2 == '') {
        $error = 'Введите хотя бы один текст';
    } else {
        $result = Diff::getDiff($text1, $text2);
    }
}

?>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="/css/style.css?v=1" media="screen">
    </head>
    <body>
        <div id="content">
            <?php if($result): ?>
            <h1>Результаты сравнения</h1>
            <a href="/">Вернуться к вводу текстов</a>
            <br />
            <br />
            <div class="result">
            <?php echo nl2br($result); ?>
            </div>
            <br />
            <a href="/">Вернуться к вводу текстов</a>
            <?php else: ?>
            <h1>Проверка текста на изменения</h1>
            <?php if($error): ?>
            <p><span class="error">Ошибка:</span> <?php echo $error; ?></p>
            <?php endif; ?>
            <form class="texts" method="post" action="/">
                <label>Введите первый текст:</label>
                <textarea name="text1"></textarea>
                <label>Введите второй текст:</label>
                <textarea name="text2"></textarea>
                <input type="submit" value="Сравнить" />
            </form>
            <?php endif; ?>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
        <script src="/js/script.js?v=1"></script>
    </body>
</html>