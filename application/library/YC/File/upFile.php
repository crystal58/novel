<?php
namespace YC\File;
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 17/12/6
 * Time: 下午6:14
 */
class upFile{
    const MAX_FILE_SIZE = 10485760; //10M
    private $_FileType = array(
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpeg",
        "jpe" => "image/jpeg",
        "png" => "image/png",
        "gif" => "image/gif",
        "bmp" => "image/bmp",
        "tiff" => "image/tiff",
    );
    private $_config = array();

    private $_file = null;
    public function __construct(){
        $config = \Yaf_Registry::get("dbconfig");
        $this->_config = $config['fastdfs'];
        $this->_file = new fastDfsUpload();

    }
    public function store($file,$waterMark = false){
        $this->_checkType($file);
        $suffix = $this->_getFileSuffix($file['name']);
        if($waterMark) {
            $file['tmp_name'] = $this->_setWaterMark($file['tmp_name']);
        }

        $result = $this->_file->store($file['tmp_name'],$suffix);
        return $result;
    }


    public function get($file){
        if(!$file)return "";
        if(strpos($file,"group1") !== false){
            $baseUrl = $this->_config['group1'];
        }else{
            $baseUrl = $this->_config['group2'];
        }

        return $baseUrl.$file;
    }
    private function _checkType($file){
        $fileType = $this->_getFileType($file);
        if(!in_array($fileType,$this->_FileType)){
            throw new Exception("No support the file type");
        }
    }

    private function _getFileType($file){
        $finfo = new \finfo(FILEINFO_MIME);

        if(!$finfo){
            throw new Exception("Opening fileinfo database failed");
        }
        $filename = $file["tmp_name"];

        $fileInfo = $finfo->file($filename);

        $fileInfo = explode("; ", $fileInfo);
        $type = "";
        if (is_array($fileInfo)) {
            $type = $fileInfo[0];
        }

        return $type;
    }
    public function getFile($fileId){
        $file = $this->_file->get($fileId);
        if(empty($file))return false;

        $fileInfo = $this->_getMediaInfo($fileId);

        $mediaInfo = array(
            "type" => $fileInfo["type"],
            "length" => strlen($file),
            "name" => $fileInfo["filename"],
            "md5" => "",
            "data" => new stdClass()
        );
        //$mediaInfo['data']->bin = $file;
        return $mediaInfo;
    }
    public function downFile($fileId,$fileName = null){

        $fileInfo = $this->getFile($fileId);
        if(empty($fileInfo)) return false;
        $fileName = $fileInfo['name'];
        echo $suffix = $this->_getFileSuffix($fileId);

        //var_dump($fileInfo);exit;

        header ( "Content-type: {$fileInfo['type']}" );
        header ( "Content-Length: {$fileInfo['length']}" );
        header ( "File-Name: {$fileInfo['name']}" );
        header ( "Content-MD5: {$fileInfo['md5']}" );
        Header ( "Content-Disposition: attachment; filename={$fileName}.{$suffix}" );
        echo $fileInfo['data']->bin;
        exit ();

    }
    private function _getMediaInfo($mediaId) {
        $id = substr($mediaId, strrpos($mediaId, "/") + 1);
        $filename = substr($id, 0, strrpos($id, ".") > 0 ? strrpos($id, ".") : strlen($id));
        $suffix = $this->_getFileSuffix($id);
        $fileType = isset($this->_fileType[$suffix]) ?$this->_fileType[$suffix] : "text/plain";
        return array(
            "filename" => $filename,
            "suffix" => $suffix,
            "type" => $fileType
        );
    }
    private function _getFileSuffix($file){
        $suffix = strrpos($file, ".") > 0 ? substr($file, strrpos($file, ".") + 1) : "txt";
        $suffix = strtolower($suffix);
        return $suffix;
    }

    private function _setWaterMark($file,$waterTxt = "文学星空 eeeaaa.cn"){
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($file));
        list($dst_w, $dst_h, $dst_type) = getimagesize($file);

        //打上文字
        $font = APPLICATION_PATH.'public/fonts/font.ttf';//字体
        $black = imagecolorallocate($dst, 0xdc, 0x14, 0x3c);//字体颜色
        imagefttext($dst, 16, 5, 20, $dst_h-$dst_h/6, $black, $font, $waterTxt);

        //输出图片

        switch ($dst_type) {
            case 1://GIF
                //header('Content-Type: image/gif');
                imagegif($dst,$file);
                break;
            case 2://JPG
               // echo 3333;exit;
                //header('Content-Type: image/jpeg');
                imagejpeg($dst,$file);
                break;
            case 3://PNG
                //header('Content-Type: image/png');
                imagepng($dst,$file);
                break;
            default:
                break;
        }

        imagedestroy($dst);
        return $file;
    }
}
