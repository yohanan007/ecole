import {  Mois } from "./agenda";
import { Table, Card, Element, Nav, Grid } from "../utilitaire/element";

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




/**
 * @todo : retraité la page en terme de classe
 */
function generateMois(obj_date, dom_id) {
    
    //const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    if (typeof dom_id === "undefined") {
        dom_id = "agenda_corps";
    }

    let mois;
    let arr;
    let arr_ligne = [];
    let arr_header;
    let n_time;
    if (typeof obj_date === "undefined") {
        mois = new Mois();
        n_time = mois.getTime();
    } else {
        mois = new Mois(obj_date.annee, obj_date.mois, obj_date.jour);
        n_time = mois.getTime();
    }
    
    arr = mois.getMonth();
    let i = 0;
    arr_header = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
    let arr_temp = [];
    let str_class = "";
    arr.forEach(element => {
        if (element.getTime() === n_time) {
            str_class = "table-active";
        } else {
            str_class = "";
        }
        if (i < 7) {
            arr_temp.push({
                "classe": str_class,
                "corps": "",
                "sujet": element.toString({ month: "long", day: "numeric" }),
                "id": element.getTime().toString()
            });
            i = i + 1;
        } else {
            arr_ligne.push(arr_temp);
            arr_temp = [];
            arr_temp.push({
                "classe": str_class,
                "corps": "",
                "sujet": element.toString({ month: "long", day: "numeric" }),
                "id": element.getTime().toString()
            });
            i = 1;
        }
    });
    arr_ligne.push(arr_temp);



    let obj_table = new Table(arr_header, arr_ligne, []);

    let table = obj_table.getTable()

    let dom_div = document.getElementById(dom_id);
    let dom_div_table = document.createElement("div");
    dom_div_table.setAttribute("class","table-responsive-xxl");
    dom_div_table.append(table);
    dom_div.append(dom_div_table);
    startActionTableau(dom_div);
}


function startActionTableau(dom_div) {
        const elementActive = document.getElementsByTagName("td");
        for (let k = 0; k < elementActive.length; k++) {
            if (typeof elementActive[k] !== "undefined") {
                elementActive[k].addEventListener("click", function (e) {
                    const str_idTemp = e.target.id;
                    const elementtab = new Element(dom_div.id);
                    let cardDom = afficheCard(str_idTemp);
                    elementtab.razElementId();
                    dom_div.append(cardDom);
                    const id_body = "body_" + str_idTemp;
                    const bodyDom = document.getElementById(id_body);
                    
                    /* generation de la selection de l'horaire */
                    const labelDom = document.createElement("label");
                    labelDom.textContent = "Heure de debut";

                    const labelDom_ = document.createElement("label");
                    labelDom_.textContent = "Heure de Fin";

                    const div_heureDebut = document.createElement("div");
                    div_heureDebut.append(labelDom);
                    div_heureDebut.append(afficheElementBody(str_idTemp,"heure_debut"))

                    const div_heureFin = document.createElement("div");

                    div_heureFin.append(labelDom_);
                    div_heureFin.append(afficheElementBody(str_idTemp,"heure_fin"))

                    const arr_elementGrid = [];
                    arr_elementGrid.push({"element":div_heureDebut});
                    arr_elementGrid.push({"element":div_heureFin});

                    obj_domGrid = new Grid(3,arr_elementGrid);
                    bodyDom.append(obj_domGrid.getElement());
                    /* fin de la generation de l'horaire */

                    //selection du header de la card crée
                    const id_header = "header_" + str_idTemp;
                    const dom_header = document.getElementById(id_header);

                    /*on intégre ici un menu avec date - retour arriére et autre bouton sinécessaire*/

                    const button_dom_return = document.createElement("button");
                    button_dom_return.textContent = "retour";
                    dom_header.append(generateMenu(str_idTemp));

                    const dom_textArea = document.createElement("textarea");
                    dom_textArea.setAttribute("class", "form-control mt-3 pt-3");
                    dom_textArea.setAttribute("rows", "4");


                    const div_top = document.createElement("div");
                    div_top.setAttribute("class", "mt-3 pt-3");

                    const div_domEditor = document.createElement("div");
                    div_domEditor.setAttribute("id", "editor");

                    div_top.append(div_domEditor);

                    bodyDom.append(div_top);

                    let editor;

                    ClassicEditor
                    .create( document.querySelector( '#editor' ), {
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
                        console.log( 'Editor was initialized', editor );

                        const id_footer = "footer_" + str_idTemp;
                        const id_button = "button_" + str_idTemp;
                        const dom_footer = document.getElementById(id_footer);
                        const dom_button = document.createElement("button");
                        dom_button.setAttribute("id", id_button);
                        dom_button.setAttribute("class", "btn btn-light");
                        dom_button.textContent = "valider";
                        /**
                         * todo: à mettre en fonction d'une classe élément
                         */
                        const str_token = document.getElementById("token").value;
                        dom_button.addEventListener("click",function(e){
                            saveInformation(editor,{'time' : str_idTemp,'create-time' : str_token});
                        });
                        dom_footer.append(dom_button);
                        e.preventDefault();
                    } )
                    .catch( error => {
                        console.error( error.stack );
                    });
                });
            }
        }
}

