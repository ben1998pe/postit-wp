/**
 * JavaScript para PostIt WP
 * 
 * @package PostIt_WP
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Variables globales
    var PostItWP = {
        version: '1.0.0',
        ajaxUrl: '',
        nonce: '',
        container: null,
        notes: []
    };
    
    /**
     * Inicializar el plugin
     */
    function init() {
        // Obtener variables de WordPress
        if (typeof postitWpAjax !== 'undefined') {
            PostItWP.ajaxUrl = postitWpAjax.ajaxUrl;
            PostItWP.nonce = postitWpAjax.nonce;
        }
        
        // Inicializar contenedor
        PostItWP.container = $('#postit-wp-container');
        
        if (PostItWP.container.length) {
            initNotes();
            bindEvents();
        }
    }
    
    /**
     * Inicializar notas existentes
     */
    function initNotes() {
        PostItWP.container.find('.postit-note').each(function() {
            var noteId = $(this).data('note-id');
            if (noteId) {
                PostItWP.notes.push(noteId);
            }
        });
        
        // Aplicar clases para contenido largo
        PostItWP.container.find('.postit-note-content').each(function() {
            var content = $(this);
            if (content.height() > 100) {
                content.closest('.postit-note').addClass('long-content');
            }
        });
    }
    
    /**
     * Vincular eventos
     */
    function bindEvents() {
        // Eventos para notas individuales
        PostItWP.container.on('click', '.postit-note', function(e) {
            // Prevenir propagación para evitar conflictos
            e.stopPropagation();
        });
        
        // Eventos para el contenedor
        PostItWP.container.on('mouseenter', '.postit-note', function() {
            $(this).addClass('hover');
        }).on('mouseleave', '.postit-note', function() {
            $(this).removeClass('hover');
        });
        
        // Eventos para el contexto de la nota
        PostItWP.container.on('mouseenter', '.postit-context', function() {
            var context = $(this).text();
            $(this).attr('title', context);
        });
    }
    
    /**
     * Agregar nueva nota (preparado para el futuro)
     */
    function addNote(noteData) {
        if (!PostItWP.ajaxUrl || !PostItWP.nonce) {
            console.error('PostIt WP: Variables AJAX no disponibles');
            return false;
        }
        
        $.ajax({
            url: PostItWP.ajaxUrl,
            type: 'POST',
            data: {
                action: 'postit_wp_add_note',
                nonce: PostItWP.nonce,
                note_data: noteData
            },
            success: function(response) {
                if (response.success) {
                    // Agregar nota al DOM
                    var noteHtml = renderNote(response.data.note);
                    PostItWP.container.append(noteHtml);
                    
                    // Actualizar lista de notas
                    PostItWP.notes.push(response.data.note.id);
                    
                    // Animación de entrada
                    var newNote = PostItWP.container.find('.postit-note').last();
                    newNote.addClass('new-note');
                    
                    setTimeout(function() {
                        newNote.removeClass('new-note');
                    }, 2000);
                } else {
                    console.error('PostIt WP: Error al agregar nota', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('PostIt WP: Error AJAX', error);
            }
        });
    }
    
    /**
     * Editar nota (preparado para el futuro)
     */
    function editNote(noteId, noteData) {
        if (!PostItWP.ajaxUrl || !PostItWP.nonce) {
            console.error('PostIt WP: Variables AJAX no disponibles');
            return false;
        }
        
        $.ajax({
            url: PostItWP.ajaxUrl,
            type: 'POST',
            data: {
                action: 'postit_wp_edit_note',
                nonce: PostItWP.nonce,
                note_id: noteId,
                note_data: noteData
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar nota en el DOM
                    var noteElement = PostItWP.container.find('[data-note-id="' + noteId + '"]');
                    if (noteElement.length) {
                        noteElement.replaceWith(renderNote(response.data.note));
                    }
                } else {
                    console.error('PostIt WP: Error al editar nota', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('PostIt WP: Error AJAX', error);
            }
        });
    }
    
    /**
     * Eliminar nota (preparado para el futuro)
     */
    function deleteNote(noteId) {
        if (!PostItWP.ajaxUrl || !PostItWP.nonce) {
            console.error('PostIt WP: Variables AJAX no disponibles');
            return false;
        }
        
        if (!confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
            return false;
        }
        
        $.ajax({
            url: PostItWP.ajaxUrl,
            type: 'POST',
            data: {
                action: 'postit_wp_delete_note',
                nonce: PostItWP.nonce,
                note_id: noteId
            },
            success: function(response) {
                if (response.success) {
                    // Eliminar nota del DOM
                    var noteElement = PostItWP.container.find('[data-note-id="' + noteId + '"]');
                    if (noteElement.length) {
                        noteElement.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                    
                    // Actualizar lista de notas
                    var index = PostItWP.notes.indexOf(noteId);
                    if (index > -1) {
                        PostItWP.notes.splice(index, 1);
                    }
                } else {
                    console.error('PostIt WP: Error al eliminar nota', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('PostIt WP: Error AJAX', error);
            }
        });
    }
    
    /**
     * Renderizar nota HTML (preparado para el futuro)
     */
    function renderNote(note) {
        // Esta función se implementará cuando se agregue la funcionalidad de crear notas
        return '';
    }
    
    /**
     * Mostrar notificación
     */
    function showNotification(message, type) {
        type = type || 'info';
        
        var notification = $('<div class="postit-notification postit-' + type + '">' + message + '</div>');
        
        // Agregar al body
        $('body').append(notification);
        
        // Mostrar con animación
        notification.fadeIn(300);
        
        // Ocultar después de 3 segundos
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    /**
     * Obtener contexto de página actual
     */
    function getCurrentPageContext() {
        return window.location.pathname;
    }
    
    /**
     * Verificar si el usuario es administrador
     */
    function isAdmin() {
        return typeof postitWpAjax !== 'undefined' && postitWpAjax.isAdmin;
    }
    
    // Exponer funciones públicas
    PostItWP.addNote = addNote;
    PostItWP.editNote = editNote;
    PostItWP.deleteNote = deleteNote;
    PostItWP.showNotification = showNotification;
    PostItWP.getCurrentPageContext = getCurrentPageContext;
    PostItWP.isAdmin = isAdmin;
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        init();
    });
    
    // Exponer objeto global
    window.PostItWP = PostItWP;
    
})(jQuery); 