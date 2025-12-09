jQuery(document).ready(function($) {
    // Upload de imagem
    $('body').on('click', '.custom-avatar-upload', function(e) {
        e.preventDefault();
        
        var targetInput = $(this).siblings('.regular-text');
        var previewContainer = $(this).closest('td').find('.avatar-preview');

        var custom_uploader = wp.media({
            title: 'Escolher Imagem de Perfil',
            button: {
                text: 'Usar esta imagem'
            },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            targetInput.val(attachment.url);
            
            // Atualiza ou cria o preview da imagem
            if (previewContainer.find('img').length) {
                previewContainer.find('img').attr('src', attachment.url);
            } else {
                previewContainer.html('<img src="' + attachment.url + '" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">');
            }
        }).open();
    });
    
    // Remover imagem
    $('body').on('click', '.custom-avatar-remove', function(e) {
        e.preventDefault();
        
        var targetInput = $(this).siblings('.regular-text');
        var previewContainer = $(this).closest('td').find('.avatar-preview');
        targetInput.val('');
        previewContainer.html('<div style="width: 100px; height: 100px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc;"><span style="color: #999; font-size: 12px;">Sem foto</span></div>');
    });
});