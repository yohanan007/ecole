
//gestion de la creation des tableau en js à partir d'objet
//permet de pouvoir formater les tableaux et donner une cohérence

class colonne
{
    classTempColonne = "";
    idTempColonne = "";
    htmlTemp = "";
    corpsTemp = "";
    sujetTemp = "";

    constructor(classTempColonne, idTempColonne, htmlTemp, corpsTemp, sujetTemp)
    {
        if(typeof classTempColonne !== "undefined")
        {
            this.classTempColonne = classTempColonne;
        }
        
        if(typeof idTempColonne !== "undefined")
        {
            this.idTempColonne = idTempColonne;
        }
        
        if(typeof htmlTemp !== "undefined")
        {
            this.htmlTemp = htmlTemp;
        }
        
        if (typeof corpsTemp !== "undefined")
        {
            this.corpsTemp = corpsTemp;
        }
        
        if (typeof sujetTemp !== "undefined")
        {
           this.sujetTemp = sujetTemp; 
        }
        
    }

    setClass(classTemp)
    {
        this.classTempColonne = classTemp;
    }

    getClass()
    {
        return this.classTempColonne;
    }

    setIdTemp(idTemp)
    {
        this.idTempColonne = idTemp;
    }

    getIdTemp()
    {
        return this.idTempColonne;
    }

    setHtml(html)
    {
        this.htmlTemp = html;
    }

    getHtml()
    {
        return this.htmlTemp;
    }

    setCorpsTemp(corps)
    {
        this.corpsTemp = corps;
    }

    getCorpsTemp()
    {
        return this.corpsTemp;
    }

    setSujet(sujetTemp)
    {
        this.sujetTemp = sujetTemp;
    }

    getSujet()
    {
        return this.sujetTemp;
    }

    getCreateColonne()
    {
        let dom_td = document.createElement("td");
        if(this.classTempColonne !== "")
        {
            dom_td.setAttribute("class", this.classTempColonne);
        }

        if(this.idTempColonne !== "")
        {
            dom_td.setAttribute("id",this.idTempColonne)    
        }

        if(this.htmlTemp !== "")
        {
            dom_td.innerHtml = this.htmlTemp;
        }
        else
        {
            dom_td.innerHTML = this.sujetTemp + "<br>" + this.corpsTemp;
        }

        return dom_td;
    }
}

//une colonne sera crée à partir d'un objet de type
/*{
    "classe":....,
    "id":...,
    "corps":...,
    "html":...,
    "sujet":...
}*/
//une ligne est un tableau de colonne

class ligne
{
    aro_colonne = [];

    constructor(aro_colonne)
    {
        if (typeof aro_colonne !== "undefined")
        {
            this.aro_colonne = aro_colonne;    
        }
    }

    setColonne(aro_colonne)
    {
        this.aro_colonne = aro_colonne;
    }

    getColonne()
    {
        return this.aro_colonne;
    }

    getLigne()
    {
        let tr = document.createElement("tr");
        this.aro_colonne.forEach((colonneElement) => {
            let colonneTemp = new colonne();
           for (const value in colonneElement)  {
                switch (value) {
                    case "classe":
                        colonneTemp.setClass(colonneElement[value]);
                        break;
                    case "html":
                        colonneTemp.setHtml(colonneElement[value]);
                        break;
                    case "id":
                        colonneTemp.setIdTemp(colonneElement[value]);
                        break;
                    case "corps":
                        colonneTemp.setCorpsTemp(colonneElement[value]);
                        break;
                    case "sujet":
                        colonneTemp.setSujet(colonneElement[value]);
                        break;
               }
            };
            let trTemp = colonneTemp.getCreateColonne();
            tr.append(trTemp);
        });
        return tr;
    }
}

/**
 * une Table
 * art_header : [header,header,..]
 * aro_ligne : [aro_colonne , aro_colonne, ...]
 */
class Table
{
    art_header = [];
    aro_ligne = [];
    art_option = [];

    constructor(art_header,aro_ligne,art_option)
    {
        if (typeof aro_ligne !== "undefined")
        {
            this.aro_ligne = aro_ligne;
        }

        if (typeof art_header !== "undefined")
        {
            this.art_header = art_header;
        }

        if (typeof art_option !== "undefined")
        {
            this.art_option = art_option;
        }
    }

