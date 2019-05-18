$( document ).ready(function() {
    let choixNb = $('#contain_number_person div');

    choixNb.click(function () {
        let number = $(this).attr('data-number'),
            html = "";

        for(let i=0;i<number;i++){
            let nb = i + 1;
            html = html + "<input class=\"form-control mb-10\" name=\"email[]\" type=\"email\" placeholder=\"Invité "+ nb + "\">";
        }

        $(choixNb).removeClass('selected');
        $(this).addClass('selected');


        $('form#nb-person #inputNb').html(html)
    })













});