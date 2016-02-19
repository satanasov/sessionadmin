(function($) {  // Avoid conflicts with other libraries
	"use strict"
	$(function () {
		var opt = { excludeUserAgent: true };
		new Fingerprint2(opt).get(function(result, components){
		  console.log(result); //a hash, representing your device fingerprint
		  console.log(components); // an array of FP components
		  alert("{SESSION_USE_FINGERPRINT}".slice(0,-1) + result);
		  $.post( "{SESSION_USE_FINGERPRINT}".slice(0,-1) + result );
		});
	});
})(jQuery); // Avoid conflicts with other libraries