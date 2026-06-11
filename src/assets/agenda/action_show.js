import Choices from 'choices.js';

/**
 * Module pour la vue détaillée d'un événement
 * Gère le filtrage des élèves par classe avec Choices.js
 */
export default class ActionShowAgenda {
  constructor() {
    this.classeFilter = document.getElementById('classeFilter');
    this.elevesContainer = document.getElementById('elevesContainer');
    this.classeGroups = document.querySelectorAll('.classe-group');
    this.choicesInstance = null;
  }

  init() {
    if (!this.classeFilter) {
      console.log('📅 Pas de filtre de classe trouvé - affichage simple');
      return;
    }

    // Initialiser le select avec Choices
    this.initializeChoicesSelect();

    // Ajouter l'écouteur de changement
    this.classeFilter.addEventListener('change', (event) => {
      this.filterByClasse(event.target.value);
    });

    console.log('✅ Filtrage des élèves activé avec Choices.js');
  }

  /**
   * Initialiser le select avec la libraire Choices
   */
  initializeChoicesSelect() {
    if (this.classeFilter) {
      this.choicesInstance = new Choices(this.classeFilter, {
        removeItemButton: true,
        searchEnabled: true,
        addItems: false,
        placeholderValue: 'Filtrer par classe...',
        shouldSort: false,
        noResultsText: 'Aucune classe trouvée',
        noChoicesText: 'Aucune option disponible',
      });
    }
  }

  /**
   * Filtrer les groupes de classe selon la sélection
   */
  filterByClasse(classeId) {
    if (!classeId || classeId === '-1') {
      // Afficher tous les groupes
      this.classeGroups.forEach((group) => {
        group.style.display = 'block';
      });
      console.log('📋 Affichage de tous les élèves');
    } else {
      // Afficher uniquement le groupe sélectionné
      this.classeGroups.forEach((group) => {
        if (group.getAttribute('data-classe-id') === classeId) {
          group.style.display = 'block';
        } else {
          group.style.display = 'none';
        }
      });
      console.log(`📋 Filtré sur la classe ID: ${classeId}`);
    }

    // Afficher le nombre d'élèves visibles
    const count = this.getVisibleElevesCount();
    console.log(`👥 ${count} élève(s) visible(s)`);
  }

  /**
   * Obtenir le nombre d'élèves filtrés
   */
  getVisibleElevesCount() {
    let count = 0;
    this.classeGroups.forEach((group) => {
      if (group.style.display !== 'none') {
        const elevesInGroup = group.querySelectorAll('.list-group-item').length;
        count += elevesInGroup;
      }
    });
    return count;
  }

  /**
   * Exporter les élèves visibles (pour future utilisation)
   */
  getVisibleEleves() {
    const eleves = [];
    this.classeGroups.forEach((group) => {
      if (group.style.display !== 'none') {
        group.querySelectorAll('.list-group-item').forEach((item) => {
          const nomComplet = item.querySelector('strong').textContent;
          eleves.push(nomComplet);
        });
      }
    });
    return eleves;
  }

  /**
   * Réinitialiser le filtre (afficher tous les élèves)
   */
  reset() {
    if (this.choicesInstance) {
      this.choicesInstance.clearStore();
    }
    this.filterByClasse('');
    console.log('🔄 Filtre réinitialisé');
  }
}

// Initialiser quand le DOM est prêt
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const agendaShow = new ActionShowAgenda();
    agendaShow.init();
  });
} else {
  const agendaShow = new ActionShowAgenda();
  agendaShow.init();
}
