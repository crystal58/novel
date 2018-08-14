<?php
class BaseController extends AbstractController{

    public function uploadAction(){
        
    
    }

    public function doUploadAction(){
        $file = $_FILES;
        $file['mimeType'] = mime_content_type($file['img']['tmp_name']);
        echo json_encode($file);exit;
    }
}
