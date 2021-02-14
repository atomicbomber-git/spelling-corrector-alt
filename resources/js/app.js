/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

require('alpinejs')
const Swal = require('sweetalert2')
window.Swal = Swal
window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});

window.confirmDialog = (attributes) => {
    return Swal.fire({
        title: `Konfirmasi`,
        titleText: `Konfirmasi Tindakan`,
        text: `Apakah Anda yakin ingin melakukan tindakan ini?`,
        icon: `warning`,
        showCancelButton: true,
        confirmButtonText: `Ya`,
        cancelButtonText: `Tidak`,
        ...attributes,
    })
}

// Load TinyMCE WYSIWYG Editor
require('tinymce');

// TinyMCE File Picker Callback
window.file_picker_callback = require('./file_picker_callback')
window.tinymce_settings = require('./tinymce_settings').default
