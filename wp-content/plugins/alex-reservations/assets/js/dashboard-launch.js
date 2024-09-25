
document.addEventListener("DOMContentLoaded", function(){

    window.config = window.SRR_config

    window.Eva = new CreateEva( window.config || {})

    /* PLUGINS HERE */

    window.Eva.start()
});
