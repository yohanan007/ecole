class Communique{
    str_method = "GET";
    str_link = "";
    obj_data = {};
    arr_header = [];
    obj_request = new XMLHttpRequest();


    constructor(str_method,obj_data,arr_header){
        if(typeof(str_method)!= "undefined"){
            this.str_method = str_method;
        }

        if(typeof(obj_data)!= "undefined"){
            Object.assign(this.obj_data,obj_data)
        }

        if(typeof(arr_header) != "undefined"){
            this.arr_header = arr_header;
        }
    }

    getData(){

    }

}