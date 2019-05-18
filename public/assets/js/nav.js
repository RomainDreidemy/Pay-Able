$( document ).ready(function() {
    let nav = $('nav'),
        navLink = $('nav ul li a'),
        pictoBurger = $('#nav_burger'),
        ul = $('nav ul');

    function animationNav(){
        // Set the effect type
        var effect = 'slide';

        // Set the options for the effect type chosen
        var options = { direction: 'right' };

        // Set the duration (default: 400 milliseconds)
        var duration = 500;

        ul.toggle(effect, options, duration);

        pictoBurger.toggleClass('open');
    }

    pictoBurger.click(function () {
        animationNav();
    });

    navLink.click(function () {
        if($(window).width() < 1024){
            animationNav();
        }
    })















});