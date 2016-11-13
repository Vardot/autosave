/**
 * @file
 * Behaviors for Autosave.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  var showingRestoreCommand;

  Drupal.behaviors.autosave = {};
  Drupal.behaviors.autosave = {
    attach: function (context) {

      if ($('#autosave-status').size() === 0) {
        // Add a div for us to put messages in.
        $('body').append('<div id="autosave-status"><span id="status"></span></div>');
      }

      $('#' + drupalSettings.autosave.formid).not('.autosave-processed').addClass('autosave-processed').autosave({
        // Time in ms.
        interval: drupalSettings.autosave.period * 1000,
        url: drupalSettings.autosave.url,
        setup: function (e, o) {
          var ignoreLink, restoreLink, callbackPath;
          
          // If there is a saved form for this user, let him know so he can reload it
          // if desired.
          if (typeof(drupalSettings.autosave.savedTimestamp) !== "undefined"
              & drupalSettings.autosave.savedTimestamp !== 0) {
                     
            showingRestoreCommand = true;

            ignoreLink = $('<a>').attr('href', '#').attr('title', Drupal.t('Ignore/Delete saved form')).html(Drupal.t('Ignore')).click(function (e) {
              Drupal.behaviors.autosave.hideMessage();
              return false;
            });

            callbackPath = drupalSettings.path.baseUrl + 'autosave/restore/' + drupalSettings.autosave.formid + '/' + drupalSettings.autosave.savedTimestamp + '/' + drupalSettings.autosave.path;
            restoreLink = $('<a>').attr('href', callbackPath).addClass('use-ajax').attr('title', Drupal.t('Restore saved form')).html(Drupal.t('Restore')).click(function (e) {
              Drupal.behaviors.autosave.hideMessage();
            });

            Drupal.behaviors.autosave.displayMessage(Drupal.t('This form was autosaved on ' + drupalSettings.autosave.savedDate), {
              // Show the message for 30 seconds, or hide it when the user starts
              // editing the form.
              timeout: 30000,
              extra: $('<span id="operations">').append(ignoreLink).append(restoreLink)
            });
          }

          // Wire up TinyMCE to autosave.
          if (typeof(tinymce) !== 'undefined') {
            setInterval(function() {
              // Save text data from the tinymce area back to the original form element.
              // Once it's in the original form element, autosave will notice it
              // and do what it needs to do.
              // Note: There seems to be a bug where after a form is restored,
              // everything works fine but tinyMCE keeps reporting an undefined
              // error internally.  As its code is compressed I have absolutely no
              // way to debug this.  If you can figure it out, please file a patch.
              var triggers = Drupal.settings.wysiwyg.triggers;
              var id;
              var field;
              for (id in triggers) {
                field = triggers[id].field;
                $('#' + field).val(tinymce.get(field).getContent());
              }
            }, drupalSettings.autosave.period * 1000);
          }

          // Wire up CKEditor to autosave.
          // @todo This does not yet support CKEditor 4.
          if (typeof(CKEDITOR) !== 'undefined') {
            CKEDITOR.on('instanceReady', function (eventInfo) {
              var editor = eventInfo.editor;
              editor.on('saveSnapshot', function () {
                editor.updateElement();
              });
            });
          }

        },
        save: function (e, o) {
          if (!drupalSettings.autosave.hidden) {
            Drupal.behaviors.autosave.displayMessage(Drupal.t('Form autosaved.'));
          }
        },
        dirty: function (e, o) {
          if (showingRestoreCommand) {
            Drupal.behaviors.autosave.hideMessage();
          }
        }
      });
    }
  };

  Drupal.behaviors.autosave.displayMessage = function (message, settings) {
    settings = settings || {};
    settings.timeout = settings.timeout || 3000;
    settings.extra = settings.extra || '';
    //settings = $.extend({}, {timeout: 3000, extra: ''}, settings);
    var status = $('#autosave-status');
    status.empty().append('<span id="status">' + message + '</span>');
    if (settings.extra) {
      status.append(settings.extra);
    }
    
    // Display Message.
    $('#autosave-status').slideDown();
    
    // Wait for settings.timeout then hide the meessage.
    setTimeout(function () { Drupal.behaviors.autosave.hideMessage() }, settings.timeout);
  };

  Drupal.behaviors.autosave.hideMessage = function() {
    $('#autosave-status').fadeOut('slow');
  };

})(jQuery, Drupal, drupalSettings);
