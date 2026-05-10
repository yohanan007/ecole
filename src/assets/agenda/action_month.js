import { Element, Dom,Table, Grid, Card } from "../utilitaire/element";
import { Mois } from "./agenda";
import { AgendaService } from "./AgendaService";


class ActionMonth{
    coll_action = [];
    date_action = new Date();
    str_principal = "agenda_corps";
    entete = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];

    constructor(coll_action,date_action){
        if(!(typeof(coll_action) === "undefined")){
            this.coll_action = coll_action;
        }

        if(!(typeof(date_action) === "undefined")){
            this.date_action = date_action;
        }
    }


    //generation du tableau lié à un mois 
    getAgenda(obj_date,coll_agenda, dom_id){
        dom_id = dom_id || "agenda_corps";

        let mois;
        let arr_ligne = [];
        let i = 0;
        let arr_temp = [];
        let str_class = "";
        let coll_tempAgenda = [];

        if (typeof coll_agenda === "undefined") {
            mois = new Mois();
            n_time = mois.getTime();

        } else {
            coll_tempAgenda = this.generateCollTime(coll_agenda);
            mois = new Mois(obj_date.annee, obj_date.mois, obj_date.jour);
            n_time = mois.getTime();
        }

        let arr_month = mois.getMonth();

        console.log(arr_month);

        arr_month.forEach(element => {
            if((coll_tempAgenda.findIndex((element1) => element1.time === element.getTime())>-1) || (n_time === element.getTime())){
                str_class = "table-active";
            }else {
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


        let obj_table = new Table(this.entete, arr_ligne, []);
        let table = obj_table.getTable()
        let dom_div = document.getElementById(dom_id);
        let dom_div_table = document.createElement("div");
        dom_div_table.setAttribute("class","table-responsive-xxl");
        dom_div_table.append(table);


        const ob_divLabel = new Element("card","card_label_agenda","card text-white bg-dark mt-2 hauteur-41");
        const dom_divLabel = ob_divLabel.getElement();

        this.generateResume(dom_divLabel,n_time,coll_agenda)
        //gestion en grid
        arr_elementGrid = [];
        arr_elementGrid.push({"element":dom_divLabel, "size":3});
        arr_elementGrid.push({"element":dom_div_table, "size": 9});

        //travail en grid ici
        const ob_grid = new Grid(2,arr_elementGrid)

        dom_div.append(ob_grid.getElement());
        return dom_div;
    }

    //generation emploi du temps de la journée selectionné
    //t_date = jour time selectionné
    generateResume(dom_card,t_date,coll_json)
    {
        if(coll_json.length>0){
            coll_json.forEach(element => {
                dateTemp = new Date(element.heureDebut.date.split(" "));
                dateTemp = new Date(dateTemp.getFullYear(),dateTemp.getMonth(),dateTemp.getDate());
                if(dateTemp.getTime() === t_date ){
                    //on est sur le jour selectionné
                    str_corps = element.corps;
                    str_lieu = element.lieu;
                    str_sujet = element.sujet;
                    str_duree = element.duree;
                    str_utilisateur = element.nom + element.prenom;

                    const ob_divLabel_ = new Element("card-header","card_label_interieur");
                    const dom_divLabel_ = ob_divLabel_.getElement();
                    dom_divLabel_.innerHTML ="<h1>"+str_lieu+"</h1>" 
                    +"<p>"+ str_utilisateur+ "<p>"
                    + str_corps + "<p>"


                    dom_card.append(dom_divLabel_);
                }
            });
        }
        if(typeof dom_divLabel_ === "undefined"){
            const ob_divLabel_ = new Dom("card-header","","","Aucune Information");
            const dom_divLabel_ = ob_divLabel_.getAttribute();
            dom_card.append(dom_divLabel_);
        }

        return dom_card;
    }

    generateCollTime(coll_agenda){
        let coll_retour = [];
        let dateTemp ;
        coll_agenda.forEach(element => {
            dateTemp = new Date(element.heureDebut.date.split(" "));
            dateTemp = new Date(dateTemp.getFullYear(),dateTemp.getMonth(),dateTemp.getDate());
            if(coll_retour.findIndex((element) => element.time === dateTemp.getTime() ) === -1){
                coll_retour.push({"time":dateTemp.getTime()})
            }
            
        });
        console.log(coll_retour);
        return coll_retour;
    }


    getActionTable(dom_div){ 
        const coll_tdAgenda = document.getElementsByTagName("td");
        const str_idGeneral = dom_div.id;
        for (let k = 0; k < coll_tdAgenda.length; k++) {
            if (typeof coll_tdAgenda[k] !== "undefined") {
                //action lors d'un clique sur l'une des dates de la page
                coll_tdAgenda[k].addEventListener("click", async (e) => {
                    const str_idTemp = e.target.id;
                    const elementtab = new Element("",str_idGeneral);
                    //reinitialisation de la page
                    elementtab.razElementId();
                    
                    // Charger les dépendances dynamiquement via le service
                    try {
                        const { ActionElementAgenda } = await import('./action_element');
                        const dom_grid = await AgendaService.getSelectAll();
                        
                        let obj_actionElement = new ActionElementAgenda("",str_idTemp);
                        //generation des select heure de debut et fin
                        const cardDom = obj_actionElement.getCard(str_idGeneral,str_idTemp);
                        const dom_select = document.getElementById("select_horaire");
                        dom_select.append(dom_grid);
                    } catch (error) {
                        console.error('Erreur lors du chargement des modules:', error);
                    }
                });
            }
        }
    }


}


export {ActionMonth};