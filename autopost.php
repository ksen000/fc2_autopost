<?php

$kiji_title = "";
$kiji_honbun = "";
$kiji_more = "";
while(true){
  $kiji_title = "";
  $kiji_honbun = "";
  $kiji_more = "";
  $i = rand(500, 2000);
  $url = 'http://owata89.blog.fc2.com/blog-entry-'.$i.'.html';
  if(!@file_get_contents($url)) continue;
  $html = file($url);

  // Title:
  $continue_next = true;
  foreach($html as $line){
    if(strstr($line, '<title>')){
      $pos1 = strpos($line, '<title>');
      $pos2 = strpos($line, '  高学歴の就活2chまとめ</title>');
      $continue_next = false;
      $kiji_title = substr($line, $pos1+strlen('<title>'), $pos2-5);
      break;
    }
  }
  if($continue_next) continue;

  // Body:
  $isBody = false;
  foreach($html as $line){
    if(strstr($line, '<div class="mainEntryBody"><div class="t_h" >1:')) {
      //$kiji_honbun = str_replace("<br /></div>", "", $line);
      $kiji_honbun = $line;
      break;
    }
  }

  // Extended:
  $isExtention = false;
  foreach($html as $line){
    if($isExtention) {
      if(strstr($line, '<div class="t_h" >')) {
        $kiji_more = $line;
        $kiji_more .= '</div>';
        break;
      }
    }
    if(strstr($line, '<a name="more" id="more"></a>')) {
      $isExtention = true;
    }
  }

  if(strlen($kiji_title) == 0) continue;
  if(strlen($kiji_honbun) == 0) continue;
  if(strlen($kiji_more) == 0) continue;

  break;
}


require_once("RPC.php"); //XML-RPC package
$GLOBALS['XML_RPC_defencoding'] = "UTF-8";

//ＦＣ２用定義情報
$fc2_blogid = "0";
$fc2_host = "blog.fc2.com";
$fc2_xmlrpc_path = "/xmlrpc.php";
// //////////////////////////////
// 変更箇所
// //////////////////////////////
//ブログのログインＩＤ（メールアドレス）を記入
$fc2_user = "";
//ブログのログインパスワードを記入
$fc2_passwd = "";
//記事情報
//投稿モード（0:下書記事、1:公開記事）
$kiji_mode = 1;

$bm = new BlogManager();
$bm->post_blog($kiji_mode,$kiji_title,$kiji_honbun,$kiji_more);

class BlogManager {
function post_blog($kiji_mode,$kiji_title,$kiji_honbun,$kiji_more){
global $fc2_xmlrpc_path,$fc2_host,$fc2_user,$fc2_passwd;
//クライアントの作成
echo "クライアント作成";
$c = new XML_RPC_client( $fc2_xmlrpc_path, $fc2_host, 80 );

//Setting the default time for "dateCreated".
date_default_timezone_set('Asia/Tokyo');
//送信データ
$blogid = new XML_RPC_Value($fc2_blogid, 'string');
$username = new XML_RPC_Value($fc2_user, 'string');
$passwd = new XML_RPC_Value($fc2_passwd, 'string');
$content = new XML_RPC_Value(array(
'title' => new XML_RPC_Value($kiji_title, 'string'),
'description'=> new XML_RPC_Value($kiji_honbun, 'string'),
'dateCreated'=> new XML_RPC_Value(date("Ymd\TH:i:s", time()), 'dateTime.iso8601'),
'mt_text_more'=> new XML_RPC_Value($kiji_more, 'string')
), 'struct');
$publish = new XML_RPC_Value($kiji_mode, 'boolean');
//XML-RPCメソッドのセット
$message = new XML_RPC_Message('metaWeblog.newPost',array($blogid, $username, $passwd, $content, $publish) );

$this->send_message($c,$message);

}

function get_users_blogs(){
global $fc2_xmlrpc_path,$fc2_host,$fc2_user,$fc2_passwd;
//クライアントの作成
echo "クライアント作成";
$c = new XML_RPC_client( $fc2_xmlrpc_path, $fc2_host, 80 );
$appkey = new XML_RPC_Value ( '' , 'string' );
$username = new XML_RPC_Value ( $fc2_user , 'string' );
$passwd = new XML_RPC_Value ( $fc2_passwd, 'string' );

//メッセージ作成
echo "メッセージ作成";
$message = new XML_RPC_Message( "blogger.getUsersBlogs",array($appkey, $username, $passwd) );

send_message($c,$message);
}

function send_message($c,$message){
//メッセージ送信
echo "メッセージ送信";
$result = $c->send($message);

if( !$result ){
exit('Could not connect to the server.');
}else if( $result ->faultCode() ){
exit('XML-RPC fault ('.$result ->faultCode().'): '
.$result ->faultString());
}

return $result ;
}
}


?>
