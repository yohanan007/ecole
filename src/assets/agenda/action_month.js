import { Element, Card } from "../utilitaire/element";

class ActionMonth{
    coll_action = [];
    date_action = new Date();
    str_principal = "agenda_corps";

    constructor(coll_action,date_action){
        if(not(typeof(coll_action) === "undefined")){
            this.coll_action = coll_action;
        }

        if(not(typeof(date_action) === "undefined")){
            this.date_action = date_action;
        }
    }
}