class Envoi{
    obj_data = null;
    str_link = "";
    obj_header = null;
    str_srcf = "";

    constructor(str_link = "", obj_data = null,obj_header = null) {
        console.log(obj_data);
        this.str_link = str_link;
        this.obj_data = obj_data;
        this.obj_header = obj_header;
    }
    
    getMethod() {
        return 'POST';
    }

    getActionFinal() {
        location.reload();
    }

    getData() {
        return JSON.stringify(this.obj_data);
    }
    
    actionEnvoi() {

        let obj_request = new XMLHttpRequest();
        
        obj_request.onload = (e) => {
            console.log(e);
            let arraybuffer = obj_request.response; // not responseText
            console.log("la");
            /* … */
          };
        obj_request.open(this.getMethod(), this.str_link);
        obj_request.setRequestHeader("Content-type", "application/json");

        obj_request.send(this.getData());
    }
}


export { Envoi }