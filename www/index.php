<?php
// URL
// http://git_log_diff.example.php74/?project=project_name

//共通
require_once substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME'])).'/includes/init.php';
$project = filter_input(INPUT_GET, 'project');
/**
 * $_GET['debug'] = デバッグ用
 * $_GET['limit'] = 履歴表示件数
 */
?>
<!DOCTYPE html>
<head>
  <meta charset="utf-8">
  <title><?php echo h($project).'のGit履歴'; ?><?php /* Pretty Git log */ ?></title>
  <style>
    body { font-family: Courier, monospace; font-size: 0.9em; }
    a, a:hover, a:active, a:visited { text-decoration: none; }
    a:hover { text-decoration: underline; }
    ul { list-style: none }
    li { overflow: hidden; height: 1.1em; }
    .rev { color: darkred; }
    .date { color: green; }
    .author, .author a { color: darkviolet; }
    .tags { color: goldenrod; }
  </style>

<?php /*
  <!-- CSS only -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

  <!-- JavaScript Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
*/ ?>

  <?php /*
  <link rel="stylesheet" href="/includes/css/bootstrap/css/bootstrap.css">
  <script src="/includes/css/bootstrap/js/bootstrap.js"></script>
  */ ?>
</head>
<body>
  <h1><?php echo h($project).'のGit履歴'; ?></h1>
  <?php
  if ( !empty($_GET['debug']) ){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
  } else {
    error_reporting(0);
    ini_set('display_errors', 0);
  }
  ini_set('memory_limit','256M');

  $limit = '--max-count=50';
  if ( !empty($_GET['limit']) ){
    if ( strtolower($_GET['limit']) == 'all' ){
      $limit = '--max';
    }
    else {
      $limit = '--max-count='.intval($_GET['limit']);
    }
  }

  if (!empty($project)) {
    $path_to_repository = '../../'.h($project);
    chdir($path_to_repository);
  }

  // コミットメッセージのみを取得
  $msgs = array();
  exec( "/usr/bin/env git log --pretty=tformat:%s --all --graph $limit", $msgs );

  // 抽出したデータをHTML整形して取得
  $lines = array();
  exec( "/usr/bin/env git log --pretty=tformat:'</span>%h - <span class=\"date\">[%cr]</span> <span class=\"tags\">%d</span> __COMMENT__ <span class=\"author\">&lt;%an&gt;</span></li>' --all --graph --abbrev-commit $limit", $lines );
  //git log --pretty='format:%C(yellow)%h %C(green)%cd %C(reset)%s %C(red)%d %C(cyan)[%an]' --date=format:'%c' --all --graph

  // コミットハッシュ値の取得
  $temp_hashes = array();
  exec( "/usr/bin/env git log --pretty=tformat:%h --all --graph $limit", $temp_hashes );
  $hashes = array();
  foreach($temp_hashes as $hash){
    if(preg_match('/[!-~]{7}/', $hash, $match)){
      $hashes[] = $match[0];
    }
  }

  //echo '<pre>';
  //var_dump($hashes);
  //echo '</pre>';
?>

<?php if(!empty($hashes)): ?>
    
  <form action="./download.php" method="get">
    <?php
      if(!empty($_GET['project'])){
        $project = $_GET['project'];
      }else{
        $project = '';
      }
    ?>
    <input type="hidden" name="project" value="<?php echo h($project); ?>" >
    上：
    <select name="up">
      <?php foreach($hashes as $hash): ?>
      <option value="<?php echo h($hash); ?>"><?php echo h($hash); ?></option>
      <?php endforeach; ?>
    </select>

    下：
    <select name="down">
      <?php foreach($hashes as $hash): ?>
      <option value="<?php echo h($hash); ?>"><?php echo h($hash); ?></option>
      <?php endforeach; ?>
    </select>
    <input type="submit" value="抽出">
  </form>
<?php endif; ?>
  

<?php if (!empty($msgs) && !empty($lines)): ?>
  <ul>
    <?php for ($i=0; $i<count($lines); $i++ ): ?>
      <?php
        $msg = htmlentities($msgs[$i],ENT_QUOTES);
        $message = str_replace('__COMMENT__',$msg,$lines[$i]);
      ?>
      <li><span class="graph"><?php echo $message; ?></span></li>
    <?php endfor; ?>
  </ul>
<?php endif; ?>
  </body>
</html>
