import { Element, Card } from "../utilitaire/element";


class ActionDay{
    coll_action = [];
    date_jour = new Date();
    str_principal = "agenda_corps";

    static id_header = "id_header_card_jour";
    static id_body = "id_body_card_jour";
    static id_footer = "id_footer_card_jour";

    constructor(coll_action, date_action,str_principal){
        if(not(typeof(coll_action) === "undefined")){
            this.coll_action = coll_action;
        }

        if(not(typeof(date_action) === "undefined")){
            this.date_action = date_action;
        }

        if(not(typeof(str_principal) === "undefined")){
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
        return {"heure":d_time.getHours(), "minute":d_time.getMinutes()};
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
}