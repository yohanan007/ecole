class Envoi{
    str_method = "GET";
    str_link = "";
    obj_data = {};
    arr_header = [];
    obj_finale = {};
    obj_request = new XMLHttpRequest();


    constructor(str_link,str_method,obj_data,arr_header,obj_finale){
        this.arr_header.push({"Content-type": "application/json"});

        if(typeof(str_link)!=="undefined"){
            this.str_link = str_link;
        }

        if(typeof(str_method)!== "undefined"){
            this.str_method = str_method;
        }

        if(typeof(obj_data)!== "undefined"){
            Object.assign(this.obj_data,obj_data)
        }

        if(typeof(arr_header) !== "undefined"){
            if(arr_header.length > 0){
                this.arr_header = arr_header;
            }
            
        }
        
        if(typeof(obj_finale) !== "undefined"){
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
        if(this.str_method === "GET"){
            if(this.obj_data !== null){
                b_start = true;
                let str_join = "?";
                for (const [key, value] of Object.entries(this.obj_data)){
                    this.str_link = this.str_link + str_join + key + "=" + value;
                    if((b_start)){
                        str_join = "&";
                        b_start = false;
                    }
                }
                this.obj_data = {};
            }
        }

        this.obj_request.open(this.str_method, this.str_link);
    }

    getActionFinal() {
        if(this.obj_finale != null){
            let b_first = true;
            str_function = this.obj_finale.nameFunction + "(";
            this.obj_finale.argument.forEach(element => {
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

    async actionEnvoi() {
        this.getOpen();
        this.getHeader();
        const result = new Promise((resolve) =>{
            this.obj_request.onload =  (e) => {
                const data = this.obj_request.response;
                this.getActionFinal();
                resolve(data);
                }
            });
        this.getData();
        return result;
    }
}


export { Envoi }