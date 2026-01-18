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
      return;
    }

    const arr_event = this.toEvent(evenements);

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

        dateClick: info => {
          this.calendar.changeView('dayGridDay', info.date);
        },

        dayCellDidMount: function(arg) {
          let date = new Date(arg.date);        // on clone la date
          date.setDate(date.getDate() + 1);     // on modifie l'objet Date
          const key = date.toISOString().slice(5, 10); // "MM-DD"

          const b_sunday = date.getDay() === 1;  // 0=Dim,1=Lun,...6=Sam On décale d’un jour l'action se deroule aprés l'apparition du jour

          const str_label = b_sunday ? "Jour férié" : `🎉(${joursFeries[key]})`;

          if (joursFeries[key] || b_sunday) {
            const top = arg.el.querySelector('.fc-daygrid-day-top');

            if (top) {
              top.style.backgroundColor = "#ffefef";
              top.style.color = "#d60000";
              top.style.fontWeight = "bold";
              top.style.borderRadius = "4px";
              top.style.padding = "2px 4px";

              const span = document.createElement('span');
              span.style.fontSize = '0.7rem';
              span.style.marginRight = '4px';
              span.style.fontWeight = 'bold';
              span.textContent = str_label;
              top.appendChild(span);
            }
          }
        },


        // 👉 On REMPLACE complètement le rendu de l’event
        eventContent: function (arg) {
          const { lieu, description, eleves = [] } = arg.event.extendedProps || {};

          const timeText = arg.timeText || ''; // ex. "10:00"
          const titleText = arg.event.title || '';

          const container = document.createElement('div');
          container.className = 'fc-event-custom';

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
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek,dayGridDay'
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
    const fin = new Date(
      new Date(obj_event.date.date.replace(' ', 'T')).getTime() +
        obj_event.duree * 1000
    );

    this.calendar.addEvent({
      title: obj_event.title,
      start: obj_event.date.date,
      end: fin,
      extendedProps: {
        lieu: obj_event.lieu,
        description: obj_event.corps,
        classe: obj_event.classe,
        eleves: obj_event.eleves
      }
    });

    this.calendar.render();
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
