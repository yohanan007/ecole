import { Envoi } from "../utilitaire/envoi_utilitaire";

class ClasseEleve{
    
    str_adresseAction = "";
    str_token = "";

    constructor(str_adresseAction,str_token){
        this.str_adresseAction = str_adresseAction;
        if(typeof(str_token) !== "undefined"){
            console.log(str_token);
            this.str_token = str_token;
        }else{
            const dom_eleve = document.getElementById("token_info_utilisateur");
            this.str_token = dom_eleve.value;
        }
       
    }

    async getAllElevesByClasse(str_selectClasse){
        const obj_fonction =  {"nameFunction":"console.log","argument":["this.obj_request.response"]};
        const obj_envoi =  new Envoi(this.str_adresseAction,'GET',{'data':'classe','classe':str_selectClasse,'info_data_eleve' : this.str_token},[],obj_fonction);
        return new Promise((resolve) =>{
            obj_envoi.actionEnvoi().then(data =>{
                resolve(data);
            });
        });
    }

    async getAllClasses(){
        const obj_fonction =  {"nameFunction":"console.log","argument":["this.obj_request.response"]};
        const obj_envoi =  new Envoi(this.str_adresseAction,'GET',{'data':'eleve','info_data_eleve' : this.str_token},[],obj_fonction);
        return new Promise((resolve) =>{
            obj_envoi.actionEnvoi().then((data)=>{
                resolve(data);
            });
        });
    }
}

export { ClasseEleve };