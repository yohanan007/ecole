import { Element, Card } from "../utilitaire/element";
import { Jour } from "./agenda";


class ActionDay{
    coll_action = [];
    date_jour = new Date();
    str_principal = "agenda_corps";

    static id_header = "id_header_card_jour";
    static id_body = "id_body_card_jour";
    static id_footer = "id_footer_card_jour";

    constructor(coll_action, date_action,str_principal){
        if(!(typeof(coll_action) === "undefined")){
            this.coll_action = coll_action;
        }

        if(!(typeof(date_action) === "undefined")){
            this.date_action = date_action;
        }

        if(!(typeof(str_principal) === "undefined")){
            this.str_principal = str_principal;
        }
    }

    getElementsDuJour(){
        const obj_element = new Element(this.str_principal);
        obj_element.razElementId();
        const dom_elementPrincipal = obj_element.ElementId();
        return dom_elementPrincipal.append(this.getCard());
    }

    getHeureDuJour(int_time){
        const d_time = new Date(int_time);
        let str_color = "bg-primary";

        if((int_time < 28800) || (int_time > 68400 )){
            str_color = "bg-secondary";
        }

        const ob_reponse = {"heure":d_time.getHours(), "minute":d_time.getMinutes(), "class":str_color, "seconde": int_time};
        return ob_reponse;
    }

    getCard(){

        const obj_header = {
            "color_text": "bg-primary",
            "text": "",
            "id" : this.id_header
        }
    
        const obj_body = {
            "color_text": "bg-primary",
            "text": "rendez-vous",
            "id":this.id_body
        }
    
        const obj_footer = {
            "color_text": "bg-dark",
            "text": " ",
            "id":this.id_footer
        }
    
        const cardClass = new Card(obj_header, obj_body, obj_footer);
        return  cardClass.getCreateCard();
    }

    getCardHeader(){
        const dom_element = document.getElementById(this.id_header);
        
        return false;
    }

    getCardBody(){
        const dom_element = document.getElementById(this.id_body);
        return false;
    }

    getCardFooter(){
        const dom_element = document.getElementById(this.id_footer);
        return false;
    }

    getInfoDuJour(str_id,str_info){
        str_info = (typeof str_info  === "undefined")? "" : str_info;
        str_id = (typeof str_id  === "undefined")? "info_agenda_date_du_jour" : str_id;

        if(str_info === ""){
            const d_dateDuJour = new Date();
            const ob_day = new Jour(d_dateDuJour.getDate(),d_dateDuJour.getMonth(),d_dateDuJour.getFullYear(),d_dateDuJour);
            str_info = ob_day.toString();
        }

        if(str_id !== ""){
            const dom_id = document.getElementById(str_id);
            dom_id.innerText = str_info.toUpperCase();
        }
    }

}

export {ActionDay};