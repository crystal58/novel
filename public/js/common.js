function Ajax(url , data , type="POST", dataType="json" , async=false, callback = ""){
   $.ajax({
        type: type,
        url: url,
        data: data,
        dataType: dataType,
        success: function(result){
            console.log(result);
            if(callback != ""){
                callback(result)
            }
        }    
}); 
}