/*on intégrera cela plus tard dans une classe*/
function generateMenu(str_time) {
    
    if (typeof str_time === "undefined") {
        const str_time = "";
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

    const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" };
    const d_time = new Date(parseInt(str_time));
    const str_day = d_time.toLocaleString('fr-FR', options);


    /*const dom_a_logo = document.createElement("a");
    dom_a_logo.setAttribute("class", "navbar-brand");
    dom_a_logo.textContent = str_day;

    dom_div_support.append(dom_a_logo);*/
    return dom_nav_master_menu;

}


function afficheCard(str_time) {
    const id_header = "header_" + str_time;
    const id_body = "body_" + str_time;
    const id_footer = "footer_" + str_time;
    const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" };
    const d_time = new Date(parseInt(str_time));
    const str_day = d_time.toLocaleString('fr-FR', options);

    const obj_header = {
        "color_text": "bg-primary",
        "text": "",
        "id" : id_header
    }

    const obj_body = {
        "color_text": "bg-primary",
        "text": "rendez-vous",
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

function afficheElementBody(str_time,id) {
    const select = document.createElement("select");
    const id_select = "select_" + id + "_" + str_time;
    select.setAttribute("id", id_select);
    select.setAttribute("name", "select_horaire");
    select.setAttribute("class", "form-select");

    let option;
    let str_secHoraire;
    //generation horaire
    let arr_horaire = [];
    let horaire = 0;
    for (let i = 1; i < 96; i++){
        horaire = horaire + 900;
        arr_horaire.push(horaire);        
    }

    arr_horaire.forEach(element => {
        str_secHoraire = element.toString();
        option = document.createElement("option");
        const ob_time  = secondeToHour(parseInt(element));
        option.setAttribute("value", str_secHoraire);
        option.textContent = ob_time.str;
        select.append(option);
    });

    return select;
}

function getSeconde(str_horaire,str_time){
    seconde = Number(str_horaire) + Number(str_time);
    str_horaire = String(seconde);
    return str_horaire;
}

function getLabelHeure(id){
    const str_id = "[id^=select_" + id + "]";
    const arr_domSelect =  document.querySelectorAll(str_id);
    let dom_select;
    let str_time =  "";
    let str_horaire = "";
    if (arr_domSelect.length > 0){
        dom_select = arr_domSelect[0];
        str_horaire = dom_select.value;
        str_time = dom_select.id.replace("select_" + id + "_","");
        console.log(str_time);
    }

    return getSeconde(str_horaire,str_time);
}



function saveInformation(editor,obj_information) {
    /*reccuperation des elements*/
    const str_link = '';
    obj_information.data = editor.getData();

    obj_information.secondeDebut = getLabelHeure("heure_debut");
    obj_information.secondeFin = getLabelHeure("heure_fin");

    const envoi = new Envoi(str_link, obj_information);
    envoi.actionEnvoi();
}

/*
cette fonction, fait à la va vite ,  suppose que on a pas plus de 24h 
on affinera plus tard cela , on pourra même créer une classe 
pour la gestion des heures
on pourra améliorer le code en utilisant datetime
*/ 
function secondeToHour(n_seconde) {
    let n_minute = 0;
    let n_heure = 0;

    let str_minute = "00";
    let str_hour = "00";
    let str_seconde = "00";

    if (n_seconde > 59) {
        //quotient de la division euclidienne
        n_minute = Math.floor(n_seconde / 60);
        //le reste est toujours inférieur au diviseur
        n_seconde = n_seconde % 60

        str_seconde = n_seconde.toString();
    }

    if (n_minute > 59) {
        n_heure = Math.floor(n_minute / 60);
        n_minute = n_minute % 60;

        str_minute = n_minute.toString();
        str_hour = n_heure.toString();
    } else {
        str_minute = n_minute.toString();
    }

    if (n_minute < 10) {
        str_minute = "0" + n_minute.toString();
    }

    if (n_heure < 10) {
        str_hour = "0" + n_heure.toString();
    }

    if (n_seconde < 10) {
        str_seconde = "0" + n_seconde.toString(); 
    }

    const str_return = str_hour + ":" + str_minute + ":" + str_seconde

    const ob_return = {
        "heure": n_heure,
        "minute": n_minute,
        "seconde": n_seconde,
        "str" :str_return
    }

    return ob_return;

}



    //initialisation des doonées
    let str_day = document.getElementById("day").value;
    let str_month = document.getElementById("month").value;
    let str_year = document.getElementById("year").value;
    let str_action = document.getElementById("action").value;   
    let str_format = document.getElementById("format").value; 

    let int_day = Number(str_day);
    let int_month = Number(str_month);
    let int_year = Number(str_year);
    if (str_format === "month") {
    let obj_date = {
        "annee": int_year,
        "mois": int_month,
        "jour" : int_day
        }
        
    generateMois(obj_date,str_action);
}