<?php
// URL
// http://git_log_diff.example.php74/?project=project_name

//共通
require_once dirname($_SERVER['SCRIPT_FILENAME']).'/includes/init.php';
/**
 * $_GET['debug'] = デバッグ用
 * $_GET['limit'] = 履歴表示件数
 */
?>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <title><?php echo h('Git履歴'); ?><?php /* Pretty Git log */ ?></title>
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
      .commitMsg { height: 2.2em; }
      .caution { font-size: 24px; color: red; font-weight: bold; }
      /*.btnArea { width:300px; }*/
      .btnArea {float:left;width:200px;min-height:1px;}
      .graph { float:left; margin-top:5px; }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  </head>
  <body>
    <h1><?php echo h('Git履歴'); ?></h1>
    <?php
    if ( !empty($_GET['debug']) ){
      error_reporting(E_ALL);
      ini_set('display_errors', 1);
    } else {
      error_reporting(0);
      ini_set('display_errors', 0);
    }
    ini_set('memory_limit','256M');

    $limit = '--max-count=500';
    if ( !empty($_GET['limit']) ){
      if ( strtolower($_GET['limit']) == 'all' ){
        $limit = '--max';
      }
      else {
        $limit = '--max-count='.intval($_GET['limit']);
      }
    }

    // ディレクトリ移動
    $path_to_repository = PATH_TO;
    chdir($path_to_repository);

    $data = [];

    // コミットメッセージのみを取得
    exec( "git log --pretty=tformat:%s --all --graph $limit", $data['graph_msgs'] );

    // コミットメッセージのみを取得
    $temp_msgs = array();
    exec( "git log --pretty=tformat:%s --all --graph $limit", $temp_msgs );
    foreach($temp_msgs as $v){
      if(preg_match('/Merge branch/', $v)){
        $data['msgs'][] = 'Merge branch 〜';
      }elseif(preg_match('/(\*\ \|\ |\*\ |\|\ \*\ |)/', $v, $match)){
        $data['msgs'][] = str_replace($match[0],'',$v);
      }else{
        $data['msgs'][] = $v;
      }
    }

    // 抽出したデータをHTML整形して取得
    exec( "git log --pretty=tformat:'</span>%h - <span class=\"date\">[%cr]</span> <span class=\"tags\">%d</span> __COMMENT__ <span class=\"author\">&lt;%an&gt;</span></li>' --all --graph --abbrev-commit $limit", $data['lines'] );

    // コミットハッシュ値の取得
    $temp_hashes = array();
    exec( "git log --pretty=tformat:%h --all --graph $limit", $temp_hashes );
    foreach($temp_hashes as $hash){
      if(preg_match('/[!-~]{7}/', $hash, $match)){
        $data['hashes'][] = $match[0];
      }else{
        $data['hashes'][] = '';
      }
    }

  ?>


  <?php if(!empty($data['hashes'])): ?>
    <form action="./download.php" method="get" name="diffForm" id="Form">
      <?php
        if(!empty($_GET['project'])){
          $project = $_GET['project'];
        }else{
          $project = '';
        }
      ?>
      <input type="hidden" name="project" value="<?php echo h($project); ?>" >
      上：
      <select name="up" id="UP">
        <?php foreach($data['hashes'] as $key => $hash): ?>
          <?php if(empty($hash)){continue;} ?>
          <option value="<?php echo h($hash); ?>"><?php echo h($hash.' : '.$data['msgs'][$key]); ?></option>
        <?php endforeach; ?>
      </select>
      <br><br>
      下：
      <select name="down" id="DOWN">
        <?php foreach($data['hashes'] as $key =>$hash): ?>
          <?php if(empty($hash)){continue;} ?>
          <option value="<?php echo h($hash); ?>"><?php echo h($hash.' : '.$data['msgs'][$key]); ?></option>
        <?php endforeach; ?>
      </select>
      <br><br>
      <input type="submit" value="抽出" id="submit_btn" onclick="return false">
      <p class="caution">「下」は抽出したい範囲の一つ下を選択してください</p>
    </form>
  <?php endif; ?>
    

  <?php if (!empty($data['graph_msgs']) && !empty($data['lines'])): ?>
    <ul>
      <?php for ($i=0; $i<count($data['lines']); $i++ ): ?>
        <?php
          $msg = htmlentities($data['graph_msgs'][$i],ENT_QUOTES);
          $message = str_replace('__COMMENT__',$msg,$data['lines'][$i]);
        ?>
        <li class="commitMsg">
          
          <span class="btnArea">
            <?php if(!empty($data['hashes'][$i])): ?>
              <button onClick="setUp('<?php echo h($data['hashes'][$i]); ?>')">上にセット</button>
              <button onClick="setDown('<?php echo h($data['hashes'][$i]); ?>')">下にセット</button>
            <?php endif; ?>
          </span>
          
          <div class="graph"><?php echo $message; ?></div>
          <?php /* echo $message; */ ?>
          
        </li>
      <?php endfor; ?>
    </ul>
  <?php endif; ?>
  </body>

  <script>
  var hash_json = <?php echo !empty($data['hashes']) ? json_encode($data['hashes']):''; ?>;
  $('#submit_btn').click(function () {
    // 同一コミットチチェック
    if($('#UP').val() == $('#DOWN').val()){
      alert('同じコミットを選択することはできません');
      return false;
    }
    for(i in hash_json){
      if(hash_json[i]==$('#UP').val()){var up_key = i;}
      if(hash_json[i]==$('#DOWN').val()){var down_key = i;}
    }
    // 上下選択の順番がおかしい
    if(up_key > down_key){
      alert('コミットの選択が正しくありません');
      return false;
    }
    // DL確認
    flg = confirm('ダウンロードしてもよろしいですか？');
    if(flg == true){
      document.diffForm.submit();
    }
  });
  function setUp(num){
    $('#UP').val(num);
  }
  function setDown(num){
    $('#DOWN').val(num);
  }
  </script>

</html>
