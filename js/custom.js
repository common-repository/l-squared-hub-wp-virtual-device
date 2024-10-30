(function( $ ) {
	"use strict";

	var vars = {};
	$(document).ready(function(){
		$('.lsnCarousel').each(function(key, value){
			vars['t'+key] = 0;
			vars['duration'+key] = 0;

			var currentId = $(this).attr('id');
			vars['currentId'+key] = currentId;
			vars['start'+key] = $('#'+vars['currentId'+key]).find('.active').attr('data-interval');

			vars['lastPlayedItemId'+key] = $('#'+vars['currentId'+key]).find('.active').attr('id');
			vars['lastPlayedId'+key]  = 'inner'+vars['lastPlayedItemId'+key];
			vars['lastPlayedElement'+key] = $('#'+vars['lastPlayedId'+key]);

			if(vars['lastPlayedElement'+key].length){
				var tagName = vars['lastPlayedElement'+key].prop("tagName").toLowerCase();
				if(tagName == 'iframe'){
					toggleVideo(vars['lastPlayedId'+key], 'play');
				}
				else if(tagName == 'audio' || tagName == 'video'){
					vars['lastPlayedElement'+key][0].play();
				}
			}

			vars['obj'+key] = { 'lastPlayedItemId' : vars['lastPlayedItemId'+key], 'lastPlayedId' : vars['lastPlayedId'+key], 'lastPlayedElement' : vars['lastPlayedElement'+key] };

			vars['t'+key] = setTimeout(function () {
				$('#'+vars['currentId'+key]).carousel('next');
			}, vars['start'+key]);

			$('#'+vars['currentId'+key]).on('slid.bs.carousel', function (){

				if(vars['obj'+key].lastPlayedElement.length){
					var tagName = vars['lastPlayedElement'+key].prop("tagName").toLowerCase();
					if(tagName == 'iframe'){
						toggleVideo(vars['obj'+key].lastPlayedId, 'pause');
					}
					else if(tagName == 'audio' || tagName == 'video'){
						vars['obj'+key].lastPlayedElement[0].pause();
					}
				}

				clearTimeout(vars['t'+key]);

				vars['duration'+key] = $('#'+vars['currentId'+key]).find('.active').attr('data-interval');

				$('#'+vars['currentId'+key]).carousel('pause');

				vars['lastPlayedItemId'+key] = $('#'+vars['currentId'+key]).find('.active').attr('id');
				vars['lastPlayedId'+key] = 'inner'+vars['lastPlayedItemId'+key];
				vars['lastPlayedElement'+key] = $('#'+vars['lastPlayedId'+key]);

				vars['obj'+key].lastPlayedItemId = vars['lastPlayedItemId'+key];
				vars['obj'+key].lastPlayedId = vars['lastPlayedId'+key];
				vars['obj'+key].lastPlayedElement = vars['lastPlayedElement'+key];

				if(vars['obj'+key].lastPlayedElement.length){
					var tagName = vars['lastPlayedElement'+key].prop("tagName").toLowerCase();
					if(tagName == 'iframe'){
						toggleVideo(vars['obj'+key].lastPlayedId, 'play');
					}
					else if(tagName == 'audio' || tagName == 'video'){
						vars['obj'+key].lastPlayedElement[0].play();
					}
				}

				vars['t'+key] = setTimeout(function(){
					$('#'+vars['currentId'+key]).carousel('next');
				}, vars['duration'+key]);
			});

			$('#'+vars['currentId'+key]+' .carousel-control.right').on('click', function () {
				clearTimeout(vars['t'+key]);
			});

			$('#'+vars['currentId'+key]+' .carousel-control.left').on('click', function () {
				clearTimeout(vars['t'+key]);
			});
		});
	});

	function toggleVideo(idElem, state) {
		// http://jsfiddle.net/3J2wT/1096/
		if(state == 'play'){
			$('#'+idElem)[0].contentWindow.postMessage('{"event":"command","func":"' + 'playVideo' + '","args":""}', '*');
		}
		else{
			$('#'+idElem)[0].contentWindow.postMessage('{"event":"command","func":"' + 'pauseVideo' + '","args":""}', '*');
		}
	}

})(jQuery);