<?php

define('PATH_TO', '../../');

// 内部文字コードをUTF8にする
mb_language("ja");
mb_internal_encoding("UTF-8");

// インクルードパス追加
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__));

// HTMLエスケイプ
if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars(html_entity_decode($string), ENT_QUOTES,'utf-8');
    }
}

// 【PHP】正しいダウンロード処理の書き方
// https://qiita.com/fallout/items/3682e529d189693109eb
// これを使わせていただいて、少し記述を追加している
function download($pPath, $pMimeType = null, $referer){
    //-- ファイルが読めない時はエラー(もっときちんと書いた方が良いが今回は割愛)
    if (!is_readable($pPath)) { die($pPath); }

    //-- Content-Typeとして送信するMIMEタイプ(第2引数を渡さない場合は自動判定) ※詳細は後述
    $mimeType = (isset($pMimeType)) ? $pMimeType
                                    : (new finfo(FILEINFO_MIME_TYPE))->file($pPath);

    //-- 適切なMIMEタイプが得られない時は、未知のファイルを示すapplication/octet-streamとする
    if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
        $mimeType = 'application/octet-stream';
    }

    //-- Content-Type
    header('Content-Type: ' . $mimeType);

    //-- ウェブブラウザが独自にMIMEタイプを判断する処理を抑止する
    header('X-Content-Type-Options: nosniff');

    //-- ダウンロードファイルのサイズ
    header('Content-Length: ' . filesize($pPath));

    //-- ダウンロード時のファイル名
    header('Content-Disposition: attachment; filename="' . basename($pPath) . '"');

    //-- keep-aliveを無効にする
    header('Connection: close');

    //-- readfile()の前に出力バッファリングを無効化する ※詳細は後述
    while (ob_get_level()) { ob_end_clean(); }

    //-- 出力
    readfile($pPath);

    // 一時ファイルに削除
    exec("rm -rf $pPath");

    // リダイレクト
    header("Location: $referer");

    //-- 最後に終了させるのを忘れない
    exit;
}


?>