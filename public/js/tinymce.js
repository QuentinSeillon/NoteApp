console.log('Script tiny.mce bien chargé');

tinymce.init({
    selector: ".mytextarea",
    license_key: 'gpl',
    height: 300,
    plugins: [
      'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak', 'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'emoticons', 'template', 'codesample'
    ],
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | ' + 'bullliqt numlist outdent indent | link image |' + 'forecolor backcolor emoticons',
    menu: {
      favs: {title: 'menu', items: 'code visualid | searchreplace | emoticons'}
    },
    menubar: 'favs file edit view insert format tools table',
    content_style: 'body{font-family:Helvetica,Arial,sans-serif; font-size:16px}'
});