
import Choices from 'choices.js';
import { ClasseEleve } from '../eleve/action_eleve.js';

export default class CreateAgenda {

  init() {
    const selectElement = document.getElementById('classe');
    const selectElementEleve = document.getElementById('eleve');
    
    // Supporte les noms de champs avec underscore ou sans (recurrence vs reccurence)
    const selectRecurrence = document.getElementById('recurrence') || document.getElementById('reccurence');

    let arr_choiceRecurrence = [];
    arr_choiceRecurrence.push({value : "aucune", label : "Pas de récurrence" });
    arr_choiceRecurrence.push({value : "jour", label : "Quotidien" });
    arr_choiceRecurrence.push({value : "semaine", label : "Hebdomadaire" });
    arr_choiceRecurrence.push({value : "deuxSemaines", label : "Bi-hebdomadaire" });
    arr_choiceRecurrence.push({value : "mois", label : "Mensuel" });

    // Initialiser le select de classe
    if (selectElement) {
      new Choices(selectElement, {
        removeItemButton: true,
        searchEnabled: true,
        addItems: true,
        maxItemCount: -1, 
        placeholderValue: 'Sélectionne une classe',
        shouldSort: false,
      });

      // Charger les élèves quand une classe est sélectionnée
      selectElement.addEventListener("change", (event) => {
        if (selectElementEleve) {
          this.elevesChoice();
        }
      });
    }

    // Initialiser le select de récurrence
    if (selectRecurrence) {
      new Choices(selectRecurrence, {
        removeItemButton: true,
        searchEnabled: true,
        addItems: false,
        placeholderValue: 'Sélectionne une récurrence',
        shouldSort: false,
      });
    }

    // Initialiser le select d'élèves
    if (selectElementEleve) {
      new Choices(selectElementEleve, {
        removeItemButton: true,
        searchEnabled: true,
        addItems: false,
        maxItemCount: -1, 
        placeholderValue: 'Sélectionne des élèves',
        shouldSort: false,
      });
    }
  }

  /**
   * Charger dynamiquement les élèves basé sur la classe sélectionnée
   */
  async elevesChoice() {
    const str_selectClasse = document.getElementById("classe").value;
    const selectElementEleve = document.getElementById('eleve');

    if (!str_selectClasse || str_selectClasse === "-1") {
      return; // Toutes les classes sélectionnées
    }

    const obj_classeEleve = new ClasseEleve("/eleve/list");
    
    try {
      const data = await obj_classeEleve.getAllElevesByClasse(str_selectClasse);
      const ob_data = JSON.parse(data);

      if (ob_data.data && Array.isArray(ob_data.data)) {
        // Trouver l'instance Choices pour le select
        const choicesInstance = selectElementEleve._instance; // PAS DIRECT: Choices stocke l'instance différemment

        // Solution plus robuste: recréer le select avec les nouvelles options
        selectElementEleve.innerHTML = '';
        
        ob_data.data.forEach(eleve => {
          const option = document.createElement('option');
          option.value = eleve.id;
          option.textContent = `${eleve.prenom} ${eleve.nom}`;
          selectElementEleve.appendChild(option);
        });

        console.log(`✅ ${ob_data.data.length} élèves chargés pour la classe`);
      }
    } catch (error) {
      console.error('❌ Erreur lors du chargement des élèves:', error);
    }
  }
}

// Initialiser quand le DOM est prêt
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const agenda = new CreateAgenda();
    agenda.init();
  });
} else {
  const agenda = new CreateAgenda();
  agenda.init();
}

