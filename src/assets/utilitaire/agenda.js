// assets/js/Agenda.js

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

export default class Agenda {
  constructor(elementId = 'agenda_corps') {
    this.elementId = elementId;
    this.calendar = null;
  }

  toEvent(evenements) {
    let arr_retour = [];

    if (evenements && Array.isArray(evenements)) {
      arr_retour = evenements.map(jour => ({
        title: jour.title,
        start: jour.start,
        end: jour.end || null,
        extendedProps: jour.extendedProps || {}
      }));
    } else {
      // Données de test
      arr_retour = [
        {
          title: 'Événement A',
          start: '2025-07-27T10:00:00',
          end: '2025-07-27T11:00:00',
          extendedProps: {
            lieu: "Lieu de l'événement A",
            description: "Description détaillée de l'événement A",
            classe: "classe exemple",
            eleves: [
              { nom: 'Dupont', prenom: 'Jean', id: 1 },
              { nom: 'Durand', prenom: 'Marie', id: 2 }
            ]
          }
        },
        {
          title: 'Événement B',
          start: '2025-07-29T14:00:00',
          end: '2025-07-29T15:00:00',
          extendedProps: {
            lieu: "Lieu de l'événement B",
            description: "Description détaillée de l'événement B",
            classe: 'aucune',
            eleves: [
              { nom: 'Dupont', prenom: 'Jean', id: 1 },
              { nom: 'Durand', prenom: 'Marie', id: 2 }
            ]
          }
        }
      ];
    }

    return arr_retour;
  }
  

  jsToPhpTimestamp(jsTimestamp) {
    return Math.floor(jsTimestamp / 1000);
  }

