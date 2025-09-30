jQuery(document).ready(function($) {
    // Handle image upload for group thumbnail
    var customUploader;
    
    $('#group_thumbnail_button').click(function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (customUploader) {
            customUploader.open();
            return;
        }
        
        // Create the media frame
        customUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Group Thumbnail',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        
        // When an image is selected, run a callback
        customUploader.on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            $('#group_thumbnail').val(attachment.id);
            $('#group_thumbnail_preview').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width:100px;" />');
        });
        
        // Open the uploader dialog
        customUploader.open();
    });
    
    // Handle remove image functionality
    $(document).on('click', '#remove_group_thumbnail', function(e) {
        e.preventDefault();
        $('#group_thumbnail').val('');
        $('#group_thumbnail_preview').html('');
    });
});