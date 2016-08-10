<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

require('smart_resize_image.function.php');
ini_set('upload_max_filesize','40M');
ini_set('post_max_size', '40M');

if (!file_exists($imageDir)) {
 mkdir($imageDir);
}
$upload_type = (@$_REQUEST['upload_type']== '')?"users":$_REQUEST['upload_type'];
if($upload_type == 'area_map') $dirname = "maps";
else $dirname = "users";

$imageDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'd2dserver'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .$dirname;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
 $chunkDir = $imageDir . DIRECTORY_SEPARATOR . $_GET['flowIdentifier'];
 $chunkFile = $chunkDir.'/chunk.part'.$_GET['flowChunkNumber'];
 if (file_exists($chunkFile)) {
  header("HTTP/1.0 200 Ok");
 } else {
  header("HTTP/1.1 204 No Content");
 }
}
// Just imitate that the file was uploaded and stored.
// Just imitate that the file was stored.
        /*echo json_encode([
            'success' => true,
            'files' => $_FILES,
            'get' => $_GET,
            'post' => $_POST,
            //optional
            'flowTotalSize' => isset($_FILES['file']) ? $_FILES['file']['size'] : $_GET['flowTotalSize'],
            'flowIdentifier' => isset($_FILES['file']) ? $_FILES['file']['name'] . '-' . $_FILES['file']['size']
                : $_GET['flowIdentifier'],
            'flowFilename' => isset($_FILES['file']) ? $_FILES['file']['name'] : $_GET['flowFilename'],
            'flowRelativePath' => isset($_FILES['file']) ? $_FILES['file']['tmp_name'] : $_GET['flowRelativePath']
        ]);*/
        /*$data = Utils::processRequest();*/
        $filename = isset($_FILES['file']) ? $_FILES['file']['tmp_name'] : $_GET['flowRelativePath'];
        $assignedname = $upload_type.time().'_'.str_replace(array('~','!','#','$','%','^','&','*','(',')','+','{','}','[',']','|'), '', isset($_FILES['file']) ? $_FILES['file']['name'] : $_GET['flowFilename']);

//sleep(2);
$destination_file =  $imageDir.'/'.$assignedname;

if(is_uploaded_file($filename)){

    if(move_uploaded_file($filename,$destination_file)){ 
        
        $thumb_name = 'pic_'.$assignedname;
        $thumb_file = $imageDir.'/'.$thumb_name;
        smart_resize_image($destination_file , null, 500 , 500 , false , $thumb_file , false , false ,100 );
         //echo $assignedname;exit;
        if($upload_type == 'area_map'){
            echo $thumb_name;exit;
        }
        else{
            $userId = isset($_REQUEST['userId'])?$_REQUEST['userId']:'';
            if($userId ==''){
                echo $thumb_name;exit;
            }
            $ch = curl_init();                    // initiate curl
            $url = "http://d2dworks.cloudbydesign.co.uk/d2dserver/web/uploadImage"; // where you want to post data
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, true);  // tell curl you want to post something
            curl_setopt($ch, CURLOPT_POSTFIELDS, "image_name=".$assignedname."&userId=".$userId); // define what you want to post
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the output in string format
            $output = curl_exec ($ch); // execute
            curl_close ($ch); // close curl handle
        }
        
    }
    else{
        die("Cannot upload ");
    }

 }
 else{
     die("not uploaded file");
 }