    setHeader(art_header)
    {
        this.art_header = art_header;
    }

    setOption(art_option)
    {
        this.art_option = art_option;
    }

    setLignes(aro_ligne)
    {
        this.aro_ligne = aro_ligne;
    }

    getLignes()
    {
        return this.aro_ligne;
    }

    getHeader()
    {
        return this.art_header;
    }

    getOption()
    {
        return this.art_option;
    }

    getTable()
    {
        //todo : gestion des option de table
        let dom_table = document.createElement("table");
        dom_table.setAttribute("class", "table table-secondary table-bordered");
        let dom_body = document.createElement("tbody");
        
        this.aro_ligne.forEach((value) => {
            let ligneTemp = new ligne(value);
            dom_body.append(ligneTemp.getLigne());
        });
        dom_table.append(dom_body);
        if (typeof this.getCreateHeader() !== "undefined")
        {

            dom_table.append(this.getCreateHeader());
        }
        return dom_table;
    }

    getCreateHeader()
    {

        if (this.art_header.length > 0)
        {

            let dom_head = document.createElement("thead");
            let dom_tr = document.createElement("tr");
            this.art_header.forEach(value => {
            let th = document.createElement("th");
            th.innerHTML = value;
            dom_tr.append(th);
            })
            dom_head.append(dom_tr);
            return dom_head;
        }
        else
        {
            return undefined;
        }
        
    }
    
}





/*@todo : à retravailler plus tard
permet la creation de barre de  nav
de type boostrap
à retravailler plus tard de maniére plus propre
intégrer :
        - les elements form et sous menu
        - couleur de la barre de navigation
        - text
*/
class Nav{
    aro_element = [];
    str_idNav = "";
    str_color_back = "";
    str_color_front = "bg-light";
    str_link_logo = "";
    str_logo_text = "";
    
    constructor(aro_element, str_idNav,str_link_logo = "",str_logo_text = "") {
        if (typeof (aro_element) !== "undefined") {
            this.aro_element = aro_element;
        }

        if (typeof (str_idNav) !== "undefined") {
            this.str_idNav = str_idNav;
        }

        this.str_link_logo = str_link_logo;
        this.str_logo_text = str_logo_text;

        //@todo : ajouter plus tard couleur et taille de maniére à construire 
        //la barre de navigation plus intuitivement
    }


    getClassPrincipale() {
        //par défault
        return 'navbar navbar-expand-lg navbar-light ' + this.str_color_front;
    }
    /**
     * gestion du logo de la navbar
     * @returns DOM Element
     */
    getLogo() {
        const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" };
        let d_time = new Date();
        str_text = d_time.toLocaleString('fr-FR', options);

        let dom_a = document.createElement("a");
        dom_a.setAttribute("class", "navbar-brand");
        dom_a.setAttribute("href", "");
        let str_text = "";

        if (this.str_link_logo !== "") {
            let dom_img = document.createElement("img");
            dom_img.setAttribute("src", this.str_link_logo);
            dom_img.setAttribute("width", "30");
            dom_img.setAttribute("height", "24");
            dom_img.setAttribute("class", "d-inline-block align-text-top");
            dom_a.append(dom_img);
        }

        if (this.str_logo_text !== "") {
            if (parseInt(this.str_logo_text) > 0) {

                d_time = new Date(parseInt(this.str_logo_text));
                str_text = d_time.toLocaleString('fr-FR', options);
            } else {
                str_text = this.str_logo_text;
            }
        }

        dom_a.textContent = str_text;

        const dom_div = document.createElement("div");
        dom_div.setAttribute("class", "container-fluid")
        
        dom_div.append(dom_a);

        return dom_div;
    }

    getForm(elmt) {
        
    }

    getAoRButton(elmt) {
        
    }

