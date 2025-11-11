// assets/js/Agenda.js

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

export default class Agenda {
  constructor(elementId = 'agenda_corps') {
    this.elementId = elementId;
    this.calendar = null;
  }

  toEvent(evenements){
    arr_retour = [];

    if(evenements){

        evenements.forEach(jour => {
          arr_retour.push({title : jour.title, start: jour.start })
        });

    }else{
      arr_retour = [
            { title: 'Événement A', start: '2025-07-27' },
            { title: 'Événement B', start: '2025-07-29' },
      ];
    }

    return arr_retour;
  }

  jsToPhpTimestamp(jsTimestamp) {
    return Math.floor(jsTimestamp / 1000);
  }


  getAgenda(initialView = 'dayGridMonth',evenements) {
    const calendarEl = document.getElementById(this.elementId);

    if (!calendarEl) {
      console.error(`❌ Élément avec l'ID "${this.elementId}" introuvable.`);
      return;
    }

    arr_event = this.toEvent();

    try {
            this.calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, interactionPlugin ],
        locale: 'fr',
        height: 600,
        nowIndicator: true,
        selectable: true,
        aspectRatio: 1,
        initialView: initialView,
        initialDate: Date.now(),
        events: arr_event,
        //possibilité d'envoyer donnée 
        //url:
        //extraParams:  
        dateClick: info => {
          console.log('Current view: ' + info.view.type);
          this.calendar.changeView('dayGridDay', info.date)
        },
        eventDidMount: function(info) {
          const { lieu, description } = info.event.extendedProps;

          // Création de la carte
          const card = document.createElement('div');
          card.className = 'fc-event-card';

          if (lieu) {
            const lieuEl = document.createElement('div');
            lieuEl.className = 'fc-event-lieu';
            lieuEl.textContent = lieu;
            card.appendChild(lieuEl);
          }

          if (description) {
            const descEl = document.createElement('div');
            descEl.className = 'fc-event-description';
            descEl.textContent = description;
            card.appendChild(descEl);
          }

          // Insertion après l'élément de l'événement
          info.el.parentElement.appendChild(card);
        },
        headerToolbar: {
          left: 'prev,next',
          center: 'title',
          right: 'dayGridWeek,dayGridYear,dayGridMonth,dayGridDay' // user can switch between the two
        }
      });


            
      document.addEventListener('DOMContentLoaded', e => {this.demmarreCalendar();});


      document.addEventListener('keydown', e => { this.eventCalendar(e);});




    } catch (error) {

      console.error('🚨 Erreur lors de l\'initialisation du calendrier :', error);
    }
  }

  demmarreCalendar(){
    console.log("demmarre");
    this.calendar.render();
  }

  addDate(obj_event){
    const fin = new Date(new Date(obj_event.date.date.replace(" ","T")).getTime() + obj_event.duree*1000);
    this.calendar.addEvent({
      title: obj_event.titre,
      start: obj_event.date.date,
      extendedProps : {
        lieu : obj_event.lieu,
        description : obj_event.corps,
      },
      end : fin,
    });
    this.calendar.render();
  }

  eventCalendar(event){
        switch (event.key) {
          case 'ArrowLeft':
            console.log(this.calendar);

            this.calendar.prev(); // Aller à la période précédente
            break;
          case 'ArrowRight':
            this.calendar.next(); // Aller à la période suivante
            break;
          case 'ArrowUp':
            // Tu peux personnaliser ce comportement, par exemple aller à "today"
            this.calendar.today();
            break;
          case 'ArrowDown':
            // Autre action personnalisée
            break;
        }
  }


}