  getAgenda(initialView = 'dayGridMonth', evenements) {
    const calendarEl = document.getElementById(this.elementId);

    if (!calendarEl) {
      console.error(`❌ Élément avec l'ID "${this.elementId}" introuvable.`);
      return null;
    }

    const arr_event = this.toEvent(evenements);

    if (!this.calendar && !Array.isArray(arr_event)) {
      console.warn('⚠️ Pas d\'événements à afficher');
    }

    try {
      const joursFeries = {
          "01-01": "Jour de l'an",      // 1er janvier
          "05-01": "Fête du Travail",   // 1er mai
          "05-08": "Victoire 1945",     // 8 mai
          "07-14": "Fête Nationale",    // 14 juillet
          "08-15": "Assomption",        // 15 août
          "11-01": "Toussaint",         // 1er novembre
          "11-11": "Armistice",         // 11 novembre
          "12-25": "Noël"               // 25 décembre
        };

      this.calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        locale: 'fr',
        height: 600,
        nowIndicator: true,
        selectable: true,
        aspectRatio: 1,
        initialView: initialView,
        initialDate: new Date(),
        events: arr_event,
        dayMaxEvents: 1,
        dayMaxEventRows: 1,
        moreLinkClick: (info) => {
          // ✅ Le click "more" fonctionne UNIQUEMENT en vue mensuelle
          if (this.calendar.view.type === 'dayGridMonth') {
            this.calendar.changeView('dayGridDay', info.date);
          }
          return false;
        },

        dateClick: info => {
          this.calendar.changeView('dayGridDay', info.date);
        },

        eventClick: info => {
          const eventId = info.event.extendedProps.id;
          if (eventId) {
            // Naviguer vers la page de détails de l'événement
            window.location.href = `/agenda/${eventId}`;
          }
        },

        dayCellDidMount: function(arg) {
          let date = new Date(arg.date);        // on clone la date
          date.setDate(date.getDate() + 1);     // on modifie l'objet Date
          const key = date.toISOString().slice(5, 10); // "MM-DD"

          const b_sunday = date.getDay() === 1;  // 0=Dim,1=Lun,...6=Sam On décale d’un jour l'action se deroule aprés l'apparition du jour

          const str_label = b_sunday ? "Jour férié" : `🎉 ${joursFeries[key]}`;

          if (joursFeries[key] || b_sunday) {
            const top = arg.el.querySelector('.fc-daygrid-day-top');

            if (top) {
              // ✅ Style plus discret pour le fond - n'occupe pas trop de place
              top.style.backgroundColor = "rgba(255, 100, 100, 0.05)";
              top.style.borderBottom = "2px solid rgba(255, 100, 100, 0.2)";
              
              // ✅ Ajouter le label de jour férié avec classe CSS
              const span = document.createElement('span');
              span.className = 'holiday-label';
              span.textContent = str_label;
              top.appendChild(span);
            }
          }
        },

        dayCellContent: function(arg) {
          // Uniquement pour la vue du mois (dayGridMonth)
          if (arg.view.type !== 'dayGridMonth') {
            return null; // Laisser FullCalendarJS faire le rendu par défaut
          }

          // Compter les événements du jour
          let eventsForDay = [];

          // Chercher les événements du jour
          if (arr_event) {
            arr_event.forEach(event => {
              const startDate = new Date(event.start);
              if (startDate.toDateString() === arg.date.toDateString()) {
                eventsForDay.push(event);
              }
            });
          }

          // Créer un conteneur personnalisé
          const content = document.createElement('div');
          content.className = 'custom-day-content';
          content.style.padding = '2px';

          // ✅ TOUJOURS afficher le numéro du jour (même s'il y a 0 événements)
          const dayNum = document.createElement('div');
          dayNum.className = 'day-number';
          dayNum.textContent = arg.date.getDate();
          dayNum.style.fontWeight = 'bold';
          dayNum.style.fontSize = '0.95rem';
          dayNum.style.marginBottom = '4px';
          content.appendChild(dayNum);

          // Afficher le résumé des événements
          if (eventsForDay.length === 0) {
            // ✅ CHANGEMENT: Retourner le conteneur avec le numéro du jour au lieu de null
            return { domNodes: [content] };
          } else if (eventsForDay.length === 1) {
            // Un seul événement: afficher le titre court
            const event = eventsForDay[0];
            const eventEl = document.createElement('div');
            eventEl.style.fontSize = '0.75rem';
            eventEl.style.fontWeight = '500';
            eventEl.style.color = '#007bff';
            eventEl.style.marginTop = '2px';
            eventEl.style.whiteSpace = 'nowrap';
            eventEl.style.overflow = 'hidden';
            eventEl.style.textOverflow = 'ellipsis';
            eventEl.style.cursor = 'pointer';
            eventEl.textContent = event.title;
            
            // Ajouter un handler de clic pour naviguer vers le détail
            eventEl.addEventListener('click', function(e) {
              e.stopPropagation();
              if (event.extendedProps && event.extendedProps.id) {
                window.location.href = `/agenda/${event.extendedProps.id}`;
              }
            });
            
            content.appendChild(eventEl);
          } else {
            // Plusieurs événements: afficher avec style de bouton
            const countEl = document.createElement('div');
            countEl.className = 'more-events-button'; // ✅ Nouvelle classe CSS
            countEl.style.marginTop = '2px';
            countEl.textContent = `${eventsForDay.length} plus`;
            
            // ✅ Ajouter un handler de clic pour le bouton "more"
            countEl.addEventListener('click', function(e) {
              e.stopPropagation();
              // Si on est en vue mensuelle, naviguer vers la vue du jour
              if (arg.view.type === 'dayGridMonth') {
                this.calendar.changeView('dayGridDay', arg.date);
              }
            }.bind(this.calendar)); // Bind le contexte du calendrier
            
            content.appendChild(countEl);
          }

          return { domNodes: [content] };
        },
        // 👉 On REMPLACE complètement le rendu de l’event
        eventContent: function (arg) {          // En vue du mois, on affiche le résumé dans dayCellContent
          if (arg.view.type === 'dayGridMonth') {
            return null; // Les événements sont gérés par dayCellContent
          }
          const { lieu, description, eleves = [], id } = arg.event.extendedProps || {};

          const timeText = arg.timeText || ''; // ex. "10:00"
          const titleText = arg.event.title || '';

          const container = document.createElement('div');
          container.className = 'fc-event-custom';
          container.style.cursor = 'pointer';
          
          // Ajouter un attribut data-event-id pour faciliter la sélection
          if (id) {
            container.setAttribute('data-event-id', id);
          }

          // Header : heure + titre sur la même ligne
          const header = document.createElement('div');
          header.className = 'fc-event-header';

          const timeSpan = document.createElement('span');
          timeSpan.className = 'fc-event-time';
          timeSpan.textContent = timeText;

          const titleSpan = document.createElement('span');
          titleSpan.className = 'fc-event-title';
          titleSpan.textContent = titleText;

          header.appendChild(timeSpan);
          if (timeText && titleText) {
            const sep = document.createElement('span');
            sep.textContent = ' — ';
            header.appendChild(sep);
          }
          header.appendChild(titleSpan);
          container.appendChild(header);

          // Lieu
          if (lieu) {
            const lieuEl = document.createElement('div');
            lieuEl.className = 'fc-event-lieu';
            lieuEl.textContent = lieu;
            container.appendChild(lieuEl);
          }

          // Description
          if (description) {
            const descEl = document.createElement('div');
            descEl.className = 'fc-event-description';
            descEl.textContent = description;
            container.appendChild(descEl);
          }

          // Élèves (affichage limité)
          if (eleves.length > 0) {
            const elevesEl = document.createElement('div');
            elevesEl.className = 'fc-event-eleves';

            const maxDisplay = 3;
            const displayed = eleves.slice(0, maxDisplay);
            const remaining = eleves.length - maxDisplay;

            const nomsAffiches = displayed
              .map(p => `${p.prenom} ${p.nom}`)
              .join(', ');

            elevesEl.textContent =
              remaining > 0
                ? `Élèves : ${nomsAffiches} + ${remaining} autres`
                : `Élèves : ${nomsAffiches}`;

            container.appendChild(elevesEl);

            // Tooltip natif avec la liste complète
            const fullList = eleves.map(p => `${p.prenom} ${p.nom}`).join('\n');
            container.title = fullList;
          }

          return { domNodes: [container] };
        },

        headerToolbar: {
          left: 'prev,next today createBtn myAgendaBtn',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek,dayGridDay'
        },
        
        customButtons: {
          createBtn: {
            text: '➕ Créer',
            click: function() {
              window.location.href = '/agenda/create';
            }
          },
          myAgendaBtn: {
            text: '👤 Mon agenda',
            click: function() {
              window.location.href = '/agenda/me';
            }
          }
        }
      });