    getNav() {
        //création du nav maitre
        const dom_nav_master_menu = document.createElement("nav");
        dom_nav_master_menu.setAttribute("id", this.str_idNav);
        dom_nav_master_menu.setAttribute("class","navbar navbar-expand-lg navbar-light bg-light")

        //création du div
        const dom_div_support_global = document.createElement("div");
        dom_div_support_global.setAttribute("class", "container-fluid");

        dom_div_support_global.append(this.getLogo());

        const dom_div_support = document.createElement("div");
        dom_div_support.setAttribute("class", "collapse navbar-collapse");
        dom_div_support.setAttribute("id", "navbarSupportedContent");

        const dom_ul_support = document.createElement("ul");
        dom_ul_support.setAttribute("class", "navbar-nav me-auto mb-2 mb-lg-0");

        let dom_li_item;
        let dom_a_button_item;

        this.aro_element.forEach(elmt => {
            console.log(elmt);
            dom_li_item = document.createElement("li");
            dom_li_item.setAttribute("class","nav-item");
            // faire en sorte de travailler avec une colection, éviter multiple if
            if (elmt.dom_element === "button") {
                dom_a_button_item = document.createElement("button");
            } else {
                dom_a_button_item = document.createElement("a");
            }

            if (typeof elmt.href !== "undefined") {
                dom_a_button_item.setAttribute("href", elmt.href);
            }

            if (typeof elmt.id !== "undefined") {
                dom_a_button_item.setAttribute("id", elmt.id);
            }

            if (typeof elmt.class !== "undefined") {
                dom_a_button_item.setAttribute("class", elmt.class);
            } else {
                dom_a_button_item.setAttribute("class", "nav-link");
            }

            if (typeof elmt.value !== "undefined") {
                dom_a_button_item.setAttribute("value", elmt.value);
            }


            if (typeof elmt.type !== "undefined") {
                dom_a_button_item.setAttribute("type", elmt.type);
            }

            if (typeof elmt.attribute !== "undefined") {
                elmt.attribute.forEach(item2 => {
                    console.log(item2);
                    dom_a_button_item.setAttribute(item2.nom, item2.value);
                })
            }

            if (elmt.text !== null) {
                dom_a_button_item.textContent = elmt.text;
            }

            dom_li_item.append(dom_a_button_item);
            dom_ul_support.append(dom_li_item);
        });
    
        dom_div_support_global.append(dom_div_support);
        dom_div_support.append(dom_ul_support);

    
        dom_nav_master_menu.append(dom_div_support_global);
        return dom_nav_master_menu;
    }
}


/**
 * Classe gerant les couleurs sur boostrap
 en cours 
class ColorBs{
    str_type;
    str_color;
    str_element;

    art_MapColor;
    art_MapType;
    art_element;
    
    static getColorTraduce(){
        art_MapColor = [];
        art_MapColor.push({
            "user": "white",
            "boostrap": "white"
        });
        art_MapColor.push({
            "user": "blanc",
            "boostrap": "white"
        });

        art_MapColor.push({
            "user": "black",
            "boostrap": "dark"
        });
        art_MapColor.push({
            "user": "noir",
            "boostrap": "dark"
        });
        art_MapColor.push({
            "user": "blue",
            "boostrap": "information"
        });
        art_MapColor.push({
            "user": "bleu",
            "boostrap": "information"
        });
        art_MapColor.push({
            "user": "jaune",
            "boostrap": "warning"
        });
        art_MapColor.push({
            "user": "yellow",
            "boostrap":"warning"
        })

        return art_MapColor;

    }

    static getTypeTraduce() {
        art_MapType = [];
        art_MapType.push({
            "user": "entete",
            "boostrap": "header"
        });

        art_MapType.push({
            "user": "header",
            "boostrap": "header"
        });
        
        art_MapType.push({
            "user": "corps",
            "boostrap": "body"
        });

        art_MapType.push({
            "user": "body",
            "boostrap": "body"
        });

        art_MapType.push({
            "user": "pied",
            "boostrap": "footer"
        });

        art_MapType.push({
            "user": "footer",
            "boostrap": "footer"
        });

        
        return art_MapType;
    }

    static getElementTraduce() {
        art_mapElememnt = [];

        art_mapElememnt.push({
            "user": "carte",
            "boostrap": "card"
        });

        art_mapElememnt.push({
            "user": "bouton",
            "boostrap": "button"
        });

        art_mapElememnt.push({
            "user": "modale",
            "boostrap": "modal"
        });

    }

    constructor(str_type, str_color, str_element) {
        if (typeof str_type !== "undefined") {
            this.str_type = str_type;
        }

        if (typeof str_color !== "undefined") {
            this.str_color = str_color;
        }

        if (typeof str_element !== "undefined") {
            this.str_element = str_element;
        }
    }

    setType(str_type) {
        this.str_type = str_type;
        return this.str_type;
    }

    strColor(str_color) {
        this.str_color = str_color;
        return this.str_color;
    }

    getType() {
        return this.str_type;
    }

    getColor() {
        return this.str_color;
    }

    getClassBoostrap() {
        str_result = "";
        return { "element": str_result };
    }
}
*/

