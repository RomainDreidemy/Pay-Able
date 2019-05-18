$( document ).ready(function() {

    let inputSolution = $('#choixSolution input'),
        price2person = $('#form-nbPerson div:nth-child(1) .prix p'),
        price3person = $('#form-nbPerson div:nth-child(2) .prix p'),
        price4person = $('#form-nbPerson div:nth-child(3) .prix p');
    function solutionCkecked(){
        let checked = $('#choixSolution input:checked'),
            price = 0;
        for (let i = 0; i < checked.length; i++){
            price = price + parseInt(checked[i].dataset.price);
        }

        if(price == 0){
            price2person.html("-");
            price3person.html("-");
            price4person.html("-");
        }else {
            price2person.html((price / 2) + "€/personne");
            price3person.html((price / 3) + "€/personne");
            price4person.html((price / 4) + "€/personne");
        }
    }

    solutionCkecked();


    inputSolution.click( function () {
        solutionCkecked();
    });

    let inputNbPerson = $('#form-nbPerson input');

    $('#form-nbPerson input:checked').siblings('.prix').children('label').children('img').attr('src', "_front/assets/img/picto-nb-person-select.png");


    inputNbPerson.click(function () {
        console.dir($(this).siblings('.prix').children('label').children('img').attr('src'));
        $('.prix label img').attr('src', "_front/assets/img/picto-nb-person.png");
        $(this).siblings('.prix').children('label').children('img').attr('src', "_front/assets/img/picto-nb-person-select.png");
    })
});