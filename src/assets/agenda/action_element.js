import { Dom, Card, Nav, Grid, Element } from "../utilitaire/element";
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
import { Envoi } from "../utilitaire/envoi_utilitaire";
import { AgendaService } from "./AgendaService";

class ActionElementAgenda{

    int_time = new Date().getTime();
    str_typeElement = "button";
    obj_element;
    editor = null; // Store CKEditor instance for use in event listeners

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


    /* aide à la gestion de classic editor */
    getPlugin(){
        return [ Essentials,
            Autoformat,
            Bold,
            Italic,
            BlockQuote,
            Heading,
            Link,
            List,
            Paragraph,];
    }

    getToolbar(){
        return [
            'heading',
            'bold',
            'italic',
            'link',
            'bulletedList',
            'numberedList',
            'blockQuote',
            'undo',
            'redo',
        ];
    }
    /* fin de aide à la gestion classic editor */

    getSelectHoraire(id_select){
        id_select =  id_select  || "select_horaire" ;
        const ob_select = new Dom("select",id_select,"form-select","",{"name":id_select})
        const dom_select = ob_select.getAttribute();

        let int_horaire = 0;
        let dom_option;
        let ob_option;
        let ob_time;
        let ob_more;

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

            ob_more = {"value":int_horaire.toString()};

            if(i === 32){
                ob_more.selected = "true";
            }

            ob_option = new Dom("option","",str_color,ob_time.secondeToHourString(),ob_more);
            dom_option = ob_option.getAttribute();
            dom_select.append(dom_option);
        }
            return dom_select;
    }

    /*on intégrera cela plus tard dans une classe*/
    getGenerateMenu(str_time) {

        if (typeof str_time === "undefined") {
            str_time = "";
        }

        //création du nav maitre
        
        const str_id_menu = "master_menu_" + str_time;

        const int_timePrec = parseInt(str_time) - 3600 * 24;
        const str_timePrec = int_timePrec.toString();
        const int_timeSuiv = parseInt(str_time) + 3600 * 24;
        const str_timeSuiv = int_timeSuiv.toString();


        const cl_nav = new Nav([{
            "dom_element": "a",
            "type": "button",
            "text": "jour precedent",
            "href":"/agenda/" + str_timePrec
        }, {
                "dom_element": "a",
                "type": "button",
                "text": "jour suivant",
                "href":"/agenda/" + str_timeSuiv
            }], str_id_menu,"",str_time);

        const dom_nav_master_menu = cl_nav.getNav();

        const dom_div_support = document.createElement("div");
        dom_div_support.setAttribute("class", "container-fluid");

        dom_nav_master_menu.append(dom_div_support);
        return dom_nav_master_menu;

    }

    /*gestion card elememnt */
    async getCard(id_add,str_idTemp) {
        let id_body;
        let id_footer;
        let id_header;

        if(typeof(str_idTemp) !== "undefined"){
            id_header = "header_" + str_idTemp;
            id_body = "body_"+str_idTemp;
            id_footer = "footer_"+str_idTemp;
        }else{
            id_header = "header_card_agenda";
            id_body = "body_card_agenda";
            id_footer = "footer_card_agenda";
        }

    
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

        const dom_tmp = document.getElementById(id_add);
        dom_tmp.append(cardDom);

        this.getCardHeader(id_header);
        await this.getCardBody(id_body);
        this.getCardFooter(id_footer,str_idTemp);
        await this.getClassicEditor(id_footer);
        return cardDom;
    }


    async getClassicEditor(id_footer,str_function){
        id_footer = id_footer || ("footer_" + this.int_time.toString());
        
        try {
            const editorElement = document.querySelector('#editor');
            if (!editorElement) {
                console.error('❌ Editor element #editor not found in DOM');
                return null;
            }
            
            // Store editor instance as class property for use in event listeners
            this.editor = await ClassicEditor.create(editorElement, {
                plugins: this.getPlugin(),
                toolbar: this.getToolbar(),
                language: 'en' // Set language explicitly - no translation plugin needed
            });
            console.log('✅ CKEditor initialized successfully');
        } catch (error) {
            console.error('❌ CKEditor error:', error);
            return null;
        }

        const str_token = document.getElementById("token").value;
        const dom_button = document.getElementById("button_action_footer");
        const str_idTemp = dom_button.getAttribute("data").replace("footer_","");

        //secondeDebut
        //secondeFin
        //data

        // Use arrow function to preserve 'this' context
        dom_button.addEventListener("click", (e) => {
            if(typeof(str_function)!== "undefined"){
                //à definir plus tard
            }else{
                //recupération donnée lié aux données de l'utilisateur
                let str_heureDebut = document.getElementById("heureDebut_select").value;
                let str_heureFin = document.getElementById("heure_fin_select").value;

                let str_idClass = document.getElementById("id_list_classe").value
                let str_idEleve = document.getElementById("id_list_eleve").value

                const editorData = this.editor ? this.editor.getData() : '';

                str_heureDebut =  String(Number(str_heureDebut) + Number(str_idTemp));
                str_heureFin =  String(Number(str_heureFin) + Number(str_idTemp));
                
                obj_envoi =  new Envoi('','POST',{'data':editorData,'eleve':str_idEleve,'classe':str_idClass,'secondeFin':str_heureFin,'secondeDebut' : str_heureDebut,'create-time' : str_token});
                obj_envoi.actionEnvoi();
            }
            
            e.preventDefault();
        });

        return editor;
    }

    getCardHeader(id_header){
        console.log(id_header);
        const dom_headerCArd = document.getElementById(id_header);
        console.log(dom_headerCArd);
        const dom_navigate = this.getNavigate();
        dom_headerCArd.append(dom_navigate);
        return dom_headerCArd;
    }

    getCardFooter(id_footer){
        //permet de sauvegarder l'élément dans editor
        const dom_footerCard = document.getElementById(id_footer);
        const dom_button = document.createElement("button");
        const id_button = "button_action_footer";
        dom_button.setAttribute("id", id_button);
        dom_button.setAttribute("class", "btn btn-light");
        dom_button.setAttribute("data",id_footer);
        dom_button.textContent = "valider";
        dom_footerCard.append(dom_button);
        return dom_footerCard;
    }


    async getCardBody(id_body){
        const dom_bodyCard = document.getElementById(id_body);
        const dom_textArea = document.createElement("textarea");
        dom_textArea.setAttribute("class", "form-control mt-3 pt-3");
        dom_textArea.setAttribute("rows", "4");

        const div_top = document.createElement("div");
        div_top.setAttribute("class", "mt-3 pt-3");

        const dom_select = document.createElement("div");
        dom_select.setAttribute("id", "select_horaire");
        dom_bodyCard.append(dom_select);

        const str_token = document.getElementById("token_info_utilisateur").value;

        const select_domClasse = await AgendaService.getSelectClasse(str_token);

        const div_domEditor = document.createElement("div");
        div_domEditor.setAttribute("id", "editor");

        const ob_label = new Dom("label","label_select_classe","","",{"for":"id_list_classe"});
        const dom_label = ob_label.getAttribute();
        dom_label.textContent = "Classe";

        const ob_divClasse = new Dom("div","div_select_classe","mb-1","");
        const dom_divClasse = ob_divClasse.getAttribute();

        dom_divClasse.append(dom_label);
        dom_divClasse.append(select_domClasse);

        const arr_elementGrid = [];
        arr_elementGrid.push({"element":dom_divClasse});
        const ob_domGrid = new Grid(3,arr_elementGrid,undefined,"grid_select");

        div_top.append(ob_domGrid.getElement());
        div_top.append(div_domEditor);
        dom_bodyCard.append(div_top);

        select_domClasse.addEventListener("change", function(e){
            //id de la classe choisi
            const ob_action = new ActionElementAgenda();
            ob_action.changeClasse(e.target.value).then(dom_selectEleve => {

                const str_id = "col-" + "grid_select-" + "1";

                const obj_element = new Element("div",str_id);
                obj_element.razElementId();

                const dom_grid = obj_element.ElementId();

                const ob_divClasse_ = new Dom("div","div_select_eleve","mb-1","");
                const dom_divClasse_ = ob_divClasse_.getAttribute();

                const ob_label_ = new Dom("label","label_select_eleve","","",{"for":"id_list_eleve"});
                const dom_label_ = ob_label_.getAttribute();
                dom_label_.textContent = "Eleve";

                dom_divClasse_.append(dom_label_);
                dom_divClasse_.append(dom_selectEleve);

                dom_grid.append(dom_divClasse_);
            });
        });
    }


    async changeClasse(str_idClasse){
        return new Promise((resolve) => {
            const str_token = document.getElementById("token_info_utilisateur").value;
            AgendaService.getSelectEleve(str_idClasse, str_token).then(value => {
                resolve(value);
            }).catch(e => {
                console.log("error" + e);
            });
        });
    }
    /*fin de gestion card element */


}

export {ActionElementAgenda};
