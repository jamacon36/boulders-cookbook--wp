class Utilities {
    // Repository of useful tools
    constructor () {
        /**
         * Get Matching Query Vars
         * @param {string} variable the variable name to match on
         * @returns {string} the value of the matching var or false
         */
        this.getQueryVariable = (variable) => {
            const query = window.location.search.substring(1)
            const vars = query.split('&')
            for (let i = 0; i < vars.length; i++) {
                let pair = vars[i].split('=')
                if (pair[0] == variable) { return pair[1] }
            }
            return (false)
        }

        /**
         * Validate and Parse JSON
         * @param {string} string un-parsed json string
         * @returns {object} parsed JSON object or the original string
         */
        this.parseIfJSON = (string) => {
            try {
                return JSON.parse(string)
            } catch (e) {
                return string
            }
        }

        /**
         * HEX to RGBA Converter
         * @param {string} hex valid hex value for color
         * @param {number} [opacity=100] level of opacity to add out of 100
         * @returns {string} valid rgba string
         */
        this.hexToRGBA = (hex, opacity = 100) => {
            const prunedHex = hex.replace('#', '')
            const r = parseInt(prunedHex.substring(0, 2), 16)
            const g = parseInt(prunedHex.substring(2, 4), 16)
            const b = parseInt(prunedHex.substring(4, 6), 16)

            const result = 'rgba(' + r + ',' + g + ',' + b + ',' + opacity / 100 + ')'
            return result
        }

        /**
         * Check if Element is HTML Element
         * @param {object} el Potential HTML element to validate
         * @returns {bool} is an HTML element or not
         */
        this.isElement = (el) => {
            return (typeof HTMLElement === 'object' ? el instanceof HTMLElement : el && typeof el === 'object' && el.nodeType === 1 && typeof el.nodeName === 'string')
        }

        /**
         * Find Matching Parent Element By Class
         * @param {element} el HTML Element to step from to find parent
         * @param {string} cls Class name to search for on parents
         * @returns {element} Furthest element with a classlist that contains the cls string or bool if no match
         */
        this.findParentByClass = (el, cls) => {
            let parent = false
            if (this.isElement(el)) {
                while ((el = el.parentElement))
                    if (el.classList.contains(cls)) {
                        parent = el
                        break
                    }
            }
            return parent
        }

        /**
         * Find Matching Sibling Element By Class
         * @param {element} el HTML Element to step from to find sibling
         * @param {string} cls Class name to search for on siblings
         * @returns {element} Next element with a classlist that contains the cls string or bool if no match
         */
        this.findNextSiblingByClass = (el, cls) => {
            let sibling = false
            if (this.isElement(el)) {
                while ((el = el.nextElementSibling))
                    if (el.classList.contains(cls)) {
                        sibling = el
                        break
                    }
            }
            return sibling
        }

        /**
         * Find Matching Sibling Element By Class
         * @param {element} el HTML Element to step from to find sibling
         * @param {string} cls Class name to search for on siblings
         * @returns {element} Next previous element with a classlist that contains the cls string or bool if no match
         */
        this.findPrevSiblingByClass = (el, cls) => {
            let sibling = false
            if (this.isElement(el)) {
                while ((el = el.previousElementSibling))
                    if (el.classList.contains(cls)) {
                        sibling = el
                        break
                    }
            }
            return sibling
        }

        /**
         * Toggle classlist member by on a parent with matching class
         * @param {object} event JS event or HTML Element to base parent search from
         * @param {string} parentClass Class name to search for on parent
         * @param {string} toggleClass Class name to toggle on parent
         */
        this.toggleByParent = (event, parentClass, toggleClass) => {
            const clicked = event.target ? event.target : event
            const parent = this.utilities.findParentByClass(clicked, parentClass)
            parent.classList.toggle(toggleClass)
        }

        // Return cookie by name
        this.getCookie = (name) => {
            let crumbs = document.cookie.split(';')
            let cookie = false
            if (crumbs.length) {
                for (let v = 0; v < crumbs.length; v++) {
                    if (crumbs[v].includes(name)) {
                        cookie = crumbs[v].split('=')[1]
                        cookie = decodeURIComponent(cookie)
                        cookie = this.parseIfJSON(cookie)
                    }
                }
            }
            return cookie
        }

        // Update or create a cookie by name with given data.
        this.updateCookie = (name, update, shelfLife = 30 * 24 * 60 * 60 * 1000) => {
            let cookie = this.getCookie(name)
            let expires = new Date()
            const location = window.location.hostname.substr('100danish') > -1 ? ';.100danish.com;path=/' : ';.bxcell.com;path=/'

            if (cookie) {
                update = Object.assign(cookie, update)
            }

            expires.setTime(expires.getTime() + shelfLife)
            expires = 'expires=' + expires.toUTCString()
            update = JSON.stringify(update)

            document.cookie = name + '=' + update + ';' + expires + location
        }

        /**
         * AJAX Request
         * @param {string} url URL to make the call to
         * @param {string} [method='GET'] GET or POST method for call
         * @param {object} [data=false] Optional data to send with call
         * @param {array} [contentHeaders=[]] Optional set of header objects to include with call. Header objects must include a header name and value
         * @param {onSuccess} [onSuccess=false] Function to fire on a successful call
         * @param {onError} [onError=false] Function to fire on a errored call
         * @param {onOpen} [onOpen=false] Function to fire on after openning the call
         * @param {onProgress} [onProgress=false] Function to fire after request has been made call
         * @param {onAlmost} [onAlmost=false] Function to fire after data has begun downloading but is incomplete
         */
        this.ajax = (url, method = 'GET', data = false, contentHeaders = [], onSuccess = false, onError = false, onOpen = false, onProgress = false, onAlmost = false) => {
            const engine = new XMLHttpRequest()
            engine.onreadystatechange = engineState

            function engineState() {
                switch (engine.readyState) {
                case 4:
                    if (engine.status == 200 && onSuccess) {
                        onSuccess(this.response)
                    } else if (engine.status != 200 && onError) {
                        onError(engine)
                    }
                    break

                case 3:
                    if (onAlmost) {
                        onAlmost()
                    }
                    break

                case 2:
                    if (onProgress) {
                        onProgress()
                    }
                    break

                case 1:
                    // engine.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
                    if (data) {
                        engine.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
                    }
                    if (contentHeaders.length) {
                        for (let i = 0; i < contentHeaders.length; i++) {
                            const header = contentHeaders[i]
                            engine.setRequestHeader(header.name, header.value)
                        }
                    }
                    if (onOpen) {
                        onOpen()
                    }
                    break
                }
            }

            engine.open(method, url)

            if (data) {
                let dataString = ''
                if (typeof data == 'object') {
                    Object.keys(data).forEach((key, index) => {
                        const val = typeof data[key] == 'object' ? JSON.stringify(data[key]) : data[key]
                        dataString += index === 0 ? `${key}=${val}` : `&${key}=${val}`
                    })
                } else {
                    dataString = data
                }
                engine.send(dataString)
            } else {
                engine.send()
            }
        }

        // Clear Form Validation Errors
        this.clearErrors = (form) => {
            const inputs = form.getElementsByClassName('invalid')
            for (let i = 0; i < inputs.length; i++) {
                const input = inputs[i]
                form.querySelector(`label[for="${input.name}"]`).innerText = ""
                input.classList.remove('invalid')
            }
        }
    }
}

export default Utilities