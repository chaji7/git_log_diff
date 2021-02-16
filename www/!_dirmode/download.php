<?php

require_once dirname($_SERVER['SCRIPT_FILENAME']).'/includes/init.php';

// 直接アクセスは404
$referer = $_SERVER['HTTP_REFERER'];
if(empty($referer)){
  // 404
  header("HTTP/1.1 404 Not Found");
  include ('404.php');
  exit;
}
$this_dir = dirname($_SERVER['SCRIPT_FILENAME']);
//$project = filter_input(INPUT_GET, 'project');
$up = filter_input(INPUT_GET, 'up');
$down = filter_input(INPUT_GET, 'down');

// ディレクトリ移動
$path_to_repository = PATH_TO;
chdir($path_to_repository);

// 差分の一時ファイルをzipファイルでtmpディレクトリ以下に出力
$msgs = array();
exec( "git archive $up `git diff --name-only $down $up --diff-filter=ACMR` -o $this_dir/tmp/diff.zip", $msgs );

// 出力したzipファイルをPHPでダウンロード
download("$this_dir/tmp/diff.zip", 'application/zip', $referer);

?>
