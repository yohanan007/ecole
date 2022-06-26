
const dom_btnAddElement = document.getElementById("add-another-collection-widget");

dom_btnAddElement.addEventListener("click", event => {
    console.log(dom_btnAddElement.getAttribute("data-list-selector").replace("#", ""));
    var list = document.getElementsByClassName(dom_btnAddElement.getAttribute("data-list-selector").replace("#",""));
    console.log(list);
    var counter = list[0].getAttribute("widget-counter") || list[0].children.length;
    let newWidget = list[0].getAttribute("data-prototype");
    newWidget = newWidget.replace(/__name__/g, counter);
    counter++;
    list[0].setAttribute("widget-counter", counter);

    var newElem = document.createElement("li");
    var classe_item = "list-group-item " + counter
    newElem.setAttribute("class",classe_item)
    newElem.innerHTML = newWidget;
    var inputs = newElem.getElementsByTagName("input");

    for (var i = 0; i < inputs.length; i++) {
        inputs[i].setAttribute("class","form-control");
        inputs[i].setAttribute("placeholder","nom de classe");
    }


    list[0].append(newElem);
    console.log(list);
});