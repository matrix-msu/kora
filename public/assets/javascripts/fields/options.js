var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};

Kora.Fields.Options = function(fieldType) {

    function initializeSelects() {
        //Most field option pages need these
        $('.single-select').chosen({
            width: '100%',
        });

        $('.multi-select').chosen({
            width: '100%',
        });
    }

    //TODO:: Will need this somewhere, but here?
    function initializeSelectAddition() {
        var select = $('.modify-select');
        var container = select.next();

        container.find('.chosen-search-input').on('keyup', function(e) {
            if (e.which === 13 && container.find('li.no-results').length > 0) {
                var option = $("<option>").val(this.value).text(this.value);

                select.prepend(option);
                select.find(option).prop('selected', true);
                select.trigger("chosen:updated");
            }
        });
    }

    //Fields that have specific functionality will have their own initialization process

    function initializeDateOptions() {
        $('.start-year-js').change(function() { printYears(); });

        $('.end-year-js').change(function() { printYears(); });

        function printYears(){
            start = $('.start-year-js').val(); end = $('.end-year-js').val();

            if(start=='')  { start = 0; }
            if(end =='') { end = 9999; }

            val = '<option></option>';
            for(var i=start;i<+end+1;i++) {
                val += "<option value=" + i + ">" + i + "</option>";
            }

            $('.default-year-js').html(val); $('.default-year-js').trigger("chosen:updated");
        }
    }

    initializeSelects();

    switch(fieldType) {
        case 'Date':
            initializeDateOptions();
            break;
        case 'Documents':
            initializeSelectAddition();
            break;
        default:
            break;
    }
}