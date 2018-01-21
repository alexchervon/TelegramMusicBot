<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '6000');
require_once('getid3/getid3.php');
require_once('getid3/write.php');

class YandexDisk {
    private $token = 'token here';
    private $header = array();

    
    function __construct() {
        $this->header = array('Accept: application/json','Authorization: OAuth '.$this->token);
 
    }
     
    public function uploadFile($url,$name,$path="") {   
            $url = "https://cloud-api.yandex.net/v1/disk/resources/upload?path=$path/$name.mp3&url=$url&overwrite=true";
            $result = $this->sendRequest($url,CURLOPT_POST); 
            return $result;
    
    }
    
    public function createFolder($name) {
            $url = "https://cloud-api.yandex.net/v1/disk/resources/?path=$name";
            $result = $this->sendRequest($url,CURLOPT_PUT); 
            return $result;     
    }
    
    public function getUrl($id) {
        $url = "https://cloud-api.yandex.net/v1/disk/resources?path=$name";
        $result = $this->sendRequest($url,CURLOPT_PUT); 
        return $result;   
    }
    public function checkState() {
        $url = "https://cloud-api.yandex.net/v1/disk/operations/4097f4ac0496fd07378639d756888201cdfeb64189d9fba90a4940b358c9727e";
        $result = $this->sendRequest($url,CURLOPT_POST); 
        return $result;   
    }
    
    public function sendRequest($url,$type){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, $type, 1); 
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;  
    }
    

}

class AudioApi {

    private $user_agent = "VKAndroidApp/4.13.1-1206 (Android 4.4.4; SDK 19; armeabi; ; ru)";
    private $curl;
    private $token = "token here";
    
    function __construct() {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT,$this->user_agent);
    }
    
    public function getMusicTrack($user_id,$count,$offset) {
       $url = "https://api.vk.com/method/audio.get?v=5.68";  
       $data = array(
            'device_id'=>'f8f11195ae1c5a17',
            'access_token'=>$this->token,
            'uid'=>$user_id,
            'count'=>$count,
           'offset'=>$offset,
            'uid'=>$user_id
            
        );
       $param = http_build_query($data);
       $sig = $this->md5Hash("/method/audio.get?v=5.68".$param."0be6228fc130f0ef2e");
       curl_setopt($this->curl, CURLOPT_URL, $url.$param."&sig=".$sig);
       $result = curl_exec($this->curl);
       curl_close($this->curl);
        
       return $result;
    }

    public function md5Hash($string){
       return md5($string);
    }
    

}

class TelegramApi {
    private $token = "token here";

    public function sendMessage() {
        
    }
    public function sendAudio($chat_id,$url) {
        $result = file_get_contents('https://api.telegram.org/bot'.$this->token.'/sendAudio?chat_id='.$chat_id.'&audio='.$url,true);
        return $result;
    }
}

class changeFile {
    
    public function changeParam($name,$artist,$link) {
        $name = str_replace(' ', '', $name);
        $uploadfile = "files/".basename("$name.mp3");
        
        if (copy($link, $uploadfile)){
            echo "Файл успешно загружен на сервер";
        }

            $TextEncoding = 'UTF-8';
            $getID3 = new getID3;
            $getID3->setOption(array('encoding'=>$TextEncoding));
            $tagwriter = new getid3_writetags;
            $tagwriter->filename = $uploadfile;
            $tagwriter->tagformats = array('id3v2.3');
            $tagwriter->overwrite_tags    = true;  
            $tagwriter->remove_other_tags = false; 
            $tagwriter->tag_encoding      = $TextEncoding;
            $tagwriter->remove_other_tags = true;

        $TagData = array(
	           'title'                  => array($name),
	           'artist'                 => array($artist),
	           'album'                  => array('Greatest Hits'),
	           'genre'                  => array('vk music bot!'),
	           'comment'                => array('vk music bot!'),
	           'track'                  => array('04/16'),
	           'popularimeter'          => array('email'=>'alexchervon@example.net', 'rating'=>128, 'data'=>0),
	           'unique_file_identifier' => array('ownerid'=>'alexchervon@example.net', 'data'=>md5(time())),
        );
        
        $tagwriter->tag_data = $TagData;
        if ($tagwriter->WriteTags()) {
	       echo 'Ошибка изменения тегов<br>';
	           if (!empty($tagwriter->warnings)) {
		          echo 'Ошибка:<br>'.implode('<br><br>', $tagwriter->warnings);
	   }
        } else {
	       echo 'Нельзя изменить теги!<br>'.implode('<br><br>', $tagwriter->errors);
        }
    }
    
    public function delete($path) {
        if (file_exists('files/')) {
        foreach (glob('files/*') as $file) {
            unlink($file);
        }
    }
        return 'deleted!';
    }
    
}
 $Audio = new AudioApi();
 $Yandex = new YandexDisk();
 $Telegram = new TelegramApi();
 $File = new changeFile();

$offset = "50";
$user_id = "97257820";
$response = json_decode($Audio->getMusicTrack($user_id,"10",$offset));
$Yandex->createFolder("97257820");

    foreach ($response->response->items as $item) {
            $File->changeParam($item->title,$item->artist,$item->url);
            $name = str_replace(' ', '', $item->title);
            $Telegram->sendAudio("385291829","https://alexchervon.ru/tmbotvk/files/$name.mp3");
            $Yandex->uploadFile($item->url,$item->title,$user_id);
        
            $File->delete("files/".$item->title);

}

echo "Ok";
