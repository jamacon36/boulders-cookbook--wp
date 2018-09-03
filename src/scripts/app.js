import 'babel-polyfill'
import Pollyfills from './includes/polyfills.js'
import Utilities from './includes/utilities.js'

class App {
    constructor() {
        // Pollyfills
        new Pollyfills()
        
        // Grab The Utilities from utilities.js
        this.utilities = new Utilities()

        // Custom Error Events
        this.errorHandling = {
            // Any project specific errors
            ThemeException: (note) => {
                this.message = note
                this.name = 'ThemeException'
            }
        }

        this.materials = {

        }
        this.globals = {
            // All global functions
            /**
			 * Modal handler
			 * @function
             * @param {HTMLcollection} triggers All elements with the class modal-trigger
             * @param {element} body Body tag for the page
             * @param {HTMLcollection} modals All elements with the class modal
             * @param {element} trigger Looped variable that is the element from triggers selected based on index
             * @param {element} tgt Uses ID string from href attribute or modal-target data-attribute to find the target modal
			 */
            modals: () => {
                const triggers = document.getElementsByClassName('modal-trigger')
                const body = document.querySelector('body')
                const modals = document.getElementsByClassName('xcell-modal')

                for (let i = 0; i < triggers.length; i++) {
                    const trigger = triggers[i]
                    const tgt = trigger.hasAttribute('href') ? document.getElementById(trigger.getAttribute('href')) : document.getElementById(trigger.dataset.modalTarget)
                    
                    trigger.addEventListener('click', (event) => {
                        event.preventDefault()
                        tgt.classList.add('modal--active')
                        body.classList.add('modal-scroll-lock')

                        if (trigger.classList.contains('video')) {
                            document.getElementById('video-modal__frame').src = `https://www.youtube.com/embed/${trigger.dataset.videoId}?showinfo=0&controls=0&modestbranding=1&rel=0&enablejsapi=1`
                        }
                    })
                }

                for (let i = 0; i < modals.length; i++) {
                    const modal = modals[i]
                    modal.addEventListener('click', (event) => {
                        if (event.target.classList.contains('xcell-modal') || event.target.classList.contains('modal__close')) {
                            event.preventDefault()
                            modal.classList.remove('modal--active')
                            body.classList.remove('modal-scroll-lock')

                            if (modal.classList.contains('modal--video')) {
                                document.getElementById('video-modal__frame').contentWindow.postMessage(JSON.stringify({
                                    event: 'command',
                                    func: 'pauseVideo',
                                    args: [],
                                    id: 'video-modal__frame'
                                }), '*')
                            }
                        }
                    })
                }
            },

            /**
			 * Accordion sections
			 * @function
             * @param {HTMLcollection} toggles All elements with the class acc--trigger
             * @param {string} [target=false] The alternative accordion target ID of the acc trigger in the acc-target data-attribute if present
			 */
            accordion: () => {
                const toggles = document.getElementsByClassName('acc--trigger')

                for (let index = 0; index < toggles.length; index++) {
                    const toggle = toggles[index]
                    const target = toggle.dataset.accTarget ? toggle.dataset.accTarget : false

                    toggle.addEventListener('click', (event) => {
                        if (!target) {
                            this.utilities.toggleByParent(event, 'acc--element', 'acc--active')
                        } else {
                            document.getElementById(target).classList.toggle('acc--active')
                        }
                    }, false)
                }
            },

            /**
			 * Scroll To Links
			 * @function
             * @param {HTMLcollection} links All elements with the class scoll-to
             * @param {element} link Looped variable that is the element from links selected based on index
             * @param {element} tgt Uses ID string from href attribute or modal-target data-attribute to find the scroll to target
			 */
            scrollToLinks: () => {
                const links = document.getElementsByClassName('scroll-to')

                for (let i = 0; i < links.length; i++) {
                    const link = links[i]
                    const tgt = link.hasAttribute('href') ? document.getElementById(link.getAttribute('href')) : document.getElementById(link.dataset.scrollTarget)

                    link.addEventListener('click', (event) => {
                        event.preventDefault()
                        window.scroll({ top: tgt.offsetTop - 105, left: 0, behavior: 'smooth' })
                    })
                }
            },

            /**
			 * Tabbed sections
			 * @function
             * @param {HTMLcollection} triggers All elements with the class tab--trigger
             * @param {element} triCnr The parent container of the clicked trigger with the class tab-tiggers
             * @param {element} tabTgt The element with the ID contained in the trigger element's target data attribute
             * @param {element} cnrTgt The element with the ID contained in the triCnr element's tabs-relation attribute
			 */
            tabs: () => {
                const triggers = document.getElementsByClassName('tab--trigger')

                for (let index = 0; index < triggers.length; index++) {
                    const trigger = triggers[index]
                    trigger.addEventListener('click', (event) => {
                        const clicked = event.target
                        const triCnr = this.utilities.findParentByClass(clicked, 'tab-triggers')
                        const tabTgt = document.getElementById(clicked.dataset.target)
                        const cnrTgt = document.getElementById(triCnr.dataset.tabsRelation)
                        const activeTriggers = triCnr.getElementsByClassName('tab--active')
                        const activeNeighbors = cnrTgt.getElementsByClassName('tab--active')

                        for (let id = 0; id < activeNeighbors.length; id++) {
                            const activeNeighbor = activeNeighbors[id]
                            activeNeighbor.classList.remove('tab--active')
                        }

                        for (let id = 0; id < activeTriggers.length; id++) {
                            const activeTrigger = activeTriggers[id]
                            activeTrigger.classList.remove('tab--active')
                        }

                        tabTgt.classList.add('tab--active')
                        clicked.classList.add('tab--active')
                    }, false)
                }
            }
        }
        this.templates = {
            // All template level functions
            page: {
                // Base page template functions
                init: () => {

                }
            }
        }
        this.pages = {
            // All page level function repositories
        }
        this.posts = {
            single: {
                init: () => {

                }
            }
        }
    }
}

export default App