class Card{
    obj_header = null; //objet contenant couleur du texte (color_text) et texte (text)
    obj_body = null; //objet contenant couleur du texte (color_text) et texte (text) 
    obj_footer = null; //objet contenant couleur du texte (color_text) et texte (text) 
    obj_option = null; //objet contenant couleur de la card (color_card) eventuel image  (img_card) largeur imposé (width_card)



    constructor(obj_header, obj_body, obj_footer, obj_option) {
        if (typeof obj_header !== "undefined") {
            this.obj_header = obj_header;
        }

        if (typeof obj_body !== "undefined") {
            this.obj_body = obj_body;
        }

        if (typeof obj_footer !== "undefined") {
            this.obj_footer = obj_footer;
        }

        if (typeof obj_option !== "undefined") {
            this.obj_option = obj_option;
        }
    }

    getArtHeader() {
        return this.art_header;
    }

    getArtBody() {
        return this.art_body;
    }

    getArtFooter() {
        return this.art_footer;
    }

    getObjOption() {
        return this.obj_option;
    }

    setArtHeader(art_header) {
        this.art_header = art_header;
    }

    setArtBody(art_body) {
        this.art_body = art_body;
    }

    setArtFooter(art_footer) {
        this.art_footer = art_footer;
    }

    setObjOption(obj_option) {
        this.obj_option = obj_option;
    }

    getCreateHeader() {
        let dom_header = document.createElement("div");
        let str_colorText = "";
        let str_text = "";
        
        if (this.obj_header !== null) {
            if (this.obj_header.color_text !== null) {
                str_colorText = this.obj_header.color_text;
            }

            if (this.obj_header.text !== null) {
                str_text = this.obj_header.text;
            }
        }
        let str_class = "card-header " + str_colorText;
        dom_header.setAttribute("class", str_class);
        dom_header.textContent = str_text;
        if (this.obj_header.id !== null) {
            if (this.obj_header.id !== "") {
                dom_header.setAttribute("id", this.obj_header.id);
            }
        }
        return dom_header;
    }

    getCreateBody() {
        let dom_body = document.createElement("div");
        let str_colorText = "";
        let str_text = "";
        if (this.obj_body !== null) {
            if (this.obj_body.color_text !== null) {
                str_colorText = this.obj_body.color_text;
            }

            if (this.obj_body.text !== null) {
                str_text = this.obj_body.text;
            }
        }
        let str_class = "card-body " + str_colorText;
        dom_body.setAttribute("class", str_class);
        let dom_p = document.createElement("p");
        dom_p.textContent = str_text
        dom_body.append(dom_p);
        if (this.obj_body.id !== null) {
            if (this.obj_body.id !== "") {
                dom_body.setAttribute("id", this.obj_body.id);
            }
        }
        return dom_body;
    }

    getCreateFooter() {
        let dom_footer = document.createElement("div");
        let str_colorText = "";
        let str_text = "";
        if (this.obj_footer !== null) {
            if (this.obj_footer.color_text !== null) {
                str_colorText = this.obj_footer.color_text;
            }

            if (this.obj_footer.text !== null) {
                str_text = this.obj_footer.text;
            }
        }
        let str_class = "card-footer " + str_colorText;
        dom_footer.setAttribute("class", str_class);
        dom_footer.textContent = str_text;

        if (this.obj_footer.id !== null) {
            if (this.obj_footer.id !== "") {
                dom_footer.setAttribute("id", this.obj_footer.id);
            }
        }
        return dom_footer;
    }


