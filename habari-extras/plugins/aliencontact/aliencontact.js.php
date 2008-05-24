<?php Header("content-type: application/x-javascript"); ?>
/* text area resizer: http://plugins.jquery.com/project/TextAreaResizer */
(function($){var textarea,staticOffset;var iLastMousePos=0;var iMin=32;var grip;$.fn.TextAreaResizer=function(){return this.each(function(){textarea=$(this).addClass('processed'),staticOffset=null;$(this).wrap('<div class="resizable-textarea"><span></span></div>').parent().append($('<div class="grippie"></div>').bind("mousedown",{el:this},startDrag));var grippie=$('div.grippie',$(this).parent())[0];grippie.style.marginRight=(grippie.offsetWidth-$(this)[0].offsetWidth)+'px'})};function startDrag(e){textarea=$(e.data.el);textarea.blur();iLastMousePos=mousePosition(e).y;staticOffset=textarea.height()-iLastMousePos;textarea.css('opacity',0.25);$(document).mousemove(performDrag).mouseup(endDrag);return false}function performDrag(e){var iThisMousePos=mousePosition(e).y;var iMousePos=staticOffset+iThisMousePos;if(iLastMousePos>=(iThisMousePos)){iMousePos-=5}iLastMousePos=iThisMousePos;iMousePos=Math.max(iMin,iMousePos);textarea.height(iMousePos+'px');if(iMousePos<iMin){endDrag(e)}return false}function endDrag(e){$(document).unbind('mousemove',performDrag).unbind('mouseup',endDrag);textarea.css('opacity',1);textarea.focus();textarea=null;staticOffset=null;iLastMousePos=0}function mousePosition(e){return{x:e.clientX+document.documentElement.scrollLeft,y:e.clientY+document.documentElement.scrollTop}}})(jQuery);

/* validation part */
$(document).ready( function() {
	var form = $("form#contactForm");
	$(form).attr({autocomplete: "of"});
	
	$('textarea:not(.processed)').TextAreaResizer();	
	
	function checkField(field) {
		var error = false;
		var val = $(field).val();
		
		var parent = $(field).parent();
		
		if($(field).attr("class").indexOf("textarea") != -1) {
			parent = $(parent).parent().parent();
		}
		
		<?php
		$elements = AlienContact::elements();
		foreach($elements as $element) { if($element['regex'] != '') { ?>
		if ($(field).attr("class").indexOf("<?php echo $element['id']; ?>") != -1) {
			if (!<?php echo $element['regex']; ?>.test(val))
				error = true;
		}<?php } } ?>
		
		// required fields
		if ($(parent).attr("class").indexOf("required") != -1) {
			if (!$(field).val().length) {
				error = true;
			}	
		} else if($(field).val().length == 0) {
			error = false;
		}
		
		return !error;
	}
	
	function validateField(field) {
		form = $("form#contactForm");
		
		error = checkField(field);
		
		var parent = $(field).parent();
		
		if($(field).attr("class").indexOf("textarea") != -1) {
			parent = $(parent).parent().parent();
		}
		
		var validationError = false;
		// for each field test it
		$("input, select, textarea", form).each( function() {
			if ($(this).attr("class")) {
				if (!checkField(this))
					validationError = true;
			}
		});
		
		if(validationError) {
			$(form).addClass("bad");
			$(form).removeClass("good");
			$(form).attr({disabled: "disabled"});
		} else {
			$(form).removeClass("bad");
			$(form).addClass("good");
			$(form).removeAttr("disabled");
		}

		if (!error) {
			$(parent).addClass("bad");
			$(parent).removeClass("good");
		} else {
			$(parent).removeClass("bad");
			$(parent).addClass("good");
		}
		
		return error;
	}
	
	$("form#contactForm").each( function() {
		// handle submissions without filling any field
		$(this).submit(function () {
			var validationError = false;
			// for each field test it
			$("input, select, textarea", this).each( function() {
				if ($(this).attr("class")) {
					if (!checkField(this))
						validationError = true;
				}
			});
			if(validationError == true) {
				return false;
			} else {
				var overlay = jQuery('<div id="contactFormResultsOverlay" class="boxOverlay"></div>').prependTo($("body"));
				overlay.fadeTo(0.001, 0);
				overlay.fadeTo("slow", 0.7);
				var results = jQuery('<div id="contactFormResults" class="boxBox"></div>').addClass('loading').prependTo($("body"));
				results.hide();
				results.fadeIn("slow");
				
				$.ajax({
				  url: "<?php echo URL::get('ajax', array('context'=>'submit_form')); ?>",
				  cache: false,
				  data: {<?php $elements = AlienContact::elements(); foreach($elements as $element) { echo $element['id']; ?>: $("#contactForm_<?php echo $element['id']; ?>").val()<?php if(next($elements)) {?>, <?php } } ?>},
				  type: "POST",
				  success: function(html){
					
					results.removeClass('loading').animate({ 
					        width: "300px",
							height: "300px",
							marginTop: "-150px",
					        marginLeft: "-150px"
					      }, 1500, function () {
								$(this).html(html);
								var closeLink = jQuery('<a href="#close" class="box closeLink">Close</a>').prependTo(results);
								overlay.add(closeLink).click( function() {
									overlay.fadeOut("slow", function() {
										overlay.remove();
									});
									results.remove();
									return false;
									});
								$(this).addClass('finished');
							} );
				  }
				});
				return false;
			}
		});
	
		// handle changes on the fly
		$("input, select, textarea", this).each( function() {
			if ($(this).attr("class")) {
				$(this).keyup( function() {
					validateField(this);
				} );
    			}
		});
	});
});