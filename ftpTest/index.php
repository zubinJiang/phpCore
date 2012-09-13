<?php
if(!$_FILES){
    exit("�ϴ��ļ����ܿ�");
}

if(!$_POST['filename']){
    exit("Ŀ¼�����ܿ�");
}

if(end(explode('.',$_FILES["file"]["name"]))!='zip'){
    exit("���ϴ�zip��ʽ���ļ�");
}

if($_FILES["file"]["error"] > 0){
  exit("Error: " . $_FILES["file"]["error"] . "<br />");
}

require("ftp_base.php");
define("ROOT_DIR", '/icon/article/subject/'.$_POST['filename']);
define("ROOT_HOST",'http://new-icon.ol-img.com/subject/'.$_POST['filename']);

$config = array(
    'host' =>  "127.0.0.1",
    'name' => "jiangzubin",
    'pass' => "jiangzubin",
    'dirname'   => '/icon/'
);

$zip = new ZipArchive(); 

$localfile = $_FILES["file"]["tmp_name"];

$tmp_dir = dirname($localfile)."\\".$_POST['filename'];

if(!file_exists($tmp_dir)){ mkdir($tmp_dir,0700); }

if($zip->open($localfile) !== TRUE) { 
    exit('���ܴ�ѹ���ļ�'); 
} 

try{
    $zip->extractTo($tmp_dir); //��ѹzip�ļ�
} catch (Exception $e) {
    throw $e;
}

$zip->close();

/**�����������ļ�**/

function get_filetree($path){
  $tree = array();
  foreach(glob($path.'\*') as $single){
    if(is_dir($single)){
      $tree = array_merge($tree, get_filetree($single));
    } else {
      $tree[] = $single;
    }
  }
  return $tree;
}

$file_arr = get_filetree($tmp_dir);

if(!$file_arr) { return; }

$ftp = new ftp($config['host'], $config['name'], $config['pass'], $config['dirname']);

foreach($file_arr as $v){
    $preg_content = $preg_test = $preg_str = $re_string = $ftp_url = $remotefile = $ftpput = $type = $file_name = '';
    $type = end(explode('.',$v));
    $file_name = end(explode('\\',$v));
    
    if($type=='js'|| $type=='swf'){
        $preg_content = file_get_contents($v);

        $preg_test = '/\/img-subject\//';
        $preg_str = ROOT_HOST.'/img/';
        $re_string = preg_replace($preg_test, $preg_str, $preg_content);
        file_put_contents($v,$re_string);
        
        $ftp_url = "/js/";
    } else if ($type=='css') {
        $preg_content = file_get_contents($v);

        $preg_test = '/\/img-subject\//';
        $preg_str = ROOT_HOST.'/img/';
        $re_string = preg_replace($preg_test, $preg_str, $preg_content);
        file_put_contents($v,$re_string);
        
        $ftp_url = "/css/";
    } else if ($type=='jpg' || $type=='gif' || $type=='png' || $type=='jpeg' || $type=='ico') {

        $ftp_url = "/img/";

    } else if ($type=='html' || $type=='htm') {
        $preg_content = file_get_contents($v);

        $img_preg_test = '/\/img-subject\//';
        $img_preg_str = ROOT_HOST.'/img/';
        $re_string = preg_replace($img_preg_test, $img_preg_str, $preg_content);

        $css_preg_test = '/\/css-subject\//';
        $css_preg_str = ROOT_HOST.'/css/';
        $re_string = preg_replace($css_preg_test, $css_preg_str, $re_string);

        $js_preg_test = '/\/js-subject\//';
        $js_preg_str = ROOT_HOST.'/js/';
        $re_string = preg_replace($js_preg_test, $js_preg_str, $re_string);
        file_put_contents($v,$re_string);

        $ftp_url = "/files/";
    } else {
        $ftp_url = "";
    } 

    $remotefile = ROOT_DIR.$ftp_url.$file_name;

    @$ftp->mkdir(ROOT_DIR);

    @$ftp->mkdir(ROOT_DIR.$ftp_url);

    $ftpput = $ftp->put($v, $remotefile); //FTP�ϴ��ļ���Զ�̷�����
    if(!$ftpput){
        exit("�ϴ��ļ���Զ�̷�����ʧ��!");  
    }
}

$url = "http://new-icon.ol-img.com/article/subject/".$_POST['filename']."/files/index.html";

echo "��������:<a href='".$url."'>".$url."</a>";

//system("rm -rf", $tmp_dir);

$ftp->bye(); //�ر�FTP����
