class Envoi{
    str_method = "GET";
    str_link = "";
    obj_data = {};
    arr_header = [];
    obj_finale = null;
    obj_request = new XMLHttpRequest();


    constructor(str_link,str_method,obj_data,arr_header,obj_finale){
        this.arr_header.push({"Content-type": "application/json"});

        if(typeof(str_link)!="undefined"){
            this.str_link = str_link;
        }

        if(typeof(str_method)!= "undefined"){
            this.str_method = str_method;
        }

        if(typeof(obj_data)!= "undefined"){
            Object.assign(this.obj_data,obj_data)
        }

        if(typeof(arr_header) != "undefined"){
            this.arr_header = arr_header;
        }
        
        if(typeof(obj_finale) != "undefined"){
            Object.assign(this.obj_finale,obj_finale);
        }
    }


    getSend(){
        this.obj_request.send(this.obj_data);
    }

    getHeader(){
        this.arr_header.forEach((element,key) => {
            this.obj_request.setRequestHeader(key,element);
        });
    }

    getData() {
        this.obj_request.send(JSON.stringify(this.obj_data));
    }

    getOpen(){
        this.obj_request.open(this.str_method, this.str_link);
    }

    getActionFinal() {
        if(this.obj_finale != null){
            let b_first = true;
            str_function = obj_finale.nameFunction + "(";
            obj_finale.argument.forEach(element => {
                if(b_first){
                    b_first = false;
                    str_function = str_function  + element;
                }else{
                    str_function = str_function + "," + element;
                }
                
            });
            str_function = str_function + ")"
            eval(str_function);
        }else{
            location.reload();
        }
       
    }

    actionEnvoi() {
        this.getOpen();
        this.getHeader();
        this.obj_request.onload = (e) => {
            console.log(e);
            let data = this.obj_request.response;
            this.getActionFinal();
          };

          this.getSend();
    }
}


export { Envoi }