import TomSelect from 'tom-select'
import mustache from 'mustache'

export default class PonchoAutocomplete extends HTMLSelectElement {

    constructor() {
        super()

        this.tomSelect = null
        this.hasLoadedChoicesPreviously = false

        this.config = {
            loadUrl : this.dataset.loadUrl,
            minCharLength: parseInt(this.dataset.minChar || 3),
            inputTemplate: this.dataset.inputTemplate,
            optionTemplate: this.dataset.optionTemplate,
            tomSelectSettings: this.dataset.tomSelectSettings ? JSON.parse(this.dataset.tomSelectSettings) : {}
        }

        this.defaultOptions = null
    }

    connectedCallback() {
        const settings = this.config.tomSelectSettings
        settings.closeAfterSelect = true
        settings.maxOptions = settings.maxOptions || null

        // plugins
        settings.plugins = {
            remove_button: {}
        }
        
        if (this.config.loadUrl) {
            settings.plugins.virtual_scroll = {}
        }

        settings.render = {
            loading_more: () => `<div class="loading-more-results text-muted">${poncho.translator.trans('autocomplete.loading_more')}</div>`,
            no_more_results: () => null,
            no_results: () => `<div class="no-results">${poncho.translator.trans('autocomplete.no_results')}</div>`,
            option_create: (data, escape) => `<div class="create">${poncho.translator.trans('action.add')} <strong>${escape(data.input)}</strong>&hellip;</div>`
        };

        //settings.plugins['input_autogrow'] = {}

        if (this.config.inputTemplate) {
            settings.render.item = (data) => {
                return '<div>' + mustache.render(this.config.inputTemplate, data) + '</div>'
            }
        }

        if (this.config.optionTemplate) {
            settings.render.option = (data) => {
                return '<div>' + mustache.render(this.config.optionTemplate, data) + '</div>'
            }
        }

        // remote data
        if (this.config.loadUrl) {
            settings.firstUrl = (query) => {
                const separator = this.config.loadUrl.includes('?') ? '&' : '?';
                return `${this.config.loadUrl}${separator}query=${encodeURIComponent(query)}`;
            };
            // VERY IMPORTANT: use 'function (query, callback) { ... }' instead of the
            // '(query, callback) => { ... }' syntax because, otherwise,
            // the 'this.XXX' calls inside this method fail
            settings.load = async (query, callback) =>  {
                const url = this.tomSelect.getUrl(query)

                // https://github.com/orchidjs/tom-select/issues/556
                // hack disable scrollToOption to avoid scroll top when setNextUrl is called
                const _scrollToOption = this.tomSelect.scrollToOption
                this.tomSelect.scrollToOption = () => {}

                try {
                    const response = await fetch(url)
                    const json = await response.json()

                    if (json.next_url) {
                        this.tomSelect.setNextUrl(query, json.next_url);
                    }

                    callback(json.results || [], [])

                } catch (e) {
                    callback([], []);
                    throw e

                } finally {
                    this.tomSelect.scrollToOption.scrollToOption = _scrollToOption
                }
            }
            // avoid extra filtering after results are returned
            settings.score = (search) => (item) => 1
            settings.shouldLoad = (query) => {
                // if min length is specified, always use it
                if (null !== this.config.minCharLength) {
                    return query.length >= this.config.minCharLength;
                }

                // otherwise, default to 3, but always load after the first request
                // this gives nice behavior when the user deletes characters and
                // goes below the minimum length, it will still load fresh choices

                if (this.hasLoadedChoicesPreviously) {
                    return true;
                }

                // mark that the choices have loaded (but avoid initial load)
                if (query.length > 0) {
                    this.hasLoadedChoicesPreviously = true;
                }

                return query.length >= 3;
            }
        }

        this.tomSelect = new TomSelect(this, settings)

        // keep default options in memory
        this.defaultOptions = {...this.tomSelect.options}

        // force tomSelect to reset when form is reset
        const $form = this.closest('form')
        if ($form) {
            $form.addEventListener('reset', evt => {
                // hack : force sync of tomSelect after select has changed
                setTimeout(() => this.tomSelect.sync(), 10)
            })
        }
    }

    // Api

    getOptions() {
        return this.tomSelect.options
    }

    hideOptions(hide) {
        // update options
        for (const k in this.defaultOptions) {

            const option = this.defaultOptions[k] ?? null
            const hidden = typeof hide === 'boolean' ? hide : hide(option)

            if (hidden) {
                this.tomSelect.removeOption(option.value)
            } else {
                this.tomSelect.addOption(option)
            }
        }
    }

    selectAll(silent = false) {
        const values = []
        for (const k in this.tomSelect.options) {
            const opt = this.tomSelect.options[k]

            if (!opt.disabled) {
                values.push(opt.value)
            }
        }
        this.setValue(values, silent)
    }

    unselectAll(silent = false) {
        this.tomSelect.clear(silent)
        this.tomSelect.refreshOptions(false)
    }

    setValue(value, silent = false) {
        this.tomSelect.setValue(value, silent)
        this.tomSelect.refreshOptions(false)
    }

    getValue() {
        return this.tomSelect.getValue()
    }

    getSelectedOptions() {
        const options = []
        for (const k in this.tomSelect.options) {
            const opt = this.tomSelect.options[k]
            if (opt.$option.selected) {
                options.push(opt)
            }
        }
        return options
    }
}