let navCategorie = $("#nav-left ul#liste-all-click li.categorie span.click"),
    allnavHide  = $("#nav-left ul#liste-all-click li.categorie ul.sous-link");

navCategorie.click( function () {
    let navHide = $(this).parent().find("ul.sous-link");

    if(navHide.hasClass('open')){
        navHide.slideUp();
        navHide.removeClass('open');
    }else{
        navHide.slideDown();
        navHide.addClass('open');
    }
});

// Recherche utilisateur en AJAX

let inputSearchUser = $("#search_user");

inputSearchUser.keyup(function () {

    $.ajax({
        url : '/ajax/recherche-utilisateur',
        type : 'GET',
        data : 'recherche=' + this.value,
        datatype : 'json',
        success : function(data, statut){
            let html = "<div class=\"row header green\">" +
                "                <div class=\"cell\">Id</div>" +
                "                <div class=\"cell\">Nom</div>" +
                "                <div class=\"cell\">Prénom</div>" +
                "                <div class=\"cell\">Email</div>" +
                "                <div class=\"cell\">Statut</div>" +
                "                <div class=\"cell\">Action</div>" +
                "            </div>";
            for(let i=0; i < data.length; i++){
                let Datastatut = "";
                if(data[i]['admin'] == 1){
                    Datastatut = "Administrateur";
                }else{
                    Datastatut = "Utilisateur";
                }

                html += "<div class=\"row\">\n" +
                    "                    <div class=\"cell\">" + data[i]['id'] +"</div>" +
                    "                    <div class=\"cell\">" + data[i]['name'] +"</div>" +
                    "                    <div class=\"cell\">" + data[i]['surname'] +"</div>" +
                    "                    <div class=\"cell\">" + data[i]['email'] +"</div>" +
                    "                    <div class=\"cell\">" + Datastatut + "</div>" +
                    "                    <div class=\"cell\"><a href=\"https://www.pay-able.fr/backoffice/utilisateurs/modification?id=" + data[i]['id'] + "\">Modification</a></div>" +
                    "                </div>"
            }

            $('#table-user-search').html(html);
        }
    });
});