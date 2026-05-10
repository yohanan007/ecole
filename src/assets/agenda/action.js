import Agenda from '../utilitaire/agenda';


const obj_action = {};

obj_action.str_jsonAgenda = "";
obj_action.coll_agenda;
obj_action.agendaInstance = null;

obj_action.init = async function(){
    
    const agendaDataEl = document.getElementById("agenda-data");
    if (agendaDataEl) {
        this.str_jsonAgenda = agendaDataEl.value || agendaDataEl.textContent;
        this.coll_agenda = JSON.parse(this.str_jsonAgenda);
        console.log("📊 Données de l'agenda chargées:", this.coll_agenda);
        this.generateMois(this.coll_agenda);
    }
}

/**
 * Génère le calendrier avec les événements du mois
 * Supporte la nouvelle structure de données du contrôleur
 */
obj_action.generateMois = function(coll_agenda) {
    this.agendaInstance = new Agenda(); // par défaut, cherche l'élément avec id "agenda_corps"
        
    // Initialiser le calendrier avec une liste vide
    this.agendaInstance.getAgenda(undefined, []);

    if(typeof coll_agenda !== "undefined" && Array.isArray(coll_agenda)){
        if(coll_agenda.length > 0){
            // Ajouter tous les événements APRÈS l'initialisation du calendrier
            coll_agenda.forEach(element => {
                // Supporte la nouvelle structure: id, title, corps, lieu, duree, recurrence
                const base = { 
                    id: element.extendedProps.id,
                    title: element.title || element.sujet,
                    lieu: element.extendedProps.lieu || '',
                    corps: element.extendedProps.corps || element.extendedProps.description || '',
                    duree: element.duree || 60,
                    recurrence: element.extendedProps.recurrence || 'aucune',
                    classe: element.extendedProps.classe || '', 
                    eleves: element.extendedProps.eleves || []
                };
                
                // Ajouter chaque occurrence de l'événement au calendrier
                if (element.start) {
                        const payload = { 
                            ...base, 
                            date: element.start,
                            // Timestamp en secondes pour compatibility
                            timestamp: element.start.timestamp ? Math.floor(element.start.timestamp / 1000) : null
                        };
                    this.agendaInstance.addDate(payload);
                }
            });
            
            // Finaliser le rendu après tous les événements ajoutés
            if (this.agendaInstance && this.agendaInstance.calendar) {
                this.agendaInstance.calendar.render();
                console.log(`✅ Calendrier rendu avec ${coll_agenda.length} événement(s)`);
            }
        } else {
            console.log("📅 Agenda vide - aucun événement à afficher");
        }
    } else {
        console.log("⚠️ Pas de donnée agenda reçue");
    }
}

/**
 * Naviguer vers la page de détails d'un événement
 */
obj_action.navigateToEvent = function(eventId) {
    if (eventId) {
        window.location.href = `/agenda/${eventId}`;
    }
}

/**
 * Navigationvers la page de création d'un événement
 */
obj_action.navigateToCreate = function() {
    window.location.href = '/agenda/create';
}

/**
 * Naviguer vers ma page d'agenda personnel
 */
obj_action.navigateToMyAgenda = function() {
    window.location.href = '/agenda/me';
}

// Attendre que le DOM soit complètement chargé
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        obj_action.init();
    });
} else {
    obj_action.init();
}
