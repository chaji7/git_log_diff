<?php
//echo '<pre>';

require_once substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME'])).'/includes/init.php';
// 直接アクセスは404
$referer = $_SERVER['HTTP_REFERER'];
if(empty($referer)){
  // 404
}
$this_dir = dirname($_SERVER['SCRIPT_FILENAME']);
$project = filter_input(INPUT_GET, 'project');
$up = filter_input(INPUT_GET, 'up');
$down = filter_input(INPUT_GET, 'down');

// 差分の一時ファイルをzipファイルでtmpディレクトリ以下に出力
$path_to_repository = '../../'.h($project);
chdir($path_to_repository);
$msgs = array();
exec( "git archive $up `git diff --name-only $down $up --diff-filter=ACMR` -o $this_dir/tmp/diff.zip", $msgs );

// 出力したzipファイルをPHPでダウンロード
download("$this_dir/tmp/diff.zip", 'application/zip', $referer);

?>
