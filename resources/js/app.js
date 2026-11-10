// Toast UI Editor cukup besar (~500 KB), jadi hanya dimuat saat halaman
// benar-benar membutuhkannya (komponen <x-markdown-editor>).
window.loadToastEditor = async () => {
    const [{ default: Editor }] = await Promise.all([
        import('@toast-ui/editor'),
        import('@toast-ui/editor/dist/toastui-editor.css'),
    ]);

    return Editor;
};
