class Controller {
    constructor(site) {
        // Run Error Handlers
        Object.keys(site.errorHandling).forEach((handler) => {
            site.errorHandling[handler]();
        });

        // Run Global Scripts
        Object.keys(site.globals).forEach((lib) => {
            site.globals[lib]();
        });
        
        // Run Template Scripts
        Object.keys(site.templates).forEach((template) => {
            const currentTemplate = document.getElementsByClassName('page-template-' + template);
            if (currentTemplate.length) {
                site.templates[template].init();
            }
        });

        // Run Page Scripts
        Object.keys(site.pages).forEach((page) => {
            const currentPage = document.getElementsByClassName('page_' + page);
            if (currentPage.length) {
                site.pages[page].init();
            }
        });

        // Run Post Scripts
        Object.keys(site.posts).forEach((post) => {
            const currentPost = document.getElementsByClassName('single-' + post);
            if (currentPost.length) {
                site.posts[post].init();
            }
        });
    }
}

export default Controller;