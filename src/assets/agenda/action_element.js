import { Dom, Card, Nav } from "../utilitaire/element";
import { Heure } from "./agenda";
import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { Autoformat } from '@ckeditor/ckeditor5-autoformat';
import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote';
import { Heading } from '@ckeditor/ckeditor5-heading';
import { Link } from '@ckeditor/ckeditor5-link';
import { List } from '@ckeditor/ckeditor5-list';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';


class ActionElementAgenda{

    int_time = new Date().getTime();
    str_typeElement = "button";
    obj_element;

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
        const ob_divSupport = new Dom("div","","container-fluid","");
        const dom_div_support = ob_divSupport.getAttribute();
        
        dom_nav_master_menu.append(dom_div_support);
        return dom_nav_master_menu;
    }

    getButton(str_id,str_text){
        const ob_button = new Dom(this.str_typeElement,str_id,"btn btn-secondary btn-lg",str_text,this.obj_element)
        const dom_button = ob_button.getAttribute();
        return dom_button;
    }

    getAgenda(){

    }


    getSelectHoraire(){
        const id_select = "select_horaire";
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

    getCard() {
        const id_header = "header_card_agenda";
        const id_body = "body_card_agenda";
        const id_footer = "footer_card_agenda";
    
        const obj_header = {
            "color_text": "bg-primary",
            "text": "",
            "id" : id_header
        }
    
        const obj_body = {
            "color_text": "bg-primary",
            "text": "RENDEZ-VOUS",
            "id":id_body
        }
    
        const obj_footer = {
            "color_text": "bg-dark",
            "text": " ",
            "id":id_footer
        }
    
        const cardClass = new Card(obj_header, obj_body, obj_footer);
        const cardDom = cardClass.getCreateCard();
        return cardDom;
    }


    async getClassicEditor(){
        ClassicEditor
        .create(document.querySelector('#editor'), {
            plugins: [ Essentials,
                Autoformat,
                Bold,
                Italic,
                BlockQuote,
                Heading,
                Link,
                List,
                Paragraph,],
            toolbar: [
                'heading',
                'bold',
                'italic',
                'link',
                'bulletedList',
                'numberedList',
                'blockQuote',
                'undo',
                'redo'
            ]
        } )
        .then( editor => {
            console.log(editor);
            return editor;
        } )
        .catch( error => {
            console.error( error.stack );
        });
    }

    getCardHeader(id_header){
        const dom_headerCArd = document.getElementById(id_header);
        const dom_navigate = this.getNavigate();
        dom_headerCArd.append(dom_navigate);
        return dom_headerCArd;
    }


    getElementClass(){
        return [];
    }
}

export {ActionElementAgenda};