      // À toi de t’assurer que getAgenda est appelé après DOMContentLoaded
      this.demmarreCalendar();

      document.addEventListener('keydown', e => {
        this.eventCalendar(e);
      });
    } catch (error) {
      console.error("🚨 Erreur lors de l'initialisation du calendrier :", error);
    }
  }

  demmarreCalendar() {
    if (this.calendar) {
      this.calendar.render();
    }
  }

  addDate(obj_event) {
    // Vérifier que le calendrier est prêt
    if (!this.calendar || !obj_event || !obj_event.date) {
      console.log('📅 Données d\'événement invalides', obj_event);
      console.warn('⚠️ Calendrier non prêt ou données invalides', obj_event);
      return;
    }

    try {
      const dateString = obj_event.date.date || obj_event.date;
      const dateObj = new Date(dateString.replace(' ', 'T'));
      
      if (isNaN(dateObj.getTime())) {
        console.error('❌ Date invalide:', dateString);
        return;
      }

      const fin = new Date(dateObj.getTime() + (obj_event.duree || 60) * 1000);

      this.calendar.addEvent({
      title: obj_event.title,
      start: obj_event.date.date || obj_event.date,
      end: fin,
      extendedProps: {
        id: obj_event.id,
        lieu: obj_event.lieu,
        description: obj_event.corps,
        classe: obj_event.classe,
        eleves: obj_event.eleves,
        recurrence: obj_event.recurrence
      }
    });} catch (error) {
      console.error('❌ Erreur lors de l\'ajout d\'un événement:', error, obj_event);
    }
  }

  eventCalendar(event) {
    switch (event.key) {
      case 'ArrowLeft':
        this.calendar.prev();
        break;
      case 'ArrowRight':
        this.calendar.next();
        break;
      case 'ArrowUp':
        this.calendar.today();
        break;
      case 'ArrowDown':
        // libre pour autre chose
        break;
    }
  }
}
