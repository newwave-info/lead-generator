/**
 * JS Framework - 5.3.6
 * 
 * Perspect srl
 * https://www.perspect.it/
 */

//*************************
// LOADING
//*************************
function loading() {
    jQuery('#loader').addClass('nascosto');
}


//*************************
// START
//*************************
function start() {
    loading();
    menu();
    fancyPop();
    //smooth_scroll();
    //mouseFollower();
    companiesTable();
    companyAgents();
    analysesTable();
}



//*************************
// MENU
//*************************
function menu() {
    jQuery('.hamburger, #drawer_bg').click(function () {
        jQuery('body').toggleClass('drawer-in');
        return false;
    });
}



//***********************
// LAZYLOAD
//*************************
function lazyload() {
    jQuery('.lazy').Lazy({
        scrollDirection: 'vertical',
        effect: 'fadeIn',
        effectTime: 750,
        visibleOnly: true
        //delay: 3000
        //removeAttribute: false
    })
}


//***********************
// Mouse Follower
//*************************
function mouseFollower() {
    const cursor = new MouseFollower({
        visible: false,
        speed: 0.3,
        skewing: 0,
        skewingText: 0,
        skewingIcon: 0,
    });
}



//********************************
// initBootstrapComponents
// Inizializza Tooltip e Popover
//********************************
function initPopAndTip() {

    // 1. GESTIONE DEI TOOLTIP

    // Distruggi istanze Tooltip esistenti per evitare duplicati
    const existingTooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltipElements.forEach(el => {
        const instance = bootstrap.Tooltip.getInstance(el);
        if (instance) {
            instance.dispose();
        }
    });

    // Inizializza i nuovi Tooltip
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl =>
        new bootstrap.Tooltip(tooltipTriggerEl)
    );


    // 2. GESTIONE DEI POPOVER

    // Distruggi istanze Popover esistenti per evitare duplicati
    const existingPopoverElements = document.querySelectorAll('[data-bs-toggle="popover"]');
    existingPopoverElements.forEach(el => {
        const instance = bootstrap.Popover.getInstance(el);
        if (instance) {
            instance.dispose();
        }
    });

    // Inizializza i nuovi Popover
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl =>
        new bootstrap.Popover(popoverTriggerEl, {
            html: true,
            trigger: 'hover',
            placement: 'top'
        })
    );

    // Ritorna le liste, se necessario per debug o gestione successiva
    return {
        tooltips: tooltipList,
        popovers: popoverList
    };
}


//***********************
// TABLE
//*************************
function companiesTable() {

    let table = new DataTable('#companiesTable',{
        language: {
            url: '//cdn.datatables.net/plug-ins/2.3.4/i18n/it-IT.json',
        },
        scrollX: true,
        columnDefs: [
            { width: '120px', targets: [1, -1, -2] }
        ],
        paging: false,
        // scrollCollapse: true,
        // scrollY: '60vh',
        // scrollX: true,
        lengthChange: false,
        drawCallback: function() {
            initPopAndTip();
        }
    });
}

function companyAgents() {

    let table = new DataTable('#companyAgents',{
        language: {
            url: '//cdn.datatables.net/plug-ins/2.3.4/i18n/it-IT.json',
        },
        lengthChange: false,
        paging: false,
        searching: false,
        info: false,
        drawCallback: function() {
            initPopAndTip();
        }
    });
}

function analysesTable() {

    let table = new DataTable('#analysesTable',{
        language: {
            url: '//cdn.datatables.net/plug-ins/2.3.4/i18n/it-IT.json',
        },
        lengthChange: false,
        paging: false,
        drawCallback: function() {
            initPopAndTip();
        },
        initComplete: function() {
            let api = this.api();
            let column = api.column(1); // seconda colonna
            let select = $('<select class="form-select me-2" style="width:auto;"><option value="">Tutti</option></select>')
                .on('change', function() {
                    let val = $.fn.dataTable.util.escapeRegex($(this).val());
                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                });
            column.data().unique().sort().each(function(d) {
                select.append('<option value="' + d + '">' + d + '</option>');
            });
            $('.dt-layout-start').first().prepend(select);
        }
    });
}


//*************************
// SMOOTH SCROLL
//*************************
function smooth_scroll() {
    jQuery('.scroll, .toc-list a').click(function () {
        jQuery('html, body').animate({
            scrollTop: jQuery(jQuery.attr(this, 'href')).offset().top
        }, 600);
        return false;
    });
}


//*************************
// FANCYBOX
//*************************
function fancyPop() {

	jQuery(".gallery-link-overlay").fancybox({
        baseClass: 'fancybox-nw',
        hash: false,
        loop: true,
        gutter: 50,
        buttons: [
            //"zoom",
            //"share",
            //"slideShow",
            //"fullScreen",
            //"download",
            //"thumbs",
            "close"
        ],
        backFocus: false,
        protect: true,
        btnTpl: {
            // Arrows
            arrowLeft:
              '<button data-fancybox-prev class="fancybox-button fancybox-button--arrow_left" title="{{PREV}}">' +
              '<div><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7.58594 12L14.293 5.29297L15.707 6.70703L10.4141 12L15.707 17.293L14.293 18.707L7.58594 12Z" /></svg></div>' +
              "</button>",

            arrowRight:
              '<button data-fancybox-next class="fancybox-button fancybox-button--arrow_right" title="{{NEXT}}">' +
              '<div><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M16.414 12 9.707 5.293 8.293 6.707 13.586 12l-5.293 5.293 1.414 1.414L16.414 12Z" /></svg></div>' +
              "</button>",
        }
    });

    // jQuery("[data-fancybox]").fancybox({
    //     buttons: [
    //         //"zoom",
    //         //"share",
    //         //"slideShow",
    //         //"fullScreen",
    //         //"download",
    //         //"thumbs",
    //         "close"
    //     ],
    //     backFocus: false,
    //     protect: true,
    // });

}

