
let dom_agendaCorp = document.getElementById("agenda_corps");


class Heure
{
    int_seconde = 0;
    int_minute = 0;
    int_heure = 0;

    constructor(int_seconde,int_minute,int_heure){
        if(typeof(int_seconde) != "undefined"){
            this.int_seconde = int_seconde;
        }

        if(typeof(int_minute) != "undefined"){
            this.int_minute = int_minute;
        }

        if(typeof(int_seconde) != "undefined"){
            this.int_seconde = int_seconde;
        }
    }

    secondeToHour() {

    
        let str_minute = "00";
        let str_hour = "00";
        let str_seconde = "00";
    
        if (this.int_seconde > 59) {
            //quotient de la division euclidienne
            this.int_minute = Math.floor(this.int_seconde / 60);
            //le reste est toujours inférieur au diviseur
            this.int_seconde = this.int_seconde % 60
    
            str_seconde = this.int_seconde.toString();
        }
    
        if (this.int_minute > 59) {
            this.int_heure = Math.floor(this.int_minute / 60);
            this.int_minute = this.int_minute % 60;
    
            str_minute = this.int_minute.toString();
            str_hour = this.int_heure.toString();
        } else {
            str_minute = this.int_minute.toString();
        }
    
        if (this.int_minute < 10) {
            str_minute = "0" + this.int_minute.toString();
        }
    
        if (this.int_heure < 10) {
            str_hour = "0" + this.int_heure.toString();
        }
    
        if (this.int_seconde < 10) {
            str_seconde = "0" + this.int_seconde.toString(); 
        }
    
        const str_return = str_hour + ":" + str_minute + ":" + str_seconde
    
        const ob_return = {
            "heure": this.int_heure,
            "minute": this.int_minute,
            "seconde": this.int_seconde,
            "str" :str_return
        }
    
        return ob_return;
    }

    secondeToHourString(){
        const ob_horaire = this.secondeToHour();
        return ob_horaire.heure.toString() + " : " + ob_horaire.minute.toString();
    }


}

class Jour
{
    int_annee;
    int_jour;
    int_mois;
    d_jour;

    ars_jour = ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"];
    ars_mois = ["janvier", "fervier", "mars", "avril", "mai", "juin",
    "juillet", "aout", "septembre", "octobre", "novembre", "decembre"];

    constructor(int_jour, int_mois, int_annee)
    {
        this.int_annee = int_annee;
        this.int_mois = int_mois;
        this.int_jour = int_jour;
        //int_mois = int_mois - 1;
        this.d_jour = new Date(int_annee, int_mois, int_jour);
    }

    getJour()
    {
        return this.d_jour.getDate();
    }

    getTime()
    {
        return this.d_jour.getTime();
    }

    getJourDuMois()
    {
        return this.int_jour;
    }

    getMois()
    {
        return this.d_jour.getUTCMonth();
    }

    getAnnee()
    {
        return this.int_annee;
    }

    setJour(int_jour)
    {
        this.int_jour = int_jour;
    }

    setMois(int_mois)
    {
        this.int_mois = int_mois;
    }

    setAnnee(int_annee)
    {
        this.int_annee = int_annee;
    }

    getDate()
    {
        return this.d_jour;
    }

    getDateDebutSemaine()
    {
        let int_jourTemp;
        let d_debutSemaine;
        let int_time;
        int_jourTemp = this.d_jour.getDay();
        int_time = this.d_jour.getTime()  - (int_jourTemp * 86400000);
        d_debutSemaine = new Date(int_time);
        return d_debutSemaine;
    }

    toString(o_format,d_day)
    {
        let d_travail;
        let options;

        if (typeof d_day === "undefined"){
            d_travail = this.d_jour;
        }
        else{
            d_travail = d_day;
        }

        if (typeof o_format === "undefined") {
            options = {weekday: "long", year: "numeric", month: "long", day: "numeric"};
        } else {
            options = o_format;
        }
        
        let str_date = d_travail.toLocaleString('fr-FR', options);
        return str_date;
    }
}

