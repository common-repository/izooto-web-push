jQuery( document ).ready( function() {
	var iz_title_limit = 60;
	var iz_content_limit = 150;
	jQuery( document ).on( 'tinymce-editor-init', function( event, editor ) {
		if( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
			editor_get_content( editor.getContent() );
		}
	    editor.on( 'keyup', function (e) {
	    	if( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
	    		editor_get_content( editor.getContent() );
	    	}
	    } );
	} );

	/* Get post mce editor content */
	function editor_get_content( wp_editor_content ) {
    	if( ( wp_editor_content != '' ) && ( wp_editor_content != undefined ) ) {
    		var editor_text_content = jQuery( wp_editor_content ).text();
    		editor_text_content = editor_text_content.replaceAll("&amp;","&");
    		editor_text_content = editor_text_content.replaceAll("amp;","");
	      	if( jQuery( '#iz_notify_content' ).val() !== editor_text_content ) {
				editor_text_content = editor_text_content.substr( 0, iz_content_limit );
				jQuery( '#iz_notify_content' ).val( editor_text_content );
				var iz_content = jQuery( '#iz_notify_content' ).val();
				jQuery( "#iz_content_limit" ).html( iz_content.length+'/'+iz_content_limit);
			}
    	}
    	return;
	}

	if( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
		text_editor_get_content();
		var post_title = jQuery( "input#title" ).val();
		post_title_get( post_title );
	}
	else if( jQuery( "#iz_site_title" ).prop( "checked" ) == true ) {
		title_editor_swap( jQuery( "input#title" ).val() );
	}

	jQuery( "textarea#content" ).on( 'keyup', function() {
		if( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
			text_editor_get_content();
		}
	} );

	/* Get post text editor content */
	function text_editor_get_content() {
		if( jQuery( "textarea#content" ).is( ":hidden" ) == false ) {
			var wp_text_editor_content = jQuery( "textarea#content" ).val();
			title_editor_swap( wp_text_editor_content );
		}
		return;
	}

	jQuery( "input#title" ).on( 'keyup', function() {
		if( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
			var post_title = jQuery( "input#title" ).val();
			post_title_get( post_title );
		}
		else if( jQuery( "#iz_site_title" ).prop( "checked" ) == true ) {
			title_editor_swap( jQuery( "input#title" ).val() );
		}
	} );

	/* Displaying data in meta field in case of text editor and site title check */
	function title_editor_swap( wp_title_editor) {
		var wp_editor_notify_content = wp_title_editor;
		if( ( wp_editor_notify_content != '' ) && ( wp_editor_notify_content != undefined ) ) {
			var regex = /(<([^>]+)>)/gi;
			wp_editor_notify_content = wp_editor_notify_content.replaceAll(regex, "");
			wp_editor_notify_content = wp_editor_notify_content.replaceAll("&amp;","&");
			wp_editor_notify_content = wp_editor_notify_content.replaceAll("amp;","");
			if( jQuery( '#iz_notify_content' ).val() !== wp_editor_notify_content ) {
				wp_editor_notify_content = wp_editor_notify_content.substr( 0, iz_content_limit );
				jQuery( '#iz_notify_content' ).val( wp_editor_notify_content );
				var iz_content = jQuery( '#iz_notify_content' ).val();
				jQuery( "#iz_content_limit" ).html( iz_content.length+'/'+iz_content_limit);
			}
		}
		return;
	}

	/* Get post title */
	function post_title_get( post_title ) {
		var wp_editor_title = post_title;
		if( ( wp_editor_title != '' ) && ( wp_editor_title != undefined ) ) {
			wp_editor_title = wp_editor_title.replaceAll("&amp;","&");
			wp_editor_title = wp_editor_title.replaceAll("amp;","");
			if( jQuery( '#iz_notify_title' ).val() !== wp_editor_title ) {
				wp_editor_title = wp_editor_title.substr( 0, iz_title_limit );
				jQuery( '#iz_notify_title' ).val( wp_editor_title );
				var iz_title = jQuery( '#iz_notify_title' ).val();
				jQuery( "#iz_title_limit" ).html( iz_title.length+'/'+iz_title_limit);
			}
		}
		return;
	}

	/* On site title checkbox click */
	jQuery( "#iz_site_title" ).click( function() {
		var wp_editor_title = jQuery( "input#title" ).val();
		if( jQuery( "#iz_site_title" ).prop( "checked" ) == true ) {
			if( site_name_param.site_name == '' ) {
				jQuery( '#iz_notify_title' ).val('')
				jQuery( '#iz_site_title_row' ).show();
				var iz_title_length = 0;
				jQuery( "#iz_title_limit" ).html( iz_title_length+'/'+iz_title_limit);
			}
			post_title_get( site_name_param.site_name );
			title_editor_swap( wp_editor_title );
			jQuery( '.tooltip_svg_icon' ).show();
		}
		else if ( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
			if( jQuery( "textarea#content" ).is( ":hidden" ) == false ) {
				var wp_text_editor_content = jQuery( "textarea#content" ).val();
				title_editor_swap( wp_text_editor_content );
			}
			else if ( jQuery( "textarea#content" ).is( ":hidden" ) == true ) {
				editor_get_content( tinyMCE.editors.content.getContent() );
			}
			post_title_get( wp_editor_title );
			jQuery( '#iz_site_title_row' ).hide();
			jQuery( '.tooltip_svg_icon' ).hide();
		}
	} );

	/* Restrict keypress for title */
	jQuery( "#iz_notify_title" ).on( 'keypress', function(e) {
		if( ( jQuery( this ).val() != '' ) && ( jQuery( this ).val() != undefined ) ) {
			if( jQuery( this ).val().length >= iz_title_limit ) {
				e.preventDefault();
			}
		}
	} );

	/* Restrict keypress for message */
	jQuery( "#iz_notify_content" ).on( 'keypress', function(e) {
		if( ( jQuery( this ).val() != '' ) && ( jQuery( this ).val() != undefined ) ) {
			if( jQuery( this ).val().length >= iz_content_limit ) {
				e.preventDefault();
			}
		}
	} );

	/* Meta box title content counter */
	jQuery( "#iz_notify_title" ).on( 'keyup', function() {
		if( ( jQuery( this ).val() != '' ) && ( jQuery( this ).val() != undefined ) ) {
			var iz_title_length = jQuery( this ).val().length;
			if( iz_title_length > iz_title_limit ) {
				var iz_meta_title = jQuery( this ).val();
				jQuery( this ).val( iz_meta_title.substr( 0, iz_title_limit) );
				var iz_title_length = jQuery( this ).val().length;
			}
		}
		else {
			var iz_title_length = 0;
		}
		jQuery( "#iz_title_limit" ).html( iz_title_length+'/'+iz_title_limit);
		jQuery( '#iz_site_title_row' ).hide();
	} );

	/* Meta box message content counter */
	jQuery( "#iz_notify_content" ).on( 'keyup', function() {
		if( ( jQuery( this ).val() != '' ) && ( jQuery( this ).val() != undefined ) ) {
			var iz_content_length = jQuery( this ).val().length;
			if( iz_content_length > iz_content_limit ) {
				var iz_meta_content = jQuery( this ).val();
				jQuery( this ).val( iz_meta_content.substr( 0, iz_content_limit) );
				var iz_content_length = jQuery( this ).val().length;
			}
		}
		else {
			var iz_content_length = 0;
		}
		jQuery( "#iz_content_limit" ).html( iz_content_length+'/'+iz_content_limit);
	} );

} );