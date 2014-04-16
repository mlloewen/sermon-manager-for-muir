jQuery(document).ready(function($) {
	if(jQuery("#widget-postfromsermons_id-2-taxonomy").val()=="none") {
	  jQuery(".total_term_div label").css({
		display : "none"
	  });
	}
	else {
	  jQuery(".total_term_div label").css({
		display : "block"
	  });
	}
	jQuery("#widget-postfromsermons_id-2-taxonomy").on("change", function() {
	jQuery("div.terms_div_class").css({
	  display : "none"
	});
	var presentClassName = $(this).val();
	if(jQuery(this).val()=="none") {
	  jQuery(".total_term_div label").css({
		display : "none"
	  });
	}
	else {
	  jQuery(".total_term_div label").css({
		display : "block"
	  });
	}
	jQuery("div.taxonomy-"+presentClassName).css({
		display : "block"
	});
	if(jQuery.trim(jQuery("div.taxonomy-"+presentClassName+" ul").html()) == "") {
		jQuery("div.taxonomy-"+presentClassName+" ul").html("No terms avialeble");
	}
	});
});