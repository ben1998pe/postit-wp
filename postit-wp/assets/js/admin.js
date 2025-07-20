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
        
        // Eventos para el botón flotante
        $(document).on('click', '#postit-wp-add-button', function() {
            openModal();
        });
        
        // Eventos para el modal
        $(document).on('click', '.postit-wp-modal-close, .postit-wp-modal-cancel', function() {
            closeModal();
        });
        
        // Eventos para botones de editar
        $(document).on('click', '.postit-edit-btn', function(e) {
            e.stopPropagation();
            var noteId = $(this).data('note-id');
            editNote(noteId);
        });
        
        // Eventos para botones de eliminar
        $(document).on('click', '.postit-delete-btn', function(e) {
            e.stopPropagation();
            var noteId = $(this).data('note-id');
            deleteNote(noteId);
        });
        
        // Eventos para el formulario
        $(document).on('submit', '#postit-wp-note-form', function(e) {
            e.preventDefault();
            saveNote();
        });
        
        // Cerrar modal al hacer clic fuera
        $(document).on('click', '#postit-wp-modal', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
    
    /**
     * Agregar nueva nota
     */
    function addNote(noteData) {
        if (!PostItWP.ajaxUrl || !PostItWP.nonce) {
            console.error('PostIt WP: Variables AJAX no disponibles');
            showNotification('Error: Variables AJAX no disponibles', 'error');
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
                    
                    showNotification('Nota creada exitosamente', 'success');
                } else {
                    console.error('PostIt WP: Error al agregar nota', response.data);
                    showNotification('Error al crear la nota', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('PostIt WP: Error AJAX', error);
                showNotification('Error de conexión', 'error');
            }
        });
    }
    
    /**
     * Actualizar nota existente
     */
    function updateNote(noteId, noteData) {
        if (!PostItWP.ajaxUrl || !PostItWP.nonce) {
            console.error('PostIt WP: Variables AJAX no disponibles');
            showNotification('Error: Variables AJAX no disponibles', 'error');
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
                    showNotification('Nota actualizada exitosamente', 'success');
                } else {
                    console.error('PostIt WP: Error al editar nota', response.data);
                    showNotification('Error al actualizar la nota', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('PostIt WP: Error AJAX', error);
                showNotification('Error de conexión', 'error');
            }
        });
    }
    
    /**
     * Eliminar nota
     */
    function deleteNote(noteId) {
        if (!PostItWP.ajaxUrl || !PostItWP.nonce) {
            console.error('PostIt WP: Variables AJAX no disponibles');
            showNotification('Error: Variables AJAX no disponibles', 'error');
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
                    
                    showNotification('Nota eliminada exitosamente', 'success');
                } else {
                    console.error('PostIt WP: Error al eliminar nota', response.data);
                    showNotification('Error al eliminar la nota', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('PostIt WP: Error AJAX', error);
                showNotification('Error de conexión', 'error');
            }
        });
    }
    
    /**
     * Abrir modal para crear/editar nota
     */
    function openModal(noteData) {
        var modal = $('#postit-wp-modal');
        var form = $('#postit-wp-note-form');
        var title = $('#postit-wp-modal-title');
        
        // Limpiar formulario
        form[0].reset();
        $('#postit-wp-note-id').val('');
        
        if (noteData) {
            // Modo edición
            title.text('Editar Nota');
            $('#postit-wp-note-id').val(noteData.id);
            $('#postit-wp-note-text').val(noteData.note_text);
            $('#postit-wp-note-context').val(noteData.page_context);
            $('#postit-wp-note-context-display').val(noteData.page_context);
        } else {
            // Modo creación
            title.text('Nueva Nota');
            $('#postit-wp-note-context').val(PostItWP.getCurrentPageContext());
            $('#postit-wp-note-context-display').val(PostItWP.getCurrentPageContext());
        }
        
        modal.show();
        $('#postit-wp-note-text').focus();
    }
    
    /**
     * Cerrar modal
     */
    function closeModal() {
        $('#postit-wp-modal').hide();
    }
    
    /**
     * Guardar nota (crear o actualizar)
     */
    function saveNote() {
        var form = $('#postit-wp-note-form');
        var noteId = $('#postit-wp-note-id').val();
        var noteText = $('#postit-wp-note-text').val();
        var pageContext = $('#postit-wp-note-context').val();
        
        if (!noteText.trim()) {
            showNotification('Por favor, escribe el contenido de la nota.', 'error');
            return;
        }
        
        var noteData = {
            note_text: noteText,
            page_context: pageContext
        };
        
        if (noteId) {
            // Actualizar nota existente
            PostItWP.updateNote(noteId, noteData);
        } else {
            // Crear nueva nota
            PostItWP.addNote(noteData);
        }
        
        closeModal();
    }
    
    /**
     * Editar nota
     */
    function editNote(noteId) {
        // Buscar la nota en el DOM para obtener los datos
        var noteElement = $('.postit-note[data-note-id="' + noteId + '"]');
        if (noteElement.length) {
            var noteData = {
                id: noteId,
                note_text: noteElement.find('.postit-note-content').text().trim(),
                page_context: noteElement.find('.postit-context').text().trim()
            };
            openModal(noteData);
        }
    }
    
    /**
     * Eliminar nota
     */
    function deleteNote(noteId) {
        if (confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
            PostItWP.deleteNote(noteId);
        }
    }
    
    /**
     * Renderizar nota HTML
     */
    function renderNote(note) {
        var userInfo = note.author_name || 'Usuario desconocido';
        var formattedDate = new Date(note.created_at).toLocaleString();
        
        return '<div class="postit-note" data-note-id="' + note.id + '">' +
            '<div class="postit-note-header">' +
                '<span class="postit-author">' + userInfo + '</span>' +
                '<span class="postit-date">' + formattedDate + '</span>' +
                '<div class="postit-note-actions">' +
                    '<button class="postit-edit-btn" title="Editar Nota" data-note-id="' + note.id + '">' +
                        '<span class="dashicons dashicons-edit"></span>' +
                    '</button>' +
                    '<button class="postit-delete-btn" title="Eliminar Nota" data-note-id="' + note.id + '">' +
                        '<span class="dashicons dashicons-trash"></span>' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<div class="postit-note-content">' + note.note_text + '</div>' +
            '<div class="postit-note-footer">' +
                '<span class="postit-context">' + note.page_context + '</span>' +
            '</div>' +
        '</div>';
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
    PostItWP.updateNote = updateNote;
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