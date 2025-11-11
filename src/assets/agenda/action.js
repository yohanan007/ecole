import Agenda from '../utilitaire/agenda';


obj_action = {};

obj_action.str_jsonAgenda = "";
obj_action.coll_agenda;

obj_action.init = async function(){
    
    this.str_jsonAgenda = document.getElementById("agenda-data").value;
    this.coll_agenda = JSON.parse(this.str_jsonAgenda);
    this.generateMois(this.coll_agenda);
}


/**
 * @todo : retraité la page en terme de classe
 */
obj_action.generateMois = function(coll_agenda) {
      const agenda = new Agenda(); // par défaut, cherche l'élément avec id "calendar"
        
      agenda.getAgenda();

      if(typeof coll_agenda !== "undefined"){
        if(coll_agenda.length > 0){
            coll_agenda.forEach(element => {
                for(let key in element){
                  //key <=> id de l'évenement
                  element[key].forEach(item => {
                    //un item correspond à un evenement
                    agenda.addDate(item);
                  });
                }
            });
        }else{
            console.log("agenda vide");
        }
      }else{
        console.log("pas de donnée agenda");
      }
}

obj_action.init();
