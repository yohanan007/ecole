/**
 * permet de controller le niveau et les classes dans le formulaire eleve/_form.
 */
let arr_nomDeClasse = Array();
let niveau = "";

/**
 * classe principale du fichier
 */
classeArecevoir =  {
        url: url,
        niveau: niveau,
        srcf: srcf
}
    
    /**
     * 
     * determine choisi par l'utilisateur 
     */
    classeArecevoir.niveaux = function() {
        const dom_niveauSelect = document.getElementById("niveau");
        indexSelect = dom_niveauSelect.selectedIndex;
        var niveauSelect = dom_niveauSelect[indexSelect].value;
        this.niveau = niveauSelect;
        console.log(niveauSelect);
        return niveauSelect;
    }

    /**
     * fonction qui envoi les data en ajax
     */
classeArecevoir.envoiData = function () {
    const body = "id="+this.niveau+"&token="+this.srcf ;
    const oReq = new XMLHttpRequest();
    oReq.open("POST", this.url, true);
    oReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
    oReq.onreadystatechange = function() { //Appelle une fonction au changement d'état.
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200){
            o_response = JSON.parse(this.response);
            classeArecevoir.raz();
            domClasseDisponible = document.getElementById("classe_disponible");
            select = document.createElement("select");
            select.setAttribute("name", "classe_disponible");
            select.setAttribute("class","form-select")
            let t_classe;
            let t_id;
            let option;
            for (var i = 0; i < o_response.length; i++)
            {
                t_classe = o_response[i].nom
                t_id = o_response[i].id
                option = document.createElement('option');
                option.setAttribute("value", t_id);
                option.textContent = t_classe;
                select.append(option);
            }
            domClasseDisponible.append(select);
    // Requête finie, traitement ici.
        }
        else
        {
            console.log(this.status);
            console.log(this.statusText);
            }
    }
    oReq.send(body);
        return null;
    }

    /**
     * remettre le dom à vide
     */
classeArecevoir.raz = function () {
    domClasseDisponible = document.getElementById("classe_disponible");
    while (domClasseDisponible.firstChild !== null)
    {
        domClasseDisponible.removeChild(domClasseDisponible.firstChild);
    }
    return null;
    }


const selectDom = document.getElementById("niveau");

selectDom.addEventListener("click", event => {
    classeArecevoir.niveaux();
    classeArecevoir.envoiData();
})