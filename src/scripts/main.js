import App from './app.js'
import Controller from './controller.js'

(() => {
    const site = new App();
    new Controller(site);
})(App, Controller);