    getCreateCard() {
        let dom_cardMaster = document.createElement("div");
        dom_cardMaster.setAttribute("class", "card");
        let dom_header = this.getCreateHeader();
        let dom_body = this.getCreateBody();
        let dom_footer = this.getCreateFooter();
        dom_cardMaster.append(dom_header);
        dom_cardMaster.append(dom_body);
        dom_cardMaster.append(dom_footer);
        return dom_cardMaster;
    }


}

class CollectCard{
    art_Card = []; //tableau contenant des objets generant des Cards [{header:obj_header,body:obj_body,footer:obj_footer,option:obj_option}]
    obj_option = null; //option général des card . {img_card:"", color_card : "", width_card = 0, text_color = ""}

    constructor(art_Card) {
        if (typeof art_Card !== undefined) {
            this.art_Card = art_Card;
        }
    }

    getCreateCollectCard() {
        return "";
    }
}

class Element{
    str_id = "";
    str_class = "";
    str_text = "";

    constructor(str_id, str_class,str_text) {
        if (typeof (str_id) !== "undefined") {
            this.str_id = str_id;
        }

        if (typeof (str_class) !== "undefined") {
            this.str_class = str_class;
        }

        if(typeof(str_text) !== "undefined"){

        }
    }

    setId(str_id){
        this.str_id = str_id;
    }

    setClasse(str_class){
        this.str_classe = str_class;
    }

    setText(str_text){
        this.str_text = str_text;
    }

    getId(){
        return this.str_id;
    }

    getClass(){
        return this.str_class;
    }

    getText(){
        return this.str_text;
    }

    ElementId() {
        let dom_id;
        if (this.str_id !== "") {
            dom_id = document.getElementById(this.str_id);
        }
        return dom_id;
    }

    razElementId(b_withParent) {
        if (typeof b_withParent === "undefined") {
            b_withParent = false;
        }
        let dom_id = this.ElementId();
        if (typeof (dom_id !== "undefined")) {
            if (b_withParent) {
                dom_id.remove();
            } else {
                while (dom_id.children.length > 0) {
                    dom_id.children[0].remove();
                }
            }
            
        }
    }

    ElementClass() {
        let dom_class;
        if (this.str_class !== "") {
            dom_class = document.getElementsByClassName(this.str_class);
        }
        return dom_class;
    }

    razElementClass() {
        let dom_class = this.ElementClass();
        if (typeof (dom_class !== "undefined")) {
            while (dom_class.length > 0) {
                dom_class[0].remove();
            }
        }
    }
}

class Dom extends Element{
    dom_element;

    constructor(obj_element,dom_element){
        super(obj_element.getId(), obj_element.getClass(),obj_element.getText());
        if(typeof(dom_element) != "undefined"){
            this.dom_element = dom_element;
        }
    }

    getAttribute(){
        this.dom_element.setAttribute("id",this.str_id);
        this.dom_element.setAttribute("class",this.str_class);
        this.dom_element.textContent = this.str_text;
        return this.dom_element;
    }
}

class Grid{

    int_nbr = 3;
    arr_elementGrid;
    dom_elementPrincipal = document.createElement("div");

    constructor(int_nbr, arr_elementGrid,dom_elementPrincipal){
        this.int_nbr = int_nbr;
        this.arr_elementGrid = arr_elementGrid;
        if(typeof(dom_elementPrincipal)!=="undefined"){
            this.dom_elementPrincipal = dom_elementPrincipal;
        }
    }

    getContainer(){
        this.dom_elementPrincipal.setAttribute("class","container row align-items-start");
    }

    getElementGrid(int_increment){
        let dom_element = document.createElement("div");
        dom_element.setAttribute("class","col")
        if(typeof(this.arr_elementGrid[int_increment]) !== "undefined"){
            const obj_temp = this.arr_elementGrid[int_increment];
                if(typeof(obj_temp.element) !== "undefined"){
                    dom_element.append(obj_temp.element);
                }
        }
        return dom_element;
    }

    getElement(){
        this.getContainer();

        if(this.int_nbr > 0){
            for (let i = 0; i < this.int_nbr; i++){
                this.dom_elementPrincipal.append(this.getElementGrid(i));
            }
        }
        return this.dom_elementPrincipal;
    }



}

export { Table, colonne, ligne, Card, CollectCard, Element, Nav, Grid, Dom  };