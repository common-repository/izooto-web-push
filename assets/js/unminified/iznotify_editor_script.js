jQuery( document ).ready( function() {
	var iz_title_limit = 60;
	var iz_content_limit = 150;
	var iz_title_custom = false;
	var iz_content_custom = false;
	var iz_title_autosaved = "";
	var iz_content_autosaved = "";
	wp.data.subscribe( function() {
		if( ( jQuery( '#iz_notify_title' ).prop( 'readonly' ) != true ) && ( jQuery( '#iz_notify_content' ).prop( 'readonly' ) != true ) ) {
			
			if( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
				var wp_editor_title = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );
				var wp_editor_content = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'content' );
			}
			else if ( jQuery( "#iz_site_title" ).prop( "checked" ) == true ) {
				var wp_editor_content = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );
				var wp_editor_title = '';
			}
			iz_editor_content_capture( wp_editor_title, wp_editor_content );
		}

		/* Display notices */
		if( wp.data.select( 'core/editor' ).isSavingPost() == true ) {
			setTimeout( function() {
				if( ( Cookies.get('izmessage') != undefined )  && ( Cookies.get('izmessage') != '' ) ) {
					var iz_auth_cookie = Cookies.get('izmessage');
					if( iz_auth_cookie == 3 ) {
						wp.data.dispatch("core/notices").createNotice("error", "iZooto: Unable to process notification push request. Contact support@izooto.com.", {
					      isDismissible: true
					    });
					}
					else if( iz_auth_cookie == 1 ) {
						wp.data.dispatch("core/notices").createNotice("info", "iZooto: Notification has been pushed successfully.", {
					      isDismissible: true
					    });
					}
					else if( iz_auth_cookie == 4 ) {
						wp.data.dispatch("core/notices").createNotice("error", "iZooto: Daily Campaign Push Limit exceeded for the day.", {
					      isDismissible: true
					    });
					}
					else if( iz_auth_cookie == 5 ) {
						wp.data.dispatch("core/notices").createNotice("error", "iZooto: Both 'Title' and 'Message' are mandatory fields.", {
					      isDismissible: true
					    });
					}
					else if( iz_auth_cookie == 2 ) {
						wp.data.dispatch("core/notices").createNotice("error", "iZooto: There is an issue with the plugin. Please contact iZooto support for help.", {
					      isDismissible: true
					    });
					}
				    Cookies.remove('izmessage');
				}
			}, 3000);
		}
		
	} );

	function iz_editor_content_capture( wp_editor_title, wp_editor_content ) {
		/* Get post title */
		if( ( wp_editor_title != '' ) && ( wp_editor_title != undefined ) ) {
			var wp_editor_title_content = wp_editor_title.replaceAll("&amp;","&");
			wp_editor_title_content = wp_editor_title_content.replaceAll("amp;","");
			wp_editor_title_content = wp_editor_title_content.replace(/^\n/, "");
			if(wp_editor_title_content != iz_title_autosaved) {
				iz_title_custom = false;
			}
			if(iz_title_custom == false && jQuery('#iz_notify_title').val() !== wp_editor_title_content ) {
				iz_title_autosaved = wp_editor_title_content;
				wp_editor_title_content = wp_editor_title_content.substr( 0, iz_title_limit );
				jQuery( '#iz_notify_title' ).val( wp_editor_title_content );
				var iz_title = jQuery( '#iz_notify_title' ).val();
				jQuery( "#iz_title_limit" ).html( iz_title.length+'/'+iz_title_limit);
			}
		}

		/* Get post content */
		if( ( wp_editor_content != '' ) && ( wp_editor_content != undefined ) ) {
			var regex = /(<([^>]+)>)/gi;
			var editor_text_content = wp_editor_content.replaceAll(regex, "");
			editor_text_content = editor_text_content.replaceAll("&amp;","&");
			editor_text_content = editor_text_content.replaceAll("amp;","");
			editor_text_content = editor_text_content.replace(/^\n/, "");
			if(editor_text_content != iz_content_autosaved) {
				iz_content_custom = false;
			}
			if( iz_content_custom == false && jQuery('#iz_notify_content').val() !== editor_text_content ) {
				iz_content_autosaved = editor_text_content;
				editor_text_content = editor_text_content.substr( 0, iz_content_limit );
				jQuery('#iz_notify_content').val( editor_text_content );
				var iz_content = jQuery( '#iz_notify_content' ).val();
				jQuery( "#iz_content_limit" ).html( iz_content.length+'/'+iz_content_limit);
			}
		}

		return;
	}

	/* On site title checkbox click */
	jQuery( "#iz_site_title" ).click( function() {
		if( ( jQuery( '#iz_notify_title' ).prop( 'readonly' ) != true ) && ( jQuery( '#iz_notify_content' ).prop( 'readonly' ) != true ) ) {
			iz_site_title_chk_action();
		}
	} );

	/* Restrict keypress for title */
	jQuery( "#iz_notify_title" ).on( 'keypress', function(e) {
		if( ( jQuery( this ).val() != '' ) && ( jQuery( this ).val() != undefined ) ) {
			iz_title_custom = true;
			if( jQuery( this ).val().length >= iz_title_limit ) {
				e.preventDefault();
			}
		}
	} );

	/* Restrict keypress for message */
	jQuery( "#iz_notify_content" ).on( 'keypress', function(e) {
		if( ( jQuery( this ).val() != '' ) && ( jQuery( this ).val() != undefined ) ) {
			iz_content_custom = true;
			if( jQuery( this ).val().length >= iz_content_limit ) {
				e.preventDefault();
			}
		}
	} );

	/* Save meta box content */
	jQuery( "#save_custom_content" ).click( function() {
		if( jQuery( this ).hasClass( 'iz-notify-edit' ) ) {
			jQuery( '#iz_notify_title' ).prop( 'readonly', false );
			jQuery( '#iz_notify_content' ).prop( 'readonly', false );
			jQuery( '#save_custom_content_div' ).hide();
			jQuery( this ).html( 'Save' );
			jQuery( this ).removeClass( 'iz-notify-edit' );
			iz_site_title_chk_action();
		}
		else {
			jQuery( '#iz_notify_title' ).prop( 'readonly', true );
			jQuery( '#iz_notify_content' ).prop( 'readonly', true );
			jQuery( this ).addClass( 'iz-notify-edit' );
			jQuery( this ).html( 'Edit' );
		}
	} );

	function iz_site_title_chk_action() {
		var wp_editor_title = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );
		if( jQuery( "#iz_site_title" ).prop( "checked" ) == true ) {
			if( site_name_param.site_name == '' ) {
				jQuery( '#iz_notify_title' ).val('')
				jQuery( '#iz_site_title_row' ).show();
				var iz_title_length = 0;
				jQuery( "#iz_title_limit" ).html( iz_title_length+'/'+iz_title_limit);
			}
			iz_editor_content_capture( site_name_param.site_name, wp_editor_title );
			jQuery( '.tooltip_svg_icon' ).show();
		}
		else if ( jQuery( "#iz_site_title" ).prop( "checked" ) == false ) {
			var wp_editor_content = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'content' );
			iz_editor_content_capture( wp_editor_title, wp_editor_content );
			jQuery( '#iz_site_title_row' ).hide();
			jQuery( '.tooltip_svg_icon' ).hide();
		}
		return;
	}

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
		jQuery( '#save_custom_content_div' ).show();
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
		jQuery( '#save_custom_content_div' ).show();
	} );

} );