/**
 * Service centralisé pour les opérations partagées de l'agenda
 * Évite les dépendances circulaires entre ActionElementAgenda et ActionMonth
 */

import { Dom, Grid, Element } from "../utilitaire/element";
import { ClasseEleve } from "../eleve/action_eleve";

class AgendaService {
    
    /**
     * Génère le select des classes
     * @param {string} str_token - Token CSRF
     * @returns {Promise<HTMLElement>}
     */
    static async getSelectClasse(str_token) {
        const obj_classeEleve = new ClasseEleve("/eleve/list", str_token);
        const id_select = "id_list_classe";
        const ob_select = new Dom("select", id_select, "", "", { "name": id_select });
        const dom_select = ob_select.getAttribute();
        let ob_option;
        
        return new Promise((resolve) => {
            // Récupération de l'ensemble des classes
            obj_classeEleve.getAllClasses().then((data) => {
                const ob_data = JSON.parse(data);
                if (typeof(ob_data.data) !== "undefined") {
                    ob_option = new Dom("option", "", "", "TOUS", { "name": -1, "value": -1 });
                    dom_option = ob_option.getAttribute();
                    dom_select.append(dom_option);
                    
                    for (value of ob_data.data) {
                        ob_option = new Dom("option", "", "", value.nom, { "name": value.id, "value": value.id });
                        dom_select.append(ob_option.getAttribute());
                    }
                }
                resolve(dom_select);
            }).catch(error => {
                console.error("Erreur lors du chargement des classes:", error);
                resolve(dom_select);
            });
        });
    }

    /**
     * Génère le select des élèves selon la classe sélectionnée
     * @param {string} str_idClasse - ID de la classe
     * @param {string} str_token - Token CSRF (optionnel)
     * @returns {Promise<HTMLElement>}
     */
    static async getSelectEleve(str_idClasse, str_token) {
        const obj_classeEleve = new ClasseEleve("/eleve/list", str_token);
        const id_select = "id_list_eleve";
        const ob_select = new Dom("select", id_select, "", "", { "name": id_select });
        const dom_select = ob_select.getAttribute();
        let dom_option;
        let ob_option;
        
        return new Promise((resolve) => {
            // Récupération de l'ensemble des élèves de la classe
            obj_classeEleve.getAllElevesByClasse(str_idClasse).then((data) => {
                const ob_data = JSON.parse(data);
                if (typeof(ob_data.data) !== "undefined") {
                    if (ob_data.data !== null) {
                        ob_option = new Dom("option", "", "", "TOUS", { "value": -1 });
                        dom_option = ob_option.getAttribute();
                        dom_select.append(dom_option);
                        
                        for (value of ob_data.data) {
                            ob_option = new Dom("option", "", "", value.nom + " " + value.prenom, { "value": value.id });
                            dom_option = ob_option.getAttribute();
                            dom_select.append(dom_option);
                        }
                    }
                }
                resolve(dom_select);
            }).catch(error => {
                console.error("Erreur lors du chargement des élèves:", error);
                resolve(dom_select);
            });
        });
    }

    /**
     * Génère les selects horaires et grille
     * @returns {Promise<HTMLElement>}
     */
    static async getSelectAll() {
        // Import dynamique pour éviter les dépendances circulaires
        const { ActionElementAgenda } = await import('./action_element');
        
        let ob_element = new ActionElementAgenda();
        // Génération du select des horaires
        let dom_select = ob_element.getSelectHoraire("heureDebut_select");

        // Génération du label heure de début
        let ob_dom = new Dom("label", "heureDebut_label_id", "heureDebut_label_class", "heure de debut");
        const dom_label_debut = ob_dom.getAttribute();

        // Génération du label heure de fin
        ob_dom = new Dom("label", "heure_fin_label_id", "heure_fin_label_class", "heure de fin");
        const dom_label_fin = ob_dom.getAttribute();

        // Heure de début
        const div_heureDebut = document.createElement("div");
        div_heureDebut.append(dom_label_debut);
        div_heureDebut.append(dom_select);

        ob_element = new ActionElementAgenda();
        // Génération du select des horaires fin
        dom_select = ob_element.getSelectHoraire("heure_fin_select");

        // Heure de fin
        const div_heureFin = document.createElement("div");
        div_heureFin.append(dom_label_fin);
        div_heureFin.append(dom_select);

        const arr_elementGrid = [];
        arr_elementGrid.push({ "element": div_heureDebut });
        arr_elementGrid.push({ "element": div_heureFin });

        const ob_domGrid = new Grid(3, arr_elementGrid);
        return ob_domGrid.getElement();
    }
}

export { AgendaService };
