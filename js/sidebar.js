document.addEventListener("DOMContentLoaded", function (event) {

    const showNavbar = (toggleId, navId, bodyId, headerId) => {
        const toggle = document.getElementById(toggleId),
            nav = document.getElementById(navId),
            bodypd = document.getElementById(bodyId),
            headerpd = document.getElementById(headerId)

        if (toggle && nav && bodypd && headerpd) {
            toggle.addEventListener('click', () => {
                nav.classList.toggle('colapased')
             
                toggle.classList.toggle('bx-x')
               
                bodypd.classList.toggle('body-pd')
                
                headerpd.classList.toggle('body-pd')
            })
        }
    }

    showNavbar('header-toggle', 'nav-bar', 'body-pd', 'header')

    const linkColor = document.querySelectorAll('.nav_link');

    function markActiveLink() {
        linkColor.forEach(function (linkElement) {
            linkElement.classList.remove('active');

            var linkHref = linkElement.getAttribute('href');
            var isActiveLink = window.location.href.includes(linkHref);

            if (isActiveLink) {
                linkElement.classList.add('active');
            }
        });
    }

    markActiveLink();

});