class Semaine
{
    arj_jour = [];

    constructor(arj_jour)
    {
        if (typeof arj_jour !== "undefined")
        {
            this.arj_jour = arj_jour;
        }
        
    }

    addJour(j_temp)
    {
        if (typeof j_temp !== "undefined")
        {
            this.arj_jour.push(j_temp)
        }
        
    }

    getSemaine(j_temp)
    {
        if (typeof j_temp !== "undefined")
        {
            let d_debutSemaine;
            let d_temp;
            let int_dayOfMonth;
            let int_annee;
            let int_monthOfYear;
            let int_time;
            d_debutSemaine = j_temp.getDateDebutSemaine();
            for (let i = 0; i < 8; i++) {
                int_time = d_debutSemaine.getTime() + (i * 86400000);
                d_temp = new Date(int_time);
                int_dayOfMonth = d_temp.getDate();
                int_annee = d_temp.getFullYear();
                int_monthOfYear = d_temp.getMonth();
                this.arj_jour.push(new Jour(int_dayOfMonth, int_monthOfYear, int_annee));
                }
        }

        return this.arj_jour;
    }
}


class Mois
{
    j_day;
    arj_mois = [];

    constructor(int_annee, int_mois, int_jour)
    {
        let d_now = new Date(Date.now());

        if (typeof int_annee === "undefined") {
            int_annee = d_now.getUTCFullYear()
        }

        if (typeof int_mois === "undefined") {
            int_mois = d_now.getMonth();
            int_mois = int_mois + 1;
        }

        if (typeof int_jour === "undefined") {
            int_jour = d_now.getUTCDate();
        }
        //les mois vont de 0 à 11
        int_mois = int_mois - 1;
        this.j_day = new Jour(int_jour, int_mois, int_annee);
    }

    getTime() {
        return this.j_day.getTime();
    }

    getMonth()
    {
        let int_mois;
        let int_annee;
        let sem_debutMois;
        let j_temp;
        let int_moisTemp;
        let int_jour;
        let i_last;
        let i_lastDay;
        let d_;
        let int_dayOfMonth, int_monthOfYear;
        let i_temp, i_time;


        int_mois = this.j_day.getMois();
        int_annee = this.j_day.getAnnee();
        //dans un premier temps on intégre la semaine comportant
        //le debut du mois
        
        sem_debutMois = new Semaine();
        this.arj_mois = sem_debutMois.getSemaine(new Jour(1,int_mois,int_annee));
        i_last = 0;
        for (let i = 1; i < 34; i++)
        {
            j_temp = new Jour(i, int_mois, int_annee);
            int_moisTemp = j_temp.getMois();
            int_jour = j_temp.getJour();

            if ((int_moisTemp === int_mois)&(int_jour === i)){
                if ((this.arj_mois.find(element => element.getJour() === i)) === undefined) {
                    this.arj_mois.push(new Jour(i, int_mois, int_annee));
                }
                d_ = new Date(int_annee, int_mois, i);
                i_lastDay = d_.getDay();
                i_last = i;
            }
        }

        if (i_lastDay !== 6) {
            i_lastDay = i_lastDay + 1;
            for (let k = i_lastDay; k < 7; k++){ 
                d_ = new Date(int_annee, int_mois, i_last);
                i_temp = 7 - k;
                i_time = d_.getTime() + i_temp * 86400000;
                d_ = new Date(i_time);
                int_dayOfMonth = d_.getDate();
                int_annee = d_.getFullYear();
                int_monthOfYear = d_.getMonth();
                this.arj_mois.push(new Jour(int_dayOfMonth, int_monthOfYear, int_annee));
            }
        }
        this.arj_mois = this.arj_mois.sort(function (a, b) {
            return (a.getTime() > b.getTime())
        });

        return this.arj_mois;
    }
}

export { Jour, Semaine, Mois, Heure };






