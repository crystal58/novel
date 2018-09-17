<?php
namespace YC\File;
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 17/12/6
 * Time: 下午6:16
 */
class fastDfsUpload{
    public function store($file,$suffix){
        //$a = fastdfs_storage_upload_by_filename("/var/www/html/novel/public/images/11.jpg");

        $fileInfo = fastdfs_storage_upload_by_filename1($file,$suffix);
        echo json_encode(array($file,$suffix,$fileInfo));
        exit;
        return $fileInfo;
    }

    public function get($fileId){
        return \fastdfs_storage_download_file_to_buff1($fileId);;
    }
}
