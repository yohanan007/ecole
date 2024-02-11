import { Dom, Card, Nav, Element } from "../utilitaire/element";
import { Heure } from "./agenda";


class ActionElementAgenda{

    int_time = new Date().getTime();
    str_typeElement = "button";
    obj_element = new Element();

    constructor(str_typeElement,int_time,obj_element){
        if(typeof(str_typeElement)!= "undefined"){
            this.str_typeElement = str_typeElement;
        }

        if(typeof(int_time)!="undefined"){
            this.int_time = int_time;
        }

        if(typeof(obj_element) != "undefined"){
            Object.assign(this.obj_element,obj_element);
        }
    }

    setId(str_id){
        this.obj_element.setId(str_id);
    }
    
    setClass(str_class){
        this.obj_element.setClasse(str_class);
    }

    setText(str_text){
        this.obj_element.setText(str_text);
    }

    getNavigate(){
        const int_timePrec = this.int_time - 3600 * 24;
        const str_timePrec = int_timePrec.toString();
        const int_timeSuiv = this.int_time + 3600 * 24;
        const str_timeSuiv = int_timeSuiv.toString();

        const obj_nav = new Nav([{
            "dom_element": "a",
            "type": "button",
            "text": "jour precedent",
            "href":"/agenda/" + str_timePrec
        }, {
                "dom_element": "a",
                "type": "button",
                "text": "jour suivant",
                "href":"/agenda/" + str_timeSuiv
            }], this.str_idElment,"",this.int_time.toString());

        const dom_nav_master_menu = obj_nav.getNav();

        const dom_div_support = document.createElement("div");
        dom_div_support.setAttribute("class", "container-fluid");
        dom_nav_master_menu.append(dom_div_support);
        return dom_nav_master_menu;
    }

    getButton(){
        const dom_button = document.createElement("button");
        this.obj_element.setClasse(this.obj_element.getClass() + " btn btn-secondary btn-lg");
        const obj_dom = new Dom(this.obj_element,dom_button);
        dom_button = obj_dom.getAttribute();
        return dom_button;
    }

    getAgenda(){

    }


    getSelectHoraire(str_time){
        const id_select = "select_" + id + "_" + str_time;
        const ob_select = new Dom("select",id_select,"form-select","",{"name":"select_horaire"})
        const dom_select = ob_select.getAttribute();

        let int_horaire = 0;
        let dom_option;
        let ob_option;
        let ob_time;

        //900 secondes  <=> 15 minutes
        //on commence à minuit
        //on suppose que une personne commence à travailler à partir de 08h00 (il faudra rendre ca paramétrable)
        // => i =  8*4 = 32(debut)
        // => i = 19*4 = 76(fin)
        let str_color = "";

        for (let i = 1; i < 96; i++){
            int_horaire = int_horaire + 900;

            str_color = "bg-primary text-white";

            if((i < 32) || (i > 76)){
                str_color = "bg-secondary text-white";
            }

            ob_time = new Heure(int_horaire);
            ob_option = new Dom("option","",str_color,ob_time.secondeToHourString(),{"value":int_horaire.toString()});
            dom_option = ob_option.getAttribute();
            dom_select.append(dom_option);
            return dom_select;
        }

    }

    
}