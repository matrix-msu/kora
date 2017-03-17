<script>
    updateInterval_{{$prefix}}();
    $("#{{$prefix}}_left").keyup(function() {updateInterval_{{$prefix}}();});
    $("#{{$prefix}}_right").keyup(function() {updateInterval_{{$prefix}}();});
    $("#{{$prefix}}_invert").click(function() {updateInterval_{{$prefix}}();});

    /**
     * Lets the user know what the interval they will be searching over.
     */
    function updateInterval_{{$prefix}}() {
        var selector = $("#{{$prefix}}_interval");

        var left_val = $("#{{$prefix}}_left").val();
        var right_val = $("#{{$prefix}}_right").val();
        var invert = $("#{{$prefix}}_invert").is(":checked");
        var left_string;
        var right_string;

        if (left_val != "" && right_val != "" && parseFloat(left_val) > parseFloat(right_val)) { // Invalid range.
            selector.html("{{trans('advanced_search.invalid')}}");
            $("#{{$prefix}}_valid").val("0");
        }
        else {
            left_string = (left_val == "") ? "-&infin;" : parseFloat(left_val).toString();
            right_string = (right_val == "") ? "&infin;" : parseFloat(right_val).toString();

            var left_infinity = left_string.indexOf("infin") !== -1;
            var right_infinity = right_string.indexOf("infin") !== -1;

            if (left_infinity && right_infinity) { // Don't allow both inputs to be infinity.
                selector.html("{{trans('advanced_search.invalid')}}");
                $("#{{$prefix}}_valid").val("0");
                return;
            }

            var left_bound = (left_infinity) ? "(" : "[";
            var right_bound = (right_infinity) ? ")" : "]";

            if (invert) { // Should we invert the bounds? [L,R] turns to (-inf, L) and (R, inf)
                if (left_infinity && right_infinity) { // Inverting two infinite bounds shouldn't do anything.
                    selector.html(left_bound + left_string + "," + right_string + right_bound);
                }
                else if (left_infinity) {
                    selector.html("(" + right_string + ",&infin;)");
                }
                else if (right_infinity) {
                    selector.html("(-&infin;," + left_string + ")");
                }
                else { // Neither bound is infinity.
                    selector.html("(-&infin;," + left_string + ") and (" + right_string + ",&infin;)");
                }
            }
            else {
                if (left_string == right_string) {
                    selector.html("[" + left_string + "]");
                }
                else {
                    selector.html(left_bound + left_string + "," + right_string + right_bound);
                }
            }

            $("#{{$prefix}}_valid").val("1");
        }
    }
</script>