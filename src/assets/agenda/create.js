
import Choices from 'choices.js';
import { ClasseEleve } from '../eleve/action_eleve.js';

export default class CreateAgenda{

init(){
  const selectElement = document.getElementById('classe');
  const selectElementEleve = document.getElementById('eleve');
  const selectReccurence = document.getElementById('reccurence');

  let arr_choiceReccurrence = [];
  arr_choiceReccurrence.push({value : "", label : "Aucune" });
  arr_choiceReccurrence.push({value : "jour", label : "toutes les jours" });
  arr_choiceReccurrence.push({value : "semaine", label : "toutes les semaines" });
  arr_choiceReccurrence.push({value : "deuxSemaines", label : "toutes les deux semaines" });
  arr_choiceReccurrence.push({value : "mois", label : "toutes les mois" });

        if (selectElement) {
            new Choices(selectElement, {
            removeItemButton: true,
            searchEnabled: true,
            addItems: true,
            maxItemCount: -1, 
            placeholderValue: 'Sélectionne une option',
             shouldSort: false,
            });
        }

        console.log("avant reccurence");

        if(selectReccurence){
            console.log("reccurence");
            const reccurenceChoice = new Choices(selectReccurence, {
            removeItemButton: true,
            searchEnabled: true,
            addItems: true,
            placeholderValue: 'Sélectionne une option',
             shouldSort: false,
            });

            reccurenceChoice.setValue(arr_choiceReccurrence);
        }

        if(selectElementEleve){
            const eleveChoice = new Choices(selectElementEleve, {
            removeItemButton: true,
            searchEnabled: true,
            addItems: true,
            maxItemCount: -1, 
            placeholderValue: 'Sélectionne une option',
            shouldSort: false,
            });

            selectElement.addEventListener("addItem", event => {
            eleveChoice.destroy()
            this.elevesChoice();
            });
        }



    }

    async elevesChoice(){

        //reccupération de la classe choisit pas l'utilisateur
        const str_selectClasse = document.getElementById("classe").value;
        const selectElementEleve = document.getElementById('eleve');
        const obj_classeEleve = new ClasseEleve("/eleve/list");
        const eleveChoice = new Choices(selectElementEleve, {
        removeItemButton: true,
        searchEnabled: true,
        addItems: true,
        maxItemCount: -1, 
        placeholderValue: 'Sélectionne une option',
        shouldSort: false,
        });

        return new Promise((resolve) => {
            //reccupération de l'ensemble des élèves
            obj_classeEleve.getAllElevesByClasse(str_selectClasse).then((data)=>{
                const ob_data = JSON.parse(data);
                if (typeof(ob_data.data) !== "undefined"){
                    if(ob_data.data !== null){
                        let arr_choiceEleve = eleveChoice.getValue();

                        if(typeof(arr_choiceEleve.find( value  => { value === -1 }))==="undefined"){
                            eleveChoice.setValue([{value : -1, label : "Tous les élèves"},]);
                        }

                        for (item of ob_data.data){
                            arr_choiceEleve = eleveChoice.getValue();

                            if(typeof(arr_choiceEleve.find( value  => { value === item.id }))==="undefined"){
                                arr_choiceEleve.push({value : item.id, label : item.nom + " " + item.prenom });
                                eleveChoice.setValue(arr_choiceEleve);
                            }
                            
                            console.log(arr_choiceEleve);
                        }
                    }
                }
                resolve(eleveChoice);
            });
        });
    }
}



const agenda = new CreateAgenda();
agenda.init();

