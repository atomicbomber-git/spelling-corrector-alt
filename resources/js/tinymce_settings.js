import file_picker_callback from './file_picker_callback'

let tinymce_settings = {
    selector: '#content',
    body_class: 'tinymce-editor',
    plugins: 'lists,image,imagetools,link',
    image_caption: true,
    file_picker_callback: file_picker_callback,
    image_class_list: [
        {title: 'Responsive Image', value: 'img-fluid'},
    ],
    toolbar: [
        'undo redo | link | styleselect | bold italic | numlist bullist | alignleft aligncenter alignright | image'
    ],
    height: 640,
}

export default tinymce_